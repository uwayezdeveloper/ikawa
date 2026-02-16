<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class CurrencyType extends Model
{
    protected string $table = 'tbl_currency_type';
    protected string $primaryKey = 'currency_id';

    /**
     * Get all currency types
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY curre_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get currency by ID
     */
    public function getById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::fetch($sql, ['id' => $id]);
    }
}
