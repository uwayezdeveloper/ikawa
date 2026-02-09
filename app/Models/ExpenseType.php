<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ExpenseType extends Model
{
    protected string $table = 'tbl_expenses';
    protected string $primaryKey = 'expense_id';
    protected array $fillable = ['categ_id', 'expense_name', 'description', 'expense_status'];

    /**
     * Get all expense types with pagination and category details
     */
    public function getPaginatedExpenseTypes($page = 1, $perPage = 20, $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT e.*, ec.categ_name 
                FROM {$this->table} e 
                LEFT JOIN tbl_expensecategories ec ON e.categ_id = ec.categ_id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (e.expense_name LIKE :search OR e.description LIKE :search OR ec.categ_name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        // Get total count
        $countSql = str_replace('SELECT e.*, ec.categ_name', 'SELECT COUNT(*)', $sql);
        $result = Database::fetchAll($countSql, $params);
        $total = $result[0]['COUNT(*)'];
        
        // Get paginated results
        $sql .= " ORDER BY e.expense_name ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $expenseTypes = Database::fetchAll($sql, $params);
        
        return [
            'data' => $expenseTypes,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get all active expense types
     */
    public function getActiveExpenseTypes(): array
    {
        $sql = "SELECT e.*, ec.categ_name 
                FROM {$this->table} e 
                LEFT JOIN tbl_expensecategories ec ON e.categ_id = ec.categ_id 
                WHERE e.expense_status = 1 
                ORDER BY e.expense_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Create new expense type
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (categ_id, expense_name, description, expense_status) 
                VALUES (:categ_id, :expense_name, :description, :expense_status)";
        
        $params = [
            'categ_id' => $data['categ_id'] ?? null,
            'expense_name' => $data['expense_name'],
            'description' => $data['description'] ?? null,
            'expense_status' => $data['expense_status'] ?? 1
        ];
        
        Database::query($sql, $params);
        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update expense type
     */
    public function update($id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET categ_id = :categ_id, expense_name = :expense_name, description = :description, expense_status = :expense_status 
                WHERE {$this->primaryKey} = :id";
        
        $params = [
            'id' => $id,
            'categ_id' => $data['categ_id'] ?? null,
            'expense_name' => $data['expense_name'],
            'description' => $data['description'] ?? null,
            'expense_status' => $data['expense_status'] ?? 1
        ];
        
        return Database::query($sql, $params) !== false;
    }

    /**
     * Delete expense type
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::query($sql, ['id' => $id]) !== false;
    }

    /**
     * Find expense type by ID with category details
     */
    public function findById($id): ?array
    {
        $sql = "SELECT e.*, ec.categ_name 
                FROM {$this->table} e 
                LEFT JOIN tbl_expensecategories ec ON e.categ_id = ec.categ_id 
                WHERE e.{$this->primaryKey} = :id";
        $result = Database::fetchAll($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Check if expense type name exists (for validation)
     */
    public function expenseNameExists(string $name, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE expense_name = :name";
        $params = ['name' => $name];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = Database::fetchAll($sql, $params);
        return !empty($result) && $result[0]['count'] > 0;
    }

    /**
     * Toggle status
     */
    public function toggleStatus($id): bool
    {
        $expenseType = $this->findById($id);
        if (!$expenseType) {
            return false;
        }
        
        $newStatus = $expenseType['expense_status'] == 1 ? 0 : 1;
        return $this->update($id, array_merge($expenseType, ['expense_status' => $newStatus]));
    }

    /**
     * Get expense types by category
     */
    public function getByCategory($categoryId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE categ_id = :categ_id AND expense_status = 1 ORDER BY expense_name ASC";
        return Database::fetchAll($sql, ['categ_id' => $categoryId]);
    }
}