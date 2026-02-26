<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\Sale;
use App\Models\Client;

class WarehouseSaleController extends Controller
{
    private Sale $saleModel;
    private Client $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->saleModel = new Sale();
        $this->clientModel = new Client();
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
     * Display warehouse sales page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$this->hasPermission('view-stock-transfers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get warehouse location type ID
        $warehouseType = Database::fetch(
            "SELECT id FROM location_types WHERE name = 'Warehouse' AND status = 'active' LIMIT 1"
        );
        $warehouseTypeId = $warehouseType['id'] ?? 0;

        // Get warehouses
        $warehouses = Database::fetchAll(
            "SELECT l.id, l.name 
             FROM locations l 
             WHERE l.location_type_id = :type_id AND l.status = 'active' 
             ORDER BY l.name",
            ['type_id' => $warehouseTypeId]
        );

        // Get active clients
        $clients = $this->clientModel->getActive();

        // Get categories with types
        $categories = Database::fetchAll(
            "SELECT pc.id, pc.name FROM product_categories pc 
             WHERE pc.status = 'active' ORDER BY pc.name"
        );

        // Get processing steps
        $processingSteps = Database::fetchAll(
            "SELECT id, name FROM processing_steps WHERE status = 'active' ORDER BY step_order"
        );

        // Get accounts for payment
        $accounts = Database::fetchAll(
            "SELECT id, account_name FROM accounts WHERE status = 'active' ORDER BY account_name"
        );

        return View::render('warehouse/sales', [
            'title' => 'Warehouse Sales',
            'user' => $user,
            'warehouses' => $warehouses,
            'clients' => $clients,
            'categories' => $categories,
            'processingSteps' => $processingSteps,
            'accounts' => $accounts
        ], 'main');
    }

