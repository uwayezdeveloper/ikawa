<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ExpenseConsumer extends Model
{
    protected string $table = 'tbl_expenseconsumer';
    protected string $primaryKey = 'cons_id';
    protected array $fillable = ['cons_name', 'phone', 'sts'];

    /**
     * Get all expense consumers with pagination
     */
    public function getPaginatedConsumers($page = 1, $perPage = 20, $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (cons_name LIKE :search OR phone LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $result = Database::fetchAll($countSql, $params);
        $total = $result[0]['COUNT(*)'];
        
        // Get paginated results
        $sql .= " ORDER BY cons_name ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $consumers = Database::fetchAll($sql, $params);
        
        return [
            'data' => $consumers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get all active expense consumers
     */
    public function getActiveConsumers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE sts = 1 ORDER BY cons_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Create new expense consumer
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (cons_name, phone, sts) VALUES (:cons_name, :phone, :sts)";
        
        $params = [
            'cons_name' => $data['cons_name'],
            'phone' => $data['phone'],
            'sts' => $data['sts'] ?? 1
        ];
        
        Database::query($sql, $params);
        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update expense consumer
     */
    public function update($id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET cons_name = :cons_name, phone = :phone, sts = :sts WHERE {$this->primaryKey} = :id";
        
        $params = [
            'id' => $id,
            'cons_name' => $data['cons_name'],
            'phone' => $data['phone'],
            'sts' => $data['sts'] ?? 1
        ];
        
        return Database::query($sql, $params) !== false;
    }

    /**
     * Delete expense consumer
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::query($sql, ['id' => $id]) !== false;
    }

    /**
     * Find expense consumer by ID
     */
    public function findById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $result = Database::fetchAll($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Check if consumer name exists (for validation)
     */
    public function consumerNameExists(string $name, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE cons_name = :name";
        $params = ['name' => $name];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = Database::fetchAll($sql, $params);
        return !empty($result) && $result[0]['count'] > 0;
    }

    /**
     * Check if phone exists (for validation)
     */
    public function phoneExists(string $phone, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE phone = :phone";
        $params = ['phone' => $phone];
        
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
        $consumer = $this->findById($id);
        if (!$consumer) {
            return false;
        }
        
        $newStatus = $consumer['sts'] == 1 ? 0 : 1;
        return $this->update($id, array_merge($consumer, ['sts' => $newStatus]));
    }

    /**
     * Search consumers by name or phone
     */
    public function searchConsumers(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (cons_name LIKE :query OR phone LIKE :query) AND sts = 1 
                ORDER BY cons_name ASC LIMIT 10";
        return Database::fetchAll($sql, ['query' => '%' . $query . '%']);
    }
}