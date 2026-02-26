<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class StockSummary extends Model
{
    protected string $table = 'stock_summary';
    
    protected array $fillable = [
        'location_id',
        'supplier_id',
        'product_category_id',
        'category_type_unit_id',
        'total_quantity',
        'total_value',
        'avg_unit_price',
        'last_receive_date'
    ];

    /**
     * Get all stock summary with related data (aggregated by product, not by supplier)
     */
    public function getAll(): array
    {
        $sql = "SELECT 
                ss.location_id,
                ss.product_category_id,
                ss.category_type_unit_id,
                l.name as location_name,
                lt.name as location_type_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                SUM(ss.total_quantity) as total_quantity,
                SUM(ss.total_value) as total_value,
                CASE 
                    WHEN SUM(ss.total_quantity) > 0 THEN SUM(ss.total_value) / SUM(ss.total_quantity)
                    ELSE 0
                END as avg_unit_price,
                MAX(ss.last_receive_date) as last_receive_date
                FROM {$this->table} ss
                LEFT JOIN locations l ON ss.location_id = l.id
                LEFT JOIN location_types lt ON l.location_type_id = lt.id
                LEFT JOIN product_categories pc ON ss.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                GROUP BY ss.location_id, ss.product_category_id, ss.category_type_unit_id, 
                         l.name, lt.name, pc.name, ct.name, mu.name, mu.symbol
                ORDER BY l.name, pc.name, ct.name";
        return Database::fetchAll($sql);
    }

    /**
     * Get stock summary by location (aggregated by product, not by supplier)
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT 
                ss.location_id,
                ss.product_category_id,
                ss.category_type_unit_id,
                l.name as location_name,
                pc.name as category_name,
                ct.id as category_type_id,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                SUM(ss.total_quantity) as total_quantity,
                SUM(ss.total_value) as total_value,
                CASE 
                    WHEN SUM(ss.total_quantity) > 0 THEN SUM(ss.total_value) / SUM(ss.total_quantity)
                    ELSE 0
                END as avg_unit_price,
                MAX(ss.last_receive_date) as last_receive_date
                FROM {$this->table} ss
                LEFT JOIN locations l ON ss.location_id = l.id
                LEFT JOIN product_categories pc ON ss.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id
                GROUP BY ss.location_id, ss.product_category_id, ss.category_type_unit_id,
                         l.name, pc.name, ct.id, ct.name, mu.name, mu.symbol
                ORDER BY pc.name, ct.name";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get stock summary by location WITH supplier info (for production, not aggregated)
     * Used when supplier-specific stock management is needed
     */
    public function getByLocationWithSuppliers(int $locationId): array
    {
        $sql = "SELECT ss.*, 
                l.name as location_name,
                s.name as supplier_name,
                s.id as supplier_id,
                pc.name as category_name,
                ct.id as category_type_id,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                ps.name as processing_step_name,
                ps.step_order as processing_step_order
                FROM {$this->table} ss
                LEFT JOIN locations l ON ss.location_id = l.id
                LEFT JOIN suppliers s ON ss.supplier_id = s.id
                LEFT JOIN product_categories pc ON ss.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN processing_steps ps ON ss.processing_step_id = ps.id
                WHERE ss.location_id = :location_id
                ORDER BY s.name, pc.name, ct.name";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get stock summary by location type (aggregated by product, not by supplier)
     */
    public function getByLocationType(int $locationTypeId): array
    {
        $sql = "SELECT 
                ss.location_id,
                ss.product_category_id,
                ss.category_type_unit_id,
                l.name as location_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                SUM(ss.total_quantity) as total_quantity,
                SUM(ss.total_value) as total_value,
                CASE 
                    WHEN SUM(ss.total_quantity) > 0 THEN SUM(ss.total_value) / SUM(ss.total_quantity)
                    ELSE 0
                END as avg_unit_price,
                MAX(ss.last_receive_date) as last_receive_date
                FROM {$this->table} ss
                LEFT JOIN locations l ON ss.location_id = l.id
                LEFT JOIN product_categories pc ON ss.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE l.location_type_id = :location_type_id
                GROUP BY ss.location_id, ss.product_category_id, ss.category_type_unit_id,
                         l.name, pc.name, ct.name, mu.name, mu.symbol
                ORDER BY l.name, pc.name, ct.name";
        return Database::fetchAll($sql, ['location_type_id' => $locationTypeId]);
    }

    /**
     * Get aggregated stock by category across all locations
     */
    public function getTotalsByCategory(): array
    {
        $sql = "SELECT 
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                SUM(ss.total_quantity) as total_quantity,
                SUM(ss.total_value) as total_value,
                AVG(ss.avg_unit_price) as avg_unit_price
                FROM {$this->table} ss
                LEFT JOIN product_categories pc ON ss.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                GROUP BY ss.product_category_id, ss.category_type_unit_id
                ORDER BY pc.name, ct.name";
        return Database::fetchAll($sql);
    }

    /**
     * Get low stock items (below threshold)
     */
    public function getLowStock(float $threshold = 10): array
    {
        $sql = "SELECT ss.*, 
                l.name as location_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol
                FROM {$this->table} ss
                LEFT JOIN locations l ON ss.location_id = l.id
                LEFT JOIN product_categories pc ON ss.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.total_quantity <= :threshold
                ORDER BY ss.total_quantity ASC";
        return Database::fetchAll($sql, ['threshold' => $threshold]);
    }

    /**
     * Get stock value summary per location
     */
    public function getValueByLocation(): array
    {
        $sql = "SELECT 
                l.id as location_id,
                l.name as location_name,
                lt.name as location_type_name,
                COUNT(DISTINCT ss.product_category_id) as category_count,
                SUM(ss.total_quantity) as total_items,
                SUM(ss.total_value) as total_value
                FROM {$this->table} ss
                LEFT JOIN locations l ON ss.location_id = l.id
                LEFT JOIN location_types lt ON l.location_type_id = lt.id
                GROUP BY ss.location_id
                ORDER BY total_value DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get available stock at a location for a specific product unit
     */
    public function getAvailableStock(int $locationId, int $categoryTypeUnitId): float
    {
        $sql = "SELECT COALESCE(total_quantity, 0) as quantity 
                FROM {$this->table} 
                WHERE location_id = :location_id 
                AND category_type_unit_id = :category_type_unit_id";
        $result = Database::fetch($sql, [
            'location_id' => $locationId,
            'category_type_unit_id' => $categoryTypeUnitId
        ]);
        return floatval($result['quantity'] ?? 0);
    }

    /**
     * Deduct stock from a location
     */
    public function deductStock(int $locationId, int $categoryTypeUnitId, float $quantity): bool
    {
        $sql = "UPDATE {$this->table} 
                SET total_quantity = total_quantity - :quantity,
                    total_value = total_value - (avg_unit_price * :quantity2)
                WHERE location_id = :location_id 
                AND category_type_unit_id = :category_type_unit_id
                AND total_quantity >= :quantity3";
        return Database::execute($sql, [
            'quantity' => $quantity,
            'quantity2' => $quantity,
            'quantity3' => $quantity,
            'location_id' => $locationId,
            'category_type_unit_id' => $categoryTypeUnitId
        ]);
    }

    /**
     * Add stock to a location
     */
    public function addStock(int $locationId, int $categoryTypeUnitId, float $quantity, float $unitPrice = 0): bool
    {
        // Check if record exists
        $existing = Database::fetch(
            "SELECT id, total_quantity, avg_unit_price FROM {$this->table} 
             WHERE location_id = :location_id AND category_type_unit_id = :category_type_unit_id",
            ['location_id' => $locationId, 'category_type_unit_id' => $categoryTypeUnitId]
        );

        if ($existing) {
            // Update existing record
            $newQuantity = $existing['total_quantity'] + $quantity;
            $avgPrice = $unitPrice > 0 ? $unitPrice : $existing['avg_unit_price'];
            $newValue = $newQuantity * $avgPrice;
            
            $sql = "UPDATE {$this->table} 
                    SET total_quantity = :quantity,
                        total_value = :value,
                        avg_unit_price = :avg_price,
                        last_receive_date = NOW()
                    WHERE id = :id";
            return Database::execute($sql, [
                'quantity' => $newQuantity,
                'value' => $newValue,
                'avg_price' => $avgPrice,
                'id' => $existing['id']
            ]);
        } else {
            // Get product category from category_types via category_type_units
            $ctu = Database::fetch(
                "SELECT ct.category_id as product_category_id 
                 FROM category_type_units ctu
                 JOIN category_types ct ON ctu.category_type_id = ct.id
                 WHERE ctu.id = :id",
                ['id' => $categoryTypeUnitId]
            );
            
            // Create new record
            $sql = "INSERT INTO {$this->table} 
                    (location_id, product_category_id, category_type_unit_id, total_quantity, total_value, avg_unit_price, last_receive_date)
                    VALUES (:location_id, :product_category_id, :category_type_unit_id, :quantity, :value, :avg_price, NOW())";
            return Database::execute($sql, [
                'location_id' => $locationId,
                'product_category_id' => $ctu['product_category_id'] ?? 0,
                'category_type_unit_id' => $categoryTypeUnitId,
                'quantity' => $quantity,
                'value' => $quantity * $unitPrice,
                'avg_price' => $unitPrice
            ]);
        }
    }

    /**
     * Add stock with smart unit conversion
     * If existing stock is in a different unit than the incoming stock, 
     * it converts and merges into the larger/appropriate unit
     * Also handles step-up if total exceeds threshold
     * 
     * @param int $locationId Location ID
     * @param int $categoryTypeUnitId The category_type_unit_id of incoming stock
     * @param float $quantity Incoming quantity
     * @param float $unitPrice Unit price of incoming stock
     * @param int|null $supplierId Optional supplier ID
     * @param int|null $processingStepId Optional processing step ID
     * @param int|null $sourceLocationId Optional source location ID (where stock came from)
     * @return bool Success
     */
    public function addStockWithConversion(int $locationId, int $categoryTypeUnitId, float $quantity, float $unitPrice = 0, ?int $supplierId = null, ?int $processingStepId = null, ?int $sourceLocationId = null): bool
    {
        // Get info about the incoming unit
        $incomingInfo = Database::fetch(
            "SELECT ctu.id, ctu.category_type_id, ct.category_id as product_category_id,
                    mu.id as measurement_unit_id, mu.conversion_factor, mu.rank, mu.step_threshold, mu.symbol
             FROM category_type_units ctu
             JOIN category_types ct ON ctu.category_type_id = ct.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.id = :id",
            ['id' => $categoryTypeUnitId]
        );
        
        if (!$incomingInfo) {
            return false;
        }
        
        $categoryTypeId = $incomingInfo['category_type_id'];
        $productCategoryId = $incomingInfo['product_category_id'];
        
        // Build supplier condition for queries
        $supplierCondition = $supplierId !== null 
            ? "AND ss.supplier_id = :supplier_id" 
            : "AND ss.supplier_id IS NULL";
        $supplierParams = $supplierId !== null ? ['supplier_id' => $supplierId] : [];
        
        // Build processing step condition for queries
        $stepCondition = $processingStepId !== null 
            ? "AND ss.processing_step_id = :processing_step_id" 
            : "AND ss.processing_step_id IS NULL";
        $stepParams = $processingStepId !== null ? ['processing_step_id' => $processingStepId] : [];
        
        // Build source location condition for queries
        $sourceCondition = $sourceLocationId !== null 
            ? "AND ss.source_location_id = :source_location_id" 
            : "AND ss.source_location_id IS NULL";
        $sourceParams = $sourceLocationId !== null ? ['source_location_id' => $sourceLocationId] : [];
        
        // Check for existing stock in ANY unit for this category_type at this location (same supplier, step, source)
        $existingStocks = Database::fetchAll(
            "SELECT ss.*, mu.id as measurement_unit_id, mu.conversion_factor, mu.rank, 
                    mu.step_threshold, mu.symbol, ctu.id as ctu_id
             FROM {$this->table} ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ss.location_id = :location_id
             AND ctu.category_type_id = :category_type_id
             {$supplierCondition}
             {$stepCondition}
             {$sourceCondition}
             ORDER BY mu.rank DESC",
            array_merge(['location_id' => $locationId, 'category_type_id' => $categoryTypeId], $supplierParams, $stepParams, $sourceParams)
        );
        
        // Convert incoming quantity to base units
        $incomingInBase = $quantity * $incomingInfo['conversion_factor'];
        $incomingValue = $quantity * $unitPrice;
        
        if (empty($existingStocks)) {
            // No existing stock - create new record
            $sql = "INSERT INTO {$this->table} 
                    (location_id, supplier_id, processing_step_id, source_location_id, product_category_id, category_type_unit_id, total_quantity, total_value, avg_unit_price, last_receive_date)
                    VALUES (:location_id, :supplier_id, :processing_step_id, :source_location_id, :product_category_id, :category_type_unit_id, :quantity, :value, :avg_price, NOW())";
            $success = Database::execute($sql, [
                'location_id' => $locationId,
                'supplier_id' => $supplierId,
                'processing_step_id' => $processingStepId,
                'source_location_id' => $sourceLocationId,
                'product_category_id' => $productCategoryId,
                'category_type_unit_id' => $categoryTypeUnitId,
                'quantity' => $quantity,
                'value' => $incomingValue,
                'avg_price' => $unitPrice
            ]);
            
            // Check if we should step up after adding
            if ($success && $incomingInfo['step_threshold'] && $quantity >= $incomingInfo['step_threshold']) {
                $this->checkAndStepUpStock($locationId, $categoryTypeUnitId);
            }
            
            return $success;
        }
        
        // There's existing stock - need to merge
        // Calculate total in base units
        $totalInBase = $incomingInBase;
        $totalValue = $incomingValue;
        
        foreach ($existingStocks as $stock) {
            $totalInBase += $stock['total_quantity'] * $stock['conversion_factor'];
            $totalValue += $stock['total_value'];
        }
        
        // Find the best unit for the total (the one where value >= 1 and < threshold)
        $allUnits = Database::fetchAll(
            "SELECT ctu.id as category_type_unit_id, mu.id as measurement_unit_id, 
                    mu.conversion_factor, mu.rank, mu.step_threshold, mu.symbol
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :category_type_id
             ORDER BY mu.rank DESC",
            ['category_type_id' => $categoryTypeId]
        );
        
        $bestUnit = null;
        $bestQuantity = 0;
        
        foreach ($allUnits as $unit) {
            $qtyInUnit = $totalInBase / $unit['conversion_factor'];
            
            // If unit has no threshold (highest unit) and qty >= 1, use it
            if (($unit['step_threshold'] === null || $unit['step_threshold'] == 0) && $qtyInUnit >= 1) {
                $bestUnit = $unit;
                $bestQuantity = $qtyInUnit;
                break;
            }
            
            // If qty >= 1 and < threshold, this is a good fit
            if ($qtyInUnit >= 1 && $qtyInUnit < ($unit['step_threshold'] ?? PHP_INT_MAX)) {
                $bestUnit = $unit;
                $bestQuantity = $qtyInUnit;
                break;
            }
        }
        
        // Fallback to incoming unit if no better option
        if (!$bestUnit) {
            $bestUnit = [
                'category_type_unit_id' => $categoryTypeUnitId,
                'conversion_factor' => $incomingInfo['conversion_factor']
            ];
            $bestQuantity = $totalInBase / $incomingInfo['conversion_factor'];
        }
        
        // Calculate new average unit price
        $newAvgPrice = $bestQuantity > 0 ? $totalValue / $bestQuantity : $unitPrice;
        
        // Delete all existing stock for this category_type at this location
        foreach ($existingStocks as $stock) {
            Database::query(
                "DELETE FROM {$this->table} WHERE id = :id",
                ['id' => $stock['id']]
            );
        }
        
        // Create single consolidated record
        $sql = "INSERT INTO {$this->table} 
                (location_id, supplier_id, processing_step_id, source_location_id, product_category_id, category_type_unit_id, total_quantity, total_value, avg_unit_price, last_receive_date)
                VALUES (:location_id, :supplier_id, :processing_step_id, :source_location_id, :product_category_id, :category_type_unit_id, :quantity, :value, :avg_price, NOW())";
        return Database::execute($sql, [
            'location_id' => $locationId,
            'supplier_id' => $supplierId,
            'processing_step_id' => $processingStepId,
            'source_location_id' => $sourceLocationId,
            'product_category_id' => $productCategoryId,
            'category_type_unit_id' => $bestUnit['category_type_unit_id'],
            'quantity' => $bestQuantity,
            'value' => $totalValue,
            'avg_price' => $newAvgPrice
        ]);
    }

    /**
     * Check and step up stock to larger unit if quantity >= threshold
     */
    public function checkAndStepUpStock(int $locationId, int $categoryTypeUnitId): bool
    {
        // Get current stock with unit info
        $stock = Database::fetch(
            "SELECT ss.*, mu.id as measurement_unit_id, mu.conversion_factor, mu.rank, mu.step_threshold,
                    ctu.category_type_id
             FROM {$this->table} ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ss.location_id = :location_id AND ss.category_type_unit_id = :ctu_id",
            ['location_id' => $locationId, 'ctu_id' => $categoryTypeUnitId]
        );
        
        if (!$stock || !$stock['step_threshold'] || $stock['total_quantity'] < $stock['step_threshold']) {
            return false; // No need to step up
        }
        
        // Find the next larger unit for this category_type
        $largerUnit = Database::fetch(
            "SELECT ctu.id as category_type_unit_id, mu.id as measurement_unit_id, 
                    mu.conversion_factor, mu.symbol
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :category_type_id
             AND mu.rank > :current_rank
             ORDER BY mu.rank ASC
             LIMIT 1",
            [
                'category_type_id' => $stock['category_type_id'],
                'current_rank' => $stock['rank']
            ]
        );
        
        if (!$largerUnit) {
            return false; // No larger unit available
        }
        
        // Convert to larger unit
        $valueInBase = $stock['total_quantity'] * $stock['conversion_factor'];
        $newQuantity = $valueInBase / $largerUnit['conversion_factor'];
        $newUnitPrice = $stock['total_value'] / $newQuantity;
        
        // Update the record
        Database::query(
            "UPDATE {$this->table} SET 
                category_type_unit_id = :new_ctu_id,
                total_quantity = :qty,
                avg_unit_price = :price
             WHERE id = :id",
            [
                'new_ctu_id' => $largerUnit['category_type_unit_id'],
                'qty' => $newQuantity,
                'price' => $newUnitPrice,
                'id' => $stock['id']
            ]
        );
        
        return true;
    }

    /**
     * Get stock grouped by product type (category_type) at a location
     * This combines stocks in different units for the same product type
     */
    public function getByLocationGroupedByType(int $locationId): array
    {
        $sql = "SELECT 
                ss.location_id,
                ss.product_category_id,
                ctu.category_type_id,
                pc.name as category_name,
                ct.name as type_name,
                ss.category_type_unit_id,
                ss.total_quantity,
                ss.total_value,
                ss.avg_unit_price,
                mu.id as measurement_unit_id,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                mu.rank as unit_rank,
                mu.conversion_factor
                FROM {$this->table} ss
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN category_types ct ON ctu.category_type_id = ct.id
                JOIN product_categories pc ON ct.category_id = pc.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id AND ss.total_quantity > 0
                ORDER BY pc.name, ct.name, mu.rank DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get total available stock for a product type at a location (in base unit)
     * Sums up all units converted to base
     * @param int $locationId Location ID
     * @param int $categoryTypeId Category type ID
     * @param int|null $supplierId Optional supplier ID to filter by
     */
    public function getTotalStockForType(int $locationId, int $categoryTypeId, ?int $supplierId = null): array
    {
        $params = [
            'location_id' => $locationId,
            'category_type_id' => $categoryTypeId
        ];
        
        $supplierCondition = '';
        if ($supplierId !== null) {
            $supplierCondition = 'AND ss.supplier_id = :supplier_id';
            $params['supplier_id'] = $supplierId;
        }
        
        $sql = "SELECT 
                ss.id,
                ss.category_type_unit_id,
                ss.total_quantity,
                ss.total_value,
                ss.avg_unit_price,
                mu.id as measurement_unit_id,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                mu.rank as unit_rank,
                mu.conversion_factor
                FROM {$this->table} ss
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id 
                AND ctu.category_type_id = :category_type_id
                {$supplierCondition}
                AND ss.total_quantity > 0
                ORDER BY mu.rank DESC";
        return Database::fetchAll($sql, $params);
    }

    /**
     * Deduct stock with unit conversion
     * If user wants to deduct 500kg from 1.6t, this will convert and deduct properly
     * 
     * @param int $locationId Location to deduct from
     * @param int $categoryTypeId The product type (e.g., Floatant)
     * @param int $requestedUnitId The measurement unit user selected (e.g., kg)
     * @param float $requestedQuantity Quantity in requested unit (e.g., 500)
     * @param int|null $supplierId Optional supplier ID to filter by
     * @return array|false Returns deduction details or false if insufficient stock
     */
    public function deductStockWithConversion(
        int $locationId, 
        int $categoryTypeId, 
        int $requestedUnitId, 
        float $requestedQuantity,
        ?int $supplierId = null
    ): array|false {
        // Get all stock for this product type at this location (optionally filtered by supplier)
        $stocks = $this->getTotalStockForType($locationId, $categoryTypeId, $supplierId);
        
        if (empty($stocks)) {
            return false;
        }
        
        // Get the requested unit info
        $requestedUnit = Database::fetch(
            "SELECT id, name, symbol, conversion_factor FROM measurement_units WHERE id = :id",
            ['id' => $requestedUnitId]
        );
        
        if (!$requestedUnit) {
            return false;
        }
        
        // Convert requested quantity to base unit (grams)
        $requestedInBase = $requestedQuantity * $requestedUnit['conversion_factor'];
        
        // Calculate total available in base units
        $totalAvailableInBase = 0;
        foreach ($stocks as $stock) {
            $totalAvailableInBase += $stock['total_quantity'] * $stock['conversion_factor'];
        }
        
        // Check if we have enough
        if ($totalAvailableInBase < $requestedInBase) {
            return false;
        }
        
        // Deduct from stock(s), starting from largest unit
        $remainingToDeduct = $requestedInBase;
        $deductionDetails = [];
        $totalValue = 0;
        
        foreach ($stocks as $stock) {
            if ($remainingToDeduct <= 0) break;
            
            $stockInBase = $stock['total_quantity'] * $stock['conversion_factor'];
            $deductFromThisInBase = min($stockInBase, $remainingToDeduct);
            $deductFromThisInUnit = $deductFromThisInBase / $stock['conversion_factor'];
            
            // Calculate value being deducted
            $valueDeducted = $deductFromThisInUnit * $stock['avg_unit_price'];
            $totalValue += $valueDeducted;
            
            // Update the stock record
            $newQuantity = $stock['total_quantity'] - $deductFromThisInUnit;
            $newValue = $newQuantity * $stock['avg_unit_price'];
            
            if ($newQuantity <= 0) {
                // Remove the record if quantity is zero or negative
                Database::query(
                    "DELETE FROM {$this->table} WHERE id = :id",
                    ['id' => $stock['id']]
                );
            } else {
                Database::query(
                    "UPDATE {$this->table} SET total_quantity = :qty, total_value = :value 
                     WHERE id = :id",
                    [
                        'qty' => $newQuantity,
                        'value' => $newValue,
                        'id' => $stock['id']
                    ]
                );
                
                // Check if we should step down to smaller unit (when qty < 1)
                if ($newQuantity < 1 && $newQuantity > 0) {
                    $this->checkAndStepDownStock($stock['id']);
                }
            }
            
            $deductionDetails[] = [
                'unit' => $stock['unit_symbol'],
                'quantity' => $deductFromThisInUnit,
                'value' => $valueDeducted
            ];
            
            $remainingToDeduct -= $deductFromThisInBase;
        }
        
        // Calculate average unit price from deducted stock
        $avgUnitPrice = $requestedQuantity > 0 ? $totalValue / $requestedQuantity : 0;
        
        return [
            'success' => true,
            'requested_quantity' => $requestedQuantity,
            'requested_unit' => $requestedUnit['symbol'],
            'total_value' => $totalValue,
            'avg_unit_price' => $avgUnitPrice,
            'deductions' => $deductionDetails
        ];
    }

    /**
     * Get available units for a category type at a location
     * @param int $locationId Location ID  
     * @param int $categoryTypeId Category type ID
     * @param int|null $supplierId Optional supplier ID to filter by
     */
    public function getAvailableUnitsForType(int $locationId, int $categoryTypeId, ?int $supplierId = null): array
    {
        $params = [
            'location_id' => $locationId,
            'category_type_id' => $categoryTypeId
        ];
        
        $supplierCondition = '';
        if ($supplierId !== null) {
            $supplierCondition = 'AND ss.supplier_id = :supplier_id';
            $params['supplier_id'] = $supplierId;
        }
        
        // Get units that have stock
        $stockUnits = Database::fetchAll(
            "SELECT DISTINCT ctu.id as category_type_unit_id, 
                    mu.id as measurement_unit_id, mu.name, mu.symbol, mu.rank, mu.conversion_factor,
                    ss.total_quantity
             FROM {$this->table} ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ss.location_id = :location_id 
             AND ctu.category_type_id = :category_type_id
             {$supplierCondition}
             AND ss.total_quantity > 0
             ORDER BY mu.rank",
            $params
        );
        
        // Get all units assigned to this category type (for user selection)
        $allUnits = Database::fetchAll(
            "SELECT ctu.id as category_type_unit_id, 
                    mu.id as measurement_unit_id, mu.name, mu.symbol, mu.rank, mu.conversion_factor
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :category_type_id
             ORDER BY mu.rank",
            ['category_type_id' => $categoryTypeId]
        );
        
        return [
            'stock_units' => $stockUnits,
            'all_units' => $allUnits
        ];
    }

    /**
     * Check and step down stock to smaller unit if quantity < 1
     * E.g., 0.36 t becomes 360 kg
     * 
     * @param int $locationId Location ID
     * @param int $categoryTypeUnitId The current category_type_unit_id
     * @return bool True if conversion happened
     */
    public function checkAndStepDownStock(int $stockId): bool
    {
        // Get current stock record by ID
        $stock = Database::fetch(
            "SELECT ss.*, mu.id as measurement_unit_id, mu.conversion_factor, mu.rank,
                    ctu.category_type_id
             FROM {$this->table} ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ss.id = :id",
            ['id' => $stockId]
        );
        
        if (!$stock || $stock['total_quantity'] >= 1 || $stock['total_quantity'] <= 0) {
            return false; // No need to step down
        }
        
        // Find the next smaller unit that is assigned to this category_type
        $smallerUnit = Database::fetch(
            "SELECT ctu.id as category_type_unit_id, mu.id as measurement_unit_id, 
                    mu.conversion_factor, mu.symbol
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :category_type_id
             AND mu.rank < :current_rank
             ORDER BY mu.rank DESC
             LIMIT 1",
            [
                'category_type_id' => $stock['category_type_id'],
                'current_rank' => $stock['rank']
            ]
        );
        
        if (!$smallerUnit) {
            return false; // No smaller unit available
        }
        
        // Calculate new quantity in smaller unit
        $valueInBase = $stock['total_quantity'] * $stock['conversion_factor'];
        $newQuantity = $valueInBase / $smallerUnit['conversion_factor'];
        
        // Calculate new unit price (keep the same total value)
        $newUnitPrice = $stock['total_value'] / $newQuantity;
        
        // Check if stock already exists in the smaller unit (same location AND supplier)
        $existingSmaller = Database::fetch(
            "SELECT * FROM {$this->table} 
             WHERE location_id = :location_id 
             AND category_type_unit_id = :ctu_id
             AND (supplier_id = :supplier_id OR (supplier_id IS NULL AND :supplier_id2 IS NULL))",
            [
                'location_id' => $stock['location_id'], 
                'ctu_id' => $smallerUnit['category_type_unit_id'],
                'supplier_id' => $stock['supplier_id'],
                'supplier_id2' => $stock['supplier_id']
            ]
        );
        
        if ($existingSmaller) {
            // Merge into existing smaller unit stock
            $mergedQty = $existingSmaller['total_quantity'] + $newQuantity;
            $mergedValue = $existingSmaller['total_value'] + $stock['total_value'];
            $mergedAvgPrice = $mergedQty > 0 ? $mergedValue / $mergedQty : 0;
            
            Database::query(
                "UPDATE {$this->table} SET 
                    total_quantity = :qty, 
                    total_value = :value, 
                    avg_unit_price = :price
                 WHERE id = :id",
                [
                    'qty' => $mergedQty,
                    'value' => $mergedValue,
                    'price' => $mergedAvgPrice,
                    'id' => $existingSmaller['id']
                ]
            );
            
            // Delete the old (larger unit) record
            Database::query(
                "DELETE FROM {$this->table} WHERE id = :id",
                ['id' => $stock['id']]
            );
        } else {
            // Convert the existing record to the smaller unit
            Database::query(
                "UPDATE {$this->table} SET 
                    category_type_unit_id = :new_ctu_id,
                    total_quantity = :qty,
                    avg_unit_price = :price
                 WHERE id = :id",
                [
                    'new_ctu_id' => $smallerUnit['category_type_unit_id'],
                    'qty' => $newQuantity,
                    'price' => $newUnitPrice,
                    'id' => $stock['id']
                ]
            );
        }
        
        return true;
    }
}
