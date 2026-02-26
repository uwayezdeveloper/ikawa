<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class LoanDisbursement extends Model
{
    protected string $table = 'tbl_loan_disbursment';
    protected string $primaryKey = 'dis_id';

    protected array $fillable = [
        'l_id',
        'pay_accounts',
        'amount',
        'charges',
        'duedate',
        'worker_account'
    ];

    /**
     * Create loan disbursement record
     */
    public function createDisbursement($data)
    {
        $disbursementData = [
            'l_id' => $data['l_id'],
            'pay_accounts' => json_encode($data['pay_accounts']), // Store multiple accounts as JSON
            'amount' => $data['amount'],
            'charges' => $data['charges'] ?? 0,
            'worker_account' => $data['worker_account'] ?? null,
            'duedate' => $data['duedate'] ?? date('Y-m-d H:i:s')
        ];
        
        return Database::insert($this->table, $disbursementData);
    }

    /**
     * Get disbursement by loan ID
     */
    public function getByLoanId($loanId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE l_id = ?";
        return Database::fetch($sql, [$loanId]);
    }

    /**
     * Get all disbursements with loan details
     */
    public function getAllWithLoanDetails()
    {
        $sql = "SELECT d.*, l.request_amount, l.status as loan_status,
                u.first_name, u.last_name, u.email 
                FROM {$this->table} d
                LEFT JOIN tbl_worker_loan l ON d.l_id = l.l_id
                LEFT JOIN users u ON l.user_id = u.id
                ORDER BY d.dis_id DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get disbursement by ID with loan details
     */
    public function getByIdWithDetails($id)
    {
        $sql = "SELECT d.*, l.request_amount, l.status as loan_status, l.description,
                u.first_name, u.last_name, u.email 
                FROM {$this->table} d
                LEFT JOIN tbl_worker_loan l ON d.l_id = l.l_id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE d.dis_id = ?";
        return Database::fetch($sql, [$id]);
    }

    /**
     * Update disbursement
     */
    public function updateDisbursement($id, $data)
    {
        return Database::update($this->table, $data, 'dis_id = :id', ['id' => $id]);
    }
}