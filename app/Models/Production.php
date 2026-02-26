<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Production extends Model
{
    protected string $table = 'production';
    
    protected array $fillable = [
        'production_number',
        'location_id',
        'supplier_id',
        'input_category_type_unit_id',
        'input_quantity',
        'input_unit_price',
        'output_category_type_unit_id',
        'output_quantity',
        'output_unit_price',
        'production_date',
        'notes',
        'status',
        'created_by',
        'completed_by',
        'completed_at'
    ];

    /**
     * Get all production records with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT p.*, 
                l.name as location_name,
                s.name as supplier_name,
                pc_in.name as input_category_name,
                ct_in.name as input_type_name,
                mu_in.name as input_unit_name,
                mu_in.symbol as input_unit_symbol,
                pc_out.name as output_category_name,
                ct_out.name as output_type_name,
                mu_out.name as output_unit_name,
                mu_out.symbol as output_unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(uc.first_name, ' ', uc.last_name) as completed_by_name
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN category_type_units ctu_in ON p.input_category_type_unit_id = ctu_in.id
                LEFT JOIN category_types ct_in ON ctu_in.category_type_id = ct_in.id
                LEFT JOIN product_categories pc_in ON ct_in.category_id = pc_in.id
                LEFT JOIN measurement_units mu_in ON ctu_in.measurement_unit_id = mu_in.id
                LEFT JOIN category_type_units ctu_out ON p.output_category_type_unit_id = ctu_out.id
                LEFT JOIN category_types ct_out ON ctu_out.category_type_id = ct_out.id
                LEFT JOIN product_categories pc_out ON ct_out.category_id = pc_out.id
                LEFT JOIN measurement_units mu_out ON ctu_out.measurement_unit_id = mu_out.id
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN users uc ON p.completed_by = uc.id
                ORDER BY p.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get production records by location
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT p.*, 
                l.name as location_name,
                s.name as supplier_name,
                pc_in.name as input_category_name,
                ct_in.name as input_type_name,
                mu_in.name as input_unit_name,
                mu_in.symbol as input_unit_symbol,
                pc_out.name as output_category_name,
                ct_out.name as output_type_name,
                mu_out.name as output_unit_name,
                mu_out.symbol as output_unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(uc.first_name, ' ', uc.last_name) as completed_by_name
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN category_type_units ctu_in ON p.input_category_type_unit_id = ctu_in.id
                LEFT JOIN category_types ct_in ON ctu_in.category_type_id = ct_in.id
                LEFT JOIN product_categories pc_in ON ct_in.category_id = pc_in.id
                LEFT JOIN measurement_units mu_in ON ctu_in.measurement_unit_id = mu_in.id
                LEFT JOIN category_type_units ctu_out ON p.output_category_type_unit_id = ctu_out.id
                LEFT JOIN category_types ct_out ON ctu_out.category_type_id = ct_out.id
                LEFT JOIN product_categories pc_out ON ct_out.category_id = pc_out.id
                LEFT JOIN measurement_units mu_out ON ctu_out.measurement_unit_id = mu_out.id
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN users uc ON p.completed_by = uc.id
                WHERE p.location_id = :location_id
                ORDER BY p.created_at DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get pending production records by location
     */
    public function getPendingByLocation(int $locationId): array
    {
        $sql = "SELECT p.*, 
                l.name as location_name,
                s.name as supplier_name,
                pc_in.name as input_category_name,
                pc_in.id as input_category_id,
                ct_in.id as input_type_id,
                ct_in.name as input_type_name,
                mu_in.name as input_unit_name,
                mu_in.symbol as input_unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN category_type_units ctu_in ON p.input_category_type_unit_id = ctu_in.id
                LEFT JOIN category_types ct_in ON ctu_in.category_type_id = ct_in.id
                LEFT JOIN product_categories pc_in ON ct_in.category_id = pc_in.id
                LEFT JOIN measurement_units mu_in ON ctu_in.measurement_unit_id = mu_in.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.location_id = :location_id AND p.status = 'pending'
                ORDER BY p.production_date DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get production with details
     */
    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT p.*, 
                l.name as location_name,
                s.name as supplier_name,
                pc_in.name as input_category_name,
                pc_in.id as input_category_id,
                ct_in.id as input_type_id,
                ct_in.name as input_type_name,
                mu_in.name as input_unit_name,
                mu_in.symbol as input_unit_symbol,
                pc_out.name as output_category_name,
                ct_out.name as output_type_name,
                mu_out.name as output_unit_name,
                mu_out.symbol as output_unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(uc.first_name, ' ', uc.last_name) as completed_by_name
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN category_type_units ctu_in ON p.input_category_type_unit_id = ctu_in.id
                LEFT JOIN category_types ct_in ON ctu_in.category_type_id = ct_in.id
                LEFT JOIN product_categories pc_in ON ct_in.category_id = pc_in.id
                LEFT JOIN measurement_units mu_in ON ctu_in.measurement_unit_id = mu_in.id
                LEFT JOIN category_type_units ctu_out ON p.output_category_type_unit_id = ctu_out.id
                LEFT JOIN category_types ct_out ON ctu_out.category_type_id = ct_out.id
                LEFT JOIN product_categories pc_out ON ct_out.category_id = pc_out.id
                LEFT JOIN measurement_units mu_out ON ctu_out.measurement_unit_id = mu_out.id
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN users uc ON p.completed_by = uc.id
                WHERE p.id = :id";
        return Database::fetch($sql, ['id' => $id]) ?: null;
    }

    /**
     * Generate production number
     */
    private function generateProductionNumber(): string
    {
        $prefix = 'PRD-' . date('Ym') . '-';
        $sql = "SELECT MAX(CAST(SUBSTRING(production_number, LENGTH(:prefix) + 1) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE production_number LIKE :prefix_like";
        $result = Database::fetch($sql, [
            'prefix' => $prefix,
            'prefix_like' => $prefix . '%'
        ]);
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create production record (send to production)
     */
    public function createProduction(array $data): int|false
    {
        $data['production_number'] = $this->generateProductionNumber();
        $data['status'] = 'pending';
        
        return Database::insert($this->table, $data);
    }

    /**
     * Complete production (record finished product)
     */
    public function completeProduction(int $id, int $outputCategoryTypeUnitId, float $outputQuantity, float $outputUnitPrice, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET output_category_type_unit_id = :output_ctu_id,
                    output_quantity = :output_quantity,
                    output_unit_price = :output_unit_price,
                    status = 'completed',
                    completed_by = :user_id,
                    completed_at = NOW()
                WHERE id = :id AND status = 'pending'";
        
        return Database::execute($sql, [
            'output_ctu_id' => $outputCategoryTypeUnitId,
            'output_quantity' => $outputQuantity,
            'output_unit_price' => $outputUnitPrice,
            'user_id' => $userId,
            'id' => $id
        ]);
    }

    /**
     * Cancel production (return stock)
     */
    public function cancelProduction(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = :id AND status = 'pending'";
        return Database::execute($sql, ['id' => $id]);
    }
}