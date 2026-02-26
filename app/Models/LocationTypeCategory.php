<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * LocationTypeCategory Model
 * Handles location type and product category assignments
 */
class LocationTypeCategory extends Model
{
    protected string $table = 'location_type_categories';

    /**
     * Get all assignments with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT ltc.*, 
                    lt.name as location_type_name,
                    pc.name as category_name
                FROM {$this->table} ltc
                JOIN location_types lt ON ltc.location_type_id = lt.id
                JOIN product_categories pc ON ltc.product_category_id = pc.id
                ORDER BY lt.name ASC, pc.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get assignments grouped by location type
     */
    public function getGroupedByLocationType(): array
    {
        $sql = "SELECT ltc.*, 
                    lt.name as location_type_name,
                    lt.description as location_type_description,
                    pc.name as category_name,
                    pc.id as category_id
                FROM {$this->table} ltc
                JOIN location_types lt ON ltc.location_type_id = lt.id
                JOIN product_categories pc ON ltc.product_category_id = pc.id
                ORDER BY lt.name ASC, pc.name ASC";
        
        $results = Database::fetchAll($sql);
        $grouped = [];
        
        foreach ($results as $row) {
            $locationTypeId = $row['location_type_id'];
            if (!isset($grouped[$locationTypeId])) {
                $grouped[$locationTypeId] = [
                    'location_type_id' => $locationTypeId,
                    'location_type_name' => $row['location_type_name'],
                    'location_type_description' => $row['location_type_description'],
                    'categories' => []
                ];
            }
            $grouped[$locationTypeId]['categories'][] = [
                'id' => $row['id'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name']
            ];
        }
        
        return array_values($grouped);
    }

    /**
     * Get categories assigned to a specific location type
     */
    public function getCategoriesByLocationType(int $locationTypeId): array
    {
        $sql = "SELECT ltc.*, pc.name as category_name, pc.description as category_description
                FROM {$this->table} ltc
                JOIN product_categories pc ON ltc.product_category_id = pc.id
                WHERE ltc.location_type_id = :location_type_id
                ORDER BY pc.name ASC";
        return Database::fetchAll($sql, ['location_type_id' => $locationTypeId]);
    }

    /**
     * Get location types that receive a specific category
     */
    public function getLocationTypesByCategory(int $categoryId): array
    {
        $sql = "SELECT ltc.*, lt.name as location_type_name
                FROM {$this->table} ltc
                JOIN location_types lt ON ltc.location_type_id = lt.id
                WHERE ltc.product_category_id = :category_id
                ORDER BY lt.name ASC";
        return Database::fetchAll($sql, ['category_id' => $categoryId]);
    }

    /**
     * Assign a category to a location type
     */
    public function assignCategory(int $locationTypeId, int $categoryId): int
    {
        $sql = "INSERT INTO {$this->table} (location_type_id, product_category_id) 
                VALUES (:location_type_id, :category_id)
                ON DUPLICATE KEY UPDATE location_type_id = location_type_id";
        
        Database::query($sql, [
            'location_type_id' => $locationTypeId,
            'category_id' => $categoryId
        ]);
        
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Remove a category assignment
     */
    public function removeCategory(int $locationTypeId, int $categoryId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE location_type_id = :location_type_id AND product_category_id = :category_id";
        $stmt = Database::query($sql, ['location_type_id' => $locationTypeId, 'category_id' => $categoryId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Remove assignment by ID
     */
    public function removeById(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if assignment exists
     */
    public function assignmentExists(int $locationTypeId, int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE location_type_id = :location_type_id AND product_category_id = :category_id";
        $result = Database::fetch($sql, ['location_type_id' => $locationTypeId, 'category_id' => $categoryId]);
        return $result['count'] > 0;
    }

    /**
     * Bulk assign categories to a location type
     */
    public function bulkAssign(int $locationTypeId, array $categoryIds): bool
    {
        // First remove all existing assignments for this location type
        $sql = "DELETE FROM {$this->table} WHERE location_type_id = :location_type_id";
        Database::query($sql, ['location_type_id' => $locationTypeId]);
        
        // Then add new assignments
        foreach ($categoryIds as $categoryId) {
            if (!empty($categoryId)) {
                $this->assignCategory($locationTypeId, (int)$categoryId);
            }
        }
        
        return true;
    }

    /**
     * Get location types without any category assignments
     */
    public function getLocationTypesWithoutCategories(): array
    {
        $sql = "SELECT lt.*
                FROM location_types lt
                LEFT JOIN {$this->table} ltc ON lt.id = ltc.location_type_id
                WHERE ltc.id IS NULL AND lt.status = 'active'
                ORDER BY lt.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get categories not assigned to any location type
     */
    public function getCategoriesWithoutLocationTypes(): array
    {
        $sql = "SELECT pc.*
                FROM product_categories pc
                LEFT JOIN {$this->table} ltc ON pc.id = ltc.product_category_id
                WHERE ltc.id IS NULL AND pc.status = 'active'
                ORDER BY pc.name ASC";
        return Database::fetchAll($sql);
    }
}
