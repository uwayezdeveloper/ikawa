<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class PaymentMode extends Model
{
    protected string $table = 'payment_modes';

    /**
     * Get all payment modes
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active payment modes
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find payment mode by ID
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
     * Create new payment mode
     */
    public function createMode(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, description, status) VALUES (:name, :description, :status)";
        
        Database::query($sql, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Update payment mode
     */
    public function updateMode(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET name = :name, description = :description, status = :status WHERE id = :id";
        
        Database::query($sql, [
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);

        return true;
    }

    /**
     * Delete payment mode
     */
    public function deleteMode(int $id): bool
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
}