    /**
     * Display sales history page
     */
    public function history($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$this->hasPermission('view-stock-transfers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get warehouse location type ID
        $warehouseType = Database::fetch(
            "SELECT id FROM location_types WHERE name = 'Warehouse' AND status = 'active' LIMIT 1"
        );
        $warehouseTypeId = $warehouseType['id'] ?? 0;

        // Get warehouses
        $warehouses = Database::fetchAll(
            "SELECT l.id, l.name 
             FROM locations l 
             WHERE l.location_type_id = :type_id AND l.status = 'active' 
             ORDER BY l.name",
            ['type_id' => $warehouseTypeId]
        );

        // Get sales
        $filters = [];
        if (!empty($_GET['location_id'])) {
            $filters['location_id'] = $_GET['location_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        $sales = $this->saleModel->getAllSales($filters);

        return View::render('warehouse/sales_history', [
            'title' => 'Sales History',
            'user' => $user,
            'warehouses' => $warehouses,
            'sales' => $sales
        ], 'main');
    }

    /**
     * Handle sale actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                return $this->createSale($request, $response);
            case 'confirm':
                return $this->confirmSale($request, $response);
            case 'cancel':
                return $this->cancelSale($request, $response);
            case 'get_sale':
                return $this->getSale($request, $response);
            case 'get_stock':
                return $this->getAvailableStock($request, $response);
            case 'get_types':
                return $this->getCategoryTypes($request, $response);
            case 'get_units':
                return $this->getCompatibleUnits($request, $response);
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/warehouse/sales');
        }
    }

    /**
     * Create new sale
     */
    private function createSale($request, $response)
    {
        if (!$this->hasPermission('create-stock-transfers')) {
            $_SESSION['flash_error'] = 'Permission denied';
            return $response->redirect(APP_URL . '/warehouse/sales');
        }

        $user = $_SESSION['user'];
        
        $locationId = intval($_POST['location_id'] ?? 0);
        $clientId = intval($_POST['client_id'] ?? 0);
        $saleDate = $_POST['sale_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];

        if (!$locationId || !$clientId || empty($items)) {
            $_SESSION['flash_error'] = 'Location, client and at least one item are required';
            return $response->redirect(APP_URL . '/warehouse/sales');
        }

        try {
            // Generate sale number
            $saleNumber = $this->saleModel->generateSaleNumber();
            
            // Calculate totals - use sell_quantity (user entered) * unit_price for invoice
            $subtotal = 0;
            foreach ($items as $item) {
                $sellQty = floatval($item['sell_quantity'] ?? $item['quantity']);
                $subtotal += $sellQty * floatval($item['unit_price']);
            }

            // Create sale
            $saleId = $this->saleModel->createSale([
                'sale_number' => $saleNumber,
                'location_id' => $locationId,
                'client_id' => $clientId,
                'sale_date' => $saleDate,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $notes,
                'status' => 'draft',
                'created_by' => $user['id']
            ]);

            // Add items - quantity is in stock unit for stock deduction
            foreach ($items as $item) {
                $quantity = floatval($item['quantity']); // In stock unit
                $sellQty = floatval($item['sell_quantity'] ?? $quantity);
                $sellUnitId = intval($item['sell_unit_id'] ?? 0);
                $unitPrice = floatval($item['unit_price']);
                
                $this->saleModel->addSaleItem($saleId, [
                    'product_category_id' => $item['category_id'],
                    'category_type_unit_id' => $item['category_type_unit_id'],
                    'processing_step_id' => $item['processing_step_id'] ?? null,
                    'supplier_id' => $item['supplier_id'] ?? null,
                    'quantity' => $quantity, // Stock unit quantity for deduction
                    'sell_quantity' => $sellQty, // User entered quantity
                    'sell_unit_id' => $sellUnitId ?: null, // User selected unit
                    'unit_price' => $unitPrice,
                    'total_price' => $sellQty * $unitPrice, // Invoice total based on sell quantity
                    'notes' => $item['notes'] ?? null
                ]);
            }

            $_SESSION['flash_success'] = "Sale {$saleNumber} created successfully";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create sale: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/warehouse/sales');
    }

    /**
     * Confirm sale (deducts stock)
     */
    private function confirmSale($request, $response)
    {
        $redirect = $_POST['redirect'] ?? '';
        $redirectUrl = $redirect === 'history' ? '/warehouse/sales/history' : '/warehouse/sales';

        if (!$this->hasPermission('create-stock-transfers')) {
            $_SESSION['flash_error'] = 'Permission denied';
            return $response->redirect(APP_URL . $redirectUrl);
        }

        $saleId = intval($_POST['id'] ?? 0);
        $user = $_SESSION['user'];

        if (!$saleId) {
            $_SESSION['flash_error'] = 'Invalid sale ID';
            return $response->redirect(APP_URL . $redirectUrl);
        }

        if ($this->saleModel->confirmSale($saleId, $user['id'])) {
            $_SESSION['flash_success'] = 'Sale confirmed successfully. Stock has been deducted.';
        } else {
            // Check if sale exists and is draft
            $sale = $this->saleModel->getSaleById($saleId);
            if (!$sale) {
                $_SESSION['flash_error'] = 'Sale not found';
            } elseif ($sale['status'] !== 'draft') {
                $_SESSION['flash_error'] = 'Sale is not in draft status (current: ' . $sale['status'] . ')';
            } else {
                $_SESSION['flash_error'] = 'Failed to confirm sale. Check error log for details.';
            }
        }

        return $response->redirect(APP_URL . $redirectUrl);
    }

    /**
     * Cancel sale
     */
    private function cancelSale($request, $response)
    {
        $redirect = $_POST['redirect'] ?? '';
        $redirectUrl = $redirect === 'history' ? '/warehouse/sales/history' : '/warehouse/sales';

        if (!$this->hasPermission('create-stock-transfers')) {
            $_SESSION['flash_error'] = 'Permission denied';
            return $response->redirect(APP_URL . $redirectUrl);
        }

        $saleId = intval($_POST['id'] ?? 0);

        if (!$saleId) {
            $_SESSION['flash_error'] = 'Invalid sale ID';
            return $response->redirect(APP_URL . $redirectUrl);
        }

        if ($this->saleModel->cancelSale($saleId)) {
            $_SESSION['flash_success'] = 'Sale cancelled successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel sale.';
        }

        return $response->redirect(APP_URL . $redirectUrl);
    }

    /**
     * Get sale details (AJAX)
     */
    private function getSale($request, $response)
    {
        header('Content-Type: application/json');
        
        $saleId = intval($_POST['id'] ?? 0);
        
        if (!$saleId) {
            echo json_encode(['success' => false, 'message' => 'Invalid sale ID']);
            exit;
        }

        $sale = $this->saleModel->getSaleById($saleId);
        $items = $this->saleModel->getSaleItems($saleId);

        if ($sale) {
            echo json_encode(['success' => true, 'sale' => $sale, 'items' => $items]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sale not found']);
        }
        exit;
    }

    /**
     * Get available stock for a location (AJAX)
     */
    private function getAvailableStock($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationId = intval($_POST['location_id'] ?? 0);
        
        if (!$locationId) {
            echo json_encode(['success' => false, 'message' => 'Location required']);
            exit;
        }

        $stock = $this->saleModel->getAvailableStock($locationId);
        echo json_encode(['success' => true, 'stock' => $stock]);
        exit;
    }

    /**
     * Get category types with units (AJAX)
     */
    private function getCategoryTypes($request, $response)
    {
        header('Content-Type: application/json');
        
        $categoryId = intval($_POST['category_id'] ?? 0);
        
        if (!$categoryId) {
            echo json_encode(['success' => false, 'message' => 'Category required']);
            exit;
        }

        $types = Database::fetchAll(
            "SELECT ct.id as type_id, ct.name as type_name, 
                    ctu.id as category_type_unit_id,
                    mu.id as unit_id, mu.name as unit_name, mu.symbol as unit_symbol
             FROM category_types ct
             JOIN category_type_units ctu ON ct.id = ctu.category_type_id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ct.category_id = :category_id AND ct.status = 'active'
             ORDER BY ct.name, mu.name",
            ['category_id' => $categoryId]
        );

        echo json_encode(['success' => true, 'types' => $types]);
        exit;
    }

    /**
     * Get units assigned to a category type (AJAX)
     */
    private function getCompatibleUnits($request, $response)
    {
        header('Content-Type: application/json');
        
        $categoryTypeId = intval($_POST['category_type_id'] ?? 0);
        
        if (!$categoryTypeId) {
            echo json_encode(['success' => false, 'message' => 'Category type ID required']);
            exit;
        }

        // Get all units assigned to this category type via category_type_units
        $units = Database::fetchAll(
            "SELECT mu.id, mu.name, mu.symbol, mu.conversion_factor, mu.rank, ctu.is_default
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :category_type_id
             AND mu.status = 'active'
             ORDER BY ctu.is_default DESC, mu.conversion_factor ASC",
            ['category_type_id' => $categoryTypeId]
        );

        echo json_encode([
            'success' => true, 
            'units' => $units,
            'category_type_id' => $categoryTypeId
        ]);
        exit;
    }
}
