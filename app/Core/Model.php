<?php
namespace App\Core;

/**
 * Base Model Class
 * Provides common database operations
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = ['password'];

    /**
     * Find by primary key
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $result = Database::fetch($sql, ['id' => $id]);
        
        return $result ? $this->hideFields($result) : null;
    }

    /**
     * Find by column value
     */
    public function findBy(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = :value LIMIT 1";
        $result = Database::fetch($sql, ['value' => $value]);
        
        return $result ? $this->hideFields($result) : null;
    }

    /**
     * Find by column value with password (for authentication)
     */
    public function findByWithPassword(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = :value LIMIT 1";
        return Database::fetch($sql, ['value' => $value]);
    }

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT $cols FROM {$this->table}";
        $results = Database::fetchAll($sql);
        
        return array_map([$this, 'hideFields'], $results);
    }

    /**
     * Get records with pagination
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "$column = :$column";
                $params[$column] = $value;
            }
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $where";
        $total = Database::fetch($countSql, $params)['total'];
        
        // Get paginated data
        $sql = "SELECT * FROM {$this->table} $where ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $data = Database::fetchAll($sql, $params);
        
        return [
            'data' => array_map([$this, 'hideFields'], $data),
            'total' => (int) $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Create a new record
     */
    public function create(array $data): int
    {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        return Database::insert($this->table, $filteredData);
    }

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool
    {
        // Filter only fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($filteredData)) {
            return false;
        }
        
        $affected = Database::update($this->table, $filteredData, "{$this->primaryKey} = :id", ['id' => $id]);
        
        return $affected > 0;
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        $affected = Database::delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]);
        
        return $affected > 0;
    }

    /**
     * Soft delete (if table has deleted_at column)
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if record exists
     */
    public function exists(string $column, $value, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE $column = :value";
        $params = ['value' => $value];
        
        if ($excludeId !== null) {
            $sql .= " AND {$this->primaryKey} != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        
        return $result['count'] > 0;
    }

    /**
     * Hide sensitive fields
     */
    protected function hideFields(array $data): array
    {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }

    /**
     * Get where clause
     */
    public function where(array $conditions): array
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereClauses[] = "$column = :$column";
            $params[$column] = $value;
        }
        
        $where = implode(' AND ', $whereClauses);
        $sql = "SELECT * FROM {$this->table} WHERE $where";
        
        $results = Database::fetchAll($sql, $params);
        
        return array_map([$this, 'hideFields'], $results);
    }
}
