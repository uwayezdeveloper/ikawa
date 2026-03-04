<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class AccountTransaction extends Model
{
    protected string $table = 'account_transactions';

    /**
     * Get all transactions with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT at.*, 
                a.account_name,
                a.account_number,
                l.name as location_name,
                pm.name as payment_mode_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} at
                LEFT JOIN accounts a ON at.account_id = a.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN users u ON at.created_by = u.id
                ORDER BY at.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get transactions by account
     */
    public function getByAccount(int $accountId): array
    {
        $sql = "SELECT at.*, 
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} at
                LEFT JOIN users u ON at.created_by = u.id
                WHERE at.account_id = :account_id
                ORDER BY at.created_at DESC";
        return Database::fetchAll($sql, ['account_id' => $accountId]);
    }

    /**
     * Get transactions by reference
     */
    public function getByReference(string $referenceType, int $referenceId): array
    {
        $sql = "SELECT at.*, 
                a.account_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} at
                LEFT JOIN accounts a ON at.account_id = a.id
                LEFT JOIN users u ON at.created_by = u.id
                WHERE at.reference_type = :reference_type AND at.reference_id = :reference_id
                ORDER BY at.created_at DESC";
        return Database::fetchAll($sql, [
            'reference_type' => $referenceType,
            'reference_id' => $referenceId
        ]);
    }

    /**
     * Get transactions by date range
     */
    public function getByDateRange(string $startDate, string $endDate, ?int $accountId = null): array
    {
        $sql = "SELECT at.*, 
                a.account_name,
                a.account_number,
                l.name as location_name,
                pm.name as payment_mode_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} at
                LEFT JOIN accounts a ON at.account_id = a.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN users u ON at.created_by = u.id
                WHERE at.transaction_date BETWEEN :start_date AND :end_date";
        
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        
        if ($accountId) {
            $sql .= " AND at.account_id = :account_id";
            $params['account_id'] = $accountId;
        }
        
        $sql .= " ORDER BY at.created_at DESC";
        return Database::fetchAll($sql, $params);
    }

    /**
     * Generate transaction number
     */
    public function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $year = date('Y');
        $month = date('m');
        
        $sql = "SELECT MAX(CAST(SUBSTRING(transaction_number, 12) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE transaction_number LIKE :pattern";
        $result = Database::fetch($sql, ['pattern' => "{$prefix}-{$year}{$month}-%"]);
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return sprintf("%s-%s%s-%04d", $prefix, $year, $month, $nextNum);
    }

    /**
     * Create transaction (debit - money out)
     */
    public function createDebit(int $accountId, float $amount, string $referenceType, int $referenceId, string $description, ?int $createdBy = null): int
    {
        // Get current balance
        $account = Database::fetch("SELECT balance FROM accounts WHERE id = :id", ['id' => $accountId]);
        $balanceBefore = (float) $account['balance'];
        $balanceAfter = $balanceBefore - $amount;

        $transactionNumber = $this->generateTransactionNumber();
        $now = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO {$this->table} (transaction_number, account_id, transaction_type, amount, balance_before, balance_after, reference_type, reference_id, description, transaction_date, created_by, created_at) 
            VALUES (:transaction_number, :account_id, 'debit', :amount, :balance_before, :balance_after, :reference_type, :reference_id, :description, :transaction_date, :created_by, :created_at)";
        
        Database::query($sql, [
            'transaction_number' => $transactionNumber,
            'account_id' => $accountId,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'transaction_date' => date('Y-m-d', strtotime($now)),
            'created_at' => $now,
            'created_by' => $createdBy
        ]);

        // Update account balance
        Database::query("UPDATE accounts SET balance = :balance WHERE id = :id", [
            'balance' => $balanceAfter,
            'id' => $accountId
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Create transaction (credit - money in)
     */
    public function createCredit(int $accountId, float $amount, string $referenceType, int $referenceId, string $description, ?int $createdBy = null): int
    {
        // Get current balance
        $account = Database::fetch("SELECT balance FROM accounts WHERE id = :id", ['id' => $accountId]);
        $balanceBefore = (float) $account['balance'];
        $balanceAfter = $balanceBefore + $amount;

        $transactionNumber = $this->generateTransactionNumber();
        $now = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO {$this->table} (transaction_number, account_id, transaction_type, amount, balance_before, balance_after, reference_type, reference_id, description, transaction_date, created_by, created_at) 
            VALUES (:transaction_number, :account_id, 'credit', :amount, :balance_before, :balance_after, :reference_type, :reference_id, :description, :transaction_date, :created_by, :created_at)";
        
        Database::query($sql, [
            'transaction_number' => $transactionNumber,
            'account_id' => $accountId,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'transaction_date' => date('Y-m-d', strtotime($now)),
            'created_at' => $now,
            'created_by' => $createdBy
        ]);

        // Update account balance
        Database::query("UPDATE accounts SET balance = :balance WHERE id = :id", [
            'balance' => $balanceAfter,
            'id' => $accountId
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Get account summary
     */
    public function getAccountSummary(int $accountId): array
    {
        $sql = "SELECT 
                COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) as total_credits,
                COALESCE(SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END), 0) as total_debits
                FROM {$this->table}
                WHERE account_id = :account_id";
        return Database::fetch($sql, ['account_id' => $accountId]);
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT at.*, 
                a.account_name,
                a.account_number,
                l.name as location_name,
                pm.name as payment_mode_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} at
                LEFT JOIN accounts a ON at.account_id = a.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN users u ON at.created_by = u.id
                WHERE at.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }
}
