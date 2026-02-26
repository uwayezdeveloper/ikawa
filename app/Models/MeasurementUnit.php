<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * MeasurementUnit Model
 * Handles measurement unit data operations with conversion support
 */
class MeasurementUnit extends Model
{
    protected string $table = 'measurement_units';

    /**
     * Get all measurement units
     */
    public function getAll(): array
    {
        $sql = "SELECT mu.*, bu.name as base_unit_name, bu.symbol as base_unit_symbol 
                FROM {$this->table} mu 
                LEFT JOIN {$this->table} bu ON mu.base_unit_id = bu.id 
                ORDER BY mu.rank ASC, mu.conversion_factor ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get all active measurement units
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY rank ASC, conversion_factor ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get base units only (units that can be used as reference)
     */
    public function getBaseUnits(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE base_unit_id IS NULL AND status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find measurement unit by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT mu.*, bu.name as base_unit_name, bu.symbol as base_unit_symbol 
                FROM {$this->table} mu 
                LEFT JOIN {$this->table} bu ON mu.base_unit_id = bu.id 
                WHERE mu.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Create new measurement unit
     */
    public function createUnit(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, symbol, base_unit_id, conversion_factor, rank, step_threshold, description, status) 
                VALUES (:name, :symbol, :base_unit_id, :conversion_factor, :rank, :step_threshold, :description, :status)";
        
        Database::query($sql, [
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'base_unit_id' => $data['base_unit_id'] ?: null,
            'conversion_factor' => $data['conversion_factor'] ?? 1,
            'rank' => $data['rank'] ?? 0,
            'step_threshold' => !empty($data['step_threshold']) ? $data['step_threshold'] : null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
        
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update measurement unit
     */
    public function updateUnit(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET name = :name, symbol = :symbol, base_unit_id = :base_unit_id, 
                    conversion_factor = :conversion_factor, rank = :rank, 
                    step_threshold = :step_threshold, description = :description, 
                    status = :status, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = Database::query($sql, [
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'base_unit_id' => $data['base_unit_id'] ?: null,
            'conversion_factor' => $data['conversion_factor'] ?? 1,
            'rank' => $data['rank'] ?? 0,
            'step_threshold' => !empty($data['step_threshold']) ? $data['step_threshold'] : null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        
        return $stmt->rowCount() >= 0;
    }

    /**
     * Delete measurement unit
     */
    public function deleteUnit(int $id): bool
    {
        // First check if this unit is used as base for other units
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE base_unit_id = :id";
        $result = Database::fetch($sql, ['id' => $id]);
        
        if ($result['count'] > 0) {
            return false; // Cannot delete, it's a base unit for others
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if unit name exists
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Check if symbol exists
     */
    public function symbolExists(string $symbol, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE symbol = :symbol";
        $params = ['symbol' => $symbol];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Convert value from one unit to another
     * 
     * @param float $value The value to convert
     * @param int $fromUnitId Source unit ID
     * @param int $toUnitId Target unit ID
     * @return float|null Converted value or null if conversion not possible
     */
    public function convert(float $value, int $fromUnitId, int $toUnitId): ?float
    {
        $fromUnit = $this->findById($fromUnitId);
        $toUnit = $this->findById($toUnitId);
        
        if (!$fromUnit || !$toUnit) {
            return null;
        }
        
        // Convert to base unit first (grams), then to target unit
        $valueInBase = $value * $fromUnit['conversion_factor'];
        $convertedValue = $valueInBase / $toUnit['conversion_factor'];
        
        return $convertedValue;
    }

    /**
     * Get conversion table for a given value and unit
     * Returns the value in all available units
     */
    public function getConversionTable(float $value, int $fromUnitId): array
    {
        $fromUnit = $this->findById($fromUnitId);
        if (!$fromUnit) {
            return [];
        }
        
        $units = $this->getActive();
        $conversions = [];
        
        // Convert to base first
        $valueInBase = $value * $fromUnit['conversion_factor'];
        
        foreach ($units as $unit) {
            $convertedValue = $valueInBase / $unit['conversion_factor'];
            $conversions[] = [
                'unit_id' => $unit['id'],
                'unit_name' => $unit['name'],
                'symbol' => $unit['symbol'],
                'value' => $convertedValue,
                'formatted' => $this->formatValue($convertedValue, $unit['symbol'])
            ];
        }
        
        return $conversions;
    }

    /**
     * Format value with unit symbol
     */
    public function formatValue(float $value, string $symbol): string
    {
        // Use appropriate decimal places based on value
        if ($value < 0.001) {
            return number_format($value, 10) . ' ' . $symbol;
        } elseif ($value < 1) {
            return number_format($value, 6) . ' ' . $symbol;
        } elseif ($value < 1000) {
            return number_format($value, 2) . ' ' . $symbol;
        } else {
            return number_format($value, 0) . ' ' . $symbol;
        }
    }

    /**
     * Get the optimal display unit based on value and ranking thresholds
     * Automatically steps up to larger unit when value >= step_threshold
     * 
     * @param float $value The value in the current unit
     * @param int $fromUnitId The current unit ID
     * @return array ['value' => float, 'unit' => array, 'formatted' => string]
     */
    public function getOptimalUnit(float $value, int $fromUnitId): array
    {
        $fromUnit = $this->findById($fromUnitId);
        if (!$fromUnit) {
            return ['value' => $value, 'unit' => null, 'formatted' => (string)$value];
        }
        
        // Get all active units ordered by rank
        $units = $this->getActive();
        
        // Convert value to base unit first (e.g., grams)
        $valueInBase = $value * $fromUnit['conversion_factor'];
        
        // Find the optimal unit based on rank and thresholds
        $optimalUnit = null;
        $optimalValue = $value;
        
        // Start from the smallest unit (lowest rank) and work up
        foreach ($units as $unit) {
            $convertedValue = $valueInBase / $unit['conversion_factor'];
            
            // Check if this unit is appropriate
            // Value should be >= 1 (not too small) and less than threshold (not too large)
            if ($convertedValue >= 1) {
                $threshold = $unit['step_threshold'] ?? null;
                
                // If no threshold (highest unit) or value is below threshold, this is good
                if ($threshold === null || $convertedValue < $threshold) {
                    $optimalUnit = $unit;
                    $optimalValue = $convertedValue;
                    break;
                }
            }
        }
        
        // If no optimal found, use the original or the largest unit
        if (!$optimalUnit) {
            // Use the largest unit if value is very large
            $largestUnit = end($units);
            if ($largestUnit) {
                $optimalUnit = $largestUnit;
                $optimalValue = $valueInBase / $largestUnit['conversion_factor'];
            } else {
                $optimalUnit = $fromUnit;
                $optimalValue = $value;
            }
        }
        
        return [
            'value' => $optimalValue,
            'unit' => $optimalUnit,
            'formatted' => $this->formatValue($optimalValue, $optimalUnit['symbol'])
        ];
    }

    /**
     * Get optimal display for a given value, stepping down if value is too small
     * 
     * @param float $value The value
     * @param int $fromUnitId The current unit ID
     * @return array ['value' => float, 'unit' => array, 'formatted' => string]
     */
    public function getSmartDisplay(float $value, int $fromUnitId): array
    {
        $fromUnit = $this->findById($fromUnitId);
        if (!$fromUnit) {
            return ['value' => $value, 'unit' => null, 'formatted' => (string)$value];
        }
        
        // Get all active units ordered by rank (ascending: smallest first)
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY rank DESC";
        $units = Database::fetchAll($sql);
        
        // Convert value to base unit first
        $valueInBase = $value * $fromUnit['conversion_factor'];
        
        // Find the best unit where value is between 1 and threshold
        foreach ($units as $unit) {
            $convertedValue = $valueInBase / $unit['conversion_factor'];
            $threshold = $unit['step_threshold'] ?? 1000;
            
            // Good unit: value is >= 1 and < threshold (or no threshold for largest)
            if ($convertedValue >= 1 && ($unit['step_threshold'] === null || $convertedValue < $threshold)) {
                return [
                    'value' => $convertedValue,
                    'unit' => $unit,
                    'formatted' => $this->formatValue($convertedValue, $unit['symbol'])
                ];
            }
        }
        
        // Fallback: use original unit
        return [
            'value' => $value,
            'unit' => $fromUnit,
            'formatted' => $this->formatValue($value, $fromUnit['symbol'])
        ];
    }

    /**
     * Get units in ranking order
     */
    public function getUnitsInRankOrder(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY rank ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get the next unit in hierarchy (larger unit)
     */
    public function getNextLargerUnit(int $unitId): ?array
    {
        $currentUnit = $this->findById($unitId);
        if (!$currentUnit) {
            return null;
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' AND rank > :rank 
                ORDER BY rank ASC LIMIT 1";
        return Database::fetch($sql, ['rank' => $currentUnit['rank']]);
    }

    /**
     * Get the previous unit in hierarchy (smaller unit)
     */
    public function getNextSmallerUnit(int $unitId): ?array
    {
        $currentUnit = $this->findById($unitId);
        if (!$currentUnit) {
            return null;
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' AND rank < :rank 
                ORDER BY rank DESC LIMIT 1";
        return Database::fetch($sql, ['rank' => $currentUnit['rank']]);
    }

    /**
     * Get category_type_unit info with measurement unit details
     */
    public function getCategoryTypeUnitInfo(int $categoryTypeUnitId): ?array
    {
        $sql = "SELECT ctu.id, ctu.category_type_id, ctu.measurement_unit_id, ctu.is_default,
                       mu.name as unit_name, mu.symbol, mu.conversion_factor, mu.rank, mu.step_threshold
                FROM category_type_units ctu
                JOIN {$this->table} mu ON ctu.measurement_unit_id = mu.id
                WHERE ctu.id = :id";
        return Database::fetch($sql, ['id' => $categoryTypeUnitId]);
    }

    /**
     * Find category_type_unit by category_type and measurement_unit
     */
    public function findCategoryTypeUnit(int $categoryTypeId, int $measurementUnitId): ?array
    {
        $sql = "SELECT ctu.id, ctu.category_type_id, ctu.measurement_unit_id, ctu.is_default
                FROM category_type_units ctu
                WHERE ctu.category_type_id = :category_type_id 
                AND ctu.measurement_unit_id = :measurement_unit_id";
        return Database::fetch($sql, [
            'category_type_id' => $categoryTypeId,
            'measurement_unit_id' => $measurementUnitId
        ]);
    }

    /**
     * Create category_type_unit assignment
     */
    public function createCategoryTypeUnit(int $categoryTypeId, int $measurementUnitId): int
    {
        $sql = "INSERT INTO category_type_units (category_type_id, measurement_unit_id, is_default)
                VALUES (:category_type_id, :measurement_unit_id, 0)";
        Database::query($sql, [
            'category_type_id' => $categoryTypeId,
            'measurement_unit_id' => $measurementUnitId
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Find or create category_type_unit - returns the category_type_unit id
     */
    public function findOrCreateCategoryTypeUnit(int $categoryTypeId, int $measurementUnitId): int
    {
        $existing = $this->findCategoryTypeUnit($categoryTypeId, $measurementUnitId);
        if ($existing) {
            return (int) $existing['id'];
        }
        return $this->createCategoryTypeUnit($categoryTypeId, $measurementUnitId);
    }

    /**
     * Convert quantity from one unit to another using conversion factors
     * 
     * @param float $quantity The quantity to convert
     * @param int $fromUnitId Source measurement unit ID
     * @param int $toUnitId Target measurement unit ID
     * @return float The converted quantity
     */
    public function convertQuantity(float $quantity, int $fromUnitId, int $toUnitId): float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }
        
        $fromUnit = $this->findById($fromUnitId);
        $toUnit = $this->findById($toUnitId);
        
        if (!$fromUnit || !$toUnit) {
            return $quantity;
        }
        
        // Convert to base unit first, then to target unit
        $valueInBase = $quantity * $fromUnit['conversion_factor'];
        return $valueInBase / $toUnit['conversion_factor'];
    }

    /**
     * Check if quantity should be stepped up to next larger unit
     * Returns the target unit info and converted quantity, or null if no step needed
     */
    public function checkAndGetStepUp(float $quantity, int $measurementUnitId): ?array
    {
        $currentUnit = $this->findById($measurementUnitId);
        if (!$currentUnit) {
            return null;
        }
        
        $threshold = $currentUnit['step_threshold'];
        
        // No threshold means it's the largest unit - no step up needed
        if ($threshold === null || $quantity < $threshold) {
            return null;
        }
        
        // Get next larger unit
        $nextUnit = $this->getNextLargerUnit($measurementUnitId);
        if (!$nextUnit) {
            return null;
        }
        
        // Convert quantity to next unit
        $convertedQty = $this->convertQuantity($quantity, $measurementUnitId, $nextUnit['id']);
        
        return [
            'original_quantity' => $quantity,
            'original_unit' => $currentUnit,
            'converted_quantity' => $convertedQty,
            'target_unit' => $nextUnit
        ];
    }
}