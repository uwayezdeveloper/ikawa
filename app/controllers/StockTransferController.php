<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\StockTransfer;
use App\Models\StockSummary;

class StockTransferController extends Controller
{
    protected StockTransfer $transferModel;
    protected StockSummary $stockSummaryModel;

    public function __construct()
    {
        parent::__construct();
        $this->transferModel = new StockTransfer();
        $this->stockSummaryModel = new StockSummary();
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
     * Display transfer to warehouse page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-stock-transfers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $transfers = $this->transferModel->getAll();
        $locationTypes = Database::fetchAll("SELECT id, name FROM location_types WHERE status = 'active' ORDER BY name");
        $warehouses = Database::fetchAll("SELECT l.id, l.name, lt.name as type_name 
                                          FROM locations l 
                                          JOIN location_types lt ON l.location_type_id = lt.id 
                                          WHERE lt.name = 'Warehouse' AND l.status = 'active' 
                                          ORDER BY l.name");

        return View::render('stock/transfer-stock', [
            'title' => 'Transfer to Warehouse',
            'user' => $user,
            'transfers' => $transfers,
            'locationTypes' => $locationTypes,
            'warehouses' => $warehouses,
            'scripts' => ['js/pages/stock-transfer.js']
        ], 'main');
    }

    /**
     * Handle transfer actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create transfers';
                    return $response->redirect(APP_URL . '/stock/transfers');
                }
                return $this->createTransfer($request, $response);

            case 'approve':
                if (!$this->hasPermission('approve-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to approve transfers';
                    return $response->redirect(APP_URL . '/stock/transfers');
                }
                return $this->approveTransfer($request, $response);

            case 'cancel':
                if (!$this->hasPermission('approve-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to cancel transfers';
                    return $response->redirect(APP_URL . '/stock/transfers');
                }
                return $this->cancelTransfer($request, $response);

            // AJAX Cascade Actions
            case 'get_locations':
                return $this->getLocations($request, $response);
            
            case 'get_categories':
                return $this->getCategories($request, $response);
            
            case 'get_category_types':
                return $this->getCategoryTypes($request, $response);
            
            case 'get_type_units':
                return $this->getTypeUnits($request, $response);

            case 'get_stock_available':
                return $this->getStockAvailable($request, $response);

            case 'get_suppliers_for_type':
                return $this->getSuppliersForType($request, $response);

            case 'get_stock_with_conversion':
                return $this->getStockWithConversion($request, $response);

            case 'get_suppliers_at_location':
                return $this->getSuppliersAtLocation($request, $response);

            case 'get_supplier_product_types':
                return $this->getSupplierProductTypes($request, $response);

            case 'get_type_units_with_stock':
                return $this->getTypeUnitsWithStock($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/stock/transfers');
        }
    }

    /**
     * Create new transfer
     */
    private function createTransfer($request, $response)
    {
        $user = $_SESSION['user'] ?? null;

        $fromLocationTypeId = $_POST['from_location_type_id'] ?? null;
        $fromLocationId = $_POST['from_location_id'] ?? null;
        $toLocationId = $_POST['to_location_id'] ?? null;
        $productCategoryId = $_POST['product_category_id'] ?? null;
        $categoryTypeId = $_POST['category_type_id'] ?? null;
        $measurementUnitId = $_POST['measurement_unit_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;
        $categoryTypeUnitId = $_POST['category_type_unit_id'] ?? null;
        $quantity = floatval($_POST['quantity'] ?? 0);
        $unitPrice = floatval($_POST['unit_price'] ?? 0);
        $transferDate = $_POST['transfer_date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';

        // Validation
        if (!$fromLocationId || !$toLocationId || !$supplierId || !$categoryTypeId || !$measurementUnitId || $quantity <= 0) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        if ($fromLocationId == $toLocationId) {
            $_SESSION['flash_error'] = 'Source and destination cannot be the same';
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        // Check stock availability with unit conversion
        $stocks = $this->stockSummaryModel->getTotalStockForType(
            intval($fromLocationId), 
            intval($categoryTypeId), 
            $supplierId ? intval($supplierId) : null
        );

        // Get the conversion factor for requested unit
        $unit = Database::fetch(
            "SELECT id, conversion_factor FROM measurement_units WHERE id = :id",
            ['id' => $measurementUnitId]
        );

        // Calculate total available in requested unit
        $totalInBase = 0;
        foreach ($stocks as $stock) {
            $totalInBase += $stock['total_quantity'] * $stock['conversion_factor'];
        }
        $availableInUnit = $totalInBase / $unit['conversion_factor'];

        if ($availableInUnit < $quantity) {
            $_SESSION['flash_error'] = 'Insufficient stock. Available: ' . number_format($availableInUnit, 2);
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        // Get destination warehouse location type
        $toLocation = Database::fetch("SELECT location_type_id FROM locations WHERE id = :id", ['id' => $toLocationId]);

        // Get or create category_type_unit_id for the selected unit
        if (!$categoryTypeUnitId) {
            $ctu = Database::fetch(
                "SELECT id FROM category_type_units WHERE category_type_id = :type_id AND measurement_unit_id = :unit_id",
                ['type_id' => $categoryTypeId, 'unit_id' => $measurementUnitId]
            );
            $categoryTypeUnitId = $ctu ? $ctu['id'] : null;
        }

        $data = [
            'from_location_type_id' => $fromLocationTypeId,
            'from_location_id' => $fromLocationId,
            'to_location_type_id' => $toLocation['location_type_id'],
            'to_location_id' => $toLocationId,
            'product_category_id' => $productCategoryId,
            'category_type_id' => $categoryTypeId,
            'measurement_unit_id' => $measurementUnitId,
            'supplier_id' => $supplierId,
            'category_type_unit_id' => $categoryTypeUnitId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'transfer_date' => $transferDate,
            'notes' => $notes,
            'status' => 'pending',
            'created_by' => $user['id']
        ];

        $id = $this->transferModel->createTransfer($data);

        if ($id) {
            $_SESSION['flash_success'] = 'Transfer created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create transfer';
        }

        return $response->redirect(APP_URL . '/stock/transfers');
    }

    /**
     * Approve transfer (Send to transit)
     */
    private function approveTransfer($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Transfer ID is required';
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        $transfer = $this->transferModel->find($id);
        if (!$transfer) {
            $_SESSION['flash_error'] = 'Transfer not found';
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        // Use smart deduction with unit conversion
        if ($transfer['category_type_id'] && $transfer['measurement_unit_id']) {
            // New flow with unit conversion
            $deductionResult = $this->stockSummaryModel->deductStockWithConversion(
                intval($transfer['from_location_id']),
                intval($transfer['category_type_id']),
                intval($transfer['measurement_unit_id']),
                floatval($transfer['quantity']),
                $transfer['supplier_id'] ? intval($transfer['supplier_id']) : null
            );

            if ($deductionResult === false) {
                $_SESSION['flash_error'] = 'Insufficient stock for this transfer';
                return $response->redirect(APP_URL . '/stock/transfers');
            }
        } else {
            // Legacy flow for old records without new fields
            $availableStock = $this->stockSummaryModel->getAvailableStock(
                $transfer['from_location_id'], 
                $transfer['category_type_unit_id']
            );

            if ($availableStock < $transfer['quantity']) {
                $_SESSION['flash_error'] = 'Insufficient stock. Available: ' . number_format($availableStock, 2);
                return $response->redirect(APP_URL . '/stock/transfers');
            }

            $this->stockSummaryModel->deductStock(
                $transfer['from_location_id'],
                $transfer['category_type_unit_id'],
                $transfer['quantity']
            );
        }

        // Approve transfer
        $success = $this->transferModel->approveTransfer($id, $user['id']);

        if ($success) {
            $_SESSION['flash_success'] = 'Transfer approved and sent to transit';
        } else {
            $_SESSION['flash_error'] = 'Failed to approve transfer';
        }

        return $response->redirect(APP_URL . '/stock/transfers');
    }

    /**
     * Cancel transfer
     */
    private function cancelTransfer($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Transfer ID is required';
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        $transfer = $this->transferModel->find($id);
        if (!$transfer) {
            $_SESSION['flash_error'] = 'Transfer not found';
            return $response->redirect(APP_URL . '/stock/transfers');
        }

        // If transfer was in transit, restore stock to source with smart conversion
        if ($transfer['status'] === 'in_transit') {
            $this->stockSummaryModel->addStockWithConversion(
                $transfer['from_location_id'],
                $transfer['category_type_unit_id'],
                $transfer['quantity'],
                $transfer['supplier_id'] ? intval($transfer['supplier_id']) : null
            );
        }

        $success = $this->transferModel->cancelTransfer($id);

        if ($success) {
            $_SESSION['flash_success'] = 'Transfer cancelled successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel transfer';
        }

        return $response->redirect(APP_URL . '/stock/transfers');
    }

    /**
     * Get locations by type (AJAX)
     */
    private function getLocations($request, $response)
    {
        $locationTypeId = $_POST['location_type_id'] ?? null;
        
        if (!$locationTypeId) {
            return $response->json(['success' => false, 'message' => 'Location type is required']);
        }

        $locations = Database::fetchAll(
            "SELECT id, name FROM locations WHERE location_type_id = :type_id AND status = 'active' ORDER BY name",
            ['type_id' => $locationTypeId]
        );

        return $response->json(['success' => true, 'data' => $locations]);
    }

    /**
     * Get product categories (AJAX)
     */
    private function getCategories($request, $response)
    {
        $categories = Database::fetchAll(
            "SELECT id, name FROM product_categories WHERE status = 'active' ORDER BY name"
        );

        return $response->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Get category types (AJAX)
     */
    private function getCategoryTypes($request, $response)
    {
        $categoryId = $_POST['category_id'] ?? null;
        
        if (!$categoryId) {
            return $response->json(['success' => false, 'message' => 'Category is required']);
        }

        $types = Database::fetchAll(
            "SELECT DISTINCT ct.id, ct.name 
             FROM category_types ct
             JOIN category_type_units ctu ON ct.id = ctu.category_type_id
             WHERE ct.category_id = :category_id
             ORDER BY ct.name",
            ['category_id' => $categoryId]
        );

        return $response->json(['success' => true, 'data' => $types]);
    }

    /**
     * Get type units (AJAX)
     */
    private function getTypeUnits($request, $response)
    {
        $categoryId = $_POST['category_id'] ?? null;
        $typeId = $_POST['type_id'] ?? null;
        
        if (!$categoryId || !$typeId) {
            return $response->json(['success' => false, 'message' => 'Category and type are required']);
        }

        $units = Database::fetchAll(
            "SELECT ctu.id, mu.id as measurement_unit_id, mu.name, mu.symbol, mu.rank
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :type_id
             ORDER BY mu.rank DESC",
            ['type_id' => $typeId]
        );

        return $response->json(['success' => true, 'data' => $units]);
    }

    /**
     * Get available stock at location (AJAX)
     */
    private function getStockAvailable($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $categoryTypeUnitId = $_POST['category_type_unit_id'] ?? null;
        
        if (!$locationId || !$categoryTypeUnitId) {
            return $response->json(['success' => false, 'message' => 'Location and product unit are required']);
        }

        $available = $this->stockSummaryModel->getAvailableStock($locationId, $categoryTypeUnitId);

        return $response->json(['success' => true, 'data' => ['available' => $available]]);
    }

    /**
     * Get suppliers that have stock for a specific product type at a location (AJAX)
     */
    private function getSuppliersForType($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $categoryTypeId = $_POST['category_type_id'] ?? null;

        if (!$locationId || !$categoryTypeId) {
            return $response->json(['success' => false, 'message' => 'Location and category type are required']);
        }

        $suppliers = Database::fetchAll(
            "SELECT DISTINCT s.id, s.name 
             FROM stock_summary ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN suppliers s ON ss.supplier_id = s.id
             WHERE ss.location_id = :location_id
             AND ctu.category_type_id = :category_type_id
             AND ss.total_quantity > 0
             ORDER BY s.name",
            ['location_id' => $locationId, 'category_type_id' => $categoryTypeId]
        );

        return $response->json(['success' => true, 'data' => $suppliers]);
    }

    /**
     * Get total available stock for a product type with unit conversion (AJAX)
     */
    private function getStockWithConversion($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $categoryTypeId = $_POST['category_type_id'] ?? null;
        $measurementUnitId = $_POST['measurement_unit_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;

        if (!$locationId || !$categoryTypeId || !$measurementUnitId) {
            return $response->json(['success' => false, 'message' => 'Location, category type, and unit are required']);
        }

        // Get total stock in base units (grams)
        $stocks = $this->stockSummaryModel->getTotalStockForType(
            $locationId, 
            intval($categoryTypeId), 
            $supplierId ? intval($supplierId) : null
        );

        // Get the conversion factor for requested unit
        $unit = Database::fetch(
            "SELECT id, name, symbol, conversion_factor FROM measurement_units WHERE id = :id",
            ['id' => $measurementUnitId]
        );

        if (!$unit) {
            return $response->json(['success' => false, 'message' => 'Unit not found']);
        }

        // Calculate total in base units and convert to requested unit
        $totalInBase = 0;
        foreach ($stocks as $stock) {
            $totalInBase += $stock['total_quantity'] * $stock['conversion_factor'];
        }

        $availableInUnit = $totalInBase / $unit['conversion_factor'];

        return $response->json([
            'success' => true,
            'data' => [
                'available' => round($availableInUnit, 4),
                'unit_symbol' => $unit['symbol'],
                'unit_name' => $unit['name']
            ]
        ]);
    }

    /**
     * Get all suppliers with stock at a location (AJAX)
     */
    private function getSuppliersAtLocation($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;

        if (!$locationId) {
            return $response->json(['success' => false, 'message' => 'Location is required']);
        }

        $suppliers = Database::fetchAll(
            "SELECT DISTINCT s.id, s.name 
             FROM stock_summary ss
             JOIN suppliers s ON ss.supplier_id = s.id
             WHERE ss.location_id = :location_id
             AND ss.total_quantity > 0
             ORDER BY s.name",
            ['location_id' => $locationId]
        );

        return $response->json(['success' => true, 'data' => $suppliers]);
    }

    /**
     * Get product types for a supplier at a location WITH stock info (AJAX)
     */
    private function getSupplierProductTypes($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;

        if (!$locationId || !$supplierId) {
            return $response->json(['success' => false, 'message' => 'Location and supplier are required']);
        }

        // Get product types with total stock in base units (grams)
        $types = Database::fetchAll(
            "SELECT ct.id, ct.name, ct.category_id, pc.name as category_name,
                    SUM(ss.total_quantity * mu.conversion_factor) as total_in_base
             FROM stock_summary ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN category_types ct ON ctu.category_type_id = ct.id
             JOIN product_categories pc ON ct.category_id = pc.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ss.location_id = :location_id
             AND ss.supplier_id = :supplier_id
             AND ss.total_quantity > 0
             GROUP BY ct.id, ct.name, ct.category_id, pc.name
             ORDER BY ct.name",
            ['location_id' => $locationId, 'supplier_id' => $supplierId]
        );

        // Convert base units to readable format (kg or t)
        foreach ($types as &$type) {
            $baseGrams = floatval($type['total_in_base']);
            if ($baseGrams >= 1000000) {
                // Show in tonnes
                $type['stock_display'] = number_format($baseGrams / 1000000, 2) . ' t';
            } else if ($baseGrams >= 1000) {
                // Show in kg
                $type['stock_display'] = number_format($baseGrams / 1000, 2) . ' kg';
            } else {
                // Show in grams
                $type['stock_display'] = number_format($baseGrams, 2) . ' g';
            }
        }

        return $response->json(['success' => true, 'data' => $types]);
    }

    /**
     * Get units for a product type with available stock (AJAX)
     */
    private function getTypeUnitsWithStock($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;
        $categoryTypeId = $_POST['category_type_id'] ?? null;

        if (!$locationId || !$supplierId || !$categoryTypeId) {
            return $response->json(['success' => false, 'message' => 'Location, supplier, and product type are required']);
        }

        // Get all available units for this product type
        $units = Database::fetchAll(
            "SELECT ctu.id, mu.id as measurement_unit_id, mu.name, mu.symbol, mu.conversion_factor
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :category_type_id
             ORDER BY mu.rank DESC",
            ['category_type_id' => $categoryTypeId]
        );

        // Calculate total stock in base units for this supplier
        $stocks = $this->stockSummaryModel->getTotalStockForType(
            intval($locationId),
            intval($categoryTypeId),
            intval($supplierId)
        );

        $totalInBase = 0;
        foreach ($stocks as $stock) {
            $totalInBase += $stock['total_quantity'] * $stock['conversion_factor'];
        }

        return $response->json([
            'success' => true,
            'data' => [
                'units' => $units,
                'total_in_base' => $totalInBase
            ]
        ]);
    }
}
