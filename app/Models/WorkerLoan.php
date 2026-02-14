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

    /**
     * Get active loans with remaining balance for payments
     */
    public function getActiveLoansWithBalance()
    {
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email,
                (l.request_amount - l.payed_amount) as remaining_balance
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.status = 'disbursed' 
                AND l.payed_amount < l.request_amount
                ORDER BY l.l_id DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Update paid amount for a loan
     */
    public function updatePaidAmount($id, $paidAmount)
    {
        return Database::update($this->table, ['payed_amount' => $paidAmount], 'l_id = :id', ['id' => $id]);
    }

    /**
     * Update loan status
     */
    public function updateStatus($id, $status)
    {
        return Database::update($this->table, ['status' => $status], 'l_id = :id', ['id' => $id]);
    }

    /**
     * Get loan by ID (simple method)
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE l_id = ?";
        return Database::fetch($sql, [$id]);
    }

    /**
     * Get all workers who have loans for statement dropdown
     */
    public function getAllWorkersWithLoans()
    {
        $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.email 
                FROM {$this->table} l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE u.id IS NOT NULL
                ORDER BY u.first_name, u.last_name";
        return Database::fetchAll($sql);
    }

    /**
     * Get worker loan statement (all transactions for a specific worker)
     * This includes loan disbursements (debits) and payments (credits)
     */
    public function getWorkerLoanStatement($userId, $startDate = null, $endDate = null)
    {
        $params = [];
        $loanDateFilter = "";
        $paymentDateFilter = "";
        
        if ($startDate && $endDate) {
            // Build loan query with parameters
            $loanDateFilter = "AND (
                (ld.duedate IS NOT NULL AND DATE(ld.duedate) BETWEEN ? AND ?) OR 
                (ld.duedate IS NULL AND DATE(l.created_at) BETWEEN ? AND ?)
            )";
            $loanParams = [$userId, $startDate, $endDate, $startDate, $endDate];
            
            // Build payment query with parameters  
            $paymentDateFilter = "AND DATE(p.due_date) BETWEEN ? AND ?";
            $paymentParams = [$userId, $startDate, $endDate];
        } else {
            $loanParams = [$userId];
            $paymentParams = [$userId];
        }
        
        $sql = "SELECT 
                    'loan' as transaction_type,
                    COALESCE(ld.duedate, l.created_at) as transaction_date,
                    CONCAT('Loan Disbursement - ', l.description) as memo,
                    l.request_amount as debit_amount,
                    0 as credit_amount,
                    l.l_id as reference_id,
                    l.status as loan_status
                FROM {$this->table} l
                LEFT JOIN tbl_loan_disbursment ld ON l.l_id = ld.l_id
                WHERE l.user_id = ? AND LOWER(l.status) IN ('disbursed', 'paid') {$loanDateFilter}
                
                UNION ALL
                
                SELECT 
                    'payment' as transaction_type,
                    p.due_date as transaction_date,
                    COALESCE(p.description, 'Loan Payment') as memo,
                    0 as debit_amount,
                    p.payed_amount as credit_amount,
                    p.p_id as reference_id,
                    l.status as loan_status
                FROM tbl_worker_loan_payment p
                LEFT JOIN {$this->table} l ON p.loan_id = l.l_id
                WHERE l.user_id = ? {$paymentDateFilter}
                
                ORDER BY transaction_date ASC";
                
        // Combine parameters in correct order
        $allParams = array_merge($loanParams, $paymentParams);
        
        return Database::fetchAll($sql, $allParams);
    }

    /**
     * Calculate running balance for worker loan statement
     */
    public function calculateStatementBalance($userId, $startDate = null, $endDate = null)
    {
        $transactions = $this->getWorkerLoanStatement($userId, $startDate, $endDate);
        $balance = 0;
        
        foreach ($transactions as &$transaction) {
            if ($transaction['transaction_type'] === 'loan') {
                // Loan disbursement increases debt (debit)
                $balance += $transaction['debit_amount'];
            } else {
                // Payment reduces debt (credit)
                $balance -= $transaction['credit_amount'];
            }
            $transaction['running_balance'] = $balance;
        }
        
        return $transactions;
    }
    
    /**
     * Update loan status to 'paid' when fully paid
     */
    public function checkAndUpdatePaidStatus($loanId)
    {
        $loan = $this->getById($loanId);
        if (!$loan) {
            return false;
        }
        
        // Get total payments for this loan
        $sql = "SELECT COALESCE(SUM(payed_amount), 0) as total_paid 
                FROM tbl_worker_loan_payment 
                WHERE loan_id = ?";
        $result = Database::fetch($sql, [$loanId]);
        $totalPaid = $result['total_paid'] ?? 0;
        
        // If total paid equals or exceeds request amount, mark as paid
        if ($totalPaid >= $loan['request_amount'] && $loan['status'] !== 'paid') {
            $this->updateStatus($loanId, 'paid');
            return true;
        }
        
        return false;
    }

    /**
     * Debug method to see all loans for a user (for troubleshooting)
     */
    public function debugUserLoans($userId)
    {
        $sql = "SELECT l.*, ld.duedate as disbursement_date 
                FROM {$this->table} l 
                LEFT JOIN tbl_loan_disbursment ld ON l.l_id = ld.l_id 
                WHERE l.user_id = ?
                ORDER BY l.l_id";
        return Database::fetchAll($sql, [$userId]);
    }

    /**
     * Debug method to see all payments for a user (for troubleshooting)
     */
    public function debugUserPayments($userId)
    {
        $sql = "SELECT p.*, l.user_id 
                FROM tbl_worker_loan_payment p 
                LEFT JOIN {$this->table} l ON p.loan_id = l.l_id 
                WHERE l.user_id = ?
                ORDER BY p.due_date";
        return Database::fetchAll($sql, [$userId]);
    }
}