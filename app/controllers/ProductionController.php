<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\Production;
use App\Models\StockSummary;

class ProductionController extends Controller
{
    protected Production $productionModel;
    protected StockSummary $stockSummaryModel;

    public function __construct()
    {
        parent::__construct();
        $this->productionModel = new Production();
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
     * Display production page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-production')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get all stations
        $stations = Database::fetchAll(
            "SELECT l.id, l.name, lt.name as type_name 
             FROM locations l 
             JOIN location_types lt ON l.location_type_id = lt.id 
             WHERE lt.name = 'Station' AND l.status = 'active' 
             ORDER BY l.name"
        );

        // Get selected station
        $selectedStationId = $_GET['station_id'] ?? ($stations[0]['id'] ?? null);
        
        $pendingProduction = [];
        $allProduction = [];
        $stationStock = [];

        if ($selectedStationId) {
            $pendingProduction = $this->productionModel->getPendingByLocation($selectedStationId);
            $allProduction = $this->productionModel->getByLocation($selectedStationId);
            // Use getByLocationWithSuppliers for production page (needs supplier info)
            $stationStock = $this->stockSummaryModel->getByLocationWithSuppliers($selectedStationId);
        }

        // Get product categories for complete modal
        $categories = Database::fetchAll(
            "SELECT id, name FROM product_categories WHERE status = 'active' ORDER BY name"
        );

        return View::render('production/index', [
            'title' => 'Production',
            'user' => $user,
            'stations' => $stations,
            'selectedStationId' => $selectedStationId,
            'pendingProduction' => $pendingProduction,
            'allProduction' => $allProduction,
            'stationStock' => $stationStock,
            'categories' => $categories,
            'canCreate' => $this->hasPermission('create-production'),
            'canComplete' => $this->hasPermission('complete-production'),
            'scripts' => ['js/pages/production.js']
        ], 'main');
    }

