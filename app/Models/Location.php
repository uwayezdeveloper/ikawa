<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Location extends Model
{
    protected string $table = 'locations';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'description',
        'location_type_id',
        'status'
    ];

    /**
     * Get all locations with type name
     */
    public function getAllWithType(): array
    {
        $sql = "SELECT l.*, lt.name as type_name 
                FROM {$this->table} l 
                LEFT JOIN location_types lt ON l.location_type_id = lt.id 
                ORDER BY l.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active locations only
     */
    public function getActive(): array
    {
        $sql = "SELECT l.*, lt.name as type_name 
                FROM {$this->table} l 
                LEFT JOIN location_types lt ON l.location_type_id = lt.id 
                WHERE l.status = 'active' 
                ORDER BY l.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find by ID with type
     */
    public function findByIdWithType(int $id): ?array
    {
        $sql = "SELECT l.*, lt.name as type_name 
                FROM {$this->table} l 
                LEFT JOIN location_types lt ON l.location_type_id = lt.id 
                WHERE l.id = :id";
        return Database::fetch($sql, ['id' => $id]);
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
     * Create new location
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, description, location_type_id, status) 
                VALUES (:name, :description, :location_type_id, :status)";
        Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'location_type_id' => $data['location_type_id'],
            'status' => $data['status'] ?? 'active'
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update location
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                name = :name,
                description = :description,
                location_type_id = :location_type_id,
                status = :status,
                updated_at = NOW()
                WHERE id = :id";
        
        $stmt = Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'location_type_id' => $data['location_type_id'],
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        return $stmt->rowCount() >= 0;
    }

    /**
     * Delete location
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

    /**
     * Get locations by type
     */
    public function getByType(int $typeId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE location_type_id = :type_id ORDER BY name ASC";
        return Database::fetchAll($sql, ['type_id' => $typeId]);
    }
}
