<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class WorkerLoanPayment extends Model
{
    protected string $table = 'tbl_worker_loan_payment';
    protected string $primaryKey = 'p_id';

    protected array $fillable = [
        'pay_account',
        'loan_id',
        'payed_amount',
        'due_date',
        'description',
        'debited_account'
    ];

    /**
     * Create worker loan payment record
     */
    public function createPayment($data)
    {
        $paymentData = [
            'pay_account' => $data['pay_account'],
            'loan_id' => $data['loan_id'],
            'payed_amount' => $data['payed_amount'],
            'due_date' => $data['due_date'] ?? date('Y-m-d H:i:s'),
            'description' => $data['description'] ?? null,
            'debited_account' => $data['debited_account']
        ];
        
        $result = Database::insert($this->table, $paymentData);
        
        // Check and update loan status if payment was successful
        if ($result) {
            $workerLoan = new \App\Models\WorkerLoan();
            $workerLoan->checkAndUpdatePaidStatus($data['loan_id']);
        }
        
        return $result;
    }

    /**
     * Get all payments with loan and account details
     */
    public function getAllWithDetails()
    {
        $sql = "SELECT p.*, 
                l.request_amount, l.payed_amount as total_paid,
                u.first_name, u.last_name, u.email,
                pa.account_name as pay_account_name, pa.account_number as pay_account_number,
                da.account_name as debited_account_name, da.account_number as debited_account_number
                FROM {$this->table} p
                LEFT JOIN tbl_worker_loan l ON p.loan_id = l.l_id
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN accounts pa ON p.pay_account = pa.id
                LEFT JOIN accounts da ON p.debited_account = da.id
                ORDER BY p.p_id DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get payments by loan ID
     */
    public function getByLoanId($loanId)
    {
        $sql = "SELECT p.*, 
                pa.account_name as pay_account_name, pa.account_number as pay_account_number,
                da.account_name as debited_account_name, da.account_number as debited_account_number
                FROM {$this->table} p
                LEFT JOIN accounts pa ON p.pay_account = pa.id
                LEFT JOIN accounts da ON p.debited_account = da.id
                WHERE p.loan_id = ?
                ORDER BY p.p_id DESC";
        return Database::fetchAll($sql, [$loanId]);
    }

    /**
     * Get payment by ID with details
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT p.*, 
                l.request_amount, l.payed_amount as total_paid, l.description as loan_description,
                u.first_name, u.last_name, u.email,
                pa.account_name as pay_account_name, pa.account_number as pay_account_number,
                da.account_name as debited_account_name, da.account_number as debited_account_number
                FROM {$this->table} p
                LEFT JOIN tbl_worker_loan l ON p.loan_id = l.l_id
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN accounts pa ON p.pay_account = pa.id
                LEFT JOIN accounts da ON p.debited_account = da.id
                WHERE p.p_id = ?";
        return Database::fetch($sql, [$id]);
    }

    /**
     * Update payment
     */
    public function updatePayment($id, $data)
    {
        return Database::update($this->table, $data, 'p_id = :id', ['id' => $id]);
    }

    /**
     * Delete payment
     */
    public function deletePayment($id)
    {
        return Database::delete($this->table, 'p_id = :id', ['id' => $id]);
    }

    /**
     * Get total payments for a loan
     */
    public function getTotalPaymentsByLoan($loanId)
    {
        $sql = "SELECT SUM(payed_amount) as total FROM {$this->table} WHERE loan_id = ?";
        $result = Database::fetch($sql, [$loanId]);
        return $result['total'] ?? 0;
    }
}