<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class LocationType extends Model
{
    protected string $table = 'location_types';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'description',
        'status'
    ];

    /**
     * Get all location types
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active location types only
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Create new location type
     */
    public function create(array $data): int
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
     * Update location type
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                name = :name,
                description = :description,
                status = :status,
                updated_at = NOW()
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
     * Delete location type
     */
    public function delete(int $id): bool
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
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }
}
