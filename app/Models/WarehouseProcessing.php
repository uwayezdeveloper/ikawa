<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class WarehouseProcessing extends Model
{
    protected string $table = 'warehouse_processing';
    
    protected array $fillable = [
        'processing_number',
        'location_id',
        'supplier_id',
        'category_type_id',
        'measurement_unit_id',
        'input_quantity',
        'output_quantity',
        'processing_step',
        'to_processing_step_id',
        'processing_date',
        'notes',
        'status',
        'created_by',
        'completed_by',
        'completed_at'
    ];

    /**
     * Available processing steps for warehouse
     */
    public static function getProcessingSteps(): array
    {
        return [
            'drying' => 'Drying',
            'hulling' => 'Hulling',
            'sorting' => 'Sorting',
            'grading' => 'Grading',
            'packing' => 'Packing',
            'quality_check' => 'Quality Check',
            'roasting' => 'Roasting',
            'grinding' => 'Grinding',
            'blending' => 'Blending',
            'storage' => 'Storage',
            'other' => 'Other'
        ];
    }

    /**
     * Get all processing records with related data
     */
    public function getAll(): array
    {
        $sql = "SELECT wp.*, 
                l.name as location_name,
                s.name as supplier_name,
                ct.name as category_type_name,
                pc.name as category_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                ps_to.name as to_step_name,
                ps_to.step_order as to_step_order,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(uc.first_name, ' ', uc.last_name) as completed_by_name,
                (SELECT GROUP_CONCAT(DISTINCT psi.name ORDER BY psi.step_order SEPARATOR ', ')
                 FROM warehouse_processing_items wpi
                 LEFT JOIN processing_steps psi ON wpi.from_processing_step_id = psi.id
                 WHERE wpi.processing_id = wp.id) as from_step_name
                FROM {$this->table} wp
                LEFT JOIN locations l ON wp.location_id = l.id
                LEFT JOIN suppliers s ON wp.supplier_id = s.id
                LEFT JOIN category_types ct ON wp.category_type_id = ct.id
                LEFT JOIN product_categories pc ON ct.category_id = pc.id
                LEFT JOIN measurement_units mu ON wp.measurement_unit_id = mu.id
                LEFT JOIN processing_steps ps_to ON wp.to_processing_step_id = ps_to.id
                LEFT JOIN users u ON wp.created_by = u.id
                LEFT JOIN users uc ON wp.completed_by = uc.id
                ORDER BY wp.created_at DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Get processing records by location
     */
    public function getByLocation(int $locationId): array
    {
        $sql = "SELECT wp.*, 
                l.name as location_name,
                s.name as supplier_name,
                ct.name as category_type_name,
                pc.name as category_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(uc.first_name, ' ', uc.last_name) as completed_by_name
                FROM {$this->table} wp
                LEFT JOIN locations l ON wp.location_id = l.id
                LEFT JOIN suppliers s ON wp.supplier_id = s.id
                LEFT JOIN category_types ct ON wp.category_type_id = ct.id
                LEFT JOIN product_categories pc ON ct.category_id = pc.id
                LEFT JOIN measurement_units mu ON wp.measurement_unit_id = mu.id
                LEFT JOIN users u ON wp.created_by = u.id
                LEFT JOIN users uc ON wp.completed_by = uc.id
                WHERE wp.location_id = :location_id
                ORDER BY wp.created_at DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get pending processing records by location
     */
    public function getPendingByLocation(int $locationId): array
    {
        $sql = "SELECT wp.*, 
                l.name as location_name,
                s.name as supplier_name,
                ct.name as category_type_name,
                pc.name as category_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} wp
                LEFT JOIN locations l ON wp.location_id = l.id
                LEFT JOIN suppliers s ON wp.supplier_id = s.id
                LEFT JOIN category_types ct ON wp.category_type_id = ct.id
                LEFT JOIN product_categories pc ON ct.category_id = pc.id
                LEFT JOIN measurement_units mu ON wp.measurement_unit_id = mu.id
                LEFT JOIN users u ON wp.created_by = u.id
                WHERE wp.location_id = :location_id AND wp.status = 'pending'
                ORDER BY wp.processing_date DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get processing with details
     */
    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT wp.*, 
                l.name as location_name,
                s.name as supplier_name,
                ct.name as category_type_name,
                pc.name as category_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                ps_to.name as to_step_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                CONCAT(uc.first_name, ' ', uc.last_name) as completed_by_name
                FROM {$this->table} wp
                LEFT JOIN locations l ON wp.location_id = l.id
                LEFT JOIN suppliers s ON wp.supplier_id = s.id
                LEFT JOIN category_types ct ON wp.category_type_id = ct.id
                LEFT JOIN product_categories pc ON ct.category_id = pc.id
                LEFT JOIN measurement_units mu ON wp.measurement_unit_id = mu.id
                LEFT JOIN processing_steps ps_to ON wp.to_processing_step_id = ps_to.id
                LEFT JOIN users u ON wp.created_by = u.id
                LEFT JOIN users uc ON wp.completed_by = uc.id
                WHERE wp.id = :id";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Generate unique processing number
     */
    public function generateProcessingNumber(): string
    {
        $prefix = 'WP';
        $date = date('Ymd');
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(created_at) = CURDATE()";
        $result = Database::fetch($sql);
        $count = ($result['count'] ?? 0) + 1;
        
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get processing history for a product type at a location
     */
    public function getHistoryByProduct(int $locationId, int $categoryTypeId, int $supplierId): array
    {
        $sql = "SELECT wp.*, 
                l.name as location_name,
                s.name as supplier_name,
                ct.name as category_type_name,
                mu.name as unit_name,
                mu.symbol as unit_symbol,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM {$this->table} wp
                LEFT JOIN locations l ON wp.location_id = l.id
                LEFT JOIN suppliers s ON wp.supplier_id = s.id
                LEFT JOIN category_types ct ON wp.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON wp.measurement_unit_id = mu.id
                LEFT JOIN users u ON wp.created_by = u.id
                WHERE wp.location_id = :location_id 
                AND wp.category_type_id = :category_type_id
                AND wp.supplier_id = :supplier_id
                ORDER BY wp.processing_date DESC, wp.created_at DESC";
        return Database::fetchAll($sql, [
            'location_id' => $locationId,
            'category_type_id' => $categoryTypeId,
            'supplier_id' => $supplierId
        ]);
    }

    /**
     * Get summary of processing by step
     */
    public function getProcessingSummary(int $locationId): array
    {
        $sql = "SELECT 
                wp.processing_step,
                COUNT(*) as total_records,
                SUM(CASE WHEN wp.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN wp.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(wp.input_quantity) as total_input,
                SUM(COALESCE(wp.output_quantity, 0)) as total_output
                FROM {$this->table} wp
                WHERE wp.location_id = :location_id
                GROUP BY wp.processing_step
                ORDER BY total_records DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }
}