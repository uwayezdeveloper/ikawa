<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExpenseTransaction;
use App\Models\ExpenseType;
use App\Models\ExpenseConsumer;
use App\Models\ReceiptType;

class ExpenseTransactionController extends Controller
{
    protected ExpenseTransaction $expenseTransaction;
    protected ExpenseType $expenseType;
    protected ExpenseConsumer $expenseConsumer;
    protected ReceiptType $receiptType;

    public function __construct()
    {
        parent::__construct();
        $this->expenseTransaction = new ExpenseTransaction();
        $this->expenseType = new ExpenseType();
        $this->expenseConsumer = new ExpenseConsumer();
        $this->receiptType = new ReceiptType();
    }

    /**
     * Display expense transactions list
     */
    public function index(Request $request, Response $response)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        $userId = $_SESSION['user']['id'] ?? null;
        
        $result = $this->expenseTransaction->getPaginatedTransactions($page, $perPage, $search, $userId);
        $stats = $this->expenseTransaction->getTransactionStats($userId);
        
        $data = [
            'transactions' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['totalPages'],
                'per_page' => $result['perPage'],
                'total' => $result['total']
            ],
            'stats' => $stats,
            'search' => $search,
            'title' => 'Expense Transactions',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/transactions/index', $data, 'main');
    }

    /**
     * Show create form
     */
    public function create(Request $request, Response $response)
    {
        $userId = $_SESSION['user']['id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'User session not found';
            return $response->redirect(APP_URL . '/finance/expense-transactions');
        }

        // Use User ID 3 directly as station/location reference
        // Get related data
        $expenseTypes = $this->expenseType->getActiveExpenseTypes();
        $consumers = $this->expenseConsumer->getActiveConsumers();
        $accounts = $this->expenseTransaction->getAccountsByUserLocation($userId);
        $receiptTypes = $this->receiptType->getActiveReceiptTypes();
        
        $data = [
            'expenseTypes' => $expenseTypes,
            'consumers' => $consumers,
            'accounts' => $accounts,
            'receiptTypes' => $receiptTypes,
            'title' => 'New Expense Transaction',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/transactions/create', $data, 'main');
    }

    /**
     * Store new expense transaction with multi-payment support
     */
    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-transactions');
        }

        $userId = $_SESSION['user']['id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'User session not found';
            return $response->redirect(APP_URL . '/finance/expense-transactions');
        }

        // Parse payment data - check if multi-payment is used
        $payMode = null;
        if (isset($_POST['use_multi_payment']) && $_POST['use_multi_payment'] === '1') {
            // Multi-payment mode
            $payments = [];
            if (isset($_POST['payment_accounts']) && is_array($_POST['payment_accounts'])) {
                foreach ($_POST['payment_accounts'] as $index => $accountId) {
                    $amount = (float) ($_POST['payment_amounts'][$index] ?? 0);
                    if ($accountId > 0 && $amount > 0) {
                        $payments[] = [
                            'account_id' => (int) $accountId,
                            'amount' => $amount
                        ];
                    }
                }
            }
            $payMode = $payments; // Array for multi-payment
        } else {
            // Single payment mode
            $payMode = (int) ($_POST['pay_mode'] ?? 0);
        }
        
        $data = [
            'expense_id' => (int) ($_POST['expense_id'] ?? 0),
            'station_id' => 3, // Use User ID 3 as station/location reference
            'amount' => (float) ($_POST['amount'] ?? 0),
            'charges' => (float) ($_POST['charges'] ?? 0),
            'pay_mode' => $payMode,
            'payer_name' => !empty($_POST['payer_name']) ? (int) $_POST['payer_name'] : null,
            'receipt_type' => !empty($_POST['receipt_type']) ? (int) $_POST['receipt_type'] : null,
            'description' => trim($_POST['description'] ?? ''),
            'recorded_date' => $_POST['recorded_date'] ?? date('Y-m-d')
        ];

        // Validation
        $errors = [];

        if (empty($data['expense_id'])) {
            $errors[] = 'Please select an expense type';
        }

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Amount must be greater than 0';
        }

        if ($data['charges'] < 0) {
            $errors[] = 'Charges cannot be negative';
        }

        // Validate payment mode
        if (is_array($payMode)) {
            // Multi-payment validation
            if (empty($payMode)) {
                $errors[] = 'Please add at least one payment account';
            } else {
                $totalPaymentAmount = array_sum(array_column($payMode, 'amount'));
                $transactionAmount = $data['amount'] + $data['charges'];
                
                if (abs($totalPaymentAmount - $transactionAmount) > 0.01) {
                    $errors[] = 'Total payment amount (' . number_format($totalPaymentAmount, 2) . ') must equal transaction amount (' . number_format($transactionAmount, 2) . ')';
                }
            }
        } else {
            // Single payment validation
            if (empty($payMode)) {
                $errors[] = 'Please select a payment account';
            }
        }

        if (!empty($data['description']) && strlen($data['description']) > 100) {
            $errors[] = 'Description must not exceed 100 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . '/finance/expense-transactions/create');
        }

        try {
            $success = $this->expenseTransaction->createTransaction($data, $userId);
            
            if ($success) {
                if (is_array($payMode)) {
                    $_SESSION['success'] = 'Expense transaction created successfully using ' . count($payMode) . ' payment account(s)';
                } else {
                    $_SESSION['success'] = 'Expense transaction created successfully';
                }
            } else {
                $_SESSION['error'] = 'Failed to create expense transaction';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-transactions');
    }

    /**
     * Show transaction details with multi-payment support
     */
    public function show(Request $request, Response $response, array $params)
    {
        try {
            error_log("ExpenseTransactionController::show - Starting with params: " . print_r($params, true));
            
            $id = (int) $params['id'];
            error_log("ExpenseTransactionController::show - ID parsed: " . $id);
            
            $transaction = $this->expenseTransaction->findByIdWithDetails($id);
            error_log("ExpenseTransactionController::show - Transaction result: " . ($transaction ? 'found' : 'not found'));
            
            if (!$transaction) {
                error_log("ExpenseTransactionController::show - Transaction not found, redirecting");
                $_SESSION['error'] = 'Transaction not found';
                return $response->redirect(APP_URL . '/finance/expense-transactions');
            }

            error_log("ExpenseTransactionController::show - Getting transaction details for trans_id: " . $transaction['trans_id']);
            // Get transaction details
            $details = $this->expenseTransaction->getTransactionDetails($transaction['trans_id']);
            error_log("ExpenseTransactionController::show - Details count: " . count($details));

            // Get payment details from JSON pay_mode
            $paymentDetails = [];
            if (!empty($transaction['pay_mode'])) {
                $paymentDetails = $this->expenseTransaction->getPaymentDetails($transaction['pay_mode']);
                error_log("ExpenseTransactionController::show - Payment details count: " . count($paymentDetails));
            }

            $data = [
                'transaction' => $transaction,
                'details' => $details,
                'payments' => $paymentDetails,
                'title' => 'Transaction Details',
                'user' => $_SESSION['user'] ?? []
            ];
            
            error_log("ExpenseTransactionController::show - About to render view");
            return View::render('finance/expenses/transactions/show', $data, 'main');
            
        } catch (\Exception $e) {
            error_log("ExpenseTransactionController::show - Exception: " . $e->getMessage());
            error_log("ExpenseTransactionController::show - Trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Error loading transaction details: ' . $e->getMessage();
            return $response->redirect(APP_URL . '/finance/expense-transactions');
        } catch (\Error $e) {
            error_log("ExpenseTransactionController::show - Fatal Error: " . $e->getMessage());
            error_log("ExpenseTransactionController::show - Trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'System error loading transaction details';
            return $response->redirect(APP_URL . '/finance/expense-transactions');
        }
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->json(['success' => false, 'message' => 'Invalid request method']);
        }

        $id = (int) $params['id'];
        $status = (int) ($_POST['status'] ?? 0);

        try {
            $success = $this->expenseTransaction->updateStatus($id, $status);
            
            if ($success) {
                return $response->json(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to update status']);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get expense types by category for AJAX
     */
    public function getExpenseTypesByCategory(Request $request, Response $response)
    {
        $categoryId = (int) ($_GET['category_id'] ?? 0);
        
        if (!$categoryId) {
            return $response->json(['success' => false, 'message' => 'Category ID required']);
        }

        try {
            $expenseTypes = $this->expenseType->getByCategory($categoryId);
            return $response->json(['success' => true, 'data' => $expenseTypes]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Search consumers for AJAX
     */
    public function searchConsumers(Request $request, Response $response)
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query) || strlen($query) < 2) {
            return $response->json(['success' => false, 'message' => 'Query must be at least 2 characters']);
        }

        try {
            $consumers = $this->expenseConsumer->searchConsumers($query);
            return $response->json(['success' => true, 'data' => $consumers]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request, Response $response)
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        // Get all transactions for export
        $result = $this->expenseTransaction->getPaginatedTransactions(1, 10000, '', $userId);
        
        $filename = 'expense_transactions_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, [
            'Transaction ID', 'Date', 'Expense Type', 'Consumer', 'Amount', 
            'Account', 'Receipt Type', 'Status', 'Description'
        ]);
        
        // Data rows
        foreach ($result['data'] as $transaction) {
            fputcsv($output, [
                $transaction['trans_id'],
                $transaction['pay_date'],
                $transaction['expense_name'],
                $transaction['consumer_name'],
                $transaction['amount'],
                $transaction['account_name'],
                $transaction['receipt_type_name'],
                $transaction['status'] == 1 ? 'Active' : 'Inactive',
                $transaction['description']
            ]);
        }
        
        fclose($output);
        exit;
    }
}