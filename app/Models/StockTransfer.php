<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class StockTransfer extends Model
{
    protected string $table = 'stock_transfers';
    
    protected array $fillable = [
        'transfer_number',
        'from_location_type_id',
        'from_location_id',
        'to_location_type_id',
        'to_location_id',
        'product_category_id',
        'category_type_id',
        'measurement_unit_id',
        'supplier_id',
        'category_type_unit_id',
        'quantity',
        'unit_price',
        'total_price',
        'transfer_date',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at'
    ];

    /**
     * Get all transfers with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT st.*, 
                flt.name as from_location_type_name,
                fl.name as from_location_name,
                tlt.name as to_location_type_name,
                tl.name as to_location_name,
                pc.name as category_name,
                COALESCE(ct2.name, ct.name) as type_name,
                COALESCE(mu2.name, mu.name) as unit_name,
                COALESCE(mu2.symbol, mu.symbol) as unit_symbol,
                s.name as supplier_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(ua.first_name, ' ', ua.last_name) as approved_by_name,
                CONCAT(ur.first_name, ' ', ur.last_name) as received_by_name
                FROM {$this->table} st
                LEFT JOIN location_types flt ON st.from_location_type_id = flt.id
                LEFT JOIN locations fl ON st.from_location_id = fl.id
                LEFT JOIN location_types tlt ON st.to_location_type_id = tlt.id
                LEFT JOIN locations tl ON st.to_location_id = tl.id
                LEFT JOIN product_categories pc ON st.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON st.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN category_types ct2 ON st.category_type_id = ct2.id
                LEFT JOIN measurement_units mu2 ON st.measurement_unit_id = mu2.id
                LEFT JOIN suppliers s ON st.supplier_id = s.id
                LEFT JOIN users u ON st.created_by = u.id
                LEFT JOIN users ua ON st.approved_by = ua.id
                LEFT JOIN users ur ON st.received_by = ur.id
                ORDER BY st.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get transfers from a specific location (outgoing)
     */
    public function getOutgoingByLocation(int $locationId): array
    {
        $sql = "SELECT st.*, 
                flt.name as from_location_type_name,
                fl.name as from_location_name,
                tlt.name as to_location_type_name,
                tl.name as to_location_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} st
                LEFT JOIN location_types flt ON st.from_location_type_id = flt.id
                LEFT JOIN locations fl ON st.from_location_id = fl.id
                LEFT JOIN location_types tlt ON st.to_location_type_id = tlt.id
                LEFT JOIN locations tl ON st.to_location_id = tl.id
                LEFT JOIN product_categories pc ON st.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON st.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN users u ON st.created_by = u.id
                WHERE st.from_location_id = :location_id
                ORDER BY st.transfer_date DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get transfers to a specific location (incoming)
     */
    public function getIncomingByLocation(int $locationId): array
    {
        $sql = "SELECT st.*, 
                flt.name as from_location_type_name,
                fl.name as from_location_name,
                tlt.name as to_location_type_name,
                tl.name as to_location_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} st
                LEFT JOIN location_types flt ON st.from_location_type_id = flt.id
                LEFT JOIN locations fl ON st.from_location_id = fl.id
                LEFT JOIN location_types tlt ON st.to_location_type_id = tlt.id
                LEFT JOIN locations tl ON st.to_location_id = tl.id
                LEFT JOIN product_categories pc ON st.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON st.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN users u ON st.created_by = u.id
                WHERE st.to_location_id = :location_id
                ORDER BY st.transfer_date DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get pending incoming transfers for a location
     */
    public function getPendingIncoming(int $locationId): array
    {
        $sql = "SELECT st.*, 
                flt.name as from_location_type_name,
                fl.name as from_location_name,
                tlt.name as to_location_type_name,
                tl.name as to_location_name,
                pc.name as category_name,
                ct.id as category_type_id,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} st
                LEFT JOIN location_types flt ON st.from_location_type_id = flt.id
                LEFT JOIN locations fl ON st.from_location_id = fl.id
                LEFT JOIN location_types tlt ON st.to_location_type_id = tlt.id
                LEFT JOIN locations tl ON st.to_location_id = tl.id
                LEFT JOIN product_categories pc ON st.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON st.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN users u ON st.created_by = u.id
                WHERE st.to_location_id = :location_id
                AND st.status IN ('pending', 'in_transit')
                ORDER BY st.transfer_date DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Generate transfer number
     */
    private function generateTransferNumber(): string
    {
        $prefix = 'TRF-' . date('Ym') . '-';
        $sql = "SELECT MAX(CAST(SUBSTRING(transfer_number, LENGTH(:prefix) + 1) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE transfer_number LIKE :prefix_like";
        $result = Database::fetch($sql, [
            'prefix' => $prefix,
            'prefix_like' => $prefix . '%'
        ]);
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new transfer
     */
    public function createTransfer(array $data): ?int
    {
        $data['transfer_number'] = $this->generateTransferNumber();
        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        
        return $this->create($data);
    }

    /**
     * Approve transfer (send to transit)
     */
    public function approveTransfer(int $id, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'in_transit', 
                    approved_by = :user_id,
                    approved_at = NOW()
                WHERE id = :id AND status = 'pending'";
        return Database::execute($sql, ['id' => $id, 'user_id' => $userId]);
    }

    /**
     * Receive transfer at destination
     */
    public function receiveTransfer(int $id, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'received', 
                    received_by = :user_id,
                    received_at = NOW()
                WHERE id = :id AND status = 'in_transit'";
        return Database::execute($sql, ['id' => $id, 'user_id' => $userId]);
    }

    /**
     * Cancel transfer
     */
    public function cancelTransfer(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'cancelled'
                WHERE id = :id AND status IN ('pending', 'in_transit')";
        return Database::execute($sql, ['id' => $id]);
    }

    /**
     * Get transfer with full details
     */
    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT st.*, 
                flt.name as from_location_type_name,
                fl.name as from_location_name,
                tlt.name as to_location_type_name,
                tl.name as to_location_name,
                pc.name as category_name,
                ct.name as type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(ua.first_name, ' ', ua.last_name) as approved_by_name,
                CONCAT(ur.first_name, ' ', ur.last_name) as received_by_name
                FROM {$this->table} st
                LEFT JOIN location_types flt ON st.from_location_type_id = flt.id
                LEFT JOIN locations fl ON st.from_location_id = fl.id
                LEFT JOIN location_types tlt ON st.to_location_type_id = tlt.id
                LEFT JOIN locations tl ON st.to_location_id = tl.id
                LEFT JOIN product_categories pc ON st.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON st.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN users u ON st.created_by = u.id
                LEFT JOIN users ua ON st.approved_by = ua.id
                LEFT JOIN users ur ON st.received_by = ur.id
                WHERE st.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Get transfer statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                COUNT(*) as total_transfers,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_transit' THEN 1 ELSE 0 END) as in_transit,
                SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                COALESCE(SUM(CASE WHEN status = 'received' THEN total_price ELSE 0 END), 0) as total_value
                FROM {$this->table}";
        return Database::fetch($sql) ?: [];
    }
}