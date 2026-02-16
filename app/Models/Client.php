<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Client extends Model
{
    protected string $table = 'clients';
    protected string $primaryKey = 'id';

    /**
     * Get all active clients
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get client by ID
     */
    public function getById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::fetch($sql, ['id' => $id]);
    }
}
