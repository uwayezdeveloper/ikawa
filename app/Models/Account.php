<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Account extends Model
{
    protected string $table = 'accounts';

    /**
     * Get all accounts with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT a.*, 
                lt.name as location_type_name,
                l.name as location_name,
                pm.name as payment_mode_name
                FROM {$this->table} a
                LEFT JOIN location_types lt ON a.location_type_id = lt.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                ORDER BY a.account_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active accounts
     */
    public function getActive(): array
    {
        $sql = "SELECT a.*, 
                lt.name as location_type_name,
                l.name as location_name,
                pm.name as payment_mode_name
                FROM {$this->table} a
                LEFT JOIN location_types lt ON a.location_type_id = lt.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.status = 'active'
                ORDER BY a.account_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find account by ID with related data
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT a.*, 
                lt.name as location_type_name,
                l.name as location_name,
                pm.name as payment_mode_name
                FROM {$this->table} a
                LEFT JOIN location_types lt ON a.location_type_id = lt.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Check if account exists for location and payment mode
     */
    public function accountExists(int $locationId, int $paymentModeId, ?string $accountNumber = null, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE location_id = :location_id AND payment_mode_id = :payment_mode_id";
        $params = [
            'location_id' => $locationId,
            'payment_mode_id' => $paymentModeId
        ];

        if ($accountNumber) {
            $sql .= " AND account_number = :account_number";
            $params['account_number'] = $accountNumber;
        }

        if ($excludeId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Create new account
     */
    public function createAccount(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (location_type_id, location_id, payment_mode_id, account_name, account_number, balance, status) 
                VALUES (:location_type_id, :location_id, :payment_mode_id, :account_name, :account_number, :balance, :status)";
        
        Database::query($sql, [
            'location_type_id' => $data['location_type_id'],
            'location_id' => $data['location_id'],
            'payment_mode_id' => $data['payment_mode_id'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'] ?: null,
            'balance' => $data['balance'] ?? 0.00,
            'status' => $data['status'] ?? 'active'
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update account
     */
    public function updateAccount(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                location_type_id = :location_type_id,
                location_id = :location_id,
                payment_mode_id = :payment_mode_id,
                account_name = :account_name,
                account_number = :account_number,
                balance = :balance,
                status = :status
                WHERE id = :id";
        
        Database::query($sql, [
            'id' => $id,
            'location_type_id' => $data['location_type_id'],
            'location_id' => $data['location_id'],
            'payment_mode_id' => $data['payment_mode_id'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'] ?: null,
            'balance' => $data['balance'] ?? 0.00,
            'status' => $data['status'] ?? 'active'
        ]);

        return true;
    }

    /**
     * Delete account
     */
    public function deleteAccount(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Get accounts by location
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT a.*, pm.name as payment_mode_name
                FROM {$this->table} a
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.location_id = :location_id AND a.status = 'active'
                ORDER BY a.account_name ASC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Update balance
     */
    public function updateBalance(int $id, float $amount, string $operation = 'add'): bool
    {
        if ($operation === 'add') {
            $sql = "UPDATE {$this->table} SET balance = balance + :amount WHERE id = :id";
        } elseif ($operation === 'subtract') {
            $sql = "UPDATE {$this->table} SET balance = balance - :amount WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->table} SET balance = balance - :amount WHERE id = :id";
        }
        
        Database::query($sql, ['id' => $id, 'amount' => $amount]);
        return true;
    }

    /**
     * Get accounts that can transfer money (based on location_type_id = 3)
     * Only accounts with location_type_id = 3 can transfer money
     */
    public function getTransferEnabledAccounts(?int $locationTypeId = null): array
    {
        $sql = "SELECT a.*, 
                lt.name as location_type_name,
                l.name as location_name,
                pm.name as payment_mode_name
                FROM {$this->table} a
                LEFT JOIN location_types lt ON a.location_type_id = lt.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.status = 'active' AND a.balance > 0 AND a.location_type_id = 3";
        
        $params = [];
        
        // If a specific location type is requested, override the default filter
        if ($locationTypeId && $locationTypeId !== 3) {
            $sql = "SELECT a.*, 
                    lt.name as location_type_name,
                    l.name as location_name,
                    pm.name as payment_mode_name
                    FROM {$this->table} a
                    LEFT JOIN location_types lt ON a.location_type_id = lt.id
                    LEFT JOIN locations l ON a.location_id = l.id
                    LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                    WHERE a.status = 'active' AND a.balance > 0 AND a.location_type_id = :location_type_id";
            $params['location_type_id'] = $locationTypeId;
        }
        
        $sql .= " ORDER BY a.account_name ASC";
        
        return Database::fetchAll($sql, $params);
    }

    /**
     * Check if account can transfer money
     * Only accounts with location_type_id = 3 can transfer
     */
    public function canTransfer(int $accountId): bool
    {
        $account = $this->findById($accountId);
        return $account && 
               $account['status'] === 'active' && 
               $account['balance'] > 0 && 
               $account['location_type_id'] == 3;
    }

    /**
     * Transfer money between accounts
     */
    public function transferMoney(int $fromAccountId, int $toAccountId, float $amount): bool
    {
        try {
            // Deduct from source account
            $this->updateBalance($fromAccountId, $amount, 'subtract');
            
            // Add to destination account
            $this->updateBalance($toAccountId, $amount, 'add');
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Transfer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get accounts suitable for receiving transfers
     */
    public function getReceivingAccounts(int $excludeAccountId = null): array
    {
        $sql = "SELECT a.*, 
                lt.name as location_type_name,
                l.name as location_name,
                pm.name as payment_mode_name
                FROM {$this->table} a
                LEFT JOIN location_types lt ON a.location_type_id = lt.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.status = 'active'";
        
        $params = [];
        
        if ($excludeAccountId) {
            $sql .= " AND a.id != :exclude_id";
            $params['exclude_id'] = $excludeAccountId;
        }
        
        $sql .= " ORDER BY a.account_name ASC";
        
        return Database::fetchAll($sql, $params);
    }
      /**
     * Set account balance to specific amount
     */
    public function setBalance(int $id, float $balance): bool
    {
        $sql = "UPDATE {$this->table} SET balance = :balance WHERE id = :id";
        Database::query($sql, ['balance' => $balance, 'id' => $id]);
        return true;
    }
       /**
     * Get account by ID (simple method without joins)
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }
}