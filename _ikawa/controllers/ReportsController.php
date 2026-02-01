<?php
namespace Controllers;

require_once __DIR__ . '/../models/Reports.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Reports;
use Config\Response;
use Exception;

class ReportsController
{
    private $reportsModel;

    public function __construct()
    {
        $this->reportsModel = new Reports();
    }

    public function getProductTypes()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (!isset($_SESSION['loc_id'])) {
                Response::error('User session not found', 401);
                return;
            }

            $types = $this->reportsModel->getProductTypes();

            if ($types !== false) {
                Response::success('Product types retrieved successfully', $types);
            } else {
                Response::error('Failed to retrieve product types', 500);
            }
        } catch (Exception $e) {
            error_log("ReportsController::getProductTypes - Error: " . $e->getMessage());
            Response::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function getGeneralSalesReport()
    {
        try {
            // Start session if needed
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Get loc_id from URL parameter first, then fallback to session
            $loc_id = null;
            if (isset($_GET['loc_id']) && !empty($_GET['loc_id'])) {
                $loc_id = intval($_GET['loc_id']);
            } elseif (isset($_SESSION['loc_id'])) {
                $loc_id = intval($_SESSION['loc_id']);
            }

            if (!$loc_id) {
                Response::error('Location ID is required', 400);
                return;
            }
            
            // Get filter parameters
            $from_date = $_GET['from_date'] ?? date('Y-m-01');
            $to_date = $_GET['to_date'] ?? date('Y-m-d');
            $product_type = $_GET['product_type'] ?? null;

            // Validate dates
            if (!$from_date || !$to_date) {
                Response::error('From date and to date are required', 400);
                return;
            }

            $reportData = $this->reportsModel->getGeneralSalesReport($loc_id, $from_date, $to_date, $product_type);

            if ($reportData !== false) {
                Response::success('Sales report retrieved successfully', $reportData);
            } else {
                Response::error('Failed to retrieve sales report', 500);
            }
        } catch (Exception $e) {
            error_log("ReportsController::getGeneralSalesReport - Error: " . $e->getMessage());
            Response::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function exportSalesReport()
    {
        try {
            // Start session if needed
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Get loc_id from URL parameter first, then fallback to session
            $loc_id = null;
            if (isset($_GET['loc_id']) && !empty($_GET['loc_id'])) {
                $loc_id = intval($_GET['loc_id']);
            } elseif (isset($_SESSION['loc_id'])) {
                $loc_id = intval($_SESSION['loc_id']);
            }

            if (!$loc_id) {
                Response::error('Location ID is required', 400);
                return;
            }
            
            // Get filter parameters
            $from_date = $_GET['from_date'] ?? date('Y-m-01');
            $to_date = $_GET['to_date'] ?? date('Y-m-d');
            $product_type = $_GET['product_type'] ?? null;

            $reportData = $this->reportsModel->getGeneralSalesReport($loc_id, $from_date, $to_date, $product_type);

            if ($reportData !== false && !empty($reportData)) {
                $filters = [
                    'loc_id' => $loc_id,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'product_type' => $product_type
                ];
                $this->generateCSVExport($reportData, $filters);
            } else {
                Response::error('No data to export', 404);
            }
        } catch (Exception $e) {
            error_log("ReportsController::exportSalesReport - Error: " . $e->getMessage());
            Response::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    private function generateCSVExport($reportData, $filters)
    {
        $filename = 'sales_report_' . $filters['from_date'] . '_to_' . $filters['to_date'] . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, [
            'Date',
            'Sale ID',
            'Customer',
            'Product',
            'Quantity',
            'Unit',
            'Unit Price (RWF)',
            'Total Amount (RWF)',
            'Payment Method',
            'Status'
        ]);

        // CSV Data
        foreach ($reportData['sales'] as $sale) {
            fputcsv($output, [
                date('Y-m-d', strtotime($sale['sale_date'])),
                $sale['sale_id'],
                $sale['customer_name'] ?: 'Walk-in Customer',
                $sale['product_name'],
                number_format($sale['quantity'], 2),
                $sale['unit_name'],
                number_format($sale['unit_price'], 2),
                number_format($sale['total_amount'], 2),
                $sale['payment_method'] ?: 'Cash',
                ucfirst($sale['payment_status'])
            ]);
        }

        fclose($output);
        exit;
    }
}