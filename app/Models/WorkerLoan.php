<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class WorkerLoan extends Model
{
    protected string $table = 'tbl_worker_loan';
    protected string $primaryKey = 'l_id';

    protected array $fillable = [
        'user_id',
        'request_amount',
        'payed_amount',
        'description',
        'status'
    ];

    public function createLoan($data)
    {
        $loanData = [
            'user_id' => $data['user_id'],
            'request_amount' => $data['request_amount'],
            'payed_amount' => $data['payed_amount'] ?? 0,
            'description' => $data['description'],
            'status' => $data['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return Database::insert($this->table, $loanData);
    }

    public function getUserLoans($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY l_id DESC";
        return Database::fetchAll($sql, [$userId]);
    }

    public function getLoanById($id)
    {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.l_id = ?";
        return Database::fetch($sql, [$id]);
    }

    public function getAllLoans()
    {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.l_id DESC";
        return Database::fetchAll($sql);
    }

    public function updateLoanStatus($id, $status)
    {
        return Database::update($this->table, ['status' => $status], 'l_id = :id', ['id' => $id]);
    }

    /**
     * Get loans ready for disbursement (outstanding status)
     */
    public function getLoansForDisbursement()
    {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.status = 'outstanding' 
                ORDER BY l.l_id DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Check if loan exists and is outstanding
     */
    public function isLoanReadyForDisbursement($id)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE l_id = ? AND status = 'outstanding'";
        $result = Database::fetch($sql, [$id]);
        return $result['count'] > 0;
    }

    /**
     * Update loan to disbursed status
     */
    public function markAsDispursed($id)
    {
        return $this->updateLoanStatus($id, 'disbursed');
    }
}