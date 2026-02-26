<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * ProductCategory Model
 * Handles product category data operations
 */
class ProductCategory extends Model
{
    protected string $table = 'product_categories';

    /**
     * Get all product categories
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get all active product categories
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find product category by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Create new product category
     */
    public function createCategory(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, description, status) VALUES (:name, :description, :status)";
        
        Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
        
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update product category
     */
    public function updateCategory(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET name = :name, description = :description, status = :status, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        
        return $stmt->rowCount() >= 0;
    }

    /**
     * Delete product category
     */
    public function deleteCategory(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if name exists (for validation)
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
        return (int) $result['count'] > 0;
    }
}