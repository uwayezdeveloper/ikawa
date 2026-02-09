<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class WorkerLoan extends Model
{
    protected string $table = 'tbl_worker_loan';
    protected string $primaryKey = 'l_id';
    protected array $fillable = [
        'user_id', 'request_amount', 'payed_amount', 'description', 'status'
    ];

    /**
     * Get paginated worker loans with user information
     */
    public function getPaginatedLoans($page = 1, $perPage = 20, $search = '', $userId = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    wl.*, 
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM {$this->table} wl
                LEFT JOIN users u ON wl.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        // Filter by user if provided (for regular users to see only their loans)
        if ($userId) {
            $sql .= " AND wl.user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        if (!empty($search)) {
            $sql .= " AND (u.first_name LIKE :search OR u.last_name LIKE :search 
                          OR u.email LIKE :search OR wl.description LIKE :search 
                          OR wl.status LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        // Count total records
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
        $totalResult = $this->db->query($countSql, $params);
        $total = $totalResult->fetchColumn();
        
        // Add pagination
        $sql .= " ORDER BY wl.l_id DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $result = $this->db->query($sql, $params);
        $data = $result->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get loan statistics
     */
    public function getLoanStats($userId = null): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_loans,
                    SUM(CASE WHEN status = 'outstanding' THEN 1 ELSE 0 END) as outstanding_loans,
                    SUM(CASE WHEN status = 'disbursed' THEN 1 ELSE 0 END) as disbursed_loans,
                    SUM(request_amount) as total_requested,
                    SUM(payed_amount) as total_disbursed
                FROM {$this->table}";
        
        $params = [];
        
        if ($userId) {
            $sql .= " WHERE user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $result = $this->db->query($sql, $params);
        return $result->fetch() ?: [];
    }

    /**
     * Get user's loan history
     */
    public function getUserLoanHistory($userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY l_id DESC";
        
        $result = $this->db->query($sql, ['user_id' => $userId]);
        return $result->fetchAll();
    }

    /**
     * Get loan by ID
     */
    public function findById($id)
    {
        $sql = "SELECT 
                    wl.*, 
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM {$this->table} wl
                LEFT JOIN users u ON wl.user_id = u.id
                WHERE wl.l_id = :id";
        
        $result = $this->db->query($sql, ['id' => $id]);
        return $result->fetch();
    }

    /**
     * Create a new loan request
     */
    public function createLoan($data): bool
    {
        $sql = "INSERT INTO {$this->table} (user_id, request_amount, payed_amount, description, status) 
                VALUES (:user_id, :request_amount, :payed_amount, :description, :status)";
        
        $params = [
            'user_id' => $data['user_id'],
            'request_amount' => $data['request_amount'],
            'payed_amount' => $data['payed_amount'] ?? 0,
            'description' => $data['description'],
            'status' => $data['status'] ?? 'pending'
        ];
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log("Error creating loan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update loan status and payed amount
     */
    public function updateLoan($id, $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params['status'] = $data['status'];
        }
        
        if (isset($data['payed_amount'])) {
            $fields[] = "payed_amount = :payed_amount";
            $params['payed_amount'] = $data['payed_amount'];
        }
        
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params['description'] = $data['description'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE l_id = :id";
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log("Error updating loan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete loan
     */
    public function deleteLoan($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE l_id = :id";
        
        try {
            $this->db->query($sql, ['id' => $id]);
            return true;
        } catch (\Exception $e) {
            error_log("Error deleting loan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has pending loans
     */
    public function hasPendingLoans($userId): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = :user_id AND status = 'pending'";
        
        $result = $this->db->query($sql, ['user_id' => $userId]);
        return $result->fetchColumn() > 0;
    }

    /**
     * Get outstanding loan amount for user
     */
    public function getOutstandingAmount($userId): float
    {
        $sql = "SELECT SUM(request_amount - payed_amount) as outstanding 
                FROM {$this->table} 
                WHERE user_id = :user_id AND status = 'outstanding'";
        
        $result = $this->db->query($sql, ['user_id' => $userId]);
        return (float) ($result->fetchColumn() ?? 0);
    }
}