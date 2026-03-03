<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class InnerCategoryType extends Model
{
    protected string $table = 'tbl_inner_categorytypes';
    protected string $primaryKey = 'inner_id';

    protected array $fillable = [
        'categ_id',
        'type_id',
        'inner_name',
        'description',
        'status'
    ];

    /**
     * Get all inner category types with related category and type information
     */
    public function getAllWithRelations(): array
    {
        $queries = [
            "SELECT 
                ict.*, 
                pc.categ_name as category_name,
                ct.type_name as category_type_name
             FROM {$this->table} ict
             LEFT JOIN tbl_product_categories pc ON ict.categ_id = pc.categ_id
             LEFT JOIN tbl_category_types ct ON ict.type_id = ct.type_id
             ORDER BY pc.categ_name, ct.type_name, ict.inner_name",

            "SELECT 
                ict.*, 
                pc.name as category_name,
                ct.name as category_type_name
             FROM {$this->table} ict
             LEFT JOIN product_categories pc ON ict.categ_id = pc.id
             LEFT JOIN category_types ct ON ict.type_id = ct.id
             ORDER BY pc.name, ct.name, ict.inner_name",

            "SELECT 
                ict.*, 
                pc.categ_name as category_name,
                ct.name as category_type_name
             FROM {$this->table} ict
             LEFT JOIN tbl_product_categories pc ON ict.categ_id = pc.categ_id
             LEFT JOIN category_types ct ON ict.type_id = ct.id
             ORDER BY pc.categ_name, ct.name, ict.inner_name",

            "SELECT 
                ict.*, 
                pc.name as category_name,
                ct.type_name as category_type_name
             FROM {$this->table} ict
             LEFT JOIN product_categories pc ON ict.categ_id = pc.id
             LEFT JOIN tbl_category_types ct ON ict.type_id = ct.type_id
             ORDER BY pc.name, ct.type_name, ict.inner_name",
        ];

        foreach ($queries as $sql) {
            try {
                return Database::fetchAll($sql);
            } catch (\Throwable $e) {
            }
        }

        return [];
    }

    /**
     * Get inner category types by category ID
     */
    public function getByCategoryId(int $categoryId): array
    {
        $sql = "SELECT 
                    ict.*,
                    ct.type_name as category_type_name
                FROM {$this->table} ict
                LEFT JOIN tbl_category_types ct ON ict.type_id = ct.type_id
                WHERE ict.categ_id = :category_id 
                AND ict.status = 1
                ORDER BY ct.type_name, ict.inner_name";
        
        return Database::fetchAll($sql, ['category_id' => $categoryId]);
    }

    /**
     * Get inner category types by category ID and type ID
     */
    public function getByCategoryAndTypeId(int $categoryId, int $typeId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE categ_id = :category_id 
                AND type_id = :type_id 
                AND status = 1
                ORDER BY inner_name";
        
        return Database::fetchAll($sql, [
            'category_id' => $categoryId,
            'type_id' => $typeId
        ]);
    }

    /**
     * Get active inner category types
     */
    public function getActive(): array
    {
        $sql = "SELECT 
                    ict.*,
                    pc.categ_name as category_name,
                    ct.type_name as category_type_name
                FROM {$this->table} ict
                LEFT JOIN tbl_product_categories pc ON ict.categ_id = pc.categ_id
                LEFT JOIN tbl_category_types ct ON ict.type_id = ct.type_id
                WHERE ict.status = 1
                ORDER BY pc.categ_name, ct.type_name, ict.inner_name";
        
        return Database::fetchAll($sql);
    }

    /**
     * Check if inner category name exists for a specific category and type
     */
    public function nameExistsForCategoryType(string $innerName, int $categoryId, int $typeId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE LOWER(inner_name) = LOWER(:inner_name) 
                AND categ_id = :category_id 
                AND type_id = :type_id";
        
        $params = [
            'inner_name' => trim($innerName),
            'category_id' => $categoryId,
            'type_id' => $typeId
        ];

        if ($excludeId !== null) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return (int) ($result['count'] ?? 0) > 0;
    }

    /**
     * Get inner category type with full details
     */
    public function getWithRelations(int $innerId): ?array
    {
        $queries = [
            "SELECT 
                ict.*,
                pc.categ_name as category_name,
                ct.type_name as category_type_name
             FROM {$this->table} ict
             LEFT JOIN tbl_product_categories pc ON ict.categ_id = pc.categ_id
             LEFT JOIN tbl_category_types ct ON ict.type_id = ct.type_id
             WHERE ict.{$this->primaryKey} = :inner_id",

            "SELECT 
                ict.*,
                pc.name as category_name,
                ct.name as category_type_name
             FROM {$this->table} ict
             LEFT JOIN product_categories pc ON ict.categ_id = pc.id
             LEFT JOIN category_types ct ON ict.type_id = ct.id
             WHERE ict.{$this->primaryKey} = :inner_id",
        ];

        foreach ($queries as $sql) {
            try {
                $row = Database::fetch($sql, ['inner_id' => $innerId]);
                if ($row !== null) {
                    return $row;
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    /**
     * Toggle status (activate/deactivate)
     */
    public function toggleStatus(int $innerId): bool
    {
        $current = $this->getWithRelations($innerId);
        if (!$current) {
            return false;
        }

        $newStatus = $current['status'] == 1 ? 0 : 1;
        return $this->update($innerId, ['status' => $newStatus]);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive,
                    COUNT(DISTINCT categ_id) as categories_with_inner,
                    COUNT(DISTINCT type_id) as types_with_inner
                FROM {$this->table}";
        
        return Database::fetch($sql) ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'categories_with_inner' => 0,
            'types_with_inner' => 0
        ];
    }

    /**
     * Search inner category types
     */
    public function search(string $searchTerm): array
    {
        $sql = "SELECT 
                    ict.*,
                    pc.categ_name as category_name,
                    ct.type_name as category_type_name
                FROM {$this->table} ict
                LEFT JOIN tbl_product_categories pc ON ict.categ_id = pc.categ_id
                LEFT JOIN tbl_category_types ct ON ict.type_id = ct.type_id
                WHERE (ict.inner_name LIKE :search 
                    OR ict.description LIKE :search
                    OR pc.categ_name LIKE :search
                    OR ct.type_name LIKE :search)
                ORDER BY pc.categ_name, ct.type_name, ict.inner_name";
        
        return Database::fetchAll($sql, ['search' => '%' . trim($searchTerm) . '%']);
    }
}