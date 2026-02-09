<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * CategoryType Model
 * Handles category type data operations
 */
class CategoryType extends Model
{
    protected string $table = 'category_types';

    /**
     * Get all category types with their parent category
     */
    public function getAll(): array
    {
        $sql = "SELECT ct.*, pc.name as category_name 
                FROM {$this->table} ct 
                LEFT JOIN product_categories pc ON ct.category_id = pc.id 
                ORDER BY pc.name ASC, ct.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get all active category types
     */
    public function getActive(): array
    {
        $sql = "SELECT ct.*, pc.name as category_name 
                FROM {$this->table} ct 
                LEFT JOIN product_categories pc ON ct.category_id = pc.id 
                WHERE ct.status = 'active' 
                ORDER BY pc.name ASC, ct.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get category types by category ID
     */
    public function getByCategory(int $categoryId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = :category_id ORDER BY name ASC";
        return Database::fetchAll($sql, ['category_id' => $categoryId]);
    }

    /**
     * Find category type by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT ct.*, pc.name as category_name 
                FROM {$this->table} ct 
                LEFT JOIN product_categories pc ON ct.category_id = pc.id 
                WHERE ct.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Create new category type
     */
    public function createType(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (category_id, name, description, status) 
                VALUES (:category_id, :name, :description, :status)";
        
        Database::query($sql, [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
        
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update category type
     */
    public function updateType(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET category_id = :category_id, name = :name, description = :description, status = :status, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = Database::query($sql, [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        
        return $stmt->rowCount() >= 0;
    }

    /**
     * Delete category type
     */
    public function deleteType(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if category type name exists (for validation)
     */
    public function nameExists(string $name, int $categoryId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name AND category_id = :category_id";
        $params = ['name' => $name, 'category_id' => $categoryId];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }
}
