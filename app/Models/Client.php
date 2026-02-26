<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Client extends Model
{
    protected string $table = 'clients';

    protected array $fillable = [
        'name',
        'client_type',
        'phone',
        'email',
        'address',
        'contact_person',
        'contact_phone',
        'tin_number',
        'notes',
        'status'
    ];

    /**
     * Get all clients
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active clients
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get clients by type
     */
    public function getByType(string $type): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE client_type = :type AND status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql, ['type' => $type]);
    }

    /**
     * Find client by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
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
     * Create new client
     */
    public function createClient(array $data): int
    {
        return Database::insert($this->table, [
            'name' => $data['name'],
            'client_type' => $data['client_type'] ?? 'individual',
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'contact_person' => $data['contact_person'] ?: null,
            'contact_phone' => $data['contact_phone'] ?: null,
            'tin_number' => $data['tin_number'] ?: null,
            'notes' => $data['notes'] ?: null,
            'status' => $data['status'] ?? 'active'
        ]);
    }

    /**
     * Update client
     */
    public function updateClient(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                name = :name, 
                client_type = :client_type, 
                phone = :phone, 
                email = :email, 
                address = :address, 
                contact_person = :contact_person, 
                contact_phone = :contact_phone, 
                tin_number = :tin_number,
                notes = :notes,
                status = :status 
                WHERE id = :id";

        try {
            Database::query($sql, [
                'id' => $id,
                'name' => $data['name'],
                'client_type' => $data['client_type'] ?? 'individual',
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'address' => $data['address'] ?: null,
                'contact_person' => $data['contact_person'] ?: null,
                'contact_phone' => $data['contact_phone'] ?: null,
                'tin_number' => $data['tin_number'] ?: null,
                'notes' => $data['notes'] ?: null,
                'status' => $data['status'] ?? 'active'
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete client
     */
    public function deleteClient(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = Database::query($sql, ['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Toggle client status
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET 
                status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END 
                WHERE id = :id";
        try {
            Database::query($sql, ['id' => $id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Search clients
     */
    public function search(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE :query 
                OR phone LIKE :query 
                OR email LIKE :query 
                OR contact_person LIKE :query
                ORDER BY name ASC";
        return Database::fetchAll($sql, ['query' => "%{$query}%"]);
    }

    /**
     * Get client statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM {$this->table}";
        return Database::fetch($sql);
    }

    /**
     * Get all active client types
     */
    public function getClientTypes(): array
    {
        $sql = "SELECT * FROM client_types WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get all clients with type info
     */
    public function getAllWithTypes(): array
    {
        $sql = "SELECT c.*, ct.name as type_name 
                FROM {$this->table} c 
                LEFT JOIN client_types ct ON c.client_type = ct.identifier 
                ORDER BY c.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get stats by client type
     */
    public function getStatsByType(): array
    {
        $sql = "SELECT ct.identifier, ct.name, COUNT(c.id) as count 
                FROM client_types ct 
                LEFT JOIN clients c ON c.client_type = ct.identifier 
                WHERE ct.status = 'active'
                GROUP BY ct.id, ct.identifier, ct.name 
                ORDER BY ct.name ASC";
        return Database::fetchAll($sql);
    }

    // ==================== IDENTIFIER METHODS ====================

    /**
     * Get identifiers for a client type
     */
    public function getTypeIdentifiers(int $typeId): array
    {
        $sql = "SELECT * FROM client_type_identifiers 
                WHERE client_type_id = :type_id AND status = 'active' 
                ORDER BY name ASC";
        return Database::fetchAll($sql, ['type_id' => $typeId]);
    }

    /**
     * Get identifiers by type identifier (string)
     */
    public function getIdentifiersByTypeIdentifier(string $typeIdentifier): array
    {
        $sql = "SELECT cti.* FROM client_type_identifiers cti
                INNER JOIN client_types ct ON cti.client_type_id = ct.id
                WHERE ct.identifier = :identifier AND cti.status = 'active'
                ORDER BY cti.name ASC";
        return Database::fetchAll($sql, ['identifier' => $typeIdentifier]);
    }

    /**
     * Save identifier values for a client
     */
    public function saveIdentifierValues(int $clientId, array $identifierValues): void
    {
        // Delete existing values for this client
        $sql = "DELETE FROM client_identifier_values WHERE client_id = :client_id";
        Database::query($sql, ['client_id' => $clientId]);

        // Insert new values
        foreach ($identifierValues as $identifierId => $value) {
            if (!empty($value)) {
                $sql = "INSERT INTO client_identifier_values (client_id, identifier_id, value) 
                        VALUES (:client_id, :identifier_id, :value)";
                Database::query($sql, [
                    'client_id' => $clientId,
                    'identifier_id' => $identifierId,
                    'value' => $value
                ]);
            }
        }
    }

    /**
     * Get identifier values for a client
     */
    public function getIdentifierValues(int $clientId): array
    {
        $sql = "SELECT civ.*, cti.name as identifier_name, cti.is_required
                FROM client_identifier_values civ
                INNER JOIN client_type_identifiers cti ON civ.identifier_id = cti.id
                WHERE civ.client_id = :client_id";
        return Database::fetchAll($sql, ['client_id' => $clientId]);
    }

    /**
     * Get all client types with their identifiers (for dropdown)
     */
    public function getClientTypesWithIdentifiers(): array
    {
        $types = $this->getClientTypes();
        foreach ($types as &$type) {
            $type['identifiers'] = $this->getTypeIdentifiers($type['id']);
        }
        return $types;
    }
}
