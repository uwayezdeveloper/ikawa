<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Sale extends Model
{
    protected string $table = 'sales';

    /**
     * Generate unique sale number
     */
    public function generateSaleNumber(): string
    {
        $prefix = 'SL';
        $date = date('Ymd');
        
        // Get the last sale number for today
        $sql = "SELECT sale_number FROM {$this->table} 
                WHERE sale_number LIKE :pattern 
                ORDER BY id DESC LIMIT 1";
        $result = Database::fetch($sql, ['pattern' => "{$prefix}{$date}%"]);
        
        if ($result) {
            $lastNumber = intval(substr($result['sale_number'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create new sale
     */
    public function createSale(array $data): int
    {
        return Database::insert($this->table, [
            'sale_number' => $data['sale_number'],
            'location_id' => $data['location_id'],
            'client_id' => $data['client_id'],
            'sale_date' => $data['sale_date'],
            'subtotal' => $data['subtotal'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'payment_status' => $data['payment_status'] ?? 'pending',
            'amount_paid' => $data['amount_paid'] ?? 0,
            'account_id' => $data['account_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'created_by' => $data['created_by']
        ]);
    }

    /**
     * Add sale item
     */
    public function addSaleItem(int $saleId, array $item): int
    {
        return Database::insert('sale_items', [
            'sale_id' => $saleId,
            'product_category_id' => $item['product_category_id'],
            'category_type_unit_id' => $item['category_type_unit_id'],
            'processing_step_id' => $item['processing_step_id'] ?: null,
            'supplier_id' => $item['supplier_id'] ?: null,
            'quantity' => $item['quantity'],
            'sell_quantity' => $item['sell_quantity'] ?? $item['quantity'],
            'sell_unit_id' => $item['sell_unit_id'] ?? null,
            'unit_price' => $item['unit_price'],
            'total_price' => $item['total_price'],
            'notes' => $item['notes'] ?? null
        ]);
    }

    /**
     * Get sale by ID with details
     */
    public function getSaleById(int $id): ?array
    {
        $sql = "SELECT s.*, 
                    l.name as location_name,
                    c.name as client_name,
                    c.phone as client_phone,
                    c.email as client_email,
                    a.account_name,
                    CONCAT(u1.first_name, ' ', u1.last_name) as created_by_name,
                    CONCAT(u2.first_name, ' ', u2.last_name) as confirmed_by_name
                FROM {$this->table} s
                JOIN locations l ON s.location_id = l.id
                JOIN clients c ON s.client_id = c.id
                LEFT JOIN accounts a ON s.account_id = a.id
                JOIN users u1 ON s.created_by = u1.id
                LEFT JOIN users u2 ON s.confirmed_by = u2.id
                WHERE s.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Get sale items
     */
    public function getSaleItems(int $saleId): array
    {
        $sql = "SELECT si.*, 
                    pc.name as category_name,
                    ct.name as type_name,
                    mu.name as unit_name,
                    mu.symbol as unit_symbol,
                    ps.name as step_name,
                    sell_mu.name as sell_unit_name,
                    sell_mu.symbol as sell_unit_symbol
                FROM sale_items si
                JOIN product_categories pc ON si.product_category_id = pc.id
                JOIN category_type_units ctu ON si.category_type_unit_id = ctu.id
                JOIN category_types ct ON ctu.category_type_id = ct.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN measurement_units sell_mu ON si.sell_unit_id = sell_mu.id
                LEFT JOIN processing_steps ps ON si.processing_step_id = ps.id
                WHERE si.sale_id = :sale_id
                ORDER BY si.id";
        return Database::fetchAll($sql, ['sale_id' => $saleId]);
    }

    /**
     * Get all sales with details
     */
    public function getAllSales(array $filters = []): array
    {
        $sql = "SELECT s.*, 
                    l.name as location_name,
                    c.name as client_name,
                    CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                    (SELECT COUNT(*) FROM sale_items WHERE sale_id = s.id) as item_count
                FROM {$this->table} s
                JOIN locations l ON s.location_id = l.id
                JOIN clients c ON s.client_id = c.id
                JOIN users u ON s.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['location_id'])) {
            $sql .= " AND s.location_id = :location_id";
            $params['location_id'] = $filters['location_id'];
        }
        
        if (!empty($filters['client_id'])) {
            $sql .= " AND s.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND s.sale_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND s.sale_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY s.created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }

    /**
     * Confirm sale - deduct from stock
     */
    public function confirmSale(int $saleId, int $userId): bool
    {
        $logFile = __DIR__ . '/../../debug_confirm.log';
        try {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Starting confirmSale for sale $saleId\n", FILE_APPEND);
            
            $sale = $this->getSaleById($saleId);
            if (!$sale) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Sale not found. SaleId: $saleId\n", FILE_APPEND);
                return false;
            }
            if ($sale['status'] !== 'draft') {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Sale not draft. Status: " . $sale['status'] . "\n", FILE_APPEND);
                return false;
            }

            $items = $this->getSaleItems($saleId);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Found " . count($items) . " items\n", FILE_APPEND);
            
            // Deduct stock for each item
            foreach ($items as $item) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Deducting: loc={$sale['location_id']}, cat={$item['product_category_id']}, ctu={$item['category_type_unit_id']}, step={$item['processing_step_id']}, sup={$item['supplier_id']}, qty={$item['quantity']}\n", FILE_APPEND);
                $this->deductStock(
                    (int)$sale['location_id'],
                    (int)$item['product_category_id'],
                    (int)$item['category_type_unit_id'],
                    $item['processing_step_id'] ? (int)$item['processing_step_id'] : null,
                    $item['supplier_id'] ? (int)$item['supplier_id'] : null,
                    (float)$item['quantity']
                );
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Deduct completed\n", FILE_APPEND);
            }

            // Update sale status
            $sql = "UPDATE {$this->table} SET 
                    status = 'confirmed',
                    confirmed_by = :confirmed_by,
                    confirmed_at = NOW()
                    WHERE id = :id";
            Database::query($sql, ['id' => $saleId, 'confirmed_by' => $userId]);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Sale $saleId confirmed!\n", FILE_APPEND);
            
            return true;
        } catch (\Exception $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
            return false;
        }
    }

    /**
     * Deduct stock from stock_summary
     */
    private function deductStock(int $locationId, int $categoryId, int $categoryTypeUnitId, ?int $processingStepId, ?int $supplierId, float $quantity): void
    {
        // Build WHERE clause
        $whereClause = "location_id = :location_id 
                AND product_category_id = :category_id 
                AND category_type_unit_id = :ctu_id";
        
        $params = [
            'location_id' => $locationId,
            'category_id' => $categoryId,
            'ctu_id' => $categoryTypeUnitId
        ];
        
        if ($processingStepId) {
            $whereClause .= " AND processing_step_id = :step_id";
            $params['step_id'] = $processingStepId;
        } else {
            $whereClause .= " AND processing_step_id IS NULL";
        }

        if ($supplierId) {
            $whereClause .= " AND supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        } else {
            $whereClause .= " AND (supplier_id IS NULL OR supplier_id = 0)";
        }

        // First deduct the quantity
        $sql = "UPDATE stock_summary SET 
                total_quantity = total_quantity - :quantity1,
                total_value = total_value - (:quantity2 * avg_unit_price)
                WHERE $whereClause";
        
        $deductParams = array_merge($params, ['quantity1' => $quantity, 'quantity2' => $quantity]);
        Database::query($sql, $deductParams);

        // Now check if we need to convert units (smart unit conversion)
        $this->normalizeStockUnit($whereClause, $params, $categoryTypeUnitId);
    }

    /**
     * Normalize stock unit - convert to appropriate unit based on quantity
     * e.g., 0.8 t -> 800 kg if < 1000 kg
     */
    private function normalizeStockUnit(string $whereClause, array $params, int $currentCtuId): void
    {
        // Get current stock and unit info
        $stock = Database::fetch(
            "SELECT ss.id, ss.total_quantity, ss.total_value, ss.avg_unit_price,
                    ctu.category_type_id, mu.conversion_factor, mu.symbol
             FROM stock_summary ss
             JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE $whereClause",
            $params
        );

        if (!$stock || $stock['total_quantity'] <= 0) {
            return;
        }

        // Convert current quantity to grams (base unit)
        $quantityInGrams = $stock['total_quantity'] * $stock['conversion_factor'];
        $quantityInKg = $quantityInGrams / 1000;

        // Determine the appropriate unit
        // < 1000 kg -> use kg, >= 1000 kg -> use tonnes
        $targetSymbol = $quantityInKg < 1000 ? 'kg' : 't';
        
        // If already in correct unit, skip
        if ($stock['symbol'] === $targetSymbol) {
            return;
        }

        // Find the target category_type_unit for this type
        $targetCtu = Database::fetch(
            "SELECT ctu.id, mu.conversion_factor
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :type_id AND mu.symbol = :symbol",
            ['type_id' => $stock['category_type_id'], 'symbol' => $targetSymbol]
        );

        if (!$targetCtu) {
            return; // Target unit not available for this type
        }

        // Calculate new quantity in target unit
        $newQuantity = $quantityInGrams / $targetCtu['conversion_factor'];

        // Update stock_summary with new unit and quantity
        $updateSql = "UPDATE stock_summary SET 
                      category_type_unit_id = :new_ctu_id,
                      total_quantity = :new_quantity
                      WHERE id = :stock_id";
        
        Database::query($updateSql, [
            'new_ctu_id' => $targetCtu['id'],
            'new_quantity' => $newQuantity,
            'stock_id' => $stock['id']
        ]);
    }

    /**
     * Cancel sale
     */
    public function cancelSale(int $saleId): bool
    {
        try {
            $sale = $this->getSaleById($saleId);
            if (!$sale) {
                return false;
            }

            // If sale was confirmed, restore stock
            if ($sale['status'] === 'confirmed') {
                $items = $this->getSaleItems($saleId);
                foreach ($items as $item) {
                    $this->restoreStock(
                        $sale['location_id'],
                        $item['product_category_id'],
                        $item['category_type_unit_id'],
                        $item['processing_step_id'],
                        $item['supplier_id'],
                        $item['quantity']
                    );
                }
            }

            $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = :id";
            Database::query($sql, ['id' => $saleId]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Restore stock to stock_summary
     */
    private function restoreStock(int $locationId, int $categoryId, int $categoryTypeUnitId, ?int $processingStepId, ?int $supplierId, float $quantity): void
    {
        // Build WHERE clause
        $whereClause = "location_id = :location_id 
                AND product_category_id = :category_id 
                AND category_type_unit_id = :ctu_id";
        
        $params = [
            'location_id' => $locationId,
            'category_id' => $categoryId,
            'ctu_id' => $categoryTypeUnitId
        ];
        
        if ($processingStepId) {
            $whereClause .= " AND processing_step_id = :step_id";
            $params['step_id'] = $processingStepId;
        } else {
            $whereClause .= " AND processing_step_id IS NULL";
        }

        if ($supplierId) {
            $whereClause .= " AND supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        } else {
            $whereClause .= " AND (supplier_id IS NULL OR supplier_id = 0)";
        }

        // Restore the quantity
        $sql = "UPDATE stock_summary SET 
                total_quantity = total_quantity + :quantity1,
                total_value = total_value + (:quantity2 * avg_unit_price)
                WHERE $whereClause";
        
        $restoreParams = array_merge($params, ['quantity1' => $quantity, 'quantity2' => $quantity]);
        Database::query($sql, $restoreParams);

        // Normalize unit after restore
        $this->normalizeStockUnit($whereClause, $params, $categoryTypeUnitId);
    }

    /**
     * Get available stock for a location
     */
    public function getAvailableStock(int $locationId): array
    {
        $sql = "SELECT ss.*, 
                    pc.name as category_name,
                    ctu.category_type_id,
                    ct.name as type_name,
                    mu.id as unit_id,
                    mu.name as unit_name,
                    mu.symbol as unit_symbol,
                    mu.conversion_factor,
                    ps.name as step_name,
                    s.name as supplier_name
                FROM stock_summary ss
                JOIN product_categories pc ON ss.product_category_id = pc.id
                JOIN category_type_units ctu ON ss.category_type_unit_id = ctu.id
                JOIN category_types ct ON ctu.category_type_id = ct.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN processing_steps ps ON ss.processing_step_id = ps.id
                LEFT JOIN suppliers s ON ss.supplier_id = s.id
                WHERE ss.location_id = :location_id 
                AND ss.total_quantity > 0
                ORDER BY pc.name, ct.name, s.name";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Update sale totals
     */
    public function updateTotals(int $saleId): void
    {
        $sql = "UPDATE {$this->table} s SET 
                subtotal = (SELECT COALESCE(SUM(total_price), 0) FROM sale_items WHERE sale_id = s.id),
                total_amount = subtotal - discount_amount + tax_amount
                WHERE id = :id";
        Database::query($sql, ['id' => $saleId]);
    }

    /**
     * Delete sale item
     */
    public function deleteSaleItem(int $itemId): bool
    {
        try {
            $sql = "DELETE FROM sale_items WHERE id = :id";
            Database::query($sql, ['id' => $itemId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get sales statistics
     */
    public function getStats(array $filters = []): array
    {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['location_id'])) {
            $where .= " AND location_id = :location_id";
            $params['location_id'] = $filters['location_id'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_sales,
                    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_sales,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_sales,
                    COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN status = 'confirmed' AND payment_status = 'paid' THEN amount_paid ELSE 0 END), 0) as total_paid,
                    COALESCE(SUM(CASE WHEN status = 'confirmed' AND payment_status != 'paid' THEN total_amount - amount_paid ELSE 0 END), 0) as total_pending
                FROM {$this->table} {$where}";
        
        return Database::fetch($sql, $params);
    }
}