<?php

namespace Controllers;

require_once __DIR__ . '/../models/ItemPrices.php';
require_once __DIR__ . '/../config/Response.php';

use Config\Database;
use Config\Response;
use Models\ItemPrices;
use \Exception;

class ItemPricesController {
    private $itemPricesModel;

    public function __construct() {
        $this->itemPricesModel = new ItemPrices();
    }

    /**
     * Get all products with latest prices report
     */
    public function getAllProductsWithLatestPrices() {
        try {
            // Get parameters
            $assignmentId = $_GET['assignment_id'] ?? null;

            $params = [
                'assignment_id' => $assignmentId
            ];

            $result = $this->itemPricesModel->getAllProductsWithLatestPrices($params);

            if ($result === false) {
                Response::error('Failed to fetch products with latest prices');
                return;
            }

            Response::success('Products with latest prices retrieved successfully', $result);

        } catch (\Exception $e) {
            error_log("ItemPricesController Error: " . $e->getMessage());
            Response::error('Failed to fetch products with latest prices: ' . $e->getMessage());
        }
    }

    /**
     * Export all products with latest prices to Excel
     */
    public function exportProductsWithLatestPrices() {
        try {
            // Get parameters
            $assignmentId = $_GET['assignment_id'] ?? null;

            $params = [
                'assignment_id' => $assignmentId
            ];

            $data = $this->itemPricesModel->getAllProductsWithLatestPrices($params);

            if ($data === false) {
                Response::error('Failed to fetch data for export');
                return;
            }

            // Set headers for Excel download
            $productName = $assignmentId ? 'product_specific' : 'all_products';
            $filename = 'products_latest_prices_' . $productName . '_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            // Create file pointer
            $output = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($output, [
                'Location',
                'Category',
                'Product Type', 
                'Unit',
                'Current Stock',
                'Latest Unit Price (RWF)',
                'Latest Total Price (RWF)',
                'Stock Value (RWF)',
                'Supplier',
                'Price Date',
                'Price Status',
                'Record Type'
            ]);

            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['location_name'],
                    $row['category_name'],
                    $row['type_name'],
                    $row['unit_name'],
                    number_format($row['current_stock'], 2),
                    number_format($row['latest_unit_price'], 2),
                    number_format($row['latest_total_price'], 2),
                    number_format($row['stock_value'], 2),
                    $row['supplier_name'] ?? 'N/A',
                    $row['price_date'] ? date('Y-m-d H:i:s', strtotime($row['price_date'])) : 'N/A',
                    ucfirst($row['price_status'] ?? 'N/A'),
                    ucfirst($row['price_record_type'] ?? 'N/A')
                ]);
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            error_log("ItemPricesController Export Error: " . $e->getMessage());
            Response::error('Failed to export products with latest prices: ' . $e->getMessage());
        }
    }

    /**
     * Export prices report to Excel
     */
    public function exportPricesReport() {
        try {
            // Get parameters
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            $locId = $_GET['loc_id'] ?? null;
            $supplierId = $_GET['supplier_id'] ?? null;
            $priceStatus = $_GET['price_status'] ?? null;
            $recordType = $_GET['record_type'] ?? null;

            // Validate required parameters
            if (!$fromDate || !$toDate || !$locId) {
                Response::error('From date, To date, and Location ID are required');
                return;
            }

            $params = [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'loc_id' => $locId,
                'supplier_id' => $supplierId,
                'price_status' => $priceStatus,
                'record_type' => $recordType
            ];

            $data = $this->itemPricesModel->getAllProductsWithLatestPrices($params);

            if ($data === false) {
                Response::error('Failed to fetch data for export');
                return;
            }

            // Set headers for Excel download
            $filename = 'general_items_prices_report_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            // Create file pointer
            $output = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($output, [
                'Stock Detail ID',
                'Location',
                'Supplier',
                'Category',
                'Product Type', 
                'Unit',
                'Quantity',
                'Unit Price (RWF)',
                'Total Price (RWF)',
                'Record Type',
                'Price Status',
                'Created Date',
                'Approved By',
                'Approved Date'
            ]);

            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['stock_detail_id'],
                    $row['location_name'],
                    $row['supplier_name'],
                    $row['category_name'],
                    $row['type_name'],
                    $row['unit_name'],
                    number_format($row['quantity'], 2),
                    number_format($row['unit_price'], 2),
                    number_format($row['total_price'], 2),
                    ucfirst($row['record_type']),
                    ucfirst($row['price_status']),
                    date('Y-m-d H:i:s', strtotime($row['created_at'])),
                    $row['approved_by_name'] ?? 'N/A',
                    $row['approved_at'] ? date('Y-m-d H:i:s', strtotime($row['approved_at'])) : 'N/A'
                ]);
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            error_log("ItemPricesController Export Error: " . $e->getMessage());
            Response::error('Failed to export prices report: ' . $e->getMessage());
        }
    }

    /**
     * Get product assignments for dropdown
     */
    public function getProductAssignments() {
        try {
            $result = $this->itemPricesModel->getProductAssignments();
            
            if ($result === false) {
                Response::error('Failed to fetch product assignments');
                return;
            }

            Response::success('Product assignments retrieved successfully', $result);

        } catch (\Exception $e) {
            error_log("ItemPricesController Error: " . $e->getMessage());
            Response::error('Failed to fetch product assignments: ' . $e->getMessage());
        }
    }

    /**
     * Get price status options
     */
    public function getPriceStatusOptions() {
        try {
            $options = [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved']
            ];

            Response::success('Price status options retrieved successfully', $options);

        } catch (\Exception $e) {
            error_log("ItemPricesController Error: " . $e->getMessage());
            Response::error('Failed to fetch price status options: ' . $e->getMessage());
        }
    }

    /**
     * Get record type options
     */
    public function getRecordTypeOptions() {
        try {
            $options = [
                ['value' => 'purchase', 'label' => 'Purchase'],
                ['value' => 'transfer', 'label' => 'Transfer']
            ];

            Response::success('Record type options retrieved successfully', $options);

        } catch (\Exception $e) {
            error_log("ItemPricesController Error: " . $e->getMessage());
            Response::error('Failed to fetch record type options: ' . $e->getMessage());
        }
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $result = $this->itemPricesModel->testConnection();
            Response::success('Database connection test completed', $result);
        } catch (\Exception $e) {
            error_log("ItemPricesController Test Error: " . $e->getMessage());
            Response::error('Database connection test failed: ' . $e->getMessage());
        }
    }
}