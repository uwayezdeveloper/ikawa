<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\Location;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\SupplierPayable;
use App\Models\SupplierAdvance;

class StationFinanceController extends Controller
{
    protected Location $locationModel;
    protected Account $accountModel;
    protected AccountTransaction $transactionModel;
    protected SupplierPayable $payableModel;
    protected SupplierAdvance $advanceModel;

    public function __construct()
    {
        parent::__construct();
        $this->locationModel = new Location();
        $this->accountModel = new Account();
        $this->transactionModel = new AccountTransaction();
        $this->payableModel = new SupplierPayable();
        $this->advanceModel = new SupplierAdvance();
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
     * Display station finance overview
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$this->hasPermission('view-station-finances')) {
            return $response->redirect(APP_URL . '/dashboard');
        }
        
        // Get all stations (locations)
        $locations = $this->getActiveLocations();
        
        // Get selected location (default to first)
        $selectedLocationId = $_GET['location_id'] ?? ($locations[0]['id'] ?? null);
        
        $financeData = [];
        if ($selectedLocationId) {
            $financeData = $this->getLocationFinanceData($selectedLocationId);
        }

        return View::render('finance/station-finance/index', [
            'title' => 'Station Finances',
            'user' => $user,
            'locations' => $locations,
            'selectedLocationId' => $selectedLocationId,
            'financeData' => $financeData,
            'scripts' => ['js/pages/station-finance.js']
        ], 'main');
    }

    /**
     * Get active locations
     */
    private function getActiveLocations(): array
    {
        $sql = "SELECT l.*, lt.name as type_name 
                FROM locations l
                LEFT JOIN location_types lt ON l.location_type_id = lt.id
                WHERE l.status = 'active' 
                ORDER BY lt.name, l.name";
        return Database::fetchAll($sql);
    }

    /**
     * Get comprehensive finance data for a location
     */
    private function getLocationFinanceData(int $locationId): array
    {
        $location = $this->locationModel->find($locationId);
        
        // Get accounts
        $accounts = $this->getAccountsByLocation($locationId);
        $totalAccountBalance = array_sum(array_column($accounts, 'balance'));
        
        // Get recent transactions
        $transactions = $this->getRecentTransactions($locationId);
        
        // Get pending payables
        $payables = $this->payableModel->getPendingByLocation($locationId);
        $payableSummary = $this->payableModel->getSummaryByLocation($locationId);
        
        // Get active advances (given to suppliers)
        $advances = $this->getActiveAdvances($locationId);
        $totalAdvances = array_sum(array_column($advances, 'amount'));
        
        // Get stock receives summary
        $stockSummary = $this->getStockReceiveSummary($locationId);
        
        return [
            'location' => $location,
            'accounts' => $accounts,
            'totalAccountBalance' => $totalAccountBalance,
            'transactions' => $transactions,
            'payables' => $payables,
            'payableSummary' => $payableSummary,
            'advances' => $advances,
            'totalAdvances' => $totalAdvances,
            'stockSummary' => $stockSummary
        ];
    }

    /**
     * Get accounts by location with balance
     */
    private function getAccountsByLocation(int $locationId): array
    {
        $sql = "SELECT a.*, pm.name as payment_mode_name,
                (SELECT COUNT(*) FROM account_transactions WHERE account_id = a.id) as transaction_count
                FROM accounts a
                LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
                WHERE a.location_id = :location_id
                ORDER BY a.account_name";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get recent transactions for location
     */
    private function getRecentTransactions(int $locationId): array
    {
        $sql = "SELECT at.*, a.account_name
                FROM account_transactions at
                LEFT JOIN accounts a ON at.account_id = a.id
                WHERE a.location_id = :location_id
                ORDER BY at.created_at DESC
                LIMIT 20";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get active advances for location
     */
    private function getActiveAdvances(int $locationId): array
    {
        $sql = "SELECT sa.*, s.name as supplier_name
                FROM supplier_advances sa
                LEFT JOIN suppliers s ON sa.supplier_id = s.id
                WHERE sa.location_id = :location_id
                AND sa.status = 'approved'
                ORDER BY sa.created_at DESC";
        return Database::fetchAll($sql, ['location_id' => $locationId]);
    }

    /**
     * Get stock receive summary for location
     */
    private function getStockReceiveSummary(int $locationId): array
    {
        $sql = "SELECT 
                COUNT(*) as total_receives,
                COALESCE(SUM(total_price), 0) as total_value,
                COALESCE(SUM(advance_amount), 0) as paid_by_advance,
                COALESCE(SUM(account_amount), 0) as paid_by_account,
                COALESCE(SUM(payable_amount), 0) as total_payables,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count
                FROM stock_receives
                WHERE location_id = :location_id";
        return Database::fetch($sql, ['location_id' => $locationId]) ?: [];
    }

    /**
     * AJAX action handler
     */
    public function action($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'get_finance_data':
                return $this->getFinanceDataAjax($request, $response);
            case 'pay_payable':
                return $this->payPayable($request, $response);
            default:
                return $response->json(['success' => false, 'message' => 'Unknown action']);
        }
    }

    /**
     * Get finance data via AJAX
     */
    private function getFinanceDataAjax($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        
        if (!$locationId) {
            return $response->json(['success' => false, 'message' => 'Location is required']);
        }

        $data = $this->getLocationFinanceData($locationId);
        return $response->json(['success' => true, 'data' => $data]);
    }

    /**
     * Pay a supplier payable
     */
    private function payPayable($request, $response)
    {
        if (!$this->hasPermission('pay-supplier-payables')) {
            return $response->json(['success' => false, 'message' => 'Permission denied']);
        }
        
        $user = $_SESSION['user'] ?? null;
        $payableId = $_POST['payable_id'] ?? null;
        $accountId = $_POST['account_id'] ?? null;
        $amount = floatval($_POST['amount'] ?? 0);

        if (!$payableId || !$accountId || $amount <= 0) {
            return $response->json(['success' => false, 'message' => 'Invalid payment data']);
        }

        $payable = $this->payableModel->find($payableId);
        if (!$payable) {
            return $response->json(['success' => false, 'message' => 'Payable not found']);
        }

        try {
            Database::query("START TRANSACTION");

            // Create debit transaction
            $this->transactionModel->createDebit(
                $accountId,
                $amount,
                'payable_payment',
                $payableId,
                'Payment for payable #' . $payable['payable_number'],
                $user['id']
            );

            // Record payment
            $this->payableModel->recordPayment($payableId, $amount, $user['id']);

            Database::query("COMMIT");
            return $response->json(['success' => true, 'message' => 'Payment recorded successfully']);

        } catch (\Exception $e) {
            Database::query("ROLLBACK");
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Show withdraw page (same-location account transfer)
     */
    public function withdraw($request, $response)
    {
        $user = $_SESSION['user'] ?? null;

        if (!$this->hasPermission('view-station-finances')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $userLocationId = (int)($user['location_id'] ?? 0);
        if ($userLocationId <= 0) {
            $_SESSION['flash_error'] = 'Your account has no location assigned. Contact administrator.';
            return $response->redirect(APP_URL . '/finance/station-finances');
        }

        $location = $this->locationModel->find($userLocationId);
        $accounts = $this->accountModel->getByLocation($userLocationId);

        return View::render('finance/station-finance/withdraw', [
            'title' => 'Withdraw',
            'user' => $user,
            'location' => $location,
            'accounts' => $accounts,
        ], 'main');
    }

    /**
     * Process withdraw (transfer within logged-in user's location)
     */
    public function processWithdraw($request, $response)
    {
        $user = $_SESSION['user'] ?? null;

        if (!$this->hasPermission('view-station-finances')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $userLocationId = (int)($user['location_id'] ?? 0);
        if ($userLocationId <= 0) {
            $_SESSION['errors'] = ['general' => 'Your account has no location assigned.'];
            return $response->redirect(APP_URL . '/finance/station-finances/withdraw');
        }

        $fromAccountId = (int)($_POST['from_account_id'] ?? 0);
        $toAccountId = (int)($_POST['to_account_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $description = trim((string)($_POST['description'] ?? ''));

        $errors = [];

        if ($fromAccountId <= 0) {
            $errors['from_account_id'] = 'Source account is required.';
        }

        if ($toAccountId <= 0) {
            $errors['to_account_id'] = 'Destination account is required.';
        }

        if ($fromAccountId > 0 && $toAccountId > 0 && $fromAccountId === $toAccountId) {
            $errors['to_account_id'] = 'Source and destination accounts cannot be the same.';
        }

        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than 0.';
        }

        if ($description === '') {
            $errors['description'] = 'Description is required.';
        }

        $fromAccount = null;
        $toAccount = null;

        if (empty($errors)) {
            $fromAccount = $this->accountModel->findById($fromAccountId);
            $toAccount = $this->accountModel->findById($toAccountId);

            if (!$fromAccount || (int)($fromAccount['location_id'] ?? 0) !== $userLocationId) {
                $errors['from_account_id'] = 'Source account must belong to your location.';
            }

            if (!$toAccount || (int)($toAccount['location_id'] ?? 0) !== $userLocationId) {
                $errors['to_account_id'] = 'Destination account must belong to your location.';
            }

            if ($fromAccount && (float)($fromAccount['balance'] ?? 0) < $amount) {
                $errors['amount'] = 'Insufficient balance in source account.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = [
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $_POST['amount'] ?? '',
                'description' => $description,
            ];
            return $response->redirect(APP_URL . '/finance/station-finances/withdraw');
        }

        try {
            Database::query("START TRANSACTION");

            $this->transactionModel->createDebit(
                $fromAccountId,
                $amount,
                'station_withdraw',
                $toAccountId,
                'Withdraw transfer to ' . ($toAccount['account_name'] ?? ('Account #' . $toAccountId)) . ': ' . $description,
                (int)($user['id'] ?? 0)
            );

            $this->transactionModel->createCredit(
                $toAccountId,
                $amount,
                'station_withdraw',
                $fromAccountId,
                'Withdraw transfer from ' . ($fromAccount['account_name'] ?? ('Account #' . $fromAccountId)) . ': ' . $description,
                (int)($user['id'] ?? 0)
            );

            Database::query("COMMIT");

            $_SESSION['flash_success'] = 'Withdraw transfer completed successfully.';
            return $response->redirect(APP_URL . '/finance/station-finances/withdraw');
        } catch (\Exception $e) {
            Database::query("ROLLBACK");

            $_SESSION['errors'] = ['general' => 'Failed to process transfer: ' . $e->getMessage()];
            $_SESSION['old'] = [
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $_POST['amount'] ?? '',
                'description' => $description,
            ];
            return $response->redirect(APP_URL . '/finance/station-finances/withdraw');
        }
    }
}
