<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\StockTransfer;
use App\Models\StockSummary;
use App\Models\ProcessingStep;
use App\Models\Supplier;

class WarehouseController extends Controller
{
    protected StockTransfer $transferModel;
    protected StockSummary $stockSummaryModel;
    protected ProcessingStep $processingStepModel;
    protected Supplier $supplierModel;

    public function __construct()
    {
        parent::__construct();
        $this->transferModel = new StockTransfer();
        $this->stockSummaryModel = new StockSummary();
        $this->processingStepModel = new ProcessingStep();
        $this->supplierModel = new Supplier();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Display warehouse incoming transfers page
     */
    public function incoming($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-warehouse-incoming')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get all warehouses
        $warehouses = Database::fetchAll(
            "SELECT l.id, l.name, lt.name as type_name 
             FROM locations l 
             JOIN location_types lt ON l.location_type_id = lt.id 
             WHERE lt.name = 'Warehouse' AND l.status = 'active' 
             ORDER BY l.name"
        );

        // Get selected warehouse
        $selectedWarehouseId = $_GET['warehouse_id'] ?? ($warehouses[0]['id'] ?? null);
        
        $pendingTransfers = [];
        $allTransfers = [];
        $warehouseStock = [];

        if ($selectedWarehouseId) {
            $pendingTransfers = $this->transferModel->getPendingIncoming($selectedWarehouseId);
            $allTransfers = $this->transferModel->getIncomingByLocation($selectedWarehouseId);
            $warehouseStock = $this->stockSummaryModel->getByLocationWithSuppliers($selectedWarehouseId);
        }

        return View::render('warehouse/incoming', [
            'title' => 'Warehouse - Incoming Transfers',
            'user' => $user,
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $selectedWarehouseId,
            'pendingTransfers' => $pendingTransfers,
            'allTransfers' => $allTransfers,
            'warehouseStock' => $warehouseStock,
            'scripts' => ['js/pages/warehouse-incoming.js']
        ], 'main');
    }

    /**
     * Display warehouse stock page
     */
    public function stock($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-warehouse-stock')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get all warehouses
        $warehouses = Database::fetchAll(
            "SELECT l.id, l.name, lt.name as type_name 
             FROM locations l 
             JOIN location_types lt ON l.location_type_id = lt.id 
             WHERE lt.name = 'Warehouse' AND l.status = 'active' 
             ORDER BY l.name"
        );

        // Get selected warehouse
        $selectedWarehouseId = $_GET['warehouse_id'] ?? ($warehouses[0]['id'] ?? null);
        
        $warehouseStock = [];
        $warehouseInfo = null;

        if ($selectedWarehouseId) {
            $warehouseStock = $this->stockSummaryModel->getByLocationWithSuppliers($selectedWarehouseId);
            $warehouseInfo = Database::fetch(
                "SELECT l.*, lt.name as type_name 
                 FROM locations l 
                 JOIN location_types lt ON l.location_type_id = lt.id 
                 WHERE l.id = :id",
                ['id' => $selectedWarehouseId]
            );
        }

        return View::render('warehouse/stock', [
            'title' => 'Warehouse Stock',
            'user' => $user,
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $selectedWarehouseId,
            'warehouseStock' => $warehouseStock,
            'warehouseInfo' => $warehouseInfo,
            'scripts' => ['js/pages/warehouse-stock.js']
        ], 'main');
    }

    /**
     * Handle warehouse actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'receive':
                if (!$this->hasPermission('receive-warehouse-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to receive transfers';
                    return $response->redirect(APP_URL . '/warehouse/incoming');
                }
                return $this->receiveTransfer($request, $response);

            case 'reject':
                if (!$this->hasPermission('receive-warehouse-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to reject transfers';
                    return $response->redirect(APP_URL . '/warehouse/incoming');
                }
                return $this->rejectTransfer($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/warehouse/incoming');
        }
    }

    /**
     * Receive transfer at warehouse
     */
    private function receiveTransfer($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $id = $_POST['id'] ?? null;
        $warehouseId = $_POST['warehouse_id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Transfer ID is required';
            return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
        }

        $transfer = $this->transferModel->getWithDetails($id);
        if (!$transfer) {
            $_SESSION['flash_error'] = 'Transfer not found';
            return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
        }

        if ($transfer['status'] !== 'in_transit') {
            $_SESSION['flash_error'] = 'Transfer is not in transit';
            return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
        }

        // Get the first processing step (order 1) for incoming warehouse stock
        $firstStep = $this->processingStepModel->getFirstStep();
        $processingStepId = $firstStep ? $firstStep['id'] : null;

        // Get source location
        $sourceLocationId = $transfer['from_location_id'];

        // For warehouse stock, the supplier is always the SOURCE STATION, not the original farmer
        // Find or create supplier for the source station
        $supplierId = null;
        if ($sourceLocationId) {
            // Always use/create station as supplier for warehouse stock
            $supplierId = $this->supplierModel->findOrCreateByLocation($sourceLocationId);
        }

        // Add stock to warehouse with smart conversion, grouped by station supplier
        $this->stockSummaryModel->addStockWithConversion(
            $transfer['to_location_id'],
            $transfer['category_type_unit_id'],
            $transfer['quantity'],
            $transfer['unit_price'],
            $supplierId ?: null,   // Use station as supplier
            $processingStepId,     // Assign first processing step
            $sourceLocationId      // Track source location
        );

        // Mark transfer as received
        $success = $this->transferModel->receiveTransfer($id, $user['id']);

        if ($success) {
            $stepName = $firstStep ? $firstStep['name'] : 'None';
            $_SESSION['flash_success'] = "Transfer received successfully. Stock added to warehouse at step: {$stepName}";
        } else {
            $_SESSION['flash_error'] = 'Failed to receive transfer';
        }

        return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
    }

    /**
     * Reject transfer (return to sender)
     */
    private function rejectTransfer($request, $response)
    {
        $id = $_POST['id'] ?? null;
        $warehouseId = $_POST['warehouse_id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Transfer ID is required';
            return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
        }

        $transfer = $this->transferModel->getWithDetails($id);
        if (!$transfer) {
            $_SESSION['flash_error'] = 'Transfer not found';
            return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
        }

        if ($transfer['status'] !== 'in_transit') {
            $_SESSION['flash_error'] = 'Transfer is not in transit';
            return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
        }

        // Return stock to source location with smart conversion
        $this->stockSummaryModel->addStockWithConversion(
            $transfer['from_location_id'],
            $transfer['category_type_unit_id'],
            $transfer['quantity'],
            $transfer['unit_price']
        );

        // Cancel the transfer
        $success = $this->transferModel->cancelTransfer($id);

        if ($success) {
            $_SESSION['flash_success'] = 'Transfer rejected. Stock returned to source.';
        } else {
            $_SESSION['flash_error'] = 'Failed to reject transfer';
        }

        return $response->redirect(APP_URL . '/warehouse/incoming?warehouse_id=' . $warehouseId);
    }
}