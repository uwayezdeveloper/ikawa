<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\Supplier;
use App\Models\Setting;

class SupplierRecordController extends Controller
{
    protected Supplier $supplierModel;

    public function __construct()
    {
        parent::__construct();
        $this->supplierModel = new Supplier();
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            return false;
        }
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Display supplier records page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$this->hasPermission('view-supplier-records')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get all active suppliers
        $suppliers = $this->getActiveSuppliers();
        
        // Get selected supplier
        $selectedSupplierId = $_GET['supplier_id'] ?? ($suppliers[0]['id'] ?? null);
        
        $records = [];
        $supplierInfo = null;
        $summary = [];
        
        $productSummary = [];

        if ($selectedSupplierId) {
            $supplierInfo = $this->supplierModel->find($selectedSupplierId);
            $records = $this->getSupplierRecords($selectedSupplierId);
            $summary = $this->getSupplierSummary($selectedSupplierId);
            $productSummary = $this->getSupplierProductSummary($selectedSupplierId);
        }

        return View::render('suppliers/records/index', [
            'title' => 'Supplier Records',
            'user' => $user,
            'suppliers' => $suppliers,
            'selectedSupplierId' => $selectedSupplierId,
            'supplierInfo' => $supplierInfo,
            'records' => $records,
            'summary' => $summary,
            'productSummary' => $productSummary,
            'scripts' => ['js/pages/supplier-records.js']
        ], 'main');
    }

    /**
     * Get active suppliers
     */
    private function getActiveSuppliers(): array
    {
        $sql = "SELECT s.*, st.name as type_name 
                FROM suppliers s
                LEFT JOIN supplier_types st ON s.supplier_type_id = st.id
                WHERE s.status = 'active' 
                ORDER BY s.name";
        return Database::fetchAll($sql);
    }

    /**
     * Get all records for a supplier (combined timeline)
     */
    private function getSupplierRecords(int $supplierId): array
    {
        $records = [];
        
        // Get advances given to supplier
        $advances = $this->getSupplierAdvances($supplierId);
        foreach ($advances as $adv) {
            $records[] = [
                'date' => $adv['advance_date'],
                'datetime' => $adv['created_at'],
                'type' => 'advance',
                'reference' => $adv['advance_number'],
                'description' => 'Advance given to supplier',
                'location' => $adv['location_name'],
                'debit' => $adv['amount'], // Money given out
                'credit' => 0,
                'status' => $adv['status'],
                'notes' => $adv['description']
            ];
        }
        
        // Get stock receives from supplier
        $receives = $this->getSupplierStockReceives($supplierId);
        foreach ($receives as $rcv) {
            $records[] = [
                'date' => $rcv['receive_date'],
                'datetime' => $rcv['created_at'],
                'type' => 'stock_receive',
                'reference' => $rcv['receive_number'] ?? ('RCV-' . $rcv['id']),
                'description' => 'Stock received: ' . $rcv['quantity'] . ' ' . $rcv['unit_symbol'] . ' of ' . $rcv['category_name'] . ' (' . $rcv['type_name'] . ')',
                'location' => $rcv['location_name'],
                'debit' => 0,
                'credit' => $rcv['total_price'], // Goods received (credit to supplier)
                'status' => $rcv['status'],
                'notes' => 'Paid: Adv=' . number_format($rcv['advance_amount'] ?? 0) . ', Acc=' . number_format($rcv['account_amount'] ?? 0) . ', Due=' . number_format($rcv['payable_amount'] ?? 0)
            ];
        }
        
        // Get payable payments made to supplier
        $payments = $this->getSupplierPayablePayments($supplierId);
        foreach ($payments as $pay) {
            $records[] = [
                'date' => date('Y-m-d', strtotime($pay['created_at'])),
                'datetime' => $pay['created_at'],
                'type' => 'payment',
                'reference' => $pay['payable_number'],
                'description' => 'Payment for payable',
                'location' => $pay['location_name'],
                'debit' => $pay['paid_amount'], // Money paid out
                'credit' => 0,
                'status' => $pay['status'],
                'notes' => 'Total: ' . number_format($pay['amount']) . ', Remaining: ' . number_format($pay['amount'] - $pay['paid_amount'])
            ];
        }
        
        // Sort by datetime descending
        usort($records, function($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });
        
        return $records;
    }

    /**
     * Get supplier advances
     */
    private function getSupplierAdvances(int $supplierId): array
    {
        $sql = "SELECT sa.*, l.name as location_name, a.account_name
                FROM supplier_advances sa
                LEFT JOIN locations l ON sa.location_id = l.id
                LEFT JOIN accounts a ON sa.account_id = a.id
                WHERE sa.supplier_id = :supplier_id
                ORDER BY sa.advance_date DESC, sa.created_at DESC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get supplier stock receives
     */
    private function getSupplierStockReceives(int $supplierId): array
    {
        $sql = "SELECT sr.*, l.name as location_name, 
                pc.name as category_name, ct.name as type_name,
                mu.symbol as unit_symbol
                FROM stock_receives sr
                LEFT JOIN locations l ON sr.location_id = l.id
                LEFT JOIN product_categories pc ON sr.product_category_id = pc.id
                LEFT JOIN category_type_units ctu ON sr.category_type_unit_id = ctu.id
                LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
                LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                WHERE sr.supplier_id = :supplier_id
                ORDER BY sr.receive_date DESC, sr.created_at DESC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get supplier payable payments
     */
    private function getSupplierPayablePayments(int $supplierId): array
    {
        $sql = "SELECT sp.*, l.name as location_name
                FROM supplier_payables sp
                LEFT JOIN locations l ON sp.location_id = l.id
                WHERE sp.supplier_id = :supplier_id
                AND sp.paid_amount > 0
                ORDER BY sp.updated_at DESC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * Get supplier summary statistics
     */
    private function getSupplierSummary(int $supplierId): array
    {
        // Total advances given
        $advancesSql = "SELECT 
                        COUNT(*) as total_advances,
                        COALESCE(SUM(amount), 0) as total_advance_amount,
                        SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as active_advances,
                        SUM(CASE WHEN status = 'settled' THEN amount ELSE 0 END) as settled_advances
                        FROM supplier_advances 
                        WHERE supplier_id = :supplier_id";
        $advances = Database::fetch($advancesSql, ['supplier_id' => $supplierId]);
        
        // Total stock received
        $stockSql = "SELECT 
                     COUNT(*) as total_receives,
                     COALESCE(SUM(total_price), 0) as total_stock_value,
                     COALESCE(SUM(advance_amount), 0) as paid_by_advance,
                     COALESCE(SUM(account_amount), 0) as paid_by_account,
                     COALESCE(SUM(payable_amount), 0) as recorded_as_payable
                     FROM stock_receives 
                     WHERE supplier_id = :supplier_id
                     AND status = 'approved'";
        $stock = Database::fetch($stockSql, ['supplier_id' => $supplierId]);
        
        // Pending payables
        $payablesSql = "SELECT 
                        COUNT(*) as total_payables,
                        COALESCE(SUM(amount), 0) as total_payable_amount,
                        COALESCE(SUM(paid_amount), 0) as total_paid,
                        COALESCE(SUM(amount - paid_amount), 0) as pending_balance
                        FROM supplier_payables 
                        WHERE supplier_id = :supplier_id
                        AND status IN ('pending', 'partial')";
        $payables = Database::fetch($payablesSql, ['supplier_id' => $supplierId]);
        
        return [
            'advances' => $advances ?: [],
            'stock' => $stock ?: [],
            'payables' => $payables ?: []
        ];
    }

    /**
     * Get supplier product details - all individual stock receives with product info
     */
    private function getSupplierProductSummary(int $supplierId): array
    {
        $sql = "SELECT 
                    sr.id,
                    sr.receive_number,
                    sr.receive_date,
                    sr.quantity,
                    sr.unit_price,
                    sr.total_price,
                    sr.notes,
                    pc.name as product_name,
                    ct.name as type_name,
                    mu.symbol as unit_symbol,
                    l.name as location_name
                FROM stock_receives sr
                JOIN product_categories pc ON sr.product_category_id = pc.id
                JOIN category_type_units ctu ON sr.category_type_unit_id = ctu.id
                JOIN category_types ct ON ctu.category_type_id = ct.id
                JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
                LEFT JOIN locations l ON sr.location_id = l.id
                WHERE sr.supplier_id = :supplier_id
                AND sr.status = 'approved'
                ORDER BY sr.receive_date DESC, sr.id DESC";
        return Database::fetchAll($sql, ['supplier_id' => $supplierId]);
    }

    /**
     * AJAX action handler
     */
    public function action($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'get_records':
                return $this->getRecordsAjax($request, $response);
            default:
                return $response->json(['success' => false, 'message' => 'Unknown action']);
        }
    }

    /**
     * Get supplier records via AJAX
     */
    private function getRecordsAjax($request, $response)
    {
        $supplierId = $_POST['supplier_id'] ?? null;
        
        if (!$supplierId) {
            return $response->json(['success' => false, 'message' => 'Supplier is required']);
        }

        $records = $this->getSupplierRecords($supplierId);
        $summary = $this->getSupplierSummary($supplierId);
        
        return $response->json([
            'success' => true, 
            'data' => [
                'records' => $records,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Download supplier records as PDF
     */
    public function downloadPdf($request, $response)
    {
        $supplierId = $_GET['supplier_id'] ?? null;
        if ($supplierId) {
            $_SESSION['flash_info'] = 'Use the Download PDF button on the supplier records page (jsPDF).';
            return $response->redirect(APP_URL . '/suppliers/records?supplier_id=' . (int) $supplierId);
        }

        return $response->redirect(APP_URL . '/suppliers/records');
    }
}
