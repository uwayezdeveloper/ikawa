<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class SupplierPayable extends Model
{
    protected string $table = 'supplier_payables';
    
    protected array $fillable = [
        'payable_number',
        'stock_receive_id',
        'supplier_id',
        'location_id',
        'amount',
        'paid_amount',
        'status',
        'due_date',
        'notes',
        'created_by'
    ];

    /**
     * Get all payables with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT sp.*, 
                s.name as supplier_name,
                l.name as location_name,
                sr.receive_number,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM supplier_payables sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                LEFT JOIN locations l ON sp.location_id = l.id
                LEFT JOIN stock_receives sr ON sp.stock_receive_id = sr.id
                LEFT JOIN users u ON sp.created_by = u.id
                ORDER BY sp.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get payables by location
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT sp.*, 
                s.name as supplier_name,
                sr.receive_number
                FROM supplier_payables sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                LEFT JOIN stock_receives sr ON sp.stock_receive_id = sr.id
                WHERE sp.location_id = :location_id
                ORDER BY sp.created_at DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get pending payables by location
     */
    public function getPendingByLocation(int $locationId): array
    {
        $sql = "SELECT sp.*, 
                s.name as supplier_name,
                sr.receive_number
                FROM supplier_payables sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                LEFT JOIN stock_receives sr ON sp.stock_receive_id = sr.id
                WHERE sp.location_id = :location_id
                AND sp.status IN ('pending', 'partial')
                ORDER BY sp.created_at DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get total pending payables amount by location
     */
    public function getTotalPendingByLocation(int $locationId): float
    {
        $sql = "SELECT COALESCE(SUM(amount - paid_amount), 0) as total 
                FROM supplier_payables 
                WHERE location_id = :location_id 
                AND status IN ('pending', 'partial')";
        $result = Database::fetch($sql, ['location_id' => $locationId]);
        return floatval($result['total'] ?? 0);
    }

    /**
     * Record payment against a payable
     */
    public function recordPayment(int $id, float $amount, int $userId): bool
    {
        $payable = $this->find($id);
        if (!$payable) {
            return false;
        }

        $newPaidAmount = floatval($payable['paid_amount']) + $amount;
        $totalAmount = floatval($payable['amount']);
        
        $status = 'partial';
        if ($newPaidAmount >= $totalAmount) {
            $status = 'paid';
            $newPaidAmount = $totalAmount;
        }

        return $this->update($id, [
            'paid_amount' => $newPaidAmount,
            'status' => $status
        ]);
    }

    /**
     * Get payable summary by location
     */
    public function getSummaryByLocation(int $locationId): array
    {
        $sql = "SELECT 
                COUNT(*) as total_payables,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(amount - paid_amount), 0) as total_pending,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count
                FROM supplier_payables 
                WHERE location_id = :location_id";
        return Database::fetch($sql, ['location_id' => $locationId]) ?: [];
    }
}
