<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Certification extends Model
{
    protected string $table = 'tbl_certification';
    protected string $primaryKey = 'cert_id';

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY cert_name ASC";
        return Database::fetchAll($sql);
    }

    public function createCategory(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (cert_name, cert_desc, status) VALUES (:cert_name, :cert_desc, :status)";

        Database::query($sql, [
            'cert_name' => $data['cert_name'],
            'cert_desc' => $data['cert_desc'] ?? null,
            'status' => $data['status'] ?? 1
        ]);

        return (int) Database::getInstance()->lastInsertId();
    }

    public function updateCategory(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table}
                SET cert_name = :cert_name, cert_desc = :cert_desc, status = :status
                WHERE cert_id = :cert_id";

        $stmt = Database::query($sql, [
            'cert_name' => $data['cert_name'],
            'cert_desc' => $data['cert_desc'] ?? null,
            'status' => $data['status'] ?? 1,
            'cert_id' => $id
        ]);

        return $stmt->rowCount() >= 0;
    }

    public function deleteCategory(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE cert_id = :cert_id";
        $stmt = Database::query($sql, ['cert_id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS count FROM {$this->table} WHERE cert_name = :cert_name";
        $params = ['cert_name' => $name];

        if ($excludeId) {
            $sql .= " AND cert_id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return (int) ($result['count'] ?? 0) > 0;
    }
}
