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
        if (!$this->hasPermission('view-supplier-records')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $supplierId = $_GET['supplier_id'] ?? null;
        
        if (!$supplierId) {
            return $response->redirect(APP_URL . '/suppliers/records');
        }

        // Get supplier info
        $supplierInfo = $this->supplierModel->find($supplierId);
        if (!$supplierInfo) {
            return $response->redirect(APP_URL . '/suppliers/records');
        }

        // Get data
        $productSummary = $this->getSupplierProductSummary($supplierId);
        $records = $this->getSupplierRecords($supplierId);
        $summary = $this->getSupplierSummary($supplierId);

        // Get company settings
        $settingModel = new Setting();
        $companySettings = $settingModel->getCompanySettings();
        $companyName = $companySettings['company_name'] ?? 'Gihanga Coffee';
        $companyPhone = $companySettings['company_phone'] ?? '';
        $companyEmail = $companySettings['company_email'] ?? '';
        $companyAddress = $companySettings['company_address'] ?? '';
        $companyLogo = $companySettings['company_logo'] ?? '';

        // Create PDF
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator($companyName);
        $pdf->SetAuthor($companyName);
        $pdf->SetTitle('Supplier Records - ' . $supplierInfo['name']);
        $pdf->SetSubject('Supplier Transaction History');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Company Header
        $logoPath = BASE_PATH . '/assets/images/logos/' . $companyLogo;
        if ($companyLogo && file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 15, 30, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->SetXY(50, 15);
        }
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 8, $companyName, 0, 1, $companyLogo && file_exists($logoPath) ? 'L' : 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        if ($companyAddress) {
            $pdf->Cell(0, 5, $companyAddress, 0, 1, $companyLogo && file_exists($logoPath) ? 'L' : 'C');
        }
        if ($companyPhone || $companyEmail) {
            $contactLine = '';
            if ($companyPhone) $contactLine .= 'Tel: ' . $companyPhone;
            if ($companyPhone && $companyEmail) $contactLine .= '  |  ';
            if ($companyEmail) $contactLine .= 'Email: ' . $companyEmail;
            $pdf->Cell(0, 5, $contactLine, 0, 1, $companyLogo && file_exists($logoPath) ? 'L' : 'C');
        }
        
        $pdf->Ln(5);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        // Document Title
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'SUPPLIER RECORDS', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Generated on: ' . date('F d, Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);

        // Supplier Info
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Supplier Information', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->Cell(40, 6, 'Name:', 0, 0);
        $pdf->Cell(0, 6, $supplierInfo['name'], 0, 1);
        
        $pdf->Cell(40, 6, 'Phone:', 0, 0);
        $pdf->Cell(0, 6, $supplierInfo['phone'] ?? 'N/A', 0, 1);
        
        $pdf->Cell(40, 6, 'Email:', 0, 0);
        $pdf->Cell(0, 6, $supplierInfo['email'] ?? 'N/A', 0, 1);
        
        $pdf->Cell(40, 6, 'Address:', 0, 0);
        $pdf->Cell(0, 6, $supplierInfo['address'] ?? 'N/A', 0, 1);
        $pdf->Ln(5);

        // Summary Section
        $advances = $summary['advances'] ?? [];
        $stock = $summary['stock'] ?? [];
        $payables = $summary['payables'] ?? [];

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Summary', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        // Summary table
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(60, 7, 'Total Advances Given:', 1, 0, 'L', true);
        $pdf->Cell(60, 7, number_format($advances['total_advance_amount'] ?? 0, 2), 1, 1, 'R');
        
        $pdf->Cell(60, 7, 'Total Stock Value:', 1, 0, 'L', true);
        $pdf->Cell(60, 7, number_format($stock['total_stock_value'] ?? 0, 2), 1, 1, 'R');
        
        $pdf->Cell(60, 7, 'Pending Payables:', 1, 0, 'L', true);
        $pdf->Cell(60, 7, number_format($payables['pending_balance'] ?? 0, 2), 1, 1, 'R');
        $pdf->Ln(10);

        // Products Supplied
        if (!empty($productSummary)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'Products Supplied (Detailed)', 0, 1, 'L');
            
            // Table header
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(25, 7, 'Date', 1, 0, 'C', true);
            $pdf->Cell(25, 7, 'Reference', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Product', 1, 0, 'C', true);
            $pdf->Cell(25, 7, 'Type', 1, 0, 'C', true);
            $pdf->Cell(25, 7, 'Quantity', 1, 0, 'C', true);
            $pdf->Cell(25, 7, 'Unit Price', 1, 0, 'C', true);
            $pdf->Cell(25, 7, 'Total', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 8);
            $grandTotalQty = 0;
            $grandTotalValue = 0;
            
            foreach ($productSummary as $item) {
                $grandTotalQty += $item['quantity'];
                $grandTotalValue += $item['total_price'];
                
                $pdf->Cell(25, 6, date('M d, Y', strtotime($item['receive_date'])), 1, 0, 'L');
                $pdf->Cell(25, 6, $item['receive_number'] ?? 'RCV-' . $item['id'], 1, 0, 'L');
                $pdf->Cell(30, 6, $item['product_name'], 1, 0, 'L');
                $pdf->Cell(25, 6, $item['type_name'], 1, 0, 'L');
                $pdf->Cell(25, 6, number_format($item['quantity'], 2) . ' ' . $item['unit_symbol'], 1, 0, 'R');
                $pdf->Cell(25, 6, number_format($item['unit_price'], 2), 1, 0, 'R');
                $pdf->Cell(25, 6, number_format($item['total_price'], 2), 1, 1, 'R');
            }

            // Totals row
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(105, 7, 'Grand Total:', 1, 0, 'R', true);
            $pdf->Cell(25, 7, number_format($grandTotalQty, 2), 1, 0, 'R', true);
            $pdf->Cell(25, 7, '', 1, 0, 'R', true);
            $pdf->Cell(25, 7, number_format($grandTotalValue, 2), 1, 1, 'R', true);
            $pdf->Ln(10);
        }

        // Transaction History
        $pdf->AddPage();
        
        // Company Header on second page
        if ($companyLogo && file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 15, 30, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->SetXY(50, 15);
        }
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 8, $companyName, 0, 1, $companyLogo && file_exists($logoPath) ? 'L' : 'C');
        $pdf->SetFont('helvetica', '', 9);
        if ($companyAddress) {
            $pdf->Cell(0, 5, $companyAddress, 0, 1, $companyLogo && file_exists($logoPath) ? 'L' : 'C');
        }
        $pdf->Ln(3);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Transaction History', 0, 1, 'L');

        // Table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(22, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(22, 7, 'Type', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Reference', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Description', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Debit', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Credit', 1, 0, 'C', true);
        $pdf->Cell(18, 7, 'Status', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($records as $record) {
            $totalDebit += $record['debit'];
            $totalCredit += $record['credit'];
            
            $typeLabel = ucfirst(str_replace('_', ' ', $record['type']));
            
            $pdf->Cell(22, 6, date('M d, Y', strtotime($record['date'])), 1, 0, 'L');
            $pdf->Cell(22, 6, $typeLabel, 1, 0, 'L');
            $pdf->Cell(25, 6, $record['reference'], 1, 0, 'L');
            $pdf->Cell(50, 6, substr($record['description'], 0, 35), 1, 0, 'L');
            $pdf->Cell(25, 6, $record['debit'] > 0 ? number_format($record['debit'], 2) : '-', 1, 0, 'R');
            $pdf->Cell(25, 6, $record['credit'] > 0 ? number_format($record['credit'], 2) : '-', 1, 0, 'R');
            $pdf->Cell(18, 6, ucfirst($record['status']), 1, 1, 'C');
        }

        // Totals row
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(119, 7, 'Totals:', 1, 0, 'R', true);
        $pdf->Cell(25, 7, number_format($totalDebit, 2), 1, 0, 'R', true);
        $pdf->Cell(25, 7, number_format($totalCredit, 2), 1, 0, 'R', true);
        
        $balance = $totalCredit - $totalDebit;
        $pdf->Cell(18, 7, number_format($balance, 2), 1, 1, 'R', true);

        // Output PDF
        $filename = 'Supplier_Records_' . preg_replace('/[^a-zA-Z0-9]/', '_', $supplierInfo['name']) . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
}
