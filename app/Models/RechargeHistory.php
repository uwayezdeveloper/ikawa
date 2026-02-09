<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class RechargeHistory extends Model
{
    protected string $table = 'tbl_recharge_history';
    protected string $primaryKey = 'rech_id';
    
    protected array $fillable = [
        'acc_id',
        'amount',
        'to_account',
        'due_date'
    ];

    /**
     * Record a new recharge transaction
     */
    public function recordRecharge(int $accountId, float $amount, ?int $toAccount = null, ?string $dueDate = null): int
    {
        $dueDate = $dueDate ?? date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $sql = "INSERT INTO {$this->table} (acc_id, amount, to_account, due_date) 
                VALUES (:acc_id, :amount, :to_account, :due_date)";
        
        Database::query($sql, [
            'acc_id' => $accountId,
            'amount' => $amount,
            'to_account' => $toAccount,
            'due_date' => $dueDate
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Record account transfer transaction
     */
    public function recordTransfer(int $fromAccountId, int $toAccountId, float $amount): int
    {
        $dueDate = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO {$this->table} (acc_id, amount, to_account, due_date) 
                VALUES (:acc_id, :amount, :to_account, :due_date)";
        
        Database::query($sql, [
            'acc_id' => $fromAccountId,
            'amount' => $amount,
            'to_account' => $toAccountId,
            'due_date' => $dueDate
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Get paginated recharge history with account details
     */
    public function getPaginatedHistory(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = Database::fetch($countSql)['total'];
        
        // Get paginated history with account details
        $sql = "SELECT rh.*, 
                a.account_name,
                a.account_number,
                a.balance as current_balance,
                l.name as location_name,
                pm.name as payment_mode_name,
                a.account_name as from_account_name,
                a.account_number as from_account_number,
                a.balance as from_current_balance,
                l.name as from_location_name,
                ta.account_name as to_account_name,
                ta.account_number as to_account_number,
                ta.balance as to_current_balance,
                tl.name as to_location_name
                FROM {$this->table} rh
                INNER JOIN accounts a ON rh.acc_id = a.id
                LEFT JOIN locations l ON a.location_id = l.id
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                LEFT JOIN accounts ta ON rh.to_account = ta.id
                LEFT JOIN locations tl ON ta.location_id = tl.id
                ORDER BY rh.rech_id DESC
                LIMIT :limit OFFSET :offset";
        
        $results = Database::fetchAll($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get recharge history for specific account
     */
    public function getAccountHistory(int $accountId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count for the account
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE acc_id = :acc_id";
        $total = Database::fetch($countSql, ['acc_id' => $accountId])['total'];
        
        // Get paginated history for the account
        $sql = "SELECT rh.*, 
                a.account_name,
                a.account_number
                FROM {$this->table} rh
                INNER JOIN accounts a ON rh.acc_id = a.id
                WHERE rh.acc_id = :acc_id
                ORDER BY rh.rech_id DESC
                LIMIT :limit OFFSET :offset";
        
        $results = Database::fetchAll($sql, [
            'acc_id' => $accountId,
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get total recharge amount for an account
     */
    public function getTotalRechargeAmount(int $accountId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total_amount FROM {$this->table} WHERE acc_id = :acc_id";
        $result = Database::fetch($sql, ['acc_id' => $accountId]);
        return (float) $result['total_amount'];
    }

    /**
     * Get recharge statistics
     */
    public function getRechargeStats(): array
    {
        $sql = "SELECT 
                COUNT(*) as total_recharges,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount,
                MAX(amount) as max_amount,
                MIN(amount) as min_amount
                FROM {$this->table}";
        
        return Database::fetch($sql);
    }

    /**
     * Get recent recharges (last 10)
     */
    public function getRecentRecharges(int $limit = 10): array
    {
        $sql = "SELECT rh.*, 
                a.account_name,
                a.account_number,
                l.name as location_name
                FROM {$this->table} rh
                INNER JOIN accounts a ON rh.acc_id = a.id
                LEFT JOIN locations l ON a.location_id = l.id
                ORDER BY rh.rech_id DESC
                LIMIT :limit";
        
        return Database::fetchAll($sql, ['limit' => $limit]);
    }

    /**
     * Search recharge history
     */
    public function searchRecharges(string $keyword, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%$keyword%";
        
        // Get total count for search
        $countSql = "SELECT COUNT(*) as total 
                FROM {$this->table} rh
                INNER JOIN accounts a ON rh.acc_id = a.id
                WHERE a.account_name LIKE :search 
                OR a.account_number LIKE :search 
                OR rh.amount LIKE :search";
        
        $total = Database::fetch($countSql, ['search' => $searchTerm])['total'];
        
        // Get search results
        $sql = "SELECT rh.*, 
                a.account_name,
                a.account_number,
                l.name as location_name
                FROM {$this->table} rh
                INNER JOIN accounts a ON rh.acc_id = a.id
                LEFT JOIN locations l ON a.location_id = l.id
                WHERE a.account_name LIKE :search 
                OR a.account_number LIKE :search 
                OR rh.amount LIKE :search
                ORDER BY rh.rech_id DESC
                LIMIT :limit OFFSET :offset";
        
        $results = Database::fetchAll($sql, [
            'search' => $searchTerm,
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
}