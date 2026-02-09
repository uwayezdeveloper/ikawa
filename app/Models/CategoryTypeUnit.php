<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * CategoryTypeUnit Model
 * Handles category type and measurement unit assignments
 */
class CategoryTypeUnit extends Model
{
    protected string $table = 'category_type_units';

    /**
     * Get all assignments with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT ctu.*, 
                    ct.name as type_name, 
                    ct.category_id,
                    pc.name as category_name,
                    mu.name as unit_name, 
                    mu.symbol as unit_symbol
                FROM {$this->table} ctu
                JOIN category_types ct ON ctu.category_type_id = ct.id
                JOIN product_categories pc ON ct.category_id = pc.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                ORDER BY pc.name ASC, ct.name ASC, mu.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get assignments grouped by category type
     */
    public function getGroupedByType(): array
    {
        $sql = "SELECT ctu.*, 
                    ct.name as type_name, 
                    ct.category_id,
                    pc.name as category_name,
                    mu.name as unit_name, 
                    mu.symbol as unit_symbol
                FROM {$this->table} ctu
                JOIN category_types ct ON ctu.category_type_id = ct.id
                JOIN product_categories pc ON ct.category_id = pc.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                ORDER BY pc.name ASC, ct.name ASC, mu.name ASC";
        
        $results = Database::fetchAll($sql);
        $grouped = [];
        
        foreach ($results as $row) {
            $typeId = $row['category_type_id'];
            if (!isset($grouped[$typeId])) {
                $grouped[$typeId] = [
                    'type_id' => $typeId,
                    'type_name' => $row['type_name'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'units' => []
                ];
            }
            $grouped[$typeId]['units'][] = [
                'id' => $row['id'],
                'unit_id' => $row['measurement_unit_id'],
                'unit_name' => $row['unit_name'],
                'unit_symbol' => $row['unit_symbol'],
                'is_default' => $row['is_default']
            ];
        }
        
        return array_values($grouped);
    }

    /**
     * Get units assigned to a specific category type
     */
    public function getUnitsByType(int $typeId): array
    {
        $sql = "SELECT ctu.*, mu.name as unit_name, mu.symbol as unit_symbol
                FROM {$this->table} ctu
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ctu.category_type_id = :type_id
                ORDER BY ctu.is_default DESC, mu.name ASC";
        return Database::fetchAll($sql, ['type_id' => $typeId]);
    }

    /**
     * Get the default unit for a category type
     */
    public function getDefaultUnit(int $typeId): ?array
    {
        $sql = "SELECT ctu.*, mu.name as unit_name, mu.symbol as unit_symbol
                FROM {$this->table} ctu
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE ctu.category_type_id = :type_id AND ctu.is_default = 1
                LIMIT 1";
        return Database::fetch($sql, ['type_id' => $typeId]);
    }

    /**
     * Assign a unit to a category type
     */
    public function assignUnit(int $typeId, int $unitId, bool $isDefault = false): int
    {
        // If setting as default, first remove any existing default
        if ($isDefault) {
            $this->clearDefault($typeId);
        }
        
        $sql = "INSERT INTO {$this->table} (category_type_id, measurement_unit_id, is_default) 
                VALUES (:type_id, :unit_id, :is_default)
                ON DUPLICATE KEY UPDATE is_default = :is_default2";
        
        Database::query($sql, [
            'type_id' => $typeId,
            'unit_id' => $unitId,
            'is_default' => $isDefault ? 1 : 0,
            'is_default2' => $isDefault ? 1 : 0
        ]);
        
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Remove a unit assignment
     */
    public function removeUnit(int $typeId, int $unitId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE category_type_id = :type_id AND measurement_unit_id = :unit_id";
        $stmt = Database::query($sql, ['type_id' => $typeId, 'unit_id' => $unitId]);
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
     * Set a unit as default for a category type
     */
    public function setDefault(int $typeId, int $unitId): bool
    {
        // First clear any existing default
        $this->clearDefault($typeId);
        
        // Then set the new default
        $sql = "UPDATE {$this->table} SET is_default = 1 
                WHERE category_type_id = :type_id AND measurement_unit_id = :unit_id";
        $stmt = Database::query($sql, ['type_id' => $typeId, 'unit_id' => $unitId]);
        return $stmt->rowCount() >= 0;
    }

    /**
     * Clear default flag for a category type
     */
    public function clearDefault(int $typeId): bool
    {
        $sql = "UPDATE {$this->table} SET is_default = 0 WHERE category_type_id = :type_id";
        $stmt = Database::query($sql, ['type_id' => $typeId]);
        return $stmt->rowCount() >= 0;
    }

    /**
     * Check if assignment exists
     */
    public function assignmentExists(int $typeId, int $unitId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE category_type_id = :type_id AND measurement_unit_id = :unit_id";
        $result = Database::fetch($sql, ['type_id' => $typeId, 'unit_id' => $unitId]);
        return $result['count'] > 0;
    }

    /**
     * Bulk assign units to a category type
     */
    public function bulkAssign(int $typeId, array $unitIds, ?int $defaultUnitId = null): bool
    {
        // First remove all existing assignments for this type
        $sql = "DELETE FROM {$this->table} WHERE category_type_id = :type_id";
        Database::query($sql, ['type_id' => $typeId]);
        
        // Then add new assignments
        foreach ($unitIds as $unitId) {
            $isDefault = ($defaultUnitId !== null && (int)$unitId === $defaultUnitId);
            $this->assignUnit($typeId, (int)$unitId, $isDefault);
        }
        
        return true;
    }

    /**
     * Get category types without any unit assignments
     */
    public function getTypesWithoutUnits(): array
    {
        $sql = "SELECT ct.*, pc.name as category_name
                FROM category_types ct
                JOIN product_categories pc ON ct.category_id = pc.id
                LEFT JOIN {$this->table} ctu ON ct.id = ctu.category_type_id
                WHERE ctu.id IS NULL AND ct.status = 'active'
                ORDER BY pc.name ASC, ct.name ASC";
        return Database::fetchAll($sql);
    }
}
