<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class SupplierAdvance extends Model
{
    protected string $table = 'supplier_advances';

    /**
     * Get all advances with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT sa.*, 
                s.name as supplier_name,
                s.supplier_type_id,
                l.name as location_name,
                a.account_name,
                a.account_number,
                pm.name as payment_mode_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} sa
                LEFT JOIN suppliers s ON sa.supplier_id = s.id
                LEFT JOIN locations l ON sa.location_id = l.id
                LEFT JOIN accounts a ON sa.account_id = a.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN users u ON sa.created_by = u.id
                ORDER BY sa.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get advances by status
     */
    public function getByStatus(string $status): array
    {
        $sql = "SELECT sa.*, 
                s.name as supplier_name,
                l.name as location_name,
                a.account_name,
                a.account_number,
                pm.name as payment_mode_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} sa
                LEFT JOIN suppliers s ON sa.supplier_id = s.id
                LEFT JOIN locations l ON sa.location_id = l.id
                LEFT JOIN accounts a ON sa.account_id = a.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN users u ON sa.created_by = u.id
                WHERE sa.status = :status
                ORDER BY sa.created_at DESC";
        return Database::fetchAll($sql, ['status' => $status]);
    }

    /**
     * Get advances by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $sql = "SELECT sa.*, 
                l.name as location_name,
                a.account_name,
                pm.name as payment_mode_name
                FROM {$this->table} sa
                LEFT JOIN locations l ON sa.location_id = l.id
                LEFT JOIN accounts a ON sa.account_id = a.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE sa.supplier_id = :supplier_id
                ORDER BY sa.created_at DESC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get pending advances for a supplier (unsettled balance)
     */
    public function getPendingBalanceBySupplier(int $supplierId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM {$this->table} 
                WHERE supplier_id = :supplier_id 
                AND status IN ('pending', 'approved')";
        $result = Database::fetch($sql, ['supplier_id' => $supplierId]);
        return (float) $result['total'];
    }

    /**
     * Find advance by ID with related data
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT sa.*, 
                s.name as supplier_name,
                l.name as location_name,
                lt.id as location_type_id,
                a.account_name,
                a.account_number,
                pm.name as payment_mode_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} sa
                LEFT JOIN suppliers s ON sa.supplier_id = s.id
                LEFT JOIN locations l ON sa.location_id = l.id
                LEFT JOIN location_types lt ON l.location_type_id = lt.id
                LEFT JOIN accounts a ON sa.account_id = a.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN users u ON sa.created_by = u.id
                WHERE sa.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Generate advance number
     */
    public function generateAdvanceNumber(): string
    {
        $prefix = 'ADV';
        $year = date('Y');
        $month = date('m');
        
        $sql = "SELECT MAX(CAST(SUBSTRING(advance_number, 12) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE advance_number LIKE :pattern";
        $result = Database::fetch($sql, ['pattern' => "{$prefix}-{$year}{$month}-%"]);
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return sprintf("%s-%s%s-%04d", $prefix, $year, $month, $nextNum);
    }

    /**
     * Create new advance
     */
    public function createAdvance(array $data): int
    {
        $advanceNumber = $this->generateAdvanceNumber();
        
        $sql = "INSERT INTO {$this->table} (advance_number, supplier_id, location_id, account_id, amount, advance_date, description, status, created_by) 
                VALUES (:advance_number, :supplier_id, :location_id, :account_id, :amount, :advance_date, :description, :status, :created_by)";
        
        Database::query($sql, [
            'advance_number' => $advanceNumber,
            'supplier_id' => $data['supplier_id'],
            'location_id' => $data['location_id'],
            'account_id' => $data['account_id'],
            'amount' => $data['amount'],
            'advance_date' => $data['advance_date'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'created_by' => $data['created_by'] ?? null
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update advance
     */
    public function updateAdvance(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                supplier_id = :supplier_id,
                location_id = :location_id,
                account_id = :account_id,
                amount = :amount,
                advance_date = :advance_date,
                description = :description
                WHERE id = :id AND status = 'pending'";
        
        Database::query($sql, [
            'id' => $id,
            'supplier_id' => $data['supplier_id'],
            'location_id' => $data['location_id'],
            'account_id' => $data['account_id'],
            'amount' => $data['amount'],
            'advance_date' => $data['advance_date'],
            'description' => $data['description'] ?? null
        ]);

        return true;
    }

    /**
     * Delete advance (only pending)
     */
    public function deleteAdvance(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND status = 'pending'";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Approve advance and deduct from account with transaction logging
     */
    public function approveAdvance(int $id, ?int $userId = null): bool
    {
        $advance = $this->findById($id);
        if (!$advance || $advance['status'] !== 'pending') {
            return false;
        }

        // Update advance status
        $sql = "UPDATE {$this->table} SET status = 'approved' WHERE id = :id";
        Database::query($sql, ['id' => $id]);

        // Create transaction record and update account balance
        $transactionModel = new AccountTransaction();
        $description = "Supplier Advance #{$advance['advance_number']} to {$advance['supplier_name']}";
        $transactionModel->createDebit(
            $advance['account_id'],
            $advance['amount'],
            'supplier_advance',
            $id,
            $description,
            $userId
        );

        return true;
    }

    /**
     * Cancel advance with transaction logging
     */
    public function cancelAdvance(int $id, ?int $userId = null): bool
    {
        $advance = $this->findById($id);
        if (!$advance) {
            return false;
        }

        // If was approved, refund to account with transaction
        if ($advance['status'] === 'approved') {
            $transactionModel = new AccountTransaction();
            $description = "Refund: Cancelled Supplier Advance #{$advance['advance_number']}";
            $transactionModel->createCredit(
                $advance['account_id'],
                $advance['amount'],
                'supplier_advance_refund',
                $id,
                $description,
                $userId
            );
        }

        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = :id";
        Database::query($sql, ['id' => $id]);

        return true;
    }

    /**
     * Mark advance as settled
     */
    public function settleAdvance(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'settled' WHERE id = :id AND status = 'approved'";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Get summary by supplier
     */
    public function getSupplierSummary(int $supplierId): array
    {
        $sql = "SELECT 
                COUNT(*) as total_advances,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as approved_amount,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN status = 'settled' THEN amount ELSE 0 END), 0) as settled_amount
                FROM {$this->table}
                WHERE supplier_id = :supplier_id";
        return Database::fetch($sql, ['supplier_id' => $supplierId]);
    }
}