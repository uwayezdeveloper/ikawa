<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Property extends Model
{
    protected string $table = 'tbl_properties';
    protected string $primaryKey = 'property_id';

    protected array $fillable = [
        'property_name',
        'location_id',
        'type_id',
        'value_amount',
        'status',
        'description',
        'created_by'
    ];

    /**
     * Get all properties with location name and property type
     */
    public function getAllWithLocation(): array
    {
        $sql = "SELECT p.*, l.name as location_name, pt.type_name as property_type_name 
                FROM {$this->table} p 
                LEFT JOIN locations l ON p.location_id = l.id 
                LEFT JOIN tbl_property_type pt ON p.type_id = pt.type_id
                ORDER BY p.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active properties only
     */
    public function getActive(): array
    {
        $sql = "SELECT p.*, l.name as location_name, pt.type_name as property_type_name 
                FROM {$this->table} p 
                LEFT JOIN locations l ON p.location_id = l.id 
                LEFT JOIN tbl_property_type pt ON p.type_id = pt.type_id
                WHERE p.status = 1 
                ORDER BY p.property_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find by ID with location and property type
     */
    public function findByIdWithLocation(int $id): ?array
    {
        $sql = "SELECT p.*, l.name as location_name, pt.type_name as property_type_name 
                FROM {$this->table} p 
                LEFT JOIN locations l ON p.location_id = l.id 
                LEFT JOIN tbl_property_type pt ON p.type_id = pt.type_id
                WHERE p.property_id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE property_id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Get properties by location
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT p.*, l.name as location_name 
                FROM {$this->table} p 
                LEFT JOIN locations l ON p.location_id = l.id 
                WHERE p.location_id = :location_id 
                ORDER BY p.property_name ASC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get properties by created user
     */
    public function getByCreatedBy(int $userId): array
    {
        $sql = "SELECT p.*, l.name as location_name 
                FROM {$this->table} p 
                LEFT JOIN locations l ON p.location_id = l.id 
                WHERE p.created_by = :user_id 
                ORDER BY p.created_at DESC";
        return Database::fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * Create new property
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (property_name, location_id, type_id, value_amount, status, description, created_by, created_at) 
                VALUES (:property_name, :location_id, :type_id, :value_amount, :status, :description, :created_by, NOW())";
        Database::query($sql, [
            'property_name' => $data['property_name'],
            'location_id' => $data['location_id'],
            'type_id' => $data['type_id'],
            'value_amount' => $data['value_amount'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'created_by' => $data['created_by']
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update property
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET property_name = :property_name, 
                    location_id = :location_id, 
                    type_id = :type_id, 
                    value_amount = :value_amount, 
                    status = :status, 
                    description = :description,
                    created_at = created_at
                WHERE property_id = :id";
        
        $result = Database::query($sql, [
            'id' => $id,
            'property_name' => $data['property_name'],
            'location_id' => $data['location_id'],
            'type_id' => $data['type_id'],
            'value_amount' => $data['value_amount'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null
        ]);
        
        return $result !== false;
    }

    /**
     * Delete property (soft delete by updating status)
     */
    public function delete(int $id): bool
    {
        // Instead of hard delete, we'll set status to 0 (inactive)
        $sql = "UPDATE {$this->table} SET status = 0 WHERE property_id = :id";
        $result = Database::query($sql, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Hard delete property (permanently remove from database)
     */
    public function hardDelete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE property_id = :id";
        $result = Database::query($sql, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Get property statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_properties,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_properties,
                    COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_properties,
                    SUM(CASE WHEN status = 1 THEN value_amount ELSE 0 END) as total_active_value,
                    AVG(CASE WHEN status = 1 THEN value_amount END) as average_property_value
                FROM {$this->table}";
        return Database::fetch($sql) ?? [];
    }

    /**
     * Search properties by name or location
     */
    public function search(string $query): array
    {
        $sql = "SELECT p.*, l.name as location_name 
                FROM {$this->table} p 
                LEFT JOIN locations l ON p.location_id = l.id 
                WHERE p.property_name LIKE :query 
                   OR l.name LIKE :query 
                   OR p.description LIKE :query
                ORDER BY p.property_name ASC";
        
        return Database::fetchAll($sql, ['query' => '%' . $query . '%']);
    }
}