<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\WarehouseProcessing;
use App\Models\StockSummary;
use App\Models\ProcessingStep;

class WarehouseProcessingController extends Controller
{
    protected WarehouseProcessing $processingModel;
    protected StockSummary $stockSummaryModel;
    protected ProcessingStep $processingStepModel;

    public function __construct()
    {
        parent::__construct();
        $this->processingModel = new WarehouseProcessing();
        $this->stockSummaryModel = new StockSummary();
        $this->processingStepModel = new ProcessingStep();
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
     * Display warehouse processing page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        // For now, use stock-related permissions
        if (!$this->hasPermission('view-stock-transfers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $processingRecords = $this->processingModel->getAll();
        $processingSteps = $this->processingStepModel->getActive();
        
        // Get warehouses
        $warehouses = Database::fetchAll(
            "SELECT l.id, l.name 
             FROM locations l 
             JOIN location_types lt ON l.location_type_id = lt.id 
             WHERE lt.name = 'Warehouse' AND l.status = 'active' 
             ORDER BY l.name"
        );

        // Get categories for receive form
        $categories = Database::fetchAll(
            "SELECT id, name FROM product_categories WHERE status = 'active' ORDER BY name"
        );

        // Get suppliers for receive form
        $suppliers = Database::fetchAll(
            "SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name"
        );

        return View::render('stock/warehouse-processing', [
            'title' => 'Warehouse Processing',
            'user' => $user,
            'processingRecords' => $processingRecords,
            'processingSteps' => $processingSteps,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'scripts' => ['js/pages/warehouse-processing.js?v=' . microtime(true)]
        ], 'main');
    }

    /**
     * Handle processing actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';
        error_log("WarehouseProcessingController::handleAction called with action: " . $action);
        error_log("POST data: " . print_r($_POST, true));

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create processing records';
                    return $response->redirect(APP_URL . '/warehouse/processing');
                }
                return $this->createProcessing($request, $response);

            case 'complete':
                if (!$this->hasPermission('approve-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to complete processing';
                    return $response->redirect(APP_URL . '/warehouse/processing');
                }
                return $this->completeProcessing($request, $response);

            case 'cancel':
                if (!$this->hasPermission('approve-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to cancel processing';
                    return $response->redirect(APP_URL . '/warehouse/processing');
                }
                return $this->cancelProcessing($request, $response);

            // AJAX Actions for cascade dropdowns
            case 'get_product_types':
                return $this->getProductTypesAtWarehouse($request, $response);

            case 'get_type_units':
                return $this->getTypeUnits($request, $response);

            case 'get_suppliers_for_product':
                return $this->getSuppliersForProduct($request, $response);

            case 'get_next_processing_steps':
                return $this->getNextProcessingSteps($request, $response);

            // AJAX Actions for complete processing
            case 'get_processing_details':
                return $this->getProcessingDetails($request, $response);

            case 'get_categories':
                return $this->getCategories($request, $response);

            case 'get_category_types':
                return $this->getCategoryTypes($request, $response);

            case 'get_output_units':
                return $this->getOutputUnits($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/warehouse/processing');
        }
    }

    /**
     * Get product types with stock at a warehouse location
     */
    private function getProductTypesAtWarehouse($request, $response)
    {
        try {
            $locationId = intval($_POST['location_id'] ?? 0);

            // Log for debugging
            error_log("getProductTypesAtWarehouse called with location_id: " . $locationId);

            if (!$locationId) {
                error_log("getProductTypesAtWarehouse: No location ID provided");
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Location ID required']);
                exit;
            }

            $sql = "SELECT DISTINCT ct.id, ct.name, pc.id as category_id, pc.name as category_name
                    FROM stock_summary ss
                    JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                    JOIN category_types ct ON ctu.category_type_id = ct.id
                    JOIN product_categories pc ON ct.category_id = pc.id
                    WHERE ss.location_id = :location_id 
                    AND ss.total_quantity > 0
                    ORDER BY pc.name, ct.name";
            
            error_log("getProductTypesAtWarehouse: Executing query for location: " . $locationId);
            $types = Database::fetchAll($sql, ['location_id' => $locationId]);
            error_log("getProductTypesAtWarehouse: Found " . count($types) . " product types");

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $types]);
            exit;
        } catch (\Exception $e) {
            error_log("getProductTypesAtWarehouse ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            exit;
        }
    }

    /**
     * Get units for a category type
     */
    private function getTypeUnits($request, $response)
    {
        try {
            $categoryTypeId = intval($_POST['category_type_id'] ?? 0);

            if (!$categoryTypeId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Category Type ID required']);
                exit;
            }

            $sql = "SELECT ctu.id, mu.id as measurement_unit_id, mu.name, mu.symbol, mu.conversion_factor
                    FROM category_type_units ctu
                    JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                    WHERE ctu.category_type_id = :category_type_id
                    ORDER BY mu.conversion_factor ASC";
            
            $units = Database::fetchAll($sql, ['category_type_id' => $categoryTypeId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $units]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get suppliers with a specific product type at a warehouse
     * Returns stock grouped by supplier with current processing step and available quantity
     */
    private function getSuppliersForProduct($request, $response)
    {
        try {
            $locationId = intval($_POST['location_id'] ?? 0);
            $categoryTypeId = intval($_POST['category_type_id'] ?? 0);
            $conversionFactor = floatval($_POST['conversion_factor'] ?? 1);

            if (!$locationId || !$categoryTypeId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Location and Product Type required']);
                exit;
            }

            // Get suppliers with this product, grouped by supplier and processing step
            $sql = "SELECT ss.id as stock_id, ss.supplier_id, 
                           COALESCE(s.name, 'Unknown Supplier') as supplier_name,
                           ss.processing_step_id, ps.name as step_name, ps.step_order,
                           SUM(ss.total_quantity * mu.conversion_factor) as total_in_base,
                           mu.symbol as unit_symbol
                    FROM stock_summary ss
                    LEFT JOIN suppliers s ON ss.supplier_id = s.id
                    JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                    JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                    LEFT JOIN processing_steps ps ON ss.processing_step_id = ps.id
                    WHERE ss.location_id = :location_id
                    AND ctu.category_type_id = :category_type_id
                    AND ss.total_quantity > 0
                    GROUP BY ss.supplier_id, ss.processing_step_id
                    ORDER BY s.name, ps.step_order";
            
            $stocks = Database::fetchAll($sql, [
                'location_id' => $locationId,
                'category_type_id' => $categoryTypeId
            ]);

            // Convert to selected unit
            foreach ($stocks as &$stock) {
                $stock['available_quantity'] = floatval($stock['total_in_base']) / $conversionFactor;
                $stock['step_order'] = intval($stock['step_order'] ?? 0);
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $stocks]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get next available processing steps based on current minimum step order
     */
    private function getNextProcessingSteps($request, $response)
    {
        try {
            $minStepOrder = intval($_POST['min_step_order'] ?? 0);

            // Get all processing steps that are AFTER the minimum step order
            $sql = "SELECT id, name, step_order 
                    FROM processing_steps 
                    WHERE status = 'active' 
                    AND step_order > :min_order
                    ORDER BY step_order ASC";
            
            $steps = Database::fetchAll($sql, ['min_order' => $minStepOrder]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $steps]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get processing details with items for complete modal
     */
    private function getProcessingDetails($request, $response)
    {
        $processingId = intval($_POST['processing_id'] ?? 0);

        if (!$processingId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Processing ID required']);
            exit;
        }

        $processing = $this->processingModel->getWithDetails($processingId);
        
        if (!$processing) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Processing not found']);
            exit;
        }

        // Get processing items with supplier names and step names
        $sql = "SELECT wpi.*, s.name as supplier_name, ps.name as step_name
                FROM warehouse_processing_items wpi
                LEFT JOIN suppliers s ON wpi.supplier_id = s.id
                LEFT JOIN processing_steps ps ON wpi.from_processing_step_id = ps.id
                WHERE wpi.processing_id = :id
                ORDER BY s.name";
        $items = Database::fetchAll($sql, ['id' => $processingId]);

        // Get unit info
        $unit = Database::fetch(
            "SELECT mu.name, mu.symbol, mu.conversion_factor 
             FROM measurement_units mu WHERE mu.id = :id",
            ['id' => $processing['measurement_unit_id']]
        );

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'processing' => $processing,
            'items' => $items,
            'unit' => $unit
        ]);
        exit;
    }

    /**
     * Get all product categories
     */
    private function getCategories($request, $response)
    {
        $sql = "SELECT id, name FROM product_categories WHERE status = 'active' ORDER BY name";
        $categories = Database::fetchAll($sql);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $categories]);
        exit;
    }

    /**
     * Get product types for a category
     */
    private function getCategoryTypes($request, $response)
    {
        $categoryId = intval($_POST['category_id'] ?? 0);

        if (!$categoryId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit;
        }

        $sql = "SELECT id, name FROM category_types WHERE category_id = :cat AND status = 'active' ORDER BY name";
        $types = Database::fetchAll($sql, ['cat' => $categoryId]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $types]);
        exit;
    }

    /**
     * Get units for a product type (for output)
     */
    private function getOutputUnits($request, $response)
    {
        $categoryTypeId = intval($_POST['category_type_id'] ?? 0);

        if (!$categoryTypeId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category Type ID required']);
            exit;
        }

        $sql = "SELECT ctu.id, mu.id as measurement_unit_id, mu.name, mu.symbol, mu.conversion_factor
                FROM category_type_units ctu
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ctu.category_type_id = :category_type_id
                ORDER BY mu.conversion_factor ASC";
        
        $units = Database::fetchAll($sql, ['category_type_id' => $categoryTypeId]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $units]);
        exit;
    }

    /**
     * Create new processing record with multi-supplier support
     * Takes quantities from multiple suppliers and moves stock to new processing step
     */
    private function createProcessing($request, $response)
    {
        $user = $_SESSION['user'] ?? null;

        $locationId = intval($_POST['location_id'] ?? 0);
        $categoryTypeId = intval($_POST['category_type_id'] ?? 0);
        $measurementUnitId = intval($_POST['measurement_unit_id'] ?? 0);
        $processingStepId = intval($_POST['processing_step_id'] ?? 0);
        $processingDate = $_POST['processing_date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        
        // Multi-supplier quantities: supplier_qty[supplier_id][processing_step_id] = quantity
        $supplierQuantities = $_POST['supplier_qty'] ?? [];

        // Validation
        if (!$locationId || !$categoryTypeId || !$measurementUnitId || !$processingStepId) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }


        // Get the conversion factor for requested unit
        $unit = Database::fetch(
            "SELECT id, conversion_factor FROM measurement_units WHERE id = :id",
            ['id' => $measurementUnitId]
        );

        if (!$unit) {
            $_SESSION['flash_error'] = 'Invalid unit selected';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        $conversionFactor = floatval($unit['conversion_factor']);
        
        // Parse and validate supplier quantities
        $validItems = [];
        $totalInputQuantity = 0;
        $maxStepOrder = 0;

        foreach ($supplierQuantities as $supplierId => $steps) {
            foreach ($steps as $fromStepId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $supplierId = intval($supplierId);
                $fromStepId = intval($fromStepId);

                // Check stock availability for this specific supplier and processing step
                $sql = "SELECT ss.id, ss.total_quantity, 
                               mu.conversion_factor as stock_conversion,
                               ps.step_order
                        FROM stock_summary ss
                        JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                        JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                        LEFT JOIN processing_steps ps ON ss.processing_step_id = ps.id
                        WHERE ss.location_id = :location_id
                        AND ss.supplier_id = :supplier_id
                        AND ss.processing_step_id <=> :step_id
                        AND ctu.category_type_id = :category_type_id
                        AND ss.total_quantity > 0";
                
                $stocks = Database::fetchAll($sql, [
                    'location_id' => $locationId,
                    'supplier_id' => $supplierId,
                    'step_id' => $fromStepId > 0 ? $fromStepId : null,
                    'category_type_id' => $categoryTypeId
                ]);

                // Calculate total available in selected unit
                $totalInBase = 0;
                foreach ($stocks as $stock) {
                    $totalInBase += $stock['total_quantity'] * $stock['stock_conversion'];
                    $stepOrder = intval($stock['step_order'] ?? 0);
                    if ($stepOrder > $maxStepOrder) {
                        $maxStepOrder = $stepOrder;
                    }
                }
                $availableInUnit = $totalInBase / $conversionFactor;

                if ($availableInUnit < $qty) {
                    $supplierName = Database::fetch("SELECT name FROM suppliers WHERE id = :id", ['id' => $supplierId])['name'] ?? 'Unknown';
                    $_SESSION['flash_error'] = "Insufficient stock for {$supplierName}. Available: " . number_format($availableInUnit, 2);
                    return $response->redirect(APP_URL . '/warehouse/processing');
                }

                $validItems[] = [
                    'supplier_id' => $supplierId,
                    'from_step_id' => $fromStepId > 0 ? $fromStepId : null,
                    'quantity' => $qty,
                    'quantity_in_base' => $qty * $conversionFactor,
                    'stocks' => $stocks
                ];
                
                $totalInputQuantity += $qty;
            }
        }

        if (empty($validItems)) {
            $_SESSION['flash_error'] = 'Please enter at least one quantity';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        // Verify target step is after all source steps
        $targetStep = $this->processingStepModel->find($processingStepId);
        if (!$targetStep || $targetStep['step_order'] <= $maxStepOrder) {
            $_SESSION['flash_error'] = 'Target processing step must be after current steps';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        Database::beginTransaction();

        try {
            // Create main processing record
            $processingNumber = $this->processingModel->generateProcessingNumber();
            
            // Get category_type_unit_id for the selected unit
            $ctu = Database::fetch(
                "SELECT id FROM category_type_units WHERE category_type_id = :ct AND measurement_unit_id = :mu",
                ['ct' => $categoryTypeId, 'mu' => $measurementUnitId]
            );

            $data = [
                'processing_number' => $processingNumber,
                'location_id' => $locationId,
                'category_type_id' => $categoryTypeId,
                'measurement_unit_id' => $measurementUnitId,
                'input_quantity' => $totalInputQuantity,
                'to_processing_step_id' => $processingStepId,
                'processing_date' => $processingDate,
                'notes' => $notes,
                'status' => 'pending',
                'created_by' => $user['id']
            ];

            $processingId = $this->processingModel->create($data);

            if (!$processingId) {
                throw new \Exception('Failed to create processing record');
            }

            // Create processing items for each supplier
            foreach ($validItems as $item) {
                $itemSql = "INSERT INTO warehouse_processing_items 
                            (processing_id, supplier_id, from_processing_step_id, quantity, quantity_in_base)
                            VALUES (:processing_id, :supplier_id, :from_step, :qty, :qty_base)";
                Database::execute($itemSql, [
                    'processing_id' => $processingId,
                    'supplier_id' => $item['supplier_id'],
                    'from_step' => $item['from_step_id'],
                    'qty' => $item['quantity'],
                    'qty_base' => $item['quantity_in_base']
                ]);
            }

            Database::commit();
            $_SESSION['flash_success'] = 'Processing record created successfully';
        } catch (\Exception $e) {
            Database::rollback();
            $_SESSION['flash_error'] = 'Failed to create processing: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/warehouse/processing');
    }

    /**
     * Complete processing - deduct stock from sources and add output per supplier
     */
    private function completeProcessing($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $processingId = $_POST['processing_id'] ?? null;
        $outputCategoryTypeId = intval($_POST['output_category_type_id'] ?? 0);
        $outputMeasurementUnitId = intval($_POST['output_measurement_unit_id'] ?? 0);
        $outputItems = $_POST['output_items'] ?? []; // Per-supplier output quantities

        if (!$processingId) {
            $_SESSION['flash_error'] = 'Processing ID required';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        // Validate output product
        if (!$outputCategoryTypeId || !$outputMeasurementUnitId) {
            $_SESSION['flash_error'] = 'Please select output product and unit';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        // Validate output items
        if (empty($outputItems)) {
            $_SESSION['flash_error'] = 'No output quantities provided';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        $processing = $this->processingModel->getWithDetails($processingId);

        if (!$processing || $processing['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Invalid processing record or already processed';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        // Verify output category_type_unit exists
        $outputCtu = Database::fetch(
            "SELECT ctu.id, ct.category_id FROM category_type_units ctu 
             JOIN category_types ct ON ctu.category_type_id = ct.id 
             WHERE ctu.category_type_id = :ct AND ctu.measurement_unit_id = :mu",
            ['ct' => $outputCategoryTypeId, 'mu' => $outputMeasurementUnitId]
        );

        if (!$outputCtu) {
            $_SESSION['flash_error'] = 'Invalid output product/unit combination';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        // Get processing items
        $items = Database::fetchAll(
            "SELECT * FROM warehouse_processing_items WHERE processing_id = :id",
            ['id' => $processingId]
        );

        // Build supplier output map from form
        $supplierOutputs = [];
        $totalOutputQuantity = 0;
        foreach ($outputItems as $item) {
            $supplierId = intval($item['supplier_id'] ?? 0);
            $outputQty = floatval($item['output_quantity'] ?? 0);
            if ($supplierId > 0 && $outputQty > 0) {
                $supplierOutputs[$supplierId] = $outputQty;
                $totalOutputQuantity += $outputQty;
            }
        }

        if (empty($supplierOutputs)) {
            $_SESSION['flash_error'] = 'Please enter at least one output quantity';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        Database::beginTransaction();

        try {
            // Process each item - deduct from source
            foreach ($items as $item) {
                $supplierId = $item['supplier_id'];
                $fromStepId = $item['from_processing_step_id'];
                $qtyInBase = floatval($item['quantity_in_base']);
                
                // Get stocks to deduct from for this supplier and step
                $sql = "SELECT ss.id, ss.total_quantity, mu.conversion_factor
                        FROM stock_summary ss
                        JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                        JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                        WHERE ss.location_id = :location_id
                        AND ss.supplier_id = :supplier_id
                        AND ss.processing_step_id <=> :step_id
                        AND ctu.category_type_id = :category_type_id
                        AND ss.total_quantity > 0
                        ORDER BY mu.conversion_factor DESC";
                
                $stocks = Database::fetchAll($sql, [
                    'location_id' => $processing['location_id'],
                    'supplier_id' => $supplierId,
                    'step_id' => $fromStepId,
                    'category_type_id' => $processing['category_type_id']
                ]);

                $remainingToDeduct = $qtyInBase;

                foreach ($stocks as $stock) {
                    if ($remainingToDeduct <= 0) break;

                    $stockInBase = $stock['total_quantity'] * $stock['conversion_factor'];

                    if ($stockInBase <= 0) continue;

                    if ($stockInBase <= $remainingToDeduct) {
                        // Deduct entire stock entry
                        $this->stockSummaryModel->update($stock['id'], ['total_quantity' => 0]);
                        $remainingToDeduct -= $stockInBase;
                    } else {
                        // Partial deduction
                        $deductQty = $remainingToDeduct / $stock['conversion_factor'];
                        $newQty = $stock['total_quantity'] - $deductQty;
                        $this->stockSummaryModel->update($stock['id'], ['total_quantity' => $newQty]);
                        $remainingToDeduct = 0;
                    }
                }
            }

            // Add output stock per supplier (using actual quantities entered)
            foreach ($supplierOutputs as $supplierId => $outputQty) {
                $this->addStockAtStepWithSupplier(
                    $processing['location_id'],
                    $outputCategoryTypeId,
                    $outputMeasurementUnitId,
                    $outputQty,
                    $processing['to_processing_step_id'],
                    $supplierId
                );
            }

            // Update processing record with output info
            $this->processingModel->update($processingId, [
                'output_category_type_id' => $outputCategoryTypeId,
                'output_measurement_unit_id' => $outputMeasurementUnitId,
                'output_quantity' => $totalOutputQuantity,
                'status' => 'completed',
                'completed_by' => $user['id'],
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            Database::commit();
            $_SESSION['flash_success'] = 'Processing completed successfully. Stock moved to new step.';
        } catch (\Exception $e) {
            Database::rollback();
            $_SESSION['flash_error'] = 'Failed to complete processing: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/warehouse/processing');
    }

    /**
     * Add stock at a specific processing step with supplier tracking
     */
    private function addStockAtStepWithSupplier(int $locationId, int $categoryTypeId, int $measurementUnitId, float $quantity, int $processingStepId, int $supplierId): void
    {
        // Get category_type_unit_id
        $ctu = Database::fetch(
            "SELECT id FROM category_type_units WHERE category_type_id = :ct AND measurement_unit_id = :mu",
            ['ct' => $categoryTypeId, 'mu' => $measurementUnitId]
        );

        if (!$ctu) {
            throw new \Exception('Category type unit not found');
        }

        // Get product category id
        $ct = Database::fetch(
            "SELECT category_id FROM category_types WHERE id = :id",
            ['id' => $categoryTypeId]
        );

        // Check if stock entry exists at this step for this supplier
        $existing = Database::fetch(
            "SELECT id, total_quantity FROM stock_summary 
             WHERE location_id = :loc AND category_type_unit_id = :ctu 
             AND processing_step_id = :step AND supplier_id = :supplier",
            ['loc' => $locationId, 'ctu' => $ctu['id'], 'step' => $processingStepId, 'supplier' => $supplierId]
        );

        if ($existing) {
            // Update existing
            $newQty = floatval($existing['total_quantity']) + $quantity;
            Database::execute(
                "UPDATE stock_summary SET total_quantity = :qty, last_receive_date = CURDATE() WHERE id = :id",
                ['qty' => $newQty, 'id' => $existing['id']]
            );
        } else {
            // Create new
            Database::execute(
                "INSERT INTO stock_summary (location_id, product_category_id, category_type_unit_id, processing_step_id, supplier_id, total_quantity, last_receive_date)
                 VALUES (:loc, :cat, :ctu, :step, :supplier, :qty, CURDATE())",
                [
                    'loc' => $locationId,
                    'cat' => $ct['category_id'],
                    'ctu' => $ctu['id'],
                    'step' => $processingStepId,
                    'supplier' => $supplierId,
                    'qty' => $quantity
                ]
            );
        }
    }

    /**
     * Add stock at a specific processing step (without supplier)
     */
    private function addStockAtStep(int $locationId, int $categoryTypeId, int $measurementUnitId, float $quantity, int $processingStepId): void
    {
        // Get category_type_unit_id
        $ctu = Database::fetch(
            "SELECT id FROM category_type_units WHERE category_type_id = :ct AND measurement_unit_id = :mu",
            ['ct' => $categoryTypeId, 'mu' => $measurementUnitId]
        );

        if (!$ctu) {
            throw new \Exception('Category type unit not found');
        }

        // Get product category id
        $ct = Database::fetch(
            "SELECT category_id FROM category_types WHERE id = :id",
            ['id' => $categoryTypeId]
        );

        // Check if stock entry exists at this step (without supplier)
        $existing = Database::fetch(
            "SELECT id, total_quantity FROM stock_summary 
             WHERE location_id = :loc AND category_type_unit_id = :ctu 
             AND processing_step_id = :step AND supplier_id IS NULL",
            ['loc' => $locationId, 'ctu' => $ctu['id'], 'step' => $processingStepId]
        );

        if ($existing) {
            // Update existing
            $newQty = floatval($existing['total_quantity']) + $quantity;
            Database::execute(
                "UPDATE stock_summary SET total_quantity = :qty, last_receive_date = CURDATE() WHERE id = :id",
                ['qty' => $newQty, 'id' => $existing['id']]
            );
        } else {
            // Create new
            Database::execute(
                "INSERT INTO stock_summary (location_id, product_category_id, category_type_unit_id, processing_step_id, total_quantity, last_receive_date)
                 VALUES (:loc, :cat, :ctu, :step, :qty, CURDATE())",
                [
                    'loc' => $locationId,
                    'cat' => $ct['category_id'],
                    'ctu' => $ctu['id'],
                    'step' => $processingStepId,
                    'qty' => $quantity
                ]
            );
        }
    }

    /**
     * Cancel processing
     */
    private function cancelProcessing($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $processingId = $_POST['processing_id'] ?? null;

        if (!$processingId) {
            $_SESSION['flash_error'] = 'Processing ID required';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        $processing = $this->processingModel->find($processingId);

        if (!$processing || $processing['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Invalid processing record or already processed';
            return $response->redirect(APP_URL . '/warehouse/processing');
        }

        $this->processingModel->update($processingId, [
            'status' => 'cancelled',
            'completed_by' => $user['id'],
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_success'] = 'Processing cancelled successfully';
        return $response->redirect(APP_URL . '/warehouse/processing');
    }

    /**
     * Get conversion factor for a measurement unit
     */
    private function getConversionFactor(int $unitId): float
    {
        $unit = Database::fetch(
            "SELECT conversion_factor FROM measurement_units WHERE id = :id",
            ['id' => $unitId]
        );
        return $unit ? floatval($unit['conversion_factor']) : 1;
    }
}