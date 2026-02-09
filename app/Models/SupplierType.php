<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class SupplierType extends Model
{
    protected string $table = 'supplier_types';

    /**
     * Get all supplier types
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get all supplier types with identifier count
     */
    public function getAllWithIdentifierCount(): array
    {
        $sql = "SELECT st.*, 
                COUNT(sti.id) as identifier_count,
                GROUP_CONCAT(sti.name SEPARATOR ', ') as identifier_names
                FROM {$this->table} st
                LEFT JOIN supplier_type_identifiers sti ON st.id = sti.supplier_type_id AND sti.status = 'active'
                GROUP BY st.id
                ORDER BY st.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active supplier types
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find supplier type by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Find supplier type by name
     */
    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name";
        return Database::fetch($sql, ['name' => $name]);
    }

    /**
     * Check if name exists (excluding a specific ID)
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];

        if ($excludeId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Create new supplier type
     */
    public function createType(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, description, status) VALUES (:name, :description, :status)";
        
        Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update supplier type
     */
    public function updateType(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET name = :name, description = :description, status = :status WHERE id = :id";
        
        Database::query($sql, [
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);

        return true;
    }

    /**
     * Delete supplier type
     */
    public function deleteType(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Get supplier type statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM {$this->table}";
        return Database::fetch($sql);
    }
}
