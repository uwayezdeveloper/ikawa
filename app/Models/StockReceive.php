<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Models\MeasurementUnit;

class StockReceive extends Model
{
    protected string $table = 'stock_receives';
    
    protected array $fillable = [
        'receive_number',
        'location_type_id',
        'location_id',
        'supplier_id',
        'product_category_id',
        'category_type_unit_id',
        'processing_step_id',
        'quantity',
        'quantity_in_kg',
        'unit_price',
        'price_per_kg',
        'total_price',
        'receive_date',
        'notes',
        'status',
        'payment_method',
        'account_id',
        'advance_id',
        'advance_amount',
        'account_amount',
        'payable_amount',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    /**
     * Get all stock receives with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT sr.*, 
                lt.name as location_type_name,
                l.name as location_name,
                s.name as supplier_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(ua.first_name, ' ', ua.last_name) as approved_by_name,
                a.account_name,
                sa.advance_number,
                sr.payment_method,
                sr.advance_amount,
                sr.account_amount,
                sr.payable_amount
                FROM {$this->table} sr
                LEFT JOIN location_types lt ON sr.location_type_id = lt.id
                LEFT JOIN locations l ON sr.location_id = l.id
                LEFT JOIN suppliers s ON sr.supplier_id = s.id
                LEFT JOIN product_categories pc ON sr.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON sr.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN users u ON sr.created_by = u.id
                LEFT JOIN users ua ON sr.approved_by = ua.id
                LEFT JOIN accounts a ON sr.account_id = a.id
                LEFT JOIN supplier_advances sa ON sr.advance_id = sa.id
                ORDER BY sr.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get stock receives by location
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT sr.*, 
                s.name as supplier_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol
                FROM {$this->table} sr
                LEFT JOIN suppliers s ON sr.supplier_id = s.id
                LEFT JOIN product_categories pc ON sr.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON sr.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE sr.location_id = :location_id
                ORDER BY sr.receive_date DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Generate receive number
     */
    private function generateReceiveNumber(): string
    {
        $prefix = 'RCV-' . date('Ym') . '-';
        $sql = "SELECT MAX(CAST(SUBSTRING(receive_number, -4) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE receive_number LIKE :prefix";
        $result = Database::fetch($sql, ['prefix' => $prefix . '%']);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create stock receive and update summary
     * unit_price is ALWAYS price per kg (base unit)
     * total_price = unit_price × quantity_in_kg
     */
    public function createReceive(array $data): ?int
    {
        try {
            Database::query("START TRANSACTION");
            
            // Generate receive number
            $data['receive_number'] = $this->generateReceiveNumber();
            
            // Get the conversion factor for the selected unit
            $unitInfo = Database::fetch(
                "SELECT mu.conversion_factor, mu.symbol 
                 FROM category_type_units ctu 
                 JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id 
                 WHERE ctu.id = :ctu_id",
                ['ctu_id' => $data['category_type_unit_id']]
            );
            
            if ($unitInfo) {
                // conversion_factor is in grams, kg = 1000 grams
                // quantity_in_kg = quantity * (conversion_factor / 1000)
                $conversionFactor = floatval($unitInfo['conversion_factor']);
                $quantityInKg = $data['quantity'] * ($conversionFactor / 1000);
                $data['quantity_in_kg'] = $quantityInKg;
                
                // unit_price IS price per kg, so price_per_kg = unit_price
                $data['price_per_kg'] = $data['unit_price'];
                
                // total_price = unit_price (per kg) × quantity_in_kg
                $data['total_price'] = $data['unit_price'] * $quantityInKg;
            } else {
                // Fallback if no unit info
                $data['total_price'] = $data['quantity'] * $data['unit_price'];
            }
            
            // Create the receive record
            $receiveId = $this->create($data);
            
            if (!$receiveId) {
                Database::query("ROLLBACK");
                return null;
            }
            
            Database::query("COMMIT");
            return $receiveId;
            
        } catch (\Exception $e) {
            Database::query("ROLLBACK");
            throw $e;
        }
    }

    /**
     * Approve stock receive, process payment, and update summary
     * Handles partial payments: advance first, then account, then payable
     */
    public function approveReceive(int $id, int $userId): bool
    {
        $receive = $this->find($id);
        if (!$receive || $receive['status'] !== 'pending') {
            return false;
        }
        
        try {
            Database::query("START TRANSACTION");
            
            $totalPrice = floatval($receive['total_price']);
            $remainingAmount = $totalPrice;
            $advanceAmount = 0;
            $accountAmount = 0;
            $payableAmount = 0;
            $paymentMethod = null;
            $accountId = $receive['account_id'] ?? null;
            $advanceId = null;
            
            // Step 1: Check for approved advances and use them
            $advances = $this->getApprovedAdvances($receive['supplier_id']);
            
            foreach ($advances as $advance) {
                if ($remainingAmount <= 0) break;
                
                $advanceAmt = floatval($advance['amount']);
                $useAmount = min($advanceAmt, $remainingAmount);
                
                $advanceAmount += $useAmount;
                $remainingAmount -= $useAmount;
                $advanceId = $advance['id']; // Track last used advance
                
                // Mark advance as settled
                Database::query("UPDATE supplier_advances SET status = 'settled' WHERE id = :id", ['id' => $advance['id']]);
            }
            
            // Step 2: Use account if there's remaining amount and account is selected
            if ($remainingAmount > 0 && $accountId) {
                // Get account balance
                $account = Database::fetch("SELECT balance FROM accounts WHERE id = :id", ['id' => $accountId]);
                $accountBalance = floatval($account['balance'] ?? 0);
                
                // Use available balance (up to remaining amount)
                $accountAmount = min($accountBalance, $remainingAmount);
                $remainingAmount -= $accountAmount;
                
                if ($accountAmount > 0) {
                    // Create debit transaction
                    $transactionModel = new AccountTransaction();
                    $description = "Stock Payment: " . $receive['quantity'] . " units to supplier";
                    $transactionModel->createDebit(
                        $accountId,
                        $accountAmount,
                        'stock_receive',
                        $id,
                        $description,
                        $userId
                    );
                }
            }
            
            // Step 3: Record remaining as payable
            if ($remainingAmount > 0) {
                $payableAmount = $remainingAmount;
                
                // Generate payable number
                $payableNumber = 'PAY-' . date('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Create supplier payable
                $sql = "INSERT INTO supplier_payables 
                        (payable_number, stock_receive_id, supplier_id, location_id, amount, created_by)
                        VALUES (:payable_number, :stock_receive_id, :supplier_id, :location_id, :amount, :created_by)";
                Database::query($sql, [
                    'payable_number' => $payableNumber,
                    'stock_receive_id' => $id,
                    'supplier_id' => $receive['supplier_id'],
                    'location_id' => $receive['location_id'],
                    'amount' => $payableAmount,
                    'created_by' => $userId
                ]);
            }
            
            // Determine payment method
            if ($advanceAmount > 0 && $accountAmount > 0) {
                $paymentMethod = 'advance'; // Mixed but primary is advance
            } elseif ($advanceAmount > 0) {
                $paymentMethod = 'advance';
            } elseif ($accountAmount > 0) {
                $paymentMethod = 'account';
            }
            
            // Update receive status and payment info
            $updated = $this->update($id, [
                'status' => 'approved',
                'payment_method' => $paymentMethod,
                'advance_id' => $advanceId,
                'advance_amount' => $advanceAmount,
                'account_amount' => $accountAmount,
                'payable_amount' => $payableAmount,
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$updated) {
                Database::query("ROLLBACK");
                return false;
            }
            
            // ========== SMART STOCK SUMMARY UPDATE ==========
            // Check if there's already stock in a LARGER unit - if so, merge into that
            $mergedIntoLarger = $this->tryMergeIntoLargerUnit(
                $receive['location_id'],
                $receive['supplier_id'],
                $receive['product_category_id'],
                $receive['category_type_unit_id'],
                floatval($receive['quantity']),
                floatval($receive['total_price']),
                $receive['receive_date']
            );
            
            if (!$mergedIntoLarger) {
                // No larger unit exists, add to same unit normally
                $sql = "INSERT INTO stock_summary 
                        (location_id, supplier_id, product_category_id, category_type_unit_id, 
                         total_quantity, total_value, avg_unit_price, last_receive_date)
                        VALUES (:location_id, :supplier_id, :category_id, :type_unit_id, 
                                :quantity, :total_price, :unit_price, :receive_date)
                        ON DUPLICATE KEY UPDATE 
                            total_quantity = total_quantity + VALUES(total_quantity),
                            total_value = total_value + VALUES(total_value),
                            avg_unit_price = (total_value + VALUES(total_value)) / (total_quantity + VALUES(total_quantity)),
                            last_receive_date = GREATEST(last_receive_date, VALUES(last_receive_date))";
                
                Database::query($sql, [
                    'location_id' => $receive['location_id'],
                    'supplier_id' => $receive['supplier_id'],
                    'category_id' => $receive['product_category_id'],
                    'type_unit_id' => $receive['category_type_unit_id'],
                    'quantity' => $receive['quantity'],
                    'total_price' => $receive['total_price'],
                    'unit_price' => $receive['unit_price'],
                    'receive_date' => $receive['receive_date']
                ]);
                
                // Check if total quantity should be stepped up to a larger unit
                $this->checkAndAutoConvertStock(
                    $receive['location_id'],
                    $receive['supplier_id'],
                    $receive['product_category_id'],
                    $receive['category_type_unit_id']
                );
            }
            // ========== END SMART STOCK UPDATE ==========
            
            Database::query("COMMIT");
            return true;
            
        } catch (\Exception $e) {
            Database::query("ROLLBACK");
            error_log("Error approving receive: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available approved advance for supplier that can cover the amount
     */
    public function getAvailableAdvance(int $supplierId, float $amount): ?array
    {
        $sql = "SELECT * FROM supplier_advances 
                WHERE supplier_id = :supplier_id 
                AND status = 'approved' 
                AND amount >= :amount
                ORDER BY created_at ASC
                LIMIT 1";
        return Database::fetch($sql, ['supplier_id' => $supplierId, 'amount' => $amount]);
    }

    /**
     * Get all approved advances for a supplier (for partial payment)
     */
    public function getApprovedAdvances(int $supplierId): array
    {
        $sql = "SELECT * FROM supplier_advances 
                WHERE supplier_id = :supplier_id 
                AND status = 'approved'
                ORDER BY created_at ASC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get total available advance amount for a supplier
     */
    public function getTotalAvailableAdvance(int $supplierId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM supplier_advances 
                WHERE supplier_id = :supplier_id 
                AND status = 'approved'";
        $result = Database::fetch($sql, ['supplier_id' => $supplierId]);
        return floatval($result['total'] ?? 0);
    }

    /**
     * Get all approved advances for a supplier
     */
    public function getSupplierAdvances(int $supplierId): array
    {
        $sql = "SELECT sa.*, 
                COALESCE(SUM(sa.amount), 0) as total_advance
                FROM supplier_advances sa
                WHERE sa.supplier_id = :supplier_id 
                AND sa.status = 'approved'
                GROUP BY sa.id
                ORDER BY sa.created_at ASC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get accounts by location
     */
    public function getAccountsByLocation(int $locationId): array
    {
        $sql = "SELECT a.id, a.account_name, a.balance, pm.name as payment_mode_name
                FROM accounts a
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.location_id = :location_id 
                AND a.status = 'active'
                ORDER BY a.account_name";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Cancel stock receive
     */
    public function cancelReceive(int $id): bool
    {
        $receive = $this->find($id);
        if (!$receive || $receive['status'] !== 'pending') {
            return false;
        }
        
        return $this->update($id, ['status' => 'cancelled']);
    }

    /**
     * Get locations by location type
     */
    public function getLocationsByType(int $locationTypeId): array
    {
        $sql = "SELECT id, name FROM locations 
                WHERE location_type_id = :location_type_id 
                AND status = 'active' 
                ORDER BY name";
        return Database::fetchAll($sql, ['location_type_id' => $locationTypeId]);
    }

    /**
     * Get product categories by location type
     */
    public function getCategoriesByLocationType(int $locationTypeId): array
    {
        $sql = "SELECT pc.id, pc.name 
                FROM product_categories pc
                INNER JOIN location_type_categories ltc ON pc.id = ltc.product_category_id
                WHERE ltc.location_type_id = :location_type_id
                AND pc.status = 'active'
                ORDER BY pc.name";
        return Database::fetchAll($sql, ['location_type_id' => $locationTypeId]);
    }

    /**
     * Get category types by product category
     */
    public function getCategoryTypes(int $categoryId): array
    {
        $sql = "SELECT id, name 
                FROM category_types 
                WHERE category_id = :category_id 
                AND status = 'active'
                ORDER BY name";
        return Database::fetchAll($sql, ['category_id' => $categoryId]);
    }

    /**
     * Get type-unit assignments by category type (returns category_type_units.id)
     */
    public function getTypeUnitsByCategoryType(int $categoryTypeId): array
    {
        $sql = "SELECT ctu.id, mu.name as unit_name, mu.symbol as unit_symbol, 
                       mu.conversion_factor, ctu.is_default
                FROM category_type_units ctu
                INNER JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ctu.category_type_id = :category_type_id
                AND mu.status = 'active'
                ORDER BY ctu.is_default DESC, mu.rank DESC";
        return Database::fetchAll($sql, ['category_type_id' => $categoryTypeId]);
    }

    /**
     * Try to merge incoming stock into an existing larger unit
     * For example: receiving 70kg when you already have 1t -> adds 0.07t to get 1.07t
     * 
     * @return bool True if merged into larger unit, false if no larger unit exists
     */
    protected function tryMergeIntoLargerUnit(
        int $locationId,
        ?int $supplierId,
        int $productCategoryId,
        int $incomingCategoryTypeUnitId,
        float $quantity,
        float $totalPrice,
        string $receiveDate
    ): bool {
        // Get information about the incoming unit
        $unitModel = new MeasurementUnit();
        $incomingUnitInfo = $unitModel->getCategoryTypeUnitInfo($incomingCategoryTypeUnitId);
        
        if (!$incomingUnitInfo) {
            return false;
        }
        
        // Get the category_type_id to find same product type in different units
        $categoryTypeId = $incomingUnitInfo['category_type_id'];
        $incomingUnitRank = $incomingUnitInfo['rank'];
        $incomingMeasurementUnitId = $incomingUnitInfo['measurement_unit_id'];
        
        // Find existing stock_summary for the same product in a LARGER unit (higher rank)
        $sql = "SELECT ss.id, ss.total_quantity, ss.total_value, ss.category_type_unit_id,
                       ctu.measurement_unit_id, mu.rank, mu.symbol, mu.conversion_factor
                FROM stock_summary ss
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id
                AND ss.product_category_id = :category_id
                AND ctu.category_type_id = :category_type_id
                AND mu.rank > :incoming_rank";
        
        $params = [
            'location_id' => $locationId,
            'category_id' => $productCategoryId,
            'category_type_id' => $categoryTypeId,
            'incoming_rank' => $incomingUnitRank
        ];
        
        if ($supplierId) {
            $sql .= " AND ss.supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        } else {
            $sql .= " AND ss.supplier_id IS NULL";
        }
        
        $sql .= " ORDER BY mu.rank DESC LIMIT 1"; // Get the largest unit
        
        $existingLargerStock = Database::fetch($sql, $params);
        
        if (!$existingLargerStock) {
            // No larger unit exists, return false to proceed with normal logic
            return false;
        }
        
        // Convert incoming quantity to the larger unit
        $convertedQuantity = $unitModel->convertQuantity(
            $quantity,
            $incomingMeasurementUnitId,
            $existingLargerStock['measurement_unit_id']
        );
        
        // Calculate new totals
        $newTotalQty = floatval($existingLargerStock['total_quantity']) + $convertedQuantity;
        $newTotalValue = floatval($existingLargerStock['total_value']) + $totalPrice;
        $newAvgPrice = $newTotalQty > 0 ? $newTotalValue / $newTotalQty : 0;
        
        // Update the existing larger unit record
        Database::query(
            "UPDATE stock_summary SET 
                total_quantity = :qty, 
                total_value = :value, 
                avg_unit_price = :avg_price,
                last_receive_date = GREATEST(last_receive_date, :receive_date)
             WHERE id = :id",
            [
                'qty' => $newTotalQty,
                'value' => $newTotalValue,
                'avg_price' => $newAvgPrice,
                'receive_date' => $receiveDate,
                'id' => $existingLargerStock['id']
            ]
        );
        
        return true; // Successfully merged into larger unit
    }

    /**
     * Check and automatically convert stock to larger unit if threshold is reached
     * This runs after stock_summary is updated
     */
    protected function checkAndAutoConvertStock(
        int $locationId, 
        ?int $supplierId, 
        int $productCategoryId, 
        int $categoryTypeUnitId
    ): void {
        // Get the current stock summary
        $sql = "SELECT ss.id, ss.total_quantity, ss.total_value, ss.avg_unit_price,
                       ctu.category_type_id, ctu.measurement_unit_id,
                       mu.name as unit_name, mu.symbol, mu.rank, mu.step_threshold, mu.conversion_factor
                FROM stock_summary ss
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id 
                AND ss.product_category_id = :category_id
                AND ss.category_type_unit_id = :type_unit_id";
        
        $params = [
            'location_id' => $locationId,
            'category_id' => $productCategoryId,
            'type_unit_id' => $categoryTypeUnitId
        ];
        
        if ($supplierId) {
            $sql .= " AND ss.supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        } else {
            $sql .= " AND ss.supplier_id IS NULL";
        }
        
        $summary = Database::fetch($sql, $params);
        
        if (!$summary) {
            return;
        }
        
        $totalQuantity = floatval($summary['total_quantity']);
        $threshold = $summary['step_threshold'];
        
        // Check if threshold is set and quantity exceeds it
        if ($threshold === null || $totalQuantity < $threshold) {
            return;
        }
        
        // Get the next larger unit
        $unitModel = new MeasurementUnit();
        $nextUnit = $unitModel->getNextLargerUnit($summary['measurement_unit_id']);
        
        if (!$nextUnit) {
            // No larger unit available
            return;
        }
        
        // Find or create the category_type_unit for the larger unit
        $newCategoryTypeUnitId = $unitModel->findOrCreateCategoryTypeUnit(
            $summary['category_type_id'],
            $nextUnit['id']
        );
        
        // Convert the quantity to the new unit
        $convertedQuantity = $unitModel->convertQuantity(
            $totalQuantity,
            $summary['measurement_unit_id'],
            $nextUnit['id']
        );
        
        // Check if there's already a stock_summary with the new unit for this location/category
        $existingSql = "SELECT id, total_quantity, total_value FROM stock_summary 
                        WHERE location_id = :location_id 
                        AND product_category_id = :category_id
                        AND category_type_unit_id = :new_type_unit_id";
        $existingParams = [
            'location_id' => $locationId,
            'category_id' => $productCategoryId,
            'new_type_unit_id' => $newCategoryTypeUnitId
        ];
        
        if ($supplierId) {
            $existingSql .= " AND supplier_id = :supplier_id";
            $existingParams['supplier_id'] = $supplierId;
        } else {
            $existingSql .= " AND supplier_id IS NULL";
        }
        
        $existingSummary = Database::fetch($existingSql, $existingParams);
        
        if ($existingSummary) {
            // Merge into existing larger unit record
            $newTotalQty = floatval($existingSummary['total_quantity']) + $convertedQuantity;
            $newTotalValue = floatval($existingSummary['total_value']) + floatval($summary['total_value']);
            $newAvgPrice = $newTotalValue / $newTotalQty;
            
            // Update existing record
            Database::query(
                "UPDATE stock_summary SET 
                    total_quantity = :qty, 
                    total_value = :value, 
                    avg_unit_price = :avg_price 
                 WHERE id = :id",
                [
                    'qty' => $newTotalQty,
                    'value' => $newTotalValue,
                    'avg_price' => $newAvgPrice,
                    'id' => $existingSummary['id']
                ]
            );
            
            // Delete the old smaller unit record
            Database::query("DELETE FROM stock_summary WHERE id = :id", ['id' => $summary['id']]);
        } else {
            // Update the current record to use the new unit
            Database::query(
                "UPDATE stock_summary SET 
                    category_type_unit_id = :new_type_unit_id, 
                    total_quantity = :qty 
                 WHERE id = :id",
                [
                    'new_type_unit_id' => $newCategoryTypeUnitId,
                    'qty' => $convertedQuantity,
                    'id' => $summary['id']
                ]
            );
        }
        
        // Recursively check if the new quantity also exceeds the next threshold
        // This handles cases like 1000000g -> 1000kg -> 1t
        $this->checkAndAutoConvertStock($locationId, $supplierId, $productCategoryId, $newCategoryTypeUnitId);
    }

    /**
     * Smart stock update for warehouse - handles unit conversion with processing_step_id support
     * Public method callable from WarehouseReceiveController
     */
    public function updateWarehouseStock(
        int $locationId,
        int $supplierId,
        int $productCategoryId,
        int $categoryTypeUnitId,
        ?int $processingStepId,
        float $quantity,
        float $totalPrice,
        string $receiveDate
    ): void {
        // First try to merge into a larger unit if exists
        $mergedIntoLarger = $this->tryMergeIntoLargerUnitWithStep(
            $locationId,
            $supplierId,
            $productCategoryId,
            $categoryTypeUnitId,
            $processingStepId,
            $quantity,
            $totalPrice,
            $receiveDate
        );
        
        if (!$mergedIntoLarger) {
            // No larger unit exists, add to same unit normally
            $existing = Database::fetch(
                "SELECT id, total_quantity, total_value FROM stock_summary 
                 WHERE location_id = :loc 
                 AND category_type_unit_id = :ctu 
                 AND supplier_id = :supplier
                 AND processing_step_id <=> :step",
                [
                    'loc' => $locationId, 
                    'ctu' => $categoryTypeUnitId, 
                    'supplier' => $supplierId,
                    'step' => $processingStepId
                ]
            );

            if ($existing) {
                $newQty = floatval($existing['total_quantity']) + $quantity;
                $newValue = floatval($existing['total_value'] ?? 0) + $totalPrice;
                $avgPrice = $newQty > 0 ? $newValue / $newQty : 0;
                Database::execute(
                    "UPDATE stock_summary SET 
                        total_quantity = :qty, 
                        total_value = :value, 
                        avg_unit_price = :avg,
                        last_receive_date = :date 
                     WHERE id = :id",
                    [
                        'qty' => $newQty, 
                        'value' => $newValue, 
                        'avg' => $avgPrice,
                        'date' => $receiveDate, 
                        'id' => $existing['id']
                    ]
                );
            } else {
                Database::execute(
                    "INSERT INTO stock_summary (location_id, product_category_id, category_type_unit_id, 
                     supplier_id, processing_step_id, total_quantity, total_value, avg_unit_price, last_receive_date)
                     VALUES (:loc, :cat, :ctu, :supplier, :step, :qty, :value, :avg, :date)",
                    [
                        'loc' => $locationId,
                        'cat' => $productCategoryId,
                        'ctu' => $categoryTypeUnitId,
                        'supplier' => $supplierId,
                        'step' => $processingStepId,
                        'qty' => $quantity,
                        'value' => $totalPrice,
                        'avg' => $quantity > 0 ? $totalPrice / $quantity : 0,
                        'date' => $receiveDate
                    ]
                );
            }
            
            // Check if total quantity should be stepped up to a larger unit
            $this->checkAndAutoConvertStockWithStep(
                $locationId,
                $supplierId,
                $productCategoryId,
                $categoryTypeUnitId,
                $processingStepId
            );
        }
    }

    /**
     * Try to merge incoming quantity into existing larger unit (with processing_step)
     */
    protected function tryMergeIntoLargerUnitWithStep(
        int $locationId,
        int $supplierId,
        int $productCategoryId,
        int $incomingCategoryTypeUnitId,
        ?int $processingStepId,
        float $quantity,
        float $totalPrice,
        string $receiveDate
    ): bool {
        $unitModel = new MeasurementUnit();
        $incomingUnitInfo = $unitModel->getCategoryTypeUnitInfo($incomingCategoryTypeUnitId);
        
        if (!$incomingUnitInfo) {
            return false;
        }
        
        $categoryTypeId = $incomingUnitInfo['category_type_id'];
        $incomingUnitRank = $incomingUnitInfo['rank'];
        $incomingMeasurementUnitId = $incomingUnitInfo['measurement_unit_id'];
        
        // Find existing stock in a LARGER unit
        $sql = "SELECT ss.id, ss.total_quantity, ss.total_value, ss.category_type_unit_id,
                       ctu.measurement_unit_id, mu.rank, mu.symbol, mu.conversion_factor
                FROM stock_summary ss
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id
                AND ss.product_category_id = :category_id
                AND ctu.category_type_id = :category_type_id
                AND ss.supplier_id = :supplier_id
                AND ss.processing_step_id <=> :step_id
                AND mu.rank > :incoming_rank
                ORDER BY mu.rank DESC LIMIT 1";
        
        $existingLargerStock = Database::fetch($sql, [
            'location_id' => $locationId,
            'category_id' => $productCategoryId,
            'category_type_id' => $categoryTypeId,
            'supplier_id' => $supplierId,
            'step_id' => $processingStepId,
            'incoming_rank' => $incomingUnitRank
        ]);
        
        if (!$existingLargerStock) {
            return false;
        }
        
        // Convert incoming quantity to the larger unit
        $convertedQuantity = $unitModel->convertQuantity(
            $quantity,
            $incomingMeasurementUnitId,
            $existingLargerStock['measurement_unit_id']
        );
        
        $newTotalQty = floatval($existingLargerStock['total_quantity']) + $convertedQuantity;
        $newTotalValue = floatval($existingLargerStock['total_value']) + $totalPrice;
        $newAvgPrice = $newTotalQty > 0 ? $newTotalValue / $newTotalQty : 0;
        
        Database::query(
            "UPDATE stock_summary SET 
                total_quantity = :qty, 
                total_value = :value, 
                avg_unit_price = :avg_price,
                last_receive_date = GREATEST(last_receive_date, :receive_date)
             WHERE id = :id",
            [
                'qty' => $newTotalQty,
                'value' => $newTotalValue,
                'avg_price' => $newAvgPrice,
                'receive_date' => $receiveDate,
                'id' => $existingLargerStock['id']
            ]
        );
        
        return true;
    }

    /**
     * Check and auto-convert stock to larger unit (with processing_step)
     */
    protected function checkAndAutoConvertStockWithStep(
        int $locationId, 
        int $supplierId, 
        int $productCategoryId, 
        int $categoryTypeUnitId,
        ?int $processingStepId
    ): void {
        $sql = "SELECT ss.id, ss.total_quantity, ss.total_value, ss.avg_unit_price,
                       ctu.category_type_id, ctu.measurement_unit_id,
                       mu.name as unit_name, mu.symbol, mu.rank, mu.step_threshold, mu.conversion_factor
                FROM stock_summary ss
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ss.location_id = :location_id 
                AND ss.product_category_id = :category_id
                AND ss.category_type_unit_id = :type_unit_id
                AND ss.supplier_id = :supplier_id
                AND ss.processing_step_id <=> :step_id";
        
        $summary = Database::fetch($sql, [
            'location_id' => $locationId,
            'category_id' => $productCategoryId,
            'type_unit_id' => $categoryTypeUnitId,
            'supplier_id' => $supplierId,
            'step_id' => $processingStepId
        ]);
        
        if (!$summary) {
            return;
        }
        
        $totalQuantity = floatval($summary['total_quantity']);
        $threshold = $summary['step_threshold'];
        
        if ($threshold === null || $totalQuantity < $threshold) {
            return;
        }
        
        $unitModel = new MeasurementUnit();
        $nextUnit = $unitModel->getNextLargerUnit($summary['measurement_unit_id']);
        
        if (!$nextUnit) {
            return;
        }
        
        $newCategoryTypeUnitId = $unitModel->findOrCreateCategoryTypeUnit(
            $summary['category_type_id'],
            $nextUnit['id']
        );
        
        $convertedQuantity = $unitModel->convertQuantity(
            $totalQuantity,
            $summary['measurement_unit_id'],
            $nextUnit['id']
        );
        
        // Check if there's already stock in the new unit
        $existingSql = "SELECT id, total_quantity, total_value FROM stock_summary 
                        WHERE location_id = :location_id 
                        AND product_category_id = :category_id
                        AND category_type_unit_id = :new_type_unit_id
                        AND supplier_id = :supplier_id
                        AND processing_step_id <=> :step_id";
        
        $existingSummary = Database::fetch($existingSql, [
            'location_id' => $locationId,
            'category_id' => $productCategoryId,
            'new_type_unit_id' => $newCategoryTypeUnitId,
            'supplier_id' => $supplierId,
            'step_id' => $processingStepId
        ]);
        
        if ($existingSummary) {
            $newTotalQty = floatval($existingSummary['total_quantity']) + $convertedQuantity;
            $newTotalValue = floatval($existingSummary['total_value']) + floatval($summary['total_value']);
            $newAvgPrice = $newTotalValue / $newTotalQty;
            
            Database::query(
                "UPDATE stock_summary SET 
                    total_quantity = :qty, 
                    total_value = :value, 
                    avg_unit_price = :avg_price 
                 WHERE id = :id",
                [
                    'qty' => $newTotalQty,
                    'value' => $newTotalValue,
                    'avg_price' => $newAvgPrice,
                    'id' => $existingSummary['id']
                ]
            );
            
            Database::query("DELETE FROM stock_summary WHERE id = :id", ['id' => $summary['id']]);
        } else {
            Database::query(
                "UPDATE stock_summary SET 
                    category_type_unit_id = :new_type_unit_id, 
                    total_quantity = :qty 
                 WHERE id = :id",
                [
                    'new_type_unit_id' => $newCategoryTypeUnitId,
                    'qty' => $convertedQuantity,
                    'id' => $summary['id']
                ]
            );
        }
        
        // Recursively check for further conversion
        $this->checkAndAutoConvertStockWithStep($locationId, $supplierId, $productCategoryId, $newCategoryTypeUnitId, $processingStepId);
    }
}