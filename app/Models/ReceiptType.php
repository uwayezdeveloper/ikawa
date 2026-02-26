<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ReceiptType extends Model
{
    protected string $table = 'tbl_receipttype';
    protected string $primaryKey = 'rec_id';
    protected array $fillable = ['rec_name', 'rec_desc', 'sts'];

    /**
     * Get all active receipt types
     */
    public function getActiveReceiptTypes(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE sts = 1 ORDER BY rec_name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Find receipt type by ID
     */
    public function findById($id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $result = Database::fetchAll($sql, ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }
}