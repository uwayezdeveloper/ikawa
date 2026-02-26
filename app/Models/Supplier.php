<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Supplier extends Model
{
    protected string $table = 'suppliers';

    /**
     * Get all suppliers with their type
     */
    public function getAll(): array
    {
        $sql = "SELECT s.*, st.name as supplier_type_name 
                FROM {$this->table} s 
                LEFT JOIN supplier_types st ON s.supplier_type_id = st.id 
                ORDER BY s.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active suppliers
     */
    public function getActive(): array
    {
        $sql = "SELECT s.*, st.name as supplier_type_name 
                FROM {$this->table} s 
                LEFT JOIN supplier_types st ON s.supplier_type_id = st.id 
                WHERE s.status = 'active' 
                ORDER BY s.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find supplier by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT s.*, st.name as supplier_type_name 
                FROM {$this->table} s 
                LEFT JOIN supplier_types st ON s.supplier_type_id = st.id 
                WHERE s.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Check if name exists
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
        return $result['count'] > 0;
    }

    /**
     * Create new supplier
     */
    public function createSupplier(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, supplier_type_id, phone, email, address, contact_person, contact_phone, status) 
                VALUES (:name, :supplier_type_id, :phone, :email, :address, :contact_person, :contact_phone, :status)";
        
        Database::query($sql, [
            'name' => $data['name'],
            'supplier_type_id' => $data['supplier_type_id'],
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'contact_person' => $data['contact_person'] ?: null,
            'contact_phone' => $data['contact_phone'] ?: null,
            'status' => $data['status'] ?? 'active'
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update supplier
     */
    public function updateSupplier(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                name = :name, 
                supplier_type_id = :supplier_type_id, 
                phone = :phone, 
                email = :email, 
                address = :address, 
                contact_person = :contact_person, 
                contact_phone = :contact_phone, 
                status = :status 
                WHERE id = :id";
        
        Database::query($sql, [
            'id' => $id,
            'name' => $data['name'],
            'supplier_type_id' => $data['supplier_type_id'],
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'contact_person' => $data['contact_person'] ?: null,
            'contact_phone' => $data['contact_phone'] ?: null,
            'status' => $data['status'] ?? 'active'
        ]);

        return true;
    }

    /**
     * Delete supplier
     */
    public function deleteSupplier(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Save supplier identifiers
     */
    public function saveIdentifiers(int $supplierId, array $identifiers): bool
    {
        // Delete existing identifiers
        $sql = "DELETE FROM supplier_identifiers WHERE supplier_id = :supplier_id";
        Database::query($sql, ['supplier_id' => $supplierId]);

        // Insert new identifiers
        foreach ($identifiers as $identifierId => $value) {
            if (!empty($value)) {
                $sql = "INSERT INTO supplier_identifiers (supplier_id, identifier_id, value) VALUES (:supplier_id, :identifier_id, :value)";
                Database::query($sql, [
                    'supplier_id' => $supplierId,
                    'identifier_id' => $identifierId,
                    'value' => $value
                ]);
            }
        }

        return true;
    }

    /**
     * Get supplier identifiers
     */
    public function getIdentifiers(int $supplierId): array
    {
        $sql = "SELECT si.*, sti.name as identifier_name, sti.is_required
                FROM supplier_identifiers si
                JOIN supplier_type_identifiers sti ON si.identifier_id = sti.id
                WHERE si.supplier_id = :supplier_id";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get supplier identifiers as key-value pairs (identifier_id => value)
     */
    public function getIdentifiersMap(int $supplierId): array
    {
        $identifiers = $this->getIdentifiers($supplierId);
        $map = [];
        foreach ($identifiers as $identifier) {
            $map[$identifier['identifier_id']] = $identifier['value'];
        }
        return $map;
    }

    /**
     * Find supplier by location name
     * Since suppliers table doesn't have location_id, we match by name
     */
    public function findByLocationId(int $locationId): ?array
    {
        // Get location name first
        $location = Database::fetch(
            "SELECT name FROM locations WHERE id = :id LIMIT 1",
            ['id' => $locationId]
        );
        
        if (!$location) {
            return null;
        }
        
        // Find supplier with matching name
        $sql = "SELECT * FROM {$this->table} WHERE name = :name LIMIT 1";
        return Database::fetch($sql, ['name' => $location['name']]);
    }

    /**
     * Find or create a supplier for a location (station)
     * Used when receiving transfers to auto-create station as supplier
     */
    public function findOrCreateByLocation(int $locationId): int
    {
        // Check if supplier exists for this location
        $existing = $this->findByLocationId($locationId);
        if ($existing) {
            return $existing['id'];
        }

        // Get location details
        $location = Database::fetch(
            "SELECT l.*, lt.name as location_type_name 
             FROM locations l 
             LEFT JOIN location_types lt ON l.location_type_id = lt.id 
             WHERE l.id = :id",
            ['id' => $locationId]
        );

        if (!$location) {
            return 0;
        }

        // Get Station supplier type (ID 3 by default, or find it)
        $stationType = Database::fetch(
            "SELECT id FROM supplier_types WHERE name = 'Station' LIMIT 1"
        );
        $supplierTypeId = $stationType ? $stationType['id'] : 2; // Fallback to general Supplier type

        // Create new supplier for this station (without location_id column)
        $sql = "INSERT INTO {$this->table} (name, supplier_type_id, address, status) 
                VALUES (:name, :supplier_type_id, :address, 'active')";
        
        Database::query($sql, [
            'name' => $location['name'],
            'supplier_type_id' => $supplierTypeId,
            'address' => $location['address'] ?? null
        ]);

        return Database::getInstance()->lastInsertId();
    }
}
