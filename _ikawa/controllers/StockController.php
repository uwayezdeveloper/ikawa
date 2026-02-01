<?php
namespace Controllers;

require_once __DIR__ . '/../models/Stock.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Stock;
use Models\Account;
use Config\Response;

class StockController
{
    private $stockModel;
    private $accountModel;

    public function __construct()
    {
        $this->stockModel = new Stock();
        $this->accountModel = new Account();
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['type_id', 'unit_id', 'sup_id', 'quantity'];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $data = [
            'type_id' => trim($input['type_id']),
            'unit_id' => trim($input['unit_id']),
            'sup_id' => trim($input['sup_id']),
            'quantity' => floatval($input['quantity']),
            'loc_id' => $_SESSION['loc_id'],
            'user_id' => $_SESSION['user_id']
        ];

        $recordType = $input['record_type'] ?? 'purchase';

        if ($this->stockModel->createStock($data, $recordType)) {
            Response::success('Stock added successfully. Pending finance approval for pricing.');
        } else {
            Response::error('Failed to add stock', 500);
        }
    }

    public function createMultiple()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || !isset($input['items']) || !is_array($input['items'])) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $items = $input['items'];
        $recordType = $input['record_type'] ?? 'purchase';
        
        if (count($items) === 0) {
            Response::error('No items to save', 400);
            return;
        }

        $required = ['type_id', 'unit_id', 'sup_id', 'quantity'];
        
        foreach ($items as $index => $item) {
            foreach ($required as $field) {
                if (!isset($item[$field]) || $item[$field] === '') {
                    Response::error("Missing field: {$field} in row " . ($index + 1), 400);
                    return;
                }
            }
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            $data = [
                'type_id' => trim($item['type_id']),
                'unit_id' => trim($item['unit_id']),
                'sup_id' => trim($item['sup_id']),
                'quantity' => floatval($item['quantity']),
                'loc_id' => $_SESSION['loc_id'],
                'user_id' => $_SESSION['user_id']
            ];

            if ($this->stockModel->createStock($data, $recordType)) {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = "Row " . ($index + 1) . " failed";
            }
        }

        if ($errorCount === 0) {
            Response::success("Successfully added {$successCount} stock item(s). Pending finance approval.");
        } else if ($successCount > 0) {
            Response::success("Added {$successCount} item(s), {$errorCount} failed: " . implode(', ', $errors));
        } else {
            Response::error('Failed to add stock items. Check error log for details.', 500);
        }
    }

    public function getDetailedStock()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Try to get loc_id from session first, then from query parameter
        $loc_id = null;
        
        if (isset($_SESSION['loc_id'])) {
            $loc_id = intval($_SESSION['loc_id']);
        } elseif (isset($_GET['loc_id'])) {
            $loc_id = intval($_GET['loc_id']);
        }
        
        // If no loc_id available, return empty data
        if (!$loc_id) {
            Response::success('Stock retrieved successfully', []);
            return;
        }

        $stock = $this->stockModel->getDetailedStock($loc_id);

        if ($stock !== false) {
            Response::success('Stock retrieved successfully', $stock);
        } else {
            Response::error('Failed to retrieve stock', 500);
        }
    }

    public function processSupplierPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        // Validate required fields
        $required = ['supplier_id', 'amount', 'payment_modes'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $supplier_id = intval($input['supplier_id']);
        $total_payment_amount = floatval($input['amount']);
        $payment_modes = $input['payment_modes']; // Array of {acc_id, amount}

        // Validate payment modes array
        if (!is_array($payment_modes) || count($payment_modes) === 0) {
            Response::error('Payment modes must be a non-empty array', 400);
            return;
        }

        // Calculate total from payment modes
        $total_from_modes = 0;
        foreach ($payment_modes as $mode) {
            if (!isset($mode['acc_id']) || !isset($mode['amount'])) {
                Response::error('Invalid payment mode format', 400);
                return;
            }
            $total_from_modes += floatval($mode['amount']);
        }

        // Verify totals match
        if (abs($total_from_modes - $total_payment_amount) > 0.01) {
            Response::error('Total from payment modes does not match payment amount', 400);
            return;
        }

        try {
            // Start transaction
            $this->stockModel->beginTransaction();

            // Validate account balances
            foreach ($payment_modes as $mode) {
                $acc_id = intval($mode['acc_id']);
                $amount = floatval($mode['amount']);
                
                $balance = $this->accountModel->getBalance($acc_id);
                if ($balance === false || $balance < $amount) {
                    $this->stockModel->rollback();
                    Response::error("Insufficient balance in account ID {$acc_id}", 400);
                    return;
                }
            }

            // Get unpaid items for this supplier (ascending order)
            $unpaid_items = $this->stockModel->getUnpaidStockItems($supplier_id);
            
            if ($unpaid_items === false || count($unpaid_items) === 0) {
                $this->stockModel->rollback();
                Response::error('No unpaid items found for this supplier', 400);
                return;
            }

            // Allocate payment to items in ascending order
            $remaining_payment = $total_payment_amount;
            $payments_made = [];

            foreach ($unpaid_items as $item) {
                if ($remaining_payment <= 0) {
                    break;
                }

                $stock_detail_id = $item['stock_detail_id'];
                $item_remaining = floatval($item['remaining']);
                $item_total = floatval($item['total_price']);
                $item_paid = floatval($item['paid_amount']);
                
                // Determine how much to pay for this item
                $amount_to_pay = min($remaining_payment, $item_remaining);
                
                // Calculate new total paid after this payment
                $new_total_paid = $item_paid + $amount_to_pay;
                
                // Determine status: 2=fully paid, 1=partial
                $payment_status = ($new_total_paid >= $item_total) ? 2 : 1;
                
                // Record payment (with pay_modes as JSON, pay_date auto-set by database)
                $payment_record = [
                    'stock_detail_id' => $stock_detail_id,
                    'pay_modes' => json_encode($payment_modes),
                    'payed_amount' => $amount_to_pay,
                    'status' => $payment_status // 1=partial, 2=paid
                ];

                if (!$this->stockModel->recordSupplierPayment($payment_record)) {
                    $this->stockModel->rollback();
                    Response::error('Failed to record supplier payment', 500);
                    return;
                }

                $payments_made[] = [
                    'stock_detail_id' => $stock_detail_id,
                    'amount_paid' => $amount_to_pay,
                    'status' => $payment_status === 2 ? 'paid' : 'partial'
                ];

                $remaining_payment -= $amount_to_pay;
            }

            // Deduct amounts from accounts
            foreach ($payment_modes as $mode) {
                $acc_id = intval($mode['acc_id']);
                $amount = floatval($mode['amount']);
                
                if (!$this->accountModel->updateBalance($acc_id, $amount)) {
                    $this->stockModel->rollback();
                    Response::error('Failed to update account balance', 500);
                    return;
                }
            }

            // Commit transaction
            $this->stockModel->commit();

            Response::success('Payment processed successfully', [
                'payments_made' => $payments_made,
                'total_paid' => $total_payment_amount,
                'remaining_unpaid' => $remaining_payment
            ]);

        } catch (\Exception $e) {
            $this->stockModel->rollback();
            error_log('Payment processing error: ' . $e->getMessage());
            Response::error('Payment processing failed: ' . $e->getMessage(), 500);
        }
    }

    public function getSummaryStock()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Try to get loc_id from session first, then from query parameter
        $loc_id = null;
        
        if (isset($_SESSION['loc_id'])) {
            $loc_id = intval($_SESSION['loc_id']);
        } elseif (isset($_GET['loc_id'])) {
            $loc_id = intval($_GET['loc_id']);
        }
        
        // If no loc_id available, return empty data
        if (!$loc_id) {
            Response::success('Summary retrieved successfully', []);
            return;
        }

        $summary = $this->stockModel->getSummaryStock($loc_id);

        if ($summary !== false) {
            Response::success('Summary retrieved successfully', $summary);
        } else {
            Response::error('Failed to retrieve summary', 500);
        }
    }

    public function getPendingStock()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pending = $this->stockModel->getPendingStockForApproval();

        if ($pending !== false) {
            Response::success('Pending stock retrieved successfully', $pending);
        } else {
            Response::error('Failed to retrieve pending stock', 500);
        }
    }

    public function supplierStatement($sup_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Query params: start, end, loc_id
        $start = isset($_GET['start']) ? trim($_GET['start']) : null;
        $end = isset($_GET['end']) ? trim($_GET['end']) : null;
        $loc_id = isset($_GET['loc_id']) ? intval($_GET['loc_id']) : null;

        if (empty($sup_id)) {
            Response::error('Supplier ID is required', 400);
            return;
        }

        $result = $this->stockModel->getSupplierStatement($sup_id, $start, $end, $loc_id);

        if ($result !== false) {
            Response::success('Supplier statement retrieved', $result);
        } else {
            Response::error('Failed to retrieve supplier statement', 500);
        }
    }

    public function getStockSuppliers()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $suppliers = $this->stockModel->getDistinctSuppliers();

        if ($suppliers !== false) {
            Response::success('Suppliers retrieved successfully', $suppliers);
        } else {
            Response::error('Failed to retrieve suppliers', 500);
        }
    }

    public function approvePrice()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $stockDetailId = $input['stock_detail_id'] ?? null;
        $unitPrice = $input['unit_price'] ?? null;

        if (!$stockDetailId || !$unitPrice || $unitPrice <= 0) {
            Response::error('Stock detail ID and valid unit price are required', 400);
            return;
        }

        if ($this->stockModel->approveStockPrice(intval($stockDetailId), floatval($unitPrice), $_SESSION['user_id'])) {
            Response::success('Stock price approved successfully');
        } else {
            Response::error('Failed to approve stock price', 500);
        }
    }

    public function approveMultiple()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || !isset($input['items']) || !is_array($input['items'])) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $items = $input['items'];
        $successCount = 0;
        $errorCount = 0;

        foreach ($items as $item) {
            $stockDetailId = $item['stock_detail_id'] ?? null;
            $unitPrice = $item['unit_price'] ?? null;

            if ($stockDetailId && $unitPrice && $unitPrice > 0) {
                if ($this->stockModel->approveStockPrice(intval($stockDetailId), floatval($unitPrice), $_SESSION['user_id'])) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }

        if ($errorCount === 0) {
            Response::success("Successfully approved {$successCount} stock item(s)");
        } else if ($successCount > 0) {
            Response::success("Approved {$successCount} item(s), {$errorCount} failed");
        } else {
            Response::error('Failed to approve stock items', 500);
        }
    }
}
