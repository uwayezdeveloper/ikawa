<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class SourceOfIncome extends Model
{
    protected string $table = 'tbl_source_of_income';
    protected string $primaryKey = 'in_id';
    protected array $fillable = ['in_name', 'in_descr', 'in_status'];

    /**
     * Get all source of income with pagination
     */
    public function getPaginatedSources($page = 1, $perPage = 20, $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (in_name LIKE :search OR in_descr LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $result = Database::fetchAll($countSql, $params);
        $total = $result[0]['COUNT(*)'];
        
        // Get paginated results
        $sql .= " ORDER BY in_name ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $sources = Database::fetchAll($sql, $params);
        
        return [
            'data' => $sources,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get all active source of income
     */
    public function getActiveSources(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE in_status = 1 ORDER BY in_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Create new source of income
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (in_name, in_descr, in_status) VALUES (:in_name, :in_descr, :in_status)";
        
        $params = [
            'in_name' => $data['in_name'],
            'in_descr' => $data['in_descr'] ?? null,
            'in_status' => $data['in_status'] ?? 1
        ];
        
        Database::query($sql, $params);
        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update source of income
     */
    public function update($id, $data): bool
    {
        $sql = "UPDATE {$this->table} SET in_name = :in_name, in_descr = :in_descr, in_status = :in_status WHERE {$this->primaryKey} = :id";
        
        $params = [
            'id' => $id,
            'in_name' => $data['in_name'],
            'in_descr' => $data['in_descr'] ?? null,
            'in_status' => $data['in_status'] ?? 1
        ];
        
        return Database::query($sql, $params) !== false;
    }

    /**
     * Delete source of income
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::query($sql, ['id' => $id]) !== false;
    }

    /**
     * Find source of income by ID
     */
    public function findById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $result = Database::fetchAll($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Check if source name exists (for validation)
     */
    public function sourceNameExists($name, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE in_name = :name";
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
        $source = $this->findById($id);
        if (!$source) {
            return false;
        }
        
        $newStatus = $source['in_status'] == 1 ? 0 : 1;
        return $this->update($id, array_merge($source, ['in_status' => $newStatus]));
    }
}