    /**
     * Handle production actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'send_to_production':
                if (!$this->hasPermission('create-production')) {
                    $_SESSION['flash_error'] = 'You do not have permission to send to production';
                    return $response->redirect(APP_URL . '/production');
                }
                return $this->sendToProduction($request, $response);

            case 'complete':
                if (!$this->hasPermission('complete-production')) {
                    $_SESSION['flash_error'] = 'You do not have permission to complete production';
                    return $response->redirect(APP_URL . '/production');
                }
                return $this->completeProduction($request, $response);

            case 'cancel':
                if (!$this->hasPermission('create-production')) {
                    $_SESSION['flash_error'] = 'You do not have permission to cancel production';
                    return $response->redirect(APP_URL . '/production');
                }
                return $this->cancelProduction($request, $response);

            case 'get_types':
                return $this->getCategoryTypes($request, $response);

            case 'get_units':
                return $this->getTypeUnits($request, $response);

            case 'get_available_units':
                return $this->getAvailableUnitsForProduction($request, $response);

            case 'get_supplier_product_types':
                return $this->getSupplierProductTypes($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/production');
        }
    }

    /**
     * Send stock to production
     * Now supports unit conversion - user can send 500kg even if stock is in tons
     */
    private function sendToProduction($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $stationId = $_POST['station_id'] ?? null;
        $supplierId = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
        $categoryTypeId = $_POST['category_type_id'] ?? null;
        $measurementUnitId = $_POST['measurement_unit_id'] ?? null;
        $quantity = floatval($_POST['quantity'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        // Log for debugging
        error_log("Send to production - Station: $stationId, Supplier: $supplierId, CategoryType: $categoryTypeId, Unit: $measurementUnitId, Qty: $quantity");

        if (!$stationId || !$supplierId || !$categoryTypeId || !$measurementUnitId || $quantity <= 0) {
            error_log("Send to production validation failed - missing required fields");
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        try {
            // Use smart deduction with conversion (filtered by supplier)
            $deductionResult = $this->stockSummaryModel->deductStockWithConversion(
                $stationId,
                $categoryTypeId,
                $measurementUnitId,
                $quantity,
                $supplierId
            );

            if ($deductionResult === false) {
                error_log("Send to production failed - insufficient stock");
                $_SESSION['flash_error'] = 'Insufficient stock for this product type';
                return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
            }

            // Find or create the category_type_unit for the requested measurement unit
            $unitModel = new \App\Models\MeasurementUnit();
            $categoryTypeUnitId = $unitModel->findOrCreateCategoryTypeUnit($categoryTypeId, $measurementUnitId);

            // Create production record with the unit user specified and supplier
            $productionId = $this->productionModel->createProduction([
                'location_id' => $stationId,
                'supplier_id' => $supplierId,
                'input_category_type_unit_id' => $categoryTypeUnitId,
                'input_quantity' => $quantity,
                'input_unit_price' => $deductionResult['avg_unit_price'],
                'production_date' => date('Y-m-d'),
                'notes' => $notes,
                'created_by' => $user['id']
            ]);

            if ($productionId) {
                error_log("Production created successfully - ID: $productionId");
                $_SESSION['flash_success'] = 'Stock sent to production: ' . number_format($quantity, 2) . ' ' . $deductionResult['requested_unit'];
            } else {
                error_log("Failed to create production record");
                $_SESSION['flash_error'] = 'Failed to create production record';
            }
        } catch (\Exception $e) {
            error_log("Send to production exception: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error sending to production: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
    }

    /**
     * Complete production (record finished product)
     */
    private function completeProduction($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $id = $_POST['id'] ?? null;
        $stationId = $_POST['station_id'] ?? null;
        $outputCategoryTypeUnitId = $_POST['output_category_type_unit_id'] ?? null;
        $outputQuantity = floatval($_POST['output_quantity'] ?? 0);
        $outputUnitPrice = floatval($_POST['output_unit_price'] ?? 0);

        if (!$id || !$outputCategoryTypeUnitId || $outputQuantity <= 0) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        $production = $this->productionModel->getWithDetails($id);
        if (!$production) {
            $_SESSION['flash_error'] = 'Production record not found';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        if ($production['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Production is not pending';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        // Complete production record
        $success = $this->productionModel->completeProduction(
            $id,
            $outputCategoryTypeUnitId,
            $outputQuantity,
            $outputUnitPrice,
            $user['id']
        );

        if ($success) {
            // Add finished product to station stock with smart conversion (keep same supplier)
            $this->stockSummaryModel->addStockWithConversion(
                $production['location_id'],
                $outputCategoryTypeUnitId,
                $outputQuantity,
                $outputUnitPrice,
                $production['supplier_id'] ?? null
            );

            $_SESSION['flash_success'] = 'Production completed. Finished product added to stock.';
        } else {
            $_SESSION['flash_error'] = 'Failed to complete production';
        }

        return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
    }

    /**
     * Cancel production (return stock)
     */
    private function cancelProduction($request, $response)
    {
        $id = $_POST['id'] ?? null;
        $stationId = $_POST['station_id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Production ID is required';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        $production = $this->productionModel->getWithDetails($id);
        if (!$production) {
            $_SESSION['flash_error'] = 'Production record not found';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        if ($production['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Production is not pending';
            return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
        }

        // Return stock to station with smart conversion (keep same supplier)
        $this->stockSummaryModel->addStockWithConversion(
            $production['location_id'],
            $production['input_category_type_unit_id'],
            $production['input_quantity'],
            $production['input_unit_price'],
            $production['supplier_id'] ?? null
        );

        // Cancel the production
        $success = $this->productionModel->cancelProduction($id);

        if ($success) {
            $_SESSION['flash_success'] = 'Production cancelled. Stock returned to station.';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel production';
        }

        return $response->redirect(APP_URL . '/production?station_id=' . $stationId);
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
        $typeId = $_POST['type_id'] ?? null;
        
        if (!$typeId) {
            return $response->json(['success' => false, 'message' => 'Type is required']);
        }

        $units = Database::fetchAll(
            "SELECT ctu.id, CONCAT(mu.name, ' (', mu.symbol, ')') as name
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :type_id
             ORDER BY mu.name",
            ['type_id' => $typeId]
        );

        return $response->json(['success' => true, 'data' => $units]);
    }

    /**
     * Get product types for a specific supplier at a location (AJAX)
     */
    private function getSupplierProductTypes($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;
        
        if (!$locationId || !$supplierId) {
            return $response->json(['success' => false, 'message' => 'Location and supplier are required']);
        }

        // Get stock grouped by product type for this supplier
        $stocks = Database::fetchAll(
            "SELECT 
                ss.id,
                ctu.category_type_id,
                ct.name as type_name,
                pc.name as category_name,
                ss.total_quantity,
                mu.symbol as unit_symbol
             FROM stock_summary ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN category_types ct ON ctu.category_type_id = ct.id
             JOIN product_categories pc ON ct.category_id = pc.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ss.location_id = :location_id
             AND ss.supplier_id = :supplier_id
             AND ss.total_quantity > 0
             ORDER BY pc.name, ct.name",
            ['location_id' => $locationId, 'supplier_id' => $supplierId]
        );

        // Group by category_type
        $grouped = [];
        foreach ($stocks as $stock) {
            $key = $stock['category_type_id'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category_type_id' => $stock['category_type_id'],
                    'category_name' => $stock['category_name'],
                    'type_name' => $stock['type_name'],
                    'units' => []
                ];
            }
            $grouped[$key]['units'][] = [
                'quantity' => $stock['total_quantity'],
                'symbol' => $stock['unit_symbol']
            ];
        }

        // Format for response
        $result = [];
        foreach ($grouped as $item) {
            $stockParts = [];
            foreach ($item['units'] as $u) {
                $stockParts[] = number_format($u['quantity'], 2) . ' ' . $u['symbol'];
            }
            $result[] = [
                'category_type_id' => $item['category_type_id'],
                'category_name' => $item['category_name'],
                'type_name' => $item['type_name'],
                'stock_display' => implode(' + ', $stockParts)
            ];
        }

        return $response->json(['success' => true, 'data' => $result]);
    }

    /**
     * Get available units for production (AJAX)
     * Returns units with stock availability info, plus all valid units for the type
     */
    private function getAvailableUnitsForProduction($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        $categoryTypeId = $_POST['category_type_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;
        
        if (!$locationId || !$categoryTypeId) {
            return $response->json(['success' => false, 'message' => 'Location and category type are required']);
        }

        // Get all available units for this category type (optionally filtered by supplier)
        $unitData = $this->stockSummaryModel->getAvailableUnitsForType($locationId, $categoryTypeId, $supplierId);

        // Create a set of units with stock for quick lookup
        $unitsWithStock = [];
        foreach ($unitData['stock_units'] as $su) {
            $unitsWithStock[$su['measurement_unit_id']] = true;
        }

        // Format all units with has_stock indicator
        $formattedUnits = [];
        foreach ($unitData['all_units'] as $unit) {
            $formattedUnits[] = [
                'measurement_unit_id' => $unit['measurement_unit_id'],
                'category_type_unit_id' => $unit['category_type_unit_id'],
                'name' => $unit['name'],
                'symbol' => $unit['symbol'],
                'rank' => $unit['rank'],
                'has_stock' => isset($unitsWithStock[$unit['measurement_unit_id']])
            ];
        }

        // Get total stock info grouped (filtered by supplier if provided)
        $totalStock = $this->stockSummaryModel->getTotalStockForType($locationId, $categoryTypeId, $supplierId);
        
        // Format total stock string
        $stockParts = [];
        foreach ($totalStock as $stock) {
            $stockParts[] = number_format($stock['total_quantity'], 2) . ' ' . $stock['unit_symbol'];
        }
        $totalStockStr = implode(' + ', $stockParts);

        return $response->json([
            'success' => true, 
            'data' => [
                'units' => $formattedUnits,
                'total_stock' => $totalStockStr,
                'stock_entries' => $totalStock
            ]
        ]);
    }
}