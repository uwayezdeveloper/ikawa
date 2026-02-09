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
                ORDER BY mu.conversion_factor ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get all active measurement units
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY conversion_factor ASC";
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
        $sql = "INSERT INTO {$this->table} (name, symbol, base_unit_id, conversion_factor, description, status) 
                VALUES (:name, :symbol, :base_unit_id, :conversion_factor, :description, :status)";
        
        Database::query($sql, [
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'base_unit_id' => $data['base_unit_id'] ?: null,
            'conversion_factor' => $data['conversion_factor'] ?? 1,
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
                    conversion_factor = :conversion_factor, description = :description, 
                    status = :status, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = Database::query($sql, [
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'base_unit_id' => $data['base_unit_id'] ?: null,
            'conversion_factor' => $data['conversion_factor'] ?? 1,
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
}
