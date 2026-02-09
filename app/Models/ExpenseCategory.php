<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ExpenseCategory extends Model
{
    protected string $table = 'tbl_expensecategories';
    protected string $primaryKey = 'categ_id';
    protected array $fillable = ['categ_name', 'description', 'status'];

    /**
     * Get all expense categories with pagination
     */
    public function getPaginatedCategories($page = 1, $perPage = 20, $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (categ_name LIKE :search OR description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $result = Database::fetchAll($countSql, $params);
        $total = $result[0]['COUNT(*)'];
        
        // Get paginated results
        $sql .= " ORDER BY categ_name ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $categories = Database::fetchAll($sql, $params);
        
        return [
            'data' => $categories,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get all active expense categories
     */
    public function getActiveCategories(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY categ_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Create new expense category
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (categ_name, description, status) VALUES (:categ_name, :description, :status)";
        
        $params = [
            'categ_name' => $data['categ_name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 1
        ];
        
        Database::query($sql, $params);
        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update expense category
     */
    public function update($id, $data): bool
    {
        $sql = "UPDATE {$this->table} SET categ_name = :categ_name, description = :description, status = :status WHERE {$this->primaryKey} = :id";
        
        $params = [
            'id' => $id,
            'categ_name' => $data['categ_name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 1
        ];
        
        return Database::query($sql, $params) !== false;
    }

    /**
     * Delete expense category
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::query($sql, ['id' => $id]) !== false;
    }

    /**
     * Find expense category by ID
     */
    public function findById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $result = Database::fetchAll($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Check if category name exists (for validation)
     */
    public function categoryNameExists($name, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE categ_name = :name";
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
        $category = $this->findById($id);
        if (!$category) {
            return false;
        }
        
        $newStatus = $category['status'] == 1 ? 0 : 1;
        return $this->update($id, array_merge($category, ['status' => $newStatus]));
    }
}