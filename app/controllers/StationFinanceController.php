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
        $withdrawToken = bin2hex(random_bytes(16));
        $_SESSION['withdraw_token'] = $withdrawToken;

        return View::render('finance/station-finance/withdraw', [
            'title' => 'Withdraw',
            'user' => $user,
            'location' => $location,
            'accounts' => $accounts,
            'withdrawToken' => $withdrawToken,
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
        $formToken = (string)($_POST['withdraw_token'] ?? '');
        $sessionToken = (string)($_SESSION['withdraw_token'] ?? '');
        unset($_SESSION['withdraw_token']);

        $errors = [];

        if ($formToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $formToken)) {
            $errors['general'] = 'Duplicate or invalid submission detected. Please try again.';
        }

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

            $debitDescription = 'Withdraw transfer to ' . ($toAccount['account_name'] ?? ('Account #' . $toAccountId)) . ': ' . $description;

            $this->transactionModel->createDebit(
                $fromAccountId,
                $amount,
                'station_withdraw',
                $toAccountId,
                $debitDescription,
                (int)($user['id'] ?? 0)
            );

            $this->accountModel->updateBalance($toAccountId, $amount, 'add');

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

    /**
     * Location journal report page
     */
    public function journal($request, $response)
    {
        $user = $_SESSION['user'] ?? null;

        if (!$this->hasPermission('view-station-finances')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $locationId = (int)($user['location_id'] ?? 0);
        if ($locationId <= 0) {
            $_SESSION['flash_error'] = 'Your account has no location assigned. Contact administrator.';
            return $response->redirect(APP_URL . '/finance/station-finances');
        }

        $dateFrom = trim((string)($_GET['date_from'] ?? ''));
        $dateTo = trim((string)($_GET['date_to'] ?? ''));

        $location = $this->locationModel->find($locationId);
        try {
            $journal = $this->getLocationJournalData($locationId, $dateFrom ?: null, $dateTo ?: null);
        } catch (\Throwable $e) {
            $journal = [
                'rows' => [],
                'totals' => ['debit' => 0.0, 'credit' => 0.0, 'balance' => 0.0],
            ];
            $_SESSION['flash_error'] = 'Failed to load journal data. Please contact administrator.';
        }

        return View::render('finance/station-finance/journal', [
            'title' => 'Location Journal',
            'user' => $user,
            'location' => $location,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'journalRows' => $journal['rows'],
            'journalTotals' => $journal['totals'],
            'scripts' => ['js/pages/station-journal.js']
        ], 'main');
    }

    /**
     * Final location report page
     */
    public function finalReport($request, $response)
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

        $debugMode = (string)($_GET['debug'] ?? '') === '1';
        $debugLocationId = (int)($_GET['location_id'] ?? 0);

        $locationId = $userLocationId;
        if ($debugMode && $debugLocationId > 0) {
            $locationId = $debugLocationId;
        }

        $location = $this->locationModel->find($locationId);
        $report = $this->getFinalLocationReportData($locationId);
        $expenseDebug = $debugMode ? $this->getFinalReportExpenseDiagnostics($locationId) : null;
        $accountDebug = $debugMode ? $this->getFinalReportAccountDiagnostics($locationId) : null;

        return View::render('finance/station-finance/final-report', [
            'title' => 'Final Location Report',
            'user' => $user,
            'location' => $location,
            'report' => $report,
            'debugMode' => $debugMode,
            'expenseDebug' => $expenseDebug,
            'accountDebug' => $accountDebug,
            'userLocationId' => $userLocationId,
            'effectiveLocationId' => $locationId,
            'scripts' => ['js/pages/station-final-report.js']
        ], 'main');
    }

    private function accountHasIdentifiersColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $column = Database::fetch("SHOW COLUMNS FROM accounts LIKE 'identifiers'");
        $hasColumn = !empty($column);

        return $hasColumn;
    }

    private function autreCreditTableExists(): bool
    {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $table = Database::fetch("SHOW TABLES LIKE 'tbl_autre_credit'");
        $exists = !empty($table);

        return $exists;
    }

    private function getFinalLocationReportData(int $locationId): array
    {
        $activeAdvances = (float)(Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total
             FROM supplier_advances
             WHERE location_id = :location_id
               AND status = 'approved'",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $incomingLoan = (float)(Database::fetch(
            "SELECT COALESCE(SUM(rh.amount), 0) as total
             FROM tbl_recharge_history rh
             INNER JOIN accounts a ON a.id = rh.acc_id
             WHERE a.location_id = :location_id
               AND rh.amount > 0
               AND rh.in_id IS NULL
               AND rh.to_account IS NULL",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $outgoingLoan = (float)(Database::fetch(
            "SELECT COALESCE(SUM(ABS(rh.amount)), 0) as total
             FROM tbl_recharge_history rh
             INNER JOIN accounts a ON a.id = rh.acc_id
             WHERE a.location_id = :location_id
               AND rh.amount < 0
               AND rh.in_id IS NULL
               AND rh.to_account IS NULL",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $transferLoanAvailable = max(0, $incomingLoan - $outgoingLoan);

        $stockValue = (float)(Database::fetch(
            "SELECT COALESCE(SUM(total_value), 0) as total
             FROM stock_summary
             WHERE location_id = :location_id",
            ['location_id' => $locationId]
        )['total'] ?? 0);

                $stockCheriesQuantity = (float)(Database::fetch(
                        "SELECT COALESCE(SUM(ss.total_quantity), 0) as total
                         FROM stock_summary ss
                         INNER JOIN product_categories pc ON pc.id = ss.product_category_id
                         WHERE ss.location_id = :location_id
                             AND pc.id = 1",
                        ['location_id' => $locationId]
                )['total'] ?? 0);

        $journalBankBreakdown = $this->getJournalBankBreakdown($locationId);
        $journalAmount = (float)($journalBankBreakdown['journal'] ?? 0);
        $bankAmount = (float)($journalBankBreakdown['bank'] ?? 0);

                $totalExpensesAll = (float)(Database::fetch(
                        "SELECT COALESCE(SUM(ec.amount), 0) as total
                         FROM tbl_expenseconsume ec
                         WHERE ec.status = 1
                             AND ec.station_id = :location_id",
                        ['location_id' => $locationId]
                )['total'] ?? 0);

                $expenseRows = Database::fetchAll(
                        "SELECT
                                ex.categ_id,
                                LOWER(COALESCE(cat.categ_name, '')) as categ_name,
                                LOWER(COALESCE(ex.expense_name, '')) as expense_name,
                                COALESCE(SUM(ec.amount), 0) as total
                         FROM tbl_expenseconsume ec
                         LEFT JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
                         LEFT JOIN tbl_expensecategories cat ON cat.categ_id = ex.categ_id
                         WHERE ec.status = 1
                             AND ec.station_id = :location_id
                         GROUP BY ex.categ_id, cat.categ_name, ex.expense_name",
                        ['location_id' => $locationId]
                );

        $expenseByCategory = [
            1 => 0.0,
            2 => 0.0,
            3 => 0.0,
            4 => 0.0,
        ];

        $unmappedExpenseAmount = 0.0;

        foreach ($expenseRows as $row) {
            $catId = (int)($row['categ_id'] ?? 0);
            $amount = (float)($row['total'] ?? 0);

            if (isset($expenseByCategory[$catId])) {
                $expenseByCategory[$catId] += $amount;
                continue;
            }

            $categoryName = (string)($row['categ_name'] ?? '');
            $expenseName = (string)($row['expense_name'] ?? '');
            $nameProbe = trim($categoryName . ' ' . $expenseName);

            if ($nameProbe !== '') {
                if (strpos($nameProbe, 'non exploitable') !== false || strpos($nameProbe, 'non-exploitable') !== false) {
                    $expenseByCategory[2] += $amount;
                    continue;
                }

                if (strpos($nameProbe, 'exploitable') !== false) {
                    $expenseByCategory[1] += $amount;
                    continue;
                }

                if (strpos($nameProbe, 'certif') !== false) {
                    $expenseByCategory[4] += $amount;
                    continue;
                }

                if (
                    strpos($nameProbe, 'invest') !== false ||
                    strpos($nameProbe, 'liabil') !== false ||
                    strpos($nameProbe, 'construction') !== false ||
                    strpos($nameProbe, 'rehabil') !== false
                ) {
                    $expenseByCategory[3] += $amount;
                    continue;
                }
            }

            $unmappedExpenseAmount += $amount;
        }

        if ($unmappedExpenseAmount > 0) {
            $expenseByCategory[3] += $unmappedExpenseAmount;
        }

        $approvisionnement = (float)(Database::fetch(
            "SELECT COALESCE(SUM(rh.amount), 0) as total
             FROM tbl_recharge_history rh
             INNER JOIN accounts src ON src.id = rh.acc_id
             INNER JOIN accounts dst ON dst.id = rh.to_account
             WHERE rh.to_account IS NOT NULL
               AND dst.location_id = :location_id
               AND src.location_type_id = 3",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $supplierLoansPending = (float)(Database::fetch(
            "SELECT COALESCE(SUM(amount - paid_amount), 0) as total
             FROM supplier_payables
             WHERE location_id = :location_id
               AND status IN ('pending', 'partial')",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $autreCreditPending = 0.0;
        if ($this->autreCreditTableExists()) {
            $autreCreditPending = (float)(Database::fetch(
                "SELECT COALESCE(SUM(ac.outstanding_amount), 0) as total
                 FROM tbl_autre_credit ac
                 INNER JOIN accounts a ON a.id = ac.account_id
                 WHERE a.location_id = :location_id
                   AND COALESCE(ac.outstanding_amount, 0) > 0",
                ['location_id' => $locationId]
            )['total'] ?? 0);
        }

        $suppliersTotal = $activeAdvances + $transferLoanAvailable;
        $journalBankTotal = $journalAmount + $bankAmount;
        $expensesTotal = $totalExpensesAll;
        $overallTotal = $suppliersTotal + $stockValue + $journalBankTotal + $expensesTotal;
        $liabilityTotal = $approvisionnement + $supplierLoansPending + $autreCreditPending;

        return [
            'suppliers' => [
                'advance' => $activeAdvances,
                'loan_transfer' => $transferLoanAvailable,
                'total' => $suppliersTotal,
            ],
            'stock' => [
                'stock_value' => $stockValue,
                'cheries_total_quantity' => $stockCheriesQuantity,
                'total' => $stockValue,
            ],
            'journal_bank' => [
                'journal' => $journalAmount,
                'bank' => $bankAmount,
                'total' => $journalBankTotal,
            ],
            'expenses' => [
                'exploitable' => $expenseByCategory[1],
                'non_exploitable' => $expenseByCategory[2],
                'investment_liability' => $expenseByCategory[3],
                'certification' => $expenseByCategory[4],
                'total' => $expensesTotal,
            ],
            'main_total' => $overallTotal,
            'liability' => [
                'approvisionnement' => $approvisionnement,
                'supplier_loans' => $supplierLoansPending,
                'autre_credit' => $autreCreditPending,
                'total' => $liabilityTotal,
            ],
        ];
    }

    private function getFinalReportExpenseDiagnostics(int $locationId): array
    {
        $databaseName = (string)(Database::fetch("SELECT DATABASE() as db")['db'] ?? '');

        $globalExpenseStats = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume"
        ) ?: ['cnt' => 0, 'total' => 0];

        $globalActiveExpenseStats = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume
             WHERE status = 1"
        ) ?: ['cnt' => 0, 'total' => 0];

        $activeForLocation = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume
             WHERE station_id = :location_id AND status = 1",
            ['location_id' => $locationId]
        ) ?: ['cnt' => 0, 'total' => 0];

        $anyStatusForLocation = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume
             WHERE station_id = :location_id",
            ['location_id' => $locationId]
        ) ?: ['cnt' => 0, 'total' => 0];

        $topStations = Database::fetchAll(
            "SELECT station_id, COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume
             WHERE status = 1
             GROUP BY station_id
             ORDER BY total DESC
             LIMIT 10"
        );

        $byCategory = Database::fetchAll(
            "SELECT
                ec.station_id,
                ex.categ_id,
                cat.categ_name,
                ex.expense_name,
                COUNT(*) as cnt,
                COALESCE(SUM(ec.amount), 0) as total
             FROM tbl_expenseconsume ec
             LEFT JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
             LEFT JOIN tbl_expensecategories cat ON cat.categ_id = ex.categ_id
             WHERE ec.station_id = :location_id
               AND ec.status = 1
             GROUP BY ec.station_id, ex.categ_id, cat.categ_name, ex.expense_name
             ORDER BY total DESC",
            ['location_id' => $locationId]
        );

        $orphans = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(ec.amount), 0) as total
             FROM tbl_expenseconsume ec
             LEFT JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
             WHERE ec.station_id = :location_id
               AND ec.status = 1
               AND ex.expense_id IS NULL",
            ['location_id' => $locationId]
        ) ?: ['cnt' => 0, 'total' => 0];

        $statusBreakdown = Database::fetchAll(
            "SELECT status, COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume
             WHERE station_id = :location_id
             GROUP BY status
             ORDER BY status",
            ['location_id' => $locationId]
        );

        $expenseIdBreakdown = Database::fetchAll(
            "SELECT expense_id, COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total
             FROM tbl_expenseconsume
             WHERE station_id = :location_id
               AND status = 1
             GROUP BY expense_id
             ORDER BY total DESC",
            ['location_id' => $locationId]
        );

        $expenseTypeMap = Database::fetchAll(
            "SELECT ex.expense_id, ex.categ_id, ex.expense_name, cat.categ_name
             FROM tbl_expenses ex
             LEFT JOIN tbl_expensecategories cat ON cat.categ_id = ex.categ_id
             ORDER BY ex.expense_id"
        );

        $recentRows = Database::fetchAll(
            "SELECT con_id, expense_id, station_id, amount, status, recorded_date
             FROM tbl_expenseconsume
             WHERE station_id = :location_id
             ORDER BY con_id DESC
             LIMIT 20",
            ['location_id' => $locationId]
        );

        return [
            'database_name' => $databaseName,
            'location_id' => $locationId,
            'global_expense_stats' => $globalExpenseStats,
            'global_active_expense_stats' => $globalActiveExpenseStats,
            'active_for_location' => $activeForLocation,
            'any_status_for_location' => $anyStatusForLocation,
            'top_stations' => $topStations,
            'by_category' => $byCategory,
            'orphans' => $orphans,
            'status_breakdown' => $statusBreakdown,
            'expense_id_breakdown' => $expenseIdBreakdown,
            'expense_type_map' => $expenseTypeMap,
            'recent_rows' => $recentRows,
        ];
    }

    private function getJournalBankBreakdown(int $locationId): array
    {
        $bank = 0.0;
        $journal = 0.0;
        $unknown = 0.0;

        if ($this->accountHasIdentifiersColumn()) {
            $rows = Database::fetchAll(
                "SELECT id, account_name, account_number, status, identifiers, balance
                 FROM accounts
                 WHERE location_id = :location_id",
                ['location_id' => $locationId]
            );

            foreach ($rows as $row) {
                $balance = (float)($row['balance'] ?? 0);
                $identifierRaw = strtolower(trim((string)($row['identifiers'] ?? '')));

                if ($identifierRaw === '1' || $identifierRaw === 'bank') {
                    $bank += $balance;
                    continue;
                }

                if ($identifierRaw === '2' || $identifierRaw === 'journal') {
                    $journal += $balance;
                    continue;
                }

                $unknown += $balance;
            }

            return [
                'bank' => $bank,
                'journal' => $journal,
                'unknown' => $unknown,
                'mode' => 'identifiers',
            ];
        }

        $rows = Database::fetchAll(
            "SELECT a.id, a.account_name, a.account_number, a.status, a.balance, pm.name as payment_mode_name
             FROM accounts a
             LEFT JOIN payment_modes pm ON pm.id = a.payment_mode_id
             WHERE a.location_id = :location_id",
            ['location_id' => $locationId]
        );

        foreach ($rows as $row) {
            $balance = (float)($row['balance'] ?? 0);
            $probe = strtolower(trim((string)($row['payment_mode_name'] ?? '')));

            if (strpos($probe, 'bank') !== false) {
                $bank += $balance;
                continue;
            }

            if (strpos($probe, 'journal') !== false || strpos($probe, 'cash') !== false) {
                $journal += $balance;
                continue;
            }

            $unknown += $balance;
        }

        return [
            'bank' => $bank,
            'journal' => $journal,
            'unknown' => $unknown,
            'mode' => 'payment_mode_fallback',
        ];
    }

    private function getFinalReportAccountDiagnostics(int $locationId): array
    {
        $databaseName = (string)(Database::fetch("SELECT DATABASE() as db")['db'] ?? '');
        $hasIdentifiers = $this->accountHasIdentifiersColumn();
        $breakdown = $this->getJournalBankBreakdown($locationId);

        $totalAll = (float)(Database::fetch(
            "SELECT COALESCE(SUM(balance), 0) as total
             FROM accounts
             WHERE location_id = :location_id",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $totalActive = (float)(Database::fetch(
            "SELECT COALESCE(SUM(balance), 0) as total
             FROM accounts
             WHERE location_id = :location_id
               AND status = 'active'",
            ['location_id' => $locationId]
        )['total'] ?? 0);

        $strictBankAll = 0.0;
        $strictJournalAll = 0.0;
        $strictBankActive = 0.0;
        $strictJournalActive = 0.0;

        if ($hasIdentifiers) {
            $strictAll = Database::fetch(
                "SELECT
                    COALESCE(SUM(CASE WHEN identifiers = 1 THEN balance ELSE 0 END), 0) as bank_total,
                    COALESCE(SUM(CASE WHEN identifiers = 2 THEN balance ELSE 0 END), 0) as journal_total
                 FROM accounts
                 WHERE location_id = :location_id",
                ['location_id' => $locationId]
            ) ?: [];

            $strictBankAll = (float)($strictAll['bank_total'] ?? 0);
            $strictJournalAll = (float)($strictAll['journal_total'] ?? 0);

            $strictActive = Database::fetch(
                "SELECT
                    COALESCE(SUM(CASE WHEN identifiers = 1 THEN balance ELSE 0 END), 0) as bank_total,
                    COALESCE(SUM(CASE WHEN identifiers = 2 THEN balance ELSE 0 END), 0) as journal_total
                 FROM accounts
                 WHERE location_id = :location_id
                   AND status = 'active'",
                ['location_id' => $locationId]
            ) ?: [];

            $strictBankActive = (float)($strictActive['bank_total'] ?? 0);
            $strictJournalActive = (float)($strictActive['journal_total'] ?? 0);
        }

        $statusBreakdown = Database::fetchAll(
            "SELECT COALESCE(status, 'NULL') as status, COUNT(*) as cnt, COALESCE(SUM(balance), 0) as total
             FROM accounts
             WHERE location_id = :location_id
             GROUP BY status
             ORDER BY status",
            ['location_id' => $locationId]
        );

        $identifierBreakdown = [];
        if ($hasIdentifiers) {
            $identifierBreakdown = Database::fetchAll(
                "SELECT COALESCE(CAST(identifiers AS CHAR), 'NULL') as identifiers, COUNT(*) as cnt, COALESCE(SUM(balance), 0) as total
                 FROM accounts
                 WHERE location_id = :location_id
                 GROUP BY identifiers
                 ORDER BY identifiers",
                ['location_id' => $locationId]
            );
        }

        $accountRows = Database::fetchAll(
            "SELECT a.id, a.account_name, a.account_number, a.status, a.balance,
                    " . ($hasIdentifiers ? "a.identifiers" : "NULL") . " as identifiers,
                    pm.name as payment_mode_name
             FROM accounts a
             LEFT JOIN payment_modes pm ON pm.id = a.payment_mode_id
             WHERE a.location_id = :location_id
             ORDER BY a.id ASC",
            ['location_id' => $locationId]
        );

        return [
            'database_name' => $databaseName,
            'location_id' => $locationId,
            'has_identifiers' => $hasIdentifiers,
            'classification_mode' => (string)($breakdown['mode'] ?? ''),
            'formula_checks' => [
                'total_balance_all_status' => $totalAll,
                'total_balance_active_only' => $totalActive,
                'strict_identifiers_all_status' => [
                    'bank' => $strictBankAll,
                    'journal' => $strictJournalAll,
                    'total' => $strictBankAll + $strictJournalAll,
                ],
                'strict_identifiers_active_only' => [
                    'bank' => $strictBankActive,
                    'journal' => $strictJournalActive,
                    'total' => $strictBankActive + $strictJournalActive,
                ],
                'flex_identifiers_all_status' => [
                    'bank' => (float)($breakdown['bank'] ?? 0),
                    'journal' => (float)($breakdown['journal'] ?? 0),
                    'unknown' => (float)($breakdown['unknown'] ?? 0),
                    'total' => (float)($breakdown['bank'] ?? 0) + (float)($breakdown['journal'] ?? 0) + (float)($breakdown['unknown'] ?? 0),
                ],
            ],
            'totals' => [
                'bank' => (float)($breakdown['bank'] ?? 0),
                'journal' => (float)($breakdown['journal'] ?? 0),
                'unknown' => (float)($breakdown['unknown'] ?? 0),
            ],
            'status_breakdown' => $statusBreakdown,
            'identifier_breakdown' => $identifierBreakdown,
            'accounts' => $accountRows,
        ];
    }

    private function getLocationJournalData(int $locationId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $events = [];
        $sortSeq = 0;

        $accounts = Database::fetchAll("SELECT id, account_name, account_number, location_id FROM accounts");
        $accountMap = [];
        $locationAccountIds = [];
        foreach ($accounts as $acc) {
            $label = $acc['account_name'];
            if (!empty($acc['account_number'])) {
                $label .= ' (' . $acc['account_number'] . ')';
            }
            $accountMap[(int)$acc['id']] = $label;
            if ((int)($acc['location_id'] ?? 0) === $locationId) {
                $locationAccountIds[] = (int)$acc['id'];
            }
        }
        $locationAccountIds = array_values(array_unique($locationAccountIds));

        // Recharge history for this location (external receive money only)
         $rechargeSql = "SELECT rh.rech_id, rh.amount, rh.due_date, rh.acc_id, rh.to_account,
                       src.account_name AS src_account_name,
                       src.account_number AS src_account_number,
                       src.location_id AS src_location_id,
                       dst.account_name AS dst_account_name,
                       dst.account_number AS dst_account_number,
                       dst.location_id AS dst_location_id
                   FROM tbl_recharge_history rh
                   LEFT JOIN accounts src ON rh.acc_id = src.id
                   LEFT JOIN accounts dst ON rh.to_account = dst.id
                   WHERE (src.location_id = :src_location_id OR dst.location_id = :dst_location_id)";

        $rechargeParams = ['src_location_id' => $locationId, 'dst_location_id' => $locationId];
        if ($dateFrom) {
            $rechargeSql .= " AND DATE(rh.due_date) >= :date_from";
            $rechargeParams['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $rechargeSql .= " AND DATE(rh.due_date) <= :date_to";
            $rechargeParams['date_to'] = $dateTo;
        }
        $rechargeSql .= " ORDER BY rh.due_date ASC, rh.rech_id ASC";

        $recharges = Database::fetchAll($rechargeSql, $rechargeParams);
        foreach ($recharges as $row) {
            $srcLocationId = (int)($row['src_location_id'] ?? 0);
            $dstLocationId = (int)($row['dst_location_id'] ?? 0);
            $toAccountId = (int)($row['to_account'] ?? 0);

            $srcLabel = (string)($row['src_account_name'] ?? 'N/A');
            if (!empty($row['src_account_number'])) {
                $srcLabel .= ' (' . $row['src_account_number'] . ')';
            }

            $dstLabel = (string)($row['dst_account_name'] ?? 'N/A');
            if (!empty($row['dst_account_number'])) {
                $dstLabel .= ' (' . $row['dst_account_number'] . ')';
            }

            $amount = (float)$row['amount'];

            // Classic recharge (money coming into source account from outside)
            if ($toAccountId <= 0) {
                if ($srcLocationId === $locationId) {
                    $events[] = [
                        'sort_datetime' => $row['due_date'],
                        'sort_seq' => ++$sortSeq,
                        'date' => date('Y-m-d', strtotime($row['due_date'])),
                        'description' => 'receive money',
                        'bank_cash' => $srcLabel,
                        'debit' => $amount,
                        'credit' => 0.0,
                    ];
                }
            }
        }

        // Paid-out entries from account transactions (only successful payments)
            $paidOutSql = "SELECT at.created_at, at.created_by, at.amount, at.reference_type, at.reference_id, at.description,
                              a.account_name, a.account_number
                       FROM account_transactions at
                       INNER JOIN accounts a ON at.account_id = a.id
                       WHERE a.location_id = :location_id
                         AND at.transaction_type = 'debit'
                         AND at.reference_type IN ('stock_receive', 'supplier_advance', 'payable_payment')";

        $paidOutParams = ['location_id' => $locationId];
        if ($dateFrom) {
            $paidOutSql .= " AND DATE(at.created_at) >= :date_from";
            $paidOutParams['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $paidOutSql .= " AND DATE(at.created_at) <= :date_to";
            $paidOutParams['date_to'] = $dateTo;
        }
        $paidOutSql .= " ORDER BY at.created_at ASC, at.created_by ASC";

        $paidOutRows = Database::fetchAll($paidOutSql, $paidOutParams);
        $payableIds = [];
        foreach ($paidOutRows as $row) {
            if (strtolower((string)($row['reference_type'] ?? '')) === 'payable_payment') {
                $payableId = (int)($row['reference_id'] ?? 0);
                if ($payableId > 0) {
                    $payableIds[] = $payableId;
                }
            }
        }
        $payableDetailsMap = $this->getJournalPayableDetailsMap(array_values(array_unique($payableIds)));

        foreach ($paidOutRows as $row) {
            $bankCash = $row['account_name'];
            if (!empty($row['account_number'])) {
                $bankCash .= ' (' . $row['account_number'] . ')';
            }

            $referenceType = strtolower((string)($row['reference_type'] ?? ''));
            $baseDescription = 'payment';
            if ($referenceType === 'stock_receive') {
                $baseDescription = 'purchase';
            } elseif ($referenceType === 'supplier_advance') {
                $baseDescription = 'advance';
            } elseif ($referenceType === 'payable_payment') {
                $baseDescription = 'payment';
            }

            $desc = $baseDescription;
            if ($referenceType === 'payable_payment') {
                $payableId = (int)($row['reference_id'] ?? 0);
                $payableInfo = $payableDetailsMap[$payableId] ?? null;

                if (!empty($payableInfo)) {
                    $supplierName = trim((string)($payableInfo['supplier_name'] ?? 'Supplier'));
                    $productName = trim((string)($payableInfo['product_name'] ?? 'Product'));
                    $desc = 'payment - ' . $supplierName . ' / ' . $productName;
                } elseif (!empty($row['description'])) {
                    $desc .= ' - ' . $row['description'];
                }
            } elseif (!empty($row['description'])) {
                $desc .= ' - ' . $row['description'];
            }

            $events[] = [
                'sort_datetime' => $row['created_at'],
                'sort_created_by' => (int)($row['created_by'] ?? 0),
                'sort_seq' => ++$sortSeq,
                'date' => date('Y-m-d', strtotime($row['created_at'])),
                'description' => $desc,
                'bank_cash' => $bankCash,
                'debit' => 0.0,
                'credit' => (float)$row['amount'],
            ];
        }

                // Station withdraw transfers (incoming to account only)
        $withdrawSql = "SELECT at.reference_id, at.created_at, at.created_by, at.transaction_type, at.amount, at.description,
                               a.account_name, a.account_number,
                               ref.account_name AS ref_account_name,
                               ref.account_number AS ref_account_number
                        FROM account_transactions at
                        INNER JOIN accounts a ON at.account_id = a.id
                        LEFT JOIN accounts ref ON at.reference_id = ref.id
                        WHERE a.location_id = :location_id
                                AND at.reference_type = 'station_withdraw'";

        $withdrawParams = ['location_id' => $locationId];
        if ($dateFrom) {
            $withdrawSql .= " AND DATE(at.created_at) >= :date_from";
            $withdrawParams['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $withdrawSql .= " AND DATE(at.created_at) <= :date_to";
            $withdrawParams['date_to'] = $dateTo;
        }
        $withdrawSql .= " ORDER BY at.created_at ASC, at.created_by ASC";

        $withdrawRows = Database::fetchAll($withdrawSql, $withdrawParams);
        foreach ($withdrawRows as $row) {
            $bankCash = $row['account_name'];
            if (!empty($row['account_number'])) {
                $bankCash .= ' (' . $row['account_number'] . ')';
            }

            $refBankCash = $row['ref_account_name'] ?? ('Account #' . (int)($row['reference_id'] ?? 0));
            if (!empty($row['ref_account_number'])) {
                $refBankCash .= ' (' . $row['ref_account_number'] . ')';
            }

            $txnType = strtolower((string)($row['transaction_type'] ?? ''));
            $desc = $txnType === 'credit'
                ? 'withdraw transfer in - from ' . $refBankCash
                : 'withdraw transfer out - to ' . $refBankCash;

            $events[] = [
                'sort_datetime' => $row['created_at'],
                'sort_created_by' => (int)($row['created_by'] ?? 0),
                'sort_seq' => ++$sortSeq,
                'date' => date('Y-m-d', strtotime($row['created_at'])),
                'description' => $desc,
                'bank_cash' => $bankCash,
                'debit' => (float)$row['amount'],
                'credit' => 0.0,
            ];
        }

        // Other account transactions history for this location (global transfers/adjustments/other operations)
        $otherTxnSql = "SELECT at.created_at, at.created_by, at.transaction_type, at.amount, at.reference_type, at.description,
                               a.account_name, a.account_number
                        FROM account_transactions at
                        INNER JOIN accounts a ON at.account_id = a.id
                        WHERE a.location_id = :location_id
                          AND at.reference_type NOT IN ('stock_receive', 'supplier_advance', 'payable_payment', 'station_withdraw')";

        $otherTxnParams = ['location_id' => $locationId];
        if ($dateFrom) {
            $otherTxnSql .= " AND DATE(at.created_at) >= :date_from";
            $otherTxnParams['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $otherTxnSql .= " AND DATE(at.created_at) <= :date_to";
            $otherTxnParams['date_to'] = $dateTo;
        }
        $otherTxnSql .= " ORDER BY at.created_at ASC, at.created_by ASC";

        $otherTxnRows = Database::fetchAll($otherTxnSql, $otherTxnParams);
        foreach ($otherTxnRows as $row) {
            $bankCash = $row['account_name'];
            if (!empty($row['account_number'])) {
                $bankCash .= ' (' . $row['account_number'] . ')';
            }

            $refType = trim((string)($row['reference_type'] ?? 'operation'));
            $refLabel = str_replace('_', ' ', strtolower($refType));
            $desc = $refLabel;
            if (!empty($row['description'])) {
                $desc .= ' - ' . $row['description'];
            }

            $isCredit = strtolower((string)($row['transaction_type'] ?? '')) === 'credit';
            $events[] = [
                'sort_datetime' => $row['created_at'],
                'sort_created_by' => (int)($row['created_by'] ?? 0),
                'sort_seq' => ++$sortSeq,
                'date' => date('Y-m-d', strtotime($row['created_at'])),
                'description' => $desc,
                'bank_cash' => $bankCash,
                'debit' => $isCredit ? (float)$row['amount'] : 0.0,
                'credit' => $isCredit ? 0.0 : (float)$row['amount'],
            ];
        }

        // Expenses from expense-transactions, allocated to accounts in this location
         $expenseSql = "SELECT ec.con_id, ec.pay_date, ec.recorded_date, ec.amount, ec.description, ec.pay_mode,
                      ex.expense_name,
                      ecr.cons_name as consumer_name
                       FROM tbl_expenseconsume ec
                       LEFT JOIN tbl_expenses ex ON ex.expense_id = ec.expense_id
                  LEFT JOIN tbl_expenseconsumer ecr ON ecr.cons_id = ec.payer_name
                       WHERE ec.status = 1";

        $expenseParams = [];
        if ($dateFrom) {
            $expenseSql .= " AND DATE(ec.pay_date) >= :date_from";
            $expenseParams['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $expenseSql .= " AND DATE(ec.pay_date) <= :date_to";
            $expenseParams['date_to'] = $dateTo;
        }
        $expenseSql .= " ORDER BY ec.pay_date ASC, ec.con_id ASC";

        $expenses = Database::fetchAll($expenseSql, $expenseParams);
        foreach ($expenses as $row) {
            $bankLabels = [];
            $allocatedCredit = 0.0;

            $payModeRaw = (string)($row['pay_mode'] ?? '');
            $payMode = json_decode($payModeRaw, true);

            if (is_array($payMode)) {
                foreach ($payMode as $pm) {
                    $accId = (int)($pm['account_id'] ?? 0);
                    if ($accId > 0 && in_array($accId, $locationAccountIds, true)) {
                        $bankLabels[] = $accountMap[$accId] ?? ('Account #' . $accId);
                        $allocatedCredit += (float)($pm['amount'] ?? 0);
                    }
                }
            } elseif (is_numeric($payModeRaw)) {
                $accId = (int)$payModeRaw;
                if ($accId > 0 && in_array($accId, $locationAccountIds, true)) {
                    $bankLabels[] = $accountMap[$accId] ?? ('Account #' . $accId);
                    $allocatedCredit = (float)($row['amount'] ?? 0);
                }
            }

            if ($allocatedCredit <= 0) {
                continue;
            }

            $expenseComment = trim((string)($row['description'] ?? ''));
            $expenseLabel = $expenseComment !== '' ? ('expense - ' . $expenseComment) : 'expense';

            $events[] = [
                'sort_datetime' => $row['pay_date'],
                'sort_created_by' => 0,
                'sort_seq' => ++$sortSeq,
                'date' => date('Y-m-d', strtotime($row['pay_date'])),
                'description' => $expenseLabel,
                'bank_cash' => !empty($bankLabels) ? implode(', ', array_unique($bankLabels)) : 'N/A',
                'debit' => 0.0,
                'credit' => $allocatedCredit,
            ];
        }

        usort($events, function ($a, $b) {
            $toMicroTime = function ($value): float {
                $raw = (string)$value;
                if ($raw === '') {
                    return 0.0;
                }
                $dt = date_create($raw);
                if ($dt !== false) {
                    return (float)$dt->format('U.u');
                }
                $seconds = strtotime($raw);
                return $seconds !== false ? (float)$seconds : 0.0;
            };

            $timeCmp = $toMicroTime($a['sort_datetime'] ?? '') <=> $toMicroTime($b['sort_datetime'] ?? '');
            if ($timeCmp !== 0) {
                return $timeCmp;
            }

            $createdByCmp = ((int)($a['sort_created_by'] ?? 0)) <=> ((int)($b['sort_created_by'] ?? 0));
            if ($createdByCmp !== 0) {
                return $createdByCmp;
            }

            return ((int)($a['sort_seq'] ?? 0)) <=> ((int)($b['sort_seq'] ?? 0));
        });

        $rows = [];
        $runningBalance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($events as $event) {
            $runningBalance += ($event['debit'] - $event['credit']);
            $totalDebit += $event['debit'];
            $totalCredit += $event['credit'];

            $rawDateTime = trim((string)($event['sort_datetime'] ?? ''));
            if ($rawDateTime === '') {
                $rawDateTime = trim((string)($event['date'] ?? ''));
            }

            if ($rawDateTime !== '') {
                $rawDateTime = str_replace('T', ' ', $rawDateTime);
                $rawDateTime = preg_replace('/\.\d+$/', '', $rawDateTime) ?? $rawDateTime;
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDateTime)) {
                    $rawDateTime .= ' 00:00:00';
                }
            }

            $rawDate = '';
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $rawDateTime, $m)) {
                $rawDate = $m[1];
            }

            $rows[] = [
                'date' => $rawDate !== '' ? $rawDate : (string)($event['date'] ?? ''),
                'datetime' => $rawDateTime,
                'description' => $event['description'],
                'bank_cash' => $event['bank_cash'],
                'debit' => $event['debit'],
                'credit' => $event['credit'],
                'balance' => $runningBalance,
            ];
        }

        return [
            'rows' => $rows,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balance' => $runningBalance,
            ],
        ];
    }

    private function getJournalPayableDetailsMap(array $payableIds): array
    {
        if (empty($payableIds)) {
            return [];
        }

        $params = [];
        $placeholders = [];
        foreach ($payableIds as $index => $payableId) {
            $key = 'payable_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = (int)$payableId;
        }

        $sql = "SELECT sp.id as payable_id,
                       sp.payable_number,
                       COALESCE(s.name, 'Unknown Supplier') as supplier_name,
                       COALESCE(pc.name, 'Unknown Product') as product_name
                FROM supplier_payables sp
                LEFT JOIN suppliers s ON s.id = sp.supplier_id
                LEFT JOIN stock_receives sr ON sr.id = sp.stock_receive_id
                LEFT JOIN product_categories pc ON pc.id = sr.product_category_id
                WHERE sp.id IN (" . implode(', ', $placeholders) . ")";

        $rows = Database::fetchAll($sql, $params);
        $map = [];
        foreach ($rows as $row) {
            $map[(int)($row['payable_id'] ?? 0)] = $row;
        }

        return $map;
    }
}
