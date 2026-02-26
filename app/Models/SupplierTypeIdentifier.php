<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class SupplierTypeIdentifier extends Model
{
    protected string $table = 'supplier_type_identifiers';

    /**
     * Get all identifiers for a supplier type
     */
    public function getBySupplierType(int $supplierTypeId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_type_id = :supplier_type_id ORDER BY name ASC";
        return Database::fetchAll($sql, ['supplier_type_id' => $supplierTypeId]);
    }

    /**
     * Get active identifiers for a supplier type
     */
    public function getActiveBySupplierType(int $supplierTypeId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_type_id = :supplier_type_id AND status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql, ['supplier_type_id' => $supplierTypeId]);
    }

    /**
     * Find identifier by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $result = Database::fetch($sql, ['id' => $id]);
        return $result ?: null;
    }

    /**
     * Check if identifier name exists for a supplier type
     */
    public function nameExists(string $name, int $supplierTypeId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name AND supplier_type_id = :supplier_type_id";
        $params = ['name' => $name, 'supplier_type_id' => $supplierTypeId];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Create a new identifier
     */
    public function createIdentifier(array $data): int|false
    {
        $sql = "INSERT INTO {$this->table} (supplier_type_id, name, is_required, status) 
                VALUES (:supplier_type_id, :name, :is_required, :status)";
        
        $result = Database::query($sql, [
            'supplier_type_id' => $data['supplier_type_id'],
            'name' => $data['name'],
            'is_required' => $data['is_required'] ?? 0,
            'status' => $data['status'] ?? 'active'
        ]);

        return $result ? Database::getInstance()->lastInsertId() : false;
    }

    /**
     * Update an identifier
     */
    public function updateIdentifier(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET name = :name, is_required = :is_required, status = :status WHERE id = :id";
        
        $result = Database::query($sql, [
            'id' => $id,
            'name' => $data['name'],
            'is_required' => $data['is_required'] ?? 0,
            'status' => $data['status'] ?? 'active'
        ]);
        
        return $result ? true : false;
    }

    /**
     * Delete an identifier
     */
    public function deleteIdentifier(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return Database::query($sql, ['id' => $id]) ? true : false;
    }

    /**
     * Toggle identifier status
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = :id";
        return Database::query($sql, ['id' => $id]) ? true : false;
    }
}
