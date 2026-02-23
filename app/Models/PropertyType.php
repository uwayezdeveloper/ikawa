<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class PropertyType extends Model
{
    protected string $table = 'tbl_property_type';
    protected string $primaryKey = 'type_id';

    protected array $fillable = [
        'type_name',
        'description',
        'status'
    ];

    /**
     * Get all property types
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY type_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active property types only
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY type_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type_id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Find by name
     */
    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type_name = :name";
        return Database::fetch($sql, ['name' => $name]);
    }

    /**
     * Create new property type
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (type_name, description, status) 
                VALUES (:type_name, :description, :status)";
        Database::query($sql, [
            'type_name' => $data['type_name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status']
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update property type
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET type_name = :type_name, 
                    description = :description, 
                    status = :status
                WHERE type_id = :id";
        
        $result = Database::query($sql, [
            'id' => $id,
            'type_name' => $data['type_name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status']
        ]);
        
        return $result !== false;
    }

    /**
     * Delete property type (soft delete by updating status)
     */
    public function delete(int $id): bool
    {
        // Instead of hard delete, we'll set status to 0 (inactive)
        $sql = "UPDATE {$this->table} SET status = 0 WHERE type_id = :id";
        $result = Database::query($sql, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Hard delete property type (permanently remove from database)
     */
    public function hardDelete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE type_id = :id";
        $result = Database::query($sql, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Check if property type name exists
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE type_name = :name";
        $params = ['name' => $name];
        
        if ($excludeId !== null) {
            $sql .= " AND type_id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get property type statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_types,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_types,
                    COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_types
                FROM {$this->table}";
        return Database::fetch($sql) ?? [];
    }

    /**
     * Search property types by name or description
     */
    public function search(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE type_name LIKE :query 
                   OR description LIKE :query
                ORDER BY type_name ASC";
        
        return Database::fetchAll($sql, ['query' => '%' . $query . '%']);
    }

    /**
     * Get property types with property count
     */
    public function getWithPropertyCount(): array
    {
        $sql = "SELECT pt.*, 
                       COUNT(p.property_id) as property_count
                FROM {$this->table} pt
                LEFT JOIN tbl_properties p ON pt.type_id = p.type_id AND p.status = 1
                GROUP BY pt.type_id
                ORDER BY pt.type_name ASC";
        return Database::fetchAll($sql);
    }
}