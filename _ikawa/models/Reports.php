<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Reports {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getProductTypes() {
        try {
            $sql = "SELECT type_id, type_name FROM tbl_category_types WHERE status = 'active' ORDER BY type_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Reports::getProductTypes - Error: " . $e->getMessage());
            return false;
        }
    }

    public function getGeneralSalesReport($locId, $fromDate, $toDate, $productType = null) {
        try {
            // Build the main sales query based on actual table structure
            $sql = "
                SELECT 
                    s.sale_id,
                    s.invoice_no,
                    s.created_at as sale_date,
                    s.total_amount,
                    s.status,
                    s.price_status,
                    COALESCE(c.full_name, s.customer_name, 'Walk-in Customer') as customer_name,
                    s.customer_phone,
                    si.quantity,
                    si.unit_price,
                    si.total_price as item_total,
                    ct.type_name as product_name,
                    u.unit_name,
                    pm.Mode_names as payment_method
                FROM tbl_sales s
                INNER JOIN tbl_sale_items si ON s.sale_id = si.sale_id
                INNER JOIN tbl_category_type_units ctu ON si.assignment_id = ctu.assignment_id
                INNER JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                INNER JOIN tbl_units u ON ctu.unit_id = u.unit_id
                LEFT JOIN tbl_clients c ON s.client_id = c.client_id
                LEFT JOIN tbl_sale_payments sp ON s.sale_id = sp.sale_id
                LEFT JOIN tbl_paymentmodes pm ON sp.mode_id = pm.Mode_id
                WHERE s.loc_id = :loc_id 
                AND DATE(s.created_at) >= :from_date 
                AND DATE(s.created_at) <= :to_date
            ";

            $params = [
                ':loc_id' => $locId,
                ':from_date' => $fromDate,
                ':to_date' => $toDate
            ];

            // Add product type filter if specified
            if (!empty($productType)) {
                $sql .= " AND ct.type_id = :product_type";
                $params[':product_type'] = $productType;
            }

            $sql .= " ORDER BY s.created_at DESC, s.sale_id DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $sales = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $sales;

        } catch (\PDOException $e) {
            error_log("Reports::getGeneralSalesReport - Error: " . $e->getMessage());
            return false;
        }
    }

    private function getSalesSummary($filters) {
        try {
            $sql = "
                SELECT 
                    COUNT(DISTINCT s.sale_id) as total_sales,
                    SUM(s.total_amount) as total_revenue,
                    SUM(si.quantity) as total_quantity,
                    COUNT(DISTINCT s.customer_name) as unique_customers
                FROM tbl_sales s
                INNER JOIN tbl_sale_items si ON s.sale_id = si.sale_id
                INNER JOIN tbl_category_type_units ctu ON si.assignment_id = ctu.assignment_id
                INNER JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                WHERE s.loc_id = :loc_id 
                AND DATE(s.created_at) >= :from_date 
                AND DATE(s.created_at) <= :to_date
            ";

            $params = [
                ':loc_id' => $filters['loc_id'],
                ':from_date' => $filters['from_date'],
                ':to_date' => $filters['to_date']
            ];

            if (!empty($filters['product_type'])) {
                $sql .= " AND ct.type_id = :product_type";
                $params[':product_type'] = $filters['product_type'];
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'total_sales' => intval($result['total_sales'] ?? 0),
                'total_revenue' => floatval($result['total_revenue'] ?? 0),
                'total_quantity' => floatval($result['total_quantity'] ?? 0),
                'unique_customers' => intval($result['unique_customers'] ?? 0)
            ];

        } catch (\PDOException $e) {
            error_log("Reports::getSalesSummary - Error: " . $e->getMessage());
            return [
                'total_sales' => 0,
                'total_revenue' => 0,
                'total_quantity' => 0,
                'unique_customers' => 0
            ];
        }
    }

    // Method to check if sales tables exist and get their structure
    public function analyzeSalesStructure() {
        try {
            // Check if tbl_sales table exists
            $sql = "SHOW TABLES LIKE 'tbl_sales'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $salesTableExists = $stmt->fetch() !== false;

            if (!$salesTableExists) {
                return ['error' => 'tbl_sales table does not exist'];
            }

            // Get tbl_sales structure
            $sql = "DESCRIBE tbl_sales";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $salesStructure = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Check if tbl_sale_items table exists
            $sql = "SHOW TABLES LIKE 'tbl_sale_items'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $saleItemsTableExists = $stmt->fetch() !== false;

            $saleItemsStructure = [];
            if ($saleItemsTableExists) {
                $sql = "DESCRIBE tbl_sale_items";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $saleItemsStructure = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            // Get sample sales data
            $sampleSales = [];
            if ($salesTableExists) {
                $sql = "SELECT * FROM tbl_sales LIMIT 5";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $sampleSales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            return [
                'sales_table_exists' => $salesTableExists,
                'sale_items_table_exists' => $saleItemsTableExists,
                'sales_structure' => $salesStructure,
                'sale_items_structure' => $saleItemsStructure,
                'sample_sales' => $sampleSales
            ];

        } catch (\PDOException $e) {
            error_log("Reports::analyzeSalesStructure - Error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}