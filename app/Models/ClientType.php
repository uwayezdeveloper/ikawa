<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ClientType extends Model
{
    protected string $table = 'client_types';

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    public function getById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    public function getByIdentifier($identifier): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE identifier = :identifier";
        return Database::fetch($sql, ['identifier' => $identifier]);
    }

    public function createType(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (name, description, identifier, status)
                VALUES (:name, :description, :identifier, :status)";

        try {
            $stmt = Database::query($sql, [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'identifier' => $data['identifier'],
                'status' => $data['status'] ?? 'active'
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateType(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET name = :name, description = :description, identifier = :identifier, status = :status
                WHERE id = :id";

        try {
            $stmt = Database::query($sql, [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'identifier' => $data['identifier'],
                'status' => $data['status'] ?? 'active',
                'id' => $id
            ]);
            return true; // UPDATE returns 0 rows affected if no changes, but it's still successful
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteType(int $id): array
    {
        // Check if any clients use this type
        $sql = "SELECT COUNT(*) as count FROM clients WHERE client_type = (SELECT identifier FROM {$this->table} WHERE id = :id)";
        $result = Database::fetch($sql, ['id' => $id]);
        $count = $result['count'] ?? 0;

        if ($count > 0) {
            return ['success' => false, 'message' => 'Cannot delete type that is in use by ' . $count . ' client(s)'];
        }

        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = Database::query($sql, ['id' => $id]);
            $success = $stmt->rowCount() > 0;
            return ['success' => $success, 'message' => $success ? 'Deleted successfully' : 'Failed to delete'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()];
        }
    }

    public function toggleStatus($id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END
                WHERE id = :id";
        try {
            Database::query($sql, ['id' => $id]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function identifierExists($identifier, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE identifier = :identifier";
        $params = ['identifier' => $identifier];

        if ($excludeId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    public function getStats(): array
    {
        $stats = [];

        // Total types
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = Database::fetch($sql);
        $stats['total'] = $result['count'] ?? 0;

        // Active types
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'";
        $result = Database::fetch($sql);
        $stats['active'] = $result['count'] ?? 0;

        // Types with clients
        $sql = "SELECT COUNT(DISTINCT ct.id) as count 
                FROM {$this->table} ct 
                INNER JOIN clients c ON c.client_type = ct.identifier";
        $result = Database::fetch($sql);
        $stats['in_use'] = $result['count'] ?? 0;

        return $stats;
    }

    // ==================== IDENTIFIER METHODS ====================

    public function getIdentifiers(int $typeId): array
    {
        $sql = "SELECT * FROM client_type_identifiers WHERE client_type_id = :type_id ORDER BY name ASC";
        return Database::fetchAll($sql, ['type_id' => $typeId]);
    }

    public function addIdentifier(int $typeId, array $data): bool
    {
        $sql = "INSERT INTO client_type_identifiers (client_type_id, name, is_required, status) 
                VALUES (:type_id, :name, :is_required, :status)";
        try {
            $stmt = Database::query($sql, [
                'type_id' => $typeId,
                'name' => $data['name'],
                'is_required' => $data['is_required'] ?? 0,
                'status' => $data['status'] ?? 'active'
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateIdentifier(int $id, array $data): bool
    {
        $sql = "UPDATE client_type_identifiers SET name = :name, is_required = :is_required, status = :status WHERE id = :id";
        try {
            Database::query($sql, [
                'id' => $id,
                'name' => $data['name'],
                'is_required' => $data['is_required'] ?? 0,
                'status' => $data['status'] ?? 'active'
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteIdentifier(int $id): bool
    {
        $sql = "DELETE FROM client_type_identifiers WHERE id = :id";
        try {
            $stmt = Database::query($sql, ['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAllWithIdentifiers(): array
    {
        $sql = "SELECT ct.*, 
                    COUNT(cti.id) as identifier_count,
                    GROUP_CONCAT(cti.name SEPARATOR ', ') as identifier_names
                FROM {$this->table} ct
                LEFT JOIN client_type_identifiers cti ON ct.id = cti.client_type_id AND cti.status = 'active'
                GROUP BY ct.id
                ORDER BY ct.name ASC";
        return Database::fetchAll($sql);
    }
}
