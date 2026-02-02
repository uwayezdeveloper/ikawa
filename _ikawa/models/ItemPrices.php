<?php

namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class ItemPrices {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all products with their latest prices and stock details
     */
    public function getAllProductsWithLatestPrices($params = []) {
        try {
            // Use correct table structure based on working Reports model
            $sql = "SELECT 
                        ss.stock_summary_id,
                        ss.loc_id,
                        ss.assignment_id,
                        ss.total_quantity as current_stock,
                        
                        -- Location information
                        COALESCE(l.location_name, 'Unknown Location') as location_name,
                        
                        -- Product information using correct table structure
                        COALESCE(c.category_name, 'Unknown Category') as category_name,
                        COALESCE(ct.type_name, 'Unknown Type') as type_name,
                        COALESCE(u.unit_name, 'Unknown Unit') as unit_name,
                        
                        -- Latest price information (simplified approach)
                        COALESCE(sd.unit_price, 0) as latest_unit_price,
                        COALESCE(sd.total_price, 0) as latest_total_price,
                        sd.created_at as price_date,
                        COALESCE(sd.record_type, 'N/A') as price_record_type,
                        COALESCE(sd.price_status, 'pending') as price_status,
                        COALESCE(s.full_name, 'N/A') as supplier_name,
                        
                        -- Stock value calculation
                        (ss.total_quantity * COALESCE(sd.unit_price, 0)) as stock_value
                        
                    FROM tbl_stock_summary ss
                    
                    -- Join with location
                    LEFT JOIN tbl_location l ON ss.loc_id = l.loc_id
                    
                    -- Join using correct table structure (like Reports model)
                    LEFT JOIN tbl_category_type_units ctu ON ss.assignment_id = ctu.assignment_id
                    LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                    LEFT JOIN tbl_categories c ON ct.category_id = c.category_id
                    LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                    
                    -- Get most recent price (simplified - just latest by date)
                    LEFT JOIN tbl_stock_details sd ON ss.assignment_id = sd.assignment_id 
                        AND sd.created_at = (
                            SELECT MAX(created_at) 
                            FROM tbl_stock_details sd2 
                            WHERE sd2.assignment_id = ss.assignment_id 
                            AND sd2.unit_price IS NOT NULL 
                            AND sd2.unit_price > 0
                        )
                    LEFT JOIN tbl_suppliers s ON sd.sup_id = s.sup_id
                    
                    WHERE ss.total_quantity > 0
                    ORDER BY c.category_name, ct.type_name, l.location_name
                    LIMIT 20";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("ItemPrices Model Error: " . $e->getMessage());
            error_log("SQL Query: " . $sql);
            // Throw the actual error instead of returning false
            throw new \Exception("Database Error: " . $e->getMessage() . " | SQL: " . $sql);
        }
    }

    /**
     * Get product assignments (categories + types) for dropdown
     */
    public function getProductAssignments() {
        try {
            $sql = "SELECT 
                        cta.assignment_id,
                        CONCAT(c.category_name, ' - ', ct.type_name) as product_name,
                        c.category_name,
                        ct.type_name,
                        u.unit_name
                    FROM tbl_category_type_assignment cta
                    LEFT JOIN tbl_categories c ON cta.category_id = c.category_id
                    LEFT JOIN tbl_category_types ct ON cta.type_id = ct.type_id
                    LEFT JOIN tbl_category_type_units ctu ON cta.assignment_id = ctu.assignment_id
                    LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                    WHERE cta.status = 'active'
                    ORDER BY c.category_name, ct.type_name";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("ItemPrices Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get summary statistics for prices report
     */
    public function getPricesSummary($params) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_records,
                        SUM(sd.quantity) as total_quantity,
                        SUM(sd.total_price) as total_value,
                        AVG(sd.unit_price) as average_unit_price,
                        COUNT(CASE WHEN sd.price_status = 'pending' THEN 1 END) as pending_count,
                        COUNT(CASE WHEN sd.price_status = 'approved' THEN 1 END) as approved_count,
                        COUNT(CASE WHEN sd.record_type = 'purchase' THEN 1 END) as purchase_count,
                        COUNT(CASE WHEN sd.record_type = 'transfer' THEN 1 END) as transfer_count
                    FROM tbl_stock_details sd
                    WHERE 1=1
                        AND DATE(sd.created_at) >= :from_date
                        AND DATE(sd.created_at) <= :to_date
                        AND sd.loc_id = :loc_id";

            $bindParams = [
                ':from_date' => $params['from_date'],
                ':to_date' => $params['to_date'],
                ':loc_id' => $params['loc_id']
            ];

            // Add optional filters
            if (!empty($params['supplier_id'])) {
                $sql .= " AND sd.sup_id = :supplier_id";
                $bindParams[':supplier_id'] = $params['supplier_id'];
            }

            if (!empty($params['price_status'])) {
                $sql .= " AND sd.price_status = :price_status";
                $bindParams[':price_status'] = $params['price_status'];
            }

            if (!empty($params['record_type'])) {
                $sql .= " AND sd.record_type = :record_type";
                $bindParams[':record_type'] = $params['record_type'];
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($bindParams);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("ItemPrices Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get price trends by month
     */
    public function getPriceTrends($params) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(sd.created_at, '%Y-%m') as month_year,
                        COUNT(*) as record_count,
                        SUM(sd.total_price) as monthly_total,
                        AVG(sd.unit_price) as avg_unit_price
                    FROM tbl_stock_details sd
                    WHERE 1=1
                        AND DATE(sd.created_at) >= :from_date
                        AND DATE(sd.created_at) <= :to_date
                        AND sd.loc_id = :loc_id";

            $bindParams = [
                ':from_date' => $params['from_date'],
                ':to_date' => $params['to_date'],
                ':loc_id' => $params['loc_id']
            ];

            // Add optional filters
            if (!empty($params['supplier_id'])) {
                $sql .= " AND sd.sup_id = :supplier_id";
                $bindParams[':supplier_id'] = $params['supplier_id'];
            }

            $sql .= " GROUP BY DATE_FORMAT(sd.created_at, '%Y-%m') 
                     ORDER BY month_year DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($bindParams);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("ItemPrices Model Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM tbl_stock_details LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return [
                'connection' => 'successful',
                'sample_count' => $result['total'],
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\PDOException $e) {
            error_log("ItemPrices Model Connection Test Error: " . $e->getMessage());
            return [
                'connection' => 'failed',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
}