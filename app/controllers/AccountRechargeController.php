<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Account;
use App\Models\RechargeHistory;
use App\Models\ExpenseTransaction;
use App\Models\SourceOfIncome;
use App\Models\CurrencyExchangeRate;
use App\Models\CurrencyType;

class AccountRechargeController extends Controller
{
    protected Account $accountModel;
    protected RechargeHistory $rechargeHistoryModel;
    protected SourceOfIncome $sourceOfIncomeModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
        $this->rechargeHistoryModel = new RechargeHistory();
        $this->sourceOfIncomeModel = new SourceOfIncome();
    }

    /**
     * Display account recharge page
     */
    public function index(Request $request, Response $response): void
    {
        try {
            // Get all active accounts for recharge
            $receivingAccounts = $this->accountModel->getReceivingAccounts();
            
            // Get all active source of income
            $sourcesOfIncome = $this->sourceOfIncomeModel->getActiveSources();
            
            $this->view('finance/recharge/index', [
                'receivingAccounts' => $receivingAccounts,
                'sourcesOfIncome' => $sourcesOfIncome,
                'user' => $_SESSION['user'] ?? [],
                'pageTitle' => 'Account Recharge'
            ], 'main');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to load accounts');
        }
    }

    /**
     * Display account transfer page
     */
    public function transfer(Request $request, Response $response): void
    {
        try {
            // Get accounts that can transfer money (with location_type_id = 3 and positive balance)
            $transferAccounts = $this->accountModel->getTransferEnabledAccounts();
            
            // Get all active accounts for receiving transfers
            $receivingAccounts = $this->accountModel->getReceivingAccounts();
            
            $this->view('finance/transfer/index', [
                'transferAccounts' => $transferAccounts,
                'receivingAccounts' => $receivingAccounts,
                'user' => $_SESSION['user'] ?? [],
                'pageTitleb' => 'Account Transfer'
            ], 'main');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to load transfer page');
        }
    }

    /**
     * Process account recharge/transfer
     */
    public function recharge(Request $request, Response $response): void
    {
        try {
            $data = $request->getBody();
            
            // Determine if this is a transfer or simple recharge
            $isTransfer = !empty($data['to_account_id']);
            
            // Validate input
            $errors = $isTransfer ? 
                $this->validateTransferData($data) : 
                $this->validateRechargeData($data);

            if (!empty($errors)) {
                if ($request->isAjax()) {
                    $response->error('Validation failed', 422, $errors);
                    return;
                }
                
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $data;
                $response->redirect(APP_URL . '/finance/account-recharge');
                return;
            }

            if ($isTransfer) {
                $this->processTransfer($data, $request, $response);
            } else {
                $this->processSimpleRecharge($data, $request, $response);
            }

        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to process transaction');
        }
    }

    /**
     * Process account transfer between two accounts
     */
    private function processTransfer(array $data, Request $request, Response $response): void
    {
        $fromAccountId = (int) $data['from_account_id'];
        $toAccountId = (int) $data['to_account_id'];
        $amount = (float) $data['amount'];
        $incomeSourceId = !empty($data['in_id']) ? (int) $data['in_id'] : null;
        
        // Check if source account can transfer
        if (!$this->accountModel->canTransfer($fromAccountId)) {
            $errorMsg = 'Source account cannot transfer money. Account must be active, have positive balance, and have a valid location type.';
            if ($request->isAjax()) {
                $response->error($errorMsg, 400);
                return;
            }
            
            $_SESSION['errors'] = ['from_account_id' => $errorMsg];
            $response->redirect(APP_URL . '/finance/account-recharge');
            return;
        }

        // Check source account balance
        $fromAccount = $this->accountModel->findById($fromAccountId);
        if ($fromAccount['balance'] < $amount) {
            $errorMsg = 'Insufficient balance. Available: $' . number_format($fromAccount['balance'], 2);
            if ($request->isAjax()) {
                $response->error($errorMsg, 400);
                return;
            }
            
            $_SESSION['errors'] = ['amount' => $errorMsg];
            $response->redirect(APP_URL . '/finance/account-recharge');
            return;
        }

        // Check destination account
        $toAccount = $this->accountModel->findById($toAccountId);
        if (!$toAccount || $toAccount['status'] !== 'active') {
            $errorMsg = 'Destination account not found or inactive';
            if ($request->isAjax()) {
                $response->error($errorMsg, 404);
                return;
            }
            
            $_SESSION['errors'] = ['to_account_id' => $errorMsg];
            $response->redirect(APP_URL . '/finance/account-recharge');
            return;
        }

        // Process the transfer
        $success = $this->processAccountTransfer($fromAccountId, $toAccountId, $amount, $incomeSourceId);

        if ($success) {
            $successMsg = "Transfer successful! $" . number_format($amount, 2) . 
                         " transferred from {$fromAccount['account_name']} to {$toAccount['account_name']}";
                         
            if ($request->isAjax()) {
                $response->success([
                    'from_account_id' => $fromAccountId,
                    'to_account_id' => $toAccountId,
                    'amount' => $amount,
                    'from_new_balance' => $fromAccount['balance'] - $amount,
                    'to_new_balance' => $toAccount['balance'] + $amount
                ], $successMsg);
                return;
            }
            
            $_SESSION['success'] = $successMsg;
            $response->redirect(APP_URL . '/finance/account-recharge');
        } else {
            $errorMsg = 'Failed to process transfer. Please try again.';
            if ($request->isAjax()) {
                $response->error($errorMsg, 500);
                return;
            }
            
            $_SESSION['errors'] = ['general' => $errorMsg];
            $response->redirect(APP_URL . '/finance/account-recharge');
        }
    }

    /**
     * Process simple recharge (no transfer)
     */
    private function processSimpleRecharge(array $data, Request $request, Response $response): void
    {
        $accountId = (int) $data['account_id'];
        $amount = (float) $data['amount'];
        $incomeSourceId = !empty($data['in_id']) ? (int) $data['in_id'] : null;
        
        // Check if account exists and is active
        $account = $this->accountModel->findById($accountId);
        if (!$account) {
            if ($request->isAjax()) {
                $response->error('Account not found', 404);
                return;
            }
            
            $_SESSION['errors'] = ['account_id' => 'Account not found'];
            $response->redirect(APP_URL . '/finance/account-recharge');
            return;
        }

        if ($account['status'] !== 'active') {
            if ($request->isAjax()) {
                $response->error('Account is not active', 400);
                return;
            }
            
            $_SESSION['errors'] = ['account_id' => 'Account is not active'];
            $response->redirect(APP_URL . '/finance/account-recharge');
            return;
        }

        // Process the recharge
        $success = $this->processRecharge($accountId, $amount, $incomeSourceId);

        if ($success) {
            if ($request->isAjax()) {
                $response->success([
                    'account_id' => $accountId,
                    'amount' => $amount,
                    'new_balance' => $account['balance'] + $amount
                ], 'Account recharged successfully');
                return;
            }
            
            $_SESSION['success'] = 'Account recharged successfully with amount: $' . number_format($amount, 2);
            $response->redirect(APP_URL . '/finance/account-recharge');
        } else {
            if ($request->isAjax()) {
                $response->error('Failed to process recharge', 500);
                return;
            }
            
            $_SESSION['errors'] = ['general' => 'Failed to process recharge. Please try again.'];
            $response->redirect(APP_URL . '/finance/account-recharge');
        }
    }

    /**
     * Get account details via AJAX
     */
    public function getAccountDetails(Request $request, Response $response): void
    {
        try {
            $accountId = $request->query('id');
            
            if (!$accountId) {
                $response->error('Account ID is required', 400);
                return;
            }

            $account = $this->accountModel->findById((int) $accountId);
            
            if (!$account) {
                $response->error('Account not found', 404);
                return;
            }

            $response->success($account, 'Account details retrieved');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to get account details');
        }
    }

    /**
     * Get transfer-enabled accounts via AJAX
     */
    public function getTransferEnabledAccounts(Request $request, Response $response): void
    {
        try {
            $locationTypeId = $request->query('location_type_id');
            $accounts = $this->accountModel->getTransferEnabledAccounts($locationTypeId ? (int)$locationTypeId : null);
            
            $response->success($accounts, 'Transfer-enabled accounts retrieved');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to get transfer-enabled accounts');
        }
    }

    /**
     * View recharge history
     */
    public function history(Request $request, Response $response): void
    {
        try {
            $page = max(1, (int) $request->query('page', 1));
            $perPage = 15;
            
            // Get recharge history with pagination
            $history = $this->rechargeHistoryModel->getPaginatedHistory($page, $perPage);
            
            $this->view('finance/recharge/history', [
                'history' => $history,
                'user' => $_SESSION['user'] ?? [],
                'pageTitle' => 'Recharge History'
            ], 'main');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to load recharge history');
        }
    }

    /**
     * Account activity report - shows per-account expenses, recharges and transfers
     */
    public function activityReport(Request $request, Response $response): void
    {
        try {
            $accounts = $this->accountModel->getAll();
            $expenseModel = new ExpenseTransaction();

            $selected = $request->query('account_ids');
            if (!$selected) {
                // support single account param
                $single = $request->query('account_id');
                if ($single) {
                    $selected = [$single];
                }
            }

            $dateFrom = $request->query('date_from');
            $dateTo = $request->query('date_to');

            $report = [];

            if (!empty($selected) && is_array($selected)) {
                foreach ($selected as $aid) {
                    $accountId = (int) $aid;
                    $acct = $this->accountModel->findById($accountId);
                    if (!$acct) continue;

                    // Expenses where account was used
                    $txs = $expenseModel->getTransactionsForAccount($accountId, $dateFrom, $dateTo);
                    $expenseCount = count($txs);
                    $expenseSum = 0.0;
                    $expenseCharges = 0.0;
                    foreach ($txs as $t) {
                        $expenseSum += (float) ($t['allocated_amount'] ?? 0);
                        $expenseCharges += (float) ($t['account_charges'] ?? 0);
                    }

                    // Recharges and transfers
                    $totalRecharges = $this->rechargeHistoryModel->getTotalRechargeAmountBetween($accountId, $dateFrom, $dateTo);
                    $transfers = $this->rechargeHistoryModel->getTransferSumsBetween($accountId, $dateFrom, $dateTo);

                    $report[] = [
                        'account' => $acct,
                        'expense_count' => $expenseCount,
                        'expense_total' => $expenseSum,
                        'expense_charges' => $expenseCharges,
                        'recharges_total' => $totalRecharges,
                        'transfer_out' => $transfers['out'],
                        'transfer_in' => $transfers['in']
                    ];
                }
            }

            $this->view('finance/reports/account_activity', [
                'accounts' => $accounts,
                'report' => $report,
                'selected' => $selected,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'pageTitle' => 'Account Activity Report'
            ], 'main');

        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to generate activity report');
        }
    }

    /**
     * Process the actual recharge transaction
     */
    private function processRecharge(int $accountId, float $amount, ?int $incomeSourceId = null): bool
    {
        try {
            // Start transaction
            \App\Core\Database::getInstance()->beginTransaction();

            // Update account balance
            $success = $this->accountModel->updateBalance($accountId, $amount);
            
            if (!$success) {
                \App\Core\Database::getInstance()->rollBack();
                return false;
            }

            // Record recharge history (simple recharge, no to_account)
            $historyId = $this->rechargeHistoryModel->recordRecharge($accountId, $amount, $incomeSourceId);
            
            if (!$historyId) {
                \App\Core\Database::getInstance()->rollBack();
                return false;
            }

            // Commit transaction
            \App\Core\Database::getInstance()->commit();
            return true;

        } catch (\Exception $e) {
            \App\Core\Database::getInstance()->rollBack();
            error_log("Recharge processing error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process account transfer
     */
    private function processAccountTransfer(int $fromAccountId, int $toAccountId, float $amount, ?int $incomeSourceId = null): bool
    {
        try {
            // Start transaction
            \App\Core\Database::getInstance()->beginTransaction();

            // Perform the transfer
            $success = $this->accountModel->transferMoney($fromAccountId, $toAccountId, $amount);
            
            if (!$success) {
                \App\Core\Database::getInstance()->rollBack();
                return false;
            }

            // Record transfer history
            $historyId = $this->rechargeHistoryModel->recordTransfer($fromAccountId, $toAccountId, $amount, $incomeSourceId);
            
            if (!$historyId) {
                \App\Core\Database::getInstance()->rollBack();
                return false;
            }

            // Commit transaction
            \App\Core\Database::getInstance()->commit();
            return true;

        } catch (\Exception $e) {
            \App\Core\Database::getInstance()->rollBack();
            error_log("Transfer processing error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate recharge data with custom numeric validation
     */
    private function validateRechargeData(array $data): array
    {
        $errors = [];
        
        // Validate account_id
        if (empty($data['account_id'])) {
            $errors['account_id'] = 'Account is required.';
        } elseif (!is_numeric($data['account_id']) || (int)$data['account_id'] <= 0) {
            $errors['account_id'] = 'Please select a valid account.';
        }
        
        // Validate amount
        if (empty($data['amount']) && $data['amount'] !== '0') {
            $errors['amount'] = 'Amount is required.';
        } elseif (!is_numeric($data['amount'])) {
            $errors['amount'] = 'Amount must be a valid number.';
        } elseif ((float)$data['amount'] <= 0) {
            $errors['amount'] = 'Amount must be greater than 0.';
        } elseif ((float)$data['amount'] < 0.01) {
            $errors['amount'] = 'Amount must be at least $0.01.';
        }
        
        return $errors;
    }

    /**
     * Validate transfer data
     */
    private function validateTransferData(array $data): array
    {
        $errors = [];
        
        // Validate from_account_id
        if (empty($data['from_account_id'])) {
            $errors['from_account_id'] = 'Source account is required.';
        } elseif (!is_numeric($data['from_account_id']) || (int)$data['from_account_id'] <= 0) {
            $errors['from_account_id'] = 'Please select a valid source account.';
        }
        
        // Validate to_account_id
        if (empty($data['to_account_id'])) {
            $errors['to_account_id'] = 'Destination account is required.';
        } elseif (!is_numeric($data['to_account_id']) || (int)$data['to_account_id'] <= 0) {
            $errors['to_account_id'] = 'Please select a valid destination account.';
        }
        
        // Check if trying to transfer to same account
        if (!empty($data['from_account_id']) && !empty($data['to_account_id']) && 
            $data['from_account_id'] == $data['to_account_id']) {
            $errors['to_account_id'] = 'Cannot transfer to the same account.';
        }
        
        // Validate amount
        if (empty($data['amount']) && $data['amount'] !== '0') {
            $errors['amount'] = 'Amount is required.';
        } elseif (!is_numeric($data['amount'])) {
            $errors['amount'] = 'Amount must be a valid number.';
        } elseif ((float)$data['amount'] <= 0) {
            $errors['amount'] = 'Amount must be greater than 0.';
        } elseif ((float)$data['amount'] < 0.01) {
            $errors['amount'] = 'Amount must be at least $0.01.';
        }
        
        return $errors;
    }

    /**
     * Handle errors consistently
     */
    private function handleError(\Exception $e, string $userMessage): void
    {
        error_log("AccountRechargeController Error: " . $e->getMessage());
        
        if (APP_ENV === 'development') {
            throw $e;
        }
        
        $_SESSION['errors'] = ['general' => $userMessage];
        (new Response())->redirect(APP_URL . '/finance/account-recharge');
    }

    /**
     * Display transfer to another account page
     */
    public function transferToAccount(Request $request, Response $response): void
    {
        try {

            $user = $_SESSION['user'] ?? [];
            $userLocationId = (int)($user['location_id'] ?? 0);
            
            // Get all accounts from user's location (source accounts)
            $sourceAccounts = [];
            if ($userLocationId > 0) {
                $sourceAccounts = $this->accountModel->getByLocation($userLocationId);
            }
            
            // Get all other accounts in the system (destination accounts)
            $destinationAccounts = $this->accountModel->getAll();
            
            // Remove user's location accounts from destination list
            if ($userLocationId > 0) {
                $destinationAccounts = array_filter($destinationAccounts, function($account) use ($userLocationId) {
                    return (int)($account['location_id'] ?? 0) !== $userLocationId;
                });
            }
            
            $this->view('finance/transfer-to-account/index', [
                'sourceAccounts' => $sourceAccounts,
                'destinationAccounts' => $destinationAccounts,
                'user' => $user,
                'pageTitle' => 'Transfer to Another Account'
            ], 'main');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to load transfer page');
        }
    }

    /**
     * Process transfer to another account
     */
    public function processTransferToAccount(Request $request, Response $response): void
    {
        try {
            $data = $request->getBody();
            
            // Validate transfer data
            $errors = $this->validateTransferToAccountData($data);

            if (!empty($errors)) {
                if ($request->isAjax()) {
                    $response->error('Validation failed', 422, $errors);
                    return;
                }
                
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $data;
                $response->redirect(APP_URL . '/finance/transfer-to-account');
                return;
            }

            // Process the transfer
            $this->processUserLocationTransfer($data, $request, $response);

        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to process transfer');
        }
    }

    /**
     * Validate transfer to account data
     */
    protected function validateTransferToAccountData(array $data): array
    {
        $errors = [];

        if (empty($data['from_account_id'])) {
            $errors['from_account_id'] = 'Source account is required';
        }

        if (empty($data['to_account_id'])) {
            $errors['to_account_id'] = 'Destination account is required';
        }

        if (empty($data['amount']) || !is_numeric($data['amount']) || floatval($data['amount']) <= 0) {
            $errors['amount'] = 'Valid transfer amount is required';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Transfer description is required';
        }

        if (!empty($data['from_account_id']) && !empty($data['to_account_id'])) {
            if ($data['from_account_id'] === $data['to_account_id']) {
                $errors['to_account_id'] = 'Source and destination accounts cannot be the same';
            }
        }

        // Validate source account belongs to user's location
        if (!empty($data['from_account_id'])) {
            $user = $_SESSION['user'] ?? [];
            $userLocationId = (int)($user['location_id'] ?? 0);
            
            if ($userLocationId > 0) {
                $sourceAccount = $this->accountModel->findById((int)$data['from_account_id']);
                if (!$sourceAccount || (int)($sourceAccount['location_id'] ?? 0) !== $userLocationId) {
                    $errors['from_account_id'] = 'You can only transfer from accounts in your location';
                }
            }
        }

        // Check if source account has sufficient balance
        if (!empty($data['from_account_id']) && !empty($data['amount'])) {
            $sourceAccount = $this->accountModel->findById((int)$data['from_account_id']);
            if ($sourceAccount && floatval($sourceAccount['balance']) < floatval($data['amount'])) {
                $errors['amount'] = 'Insufficient balance in source account';
            }
        }

        return $errors;
    }

    /**
     * Process user location transfer
     */
    protected function processUserLocationTransfer(array $data, Request $request, Response $response): void
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            $fromAccountId = (int) $data['from_account_id'];
            $toAccountId = (int) $data['to_account_id'];
            $amount = (float) $data['amount'];
            $description = $data['description'];
            $user = $_SESSION['user'] ?? [];
            $userId = (int) ($user['id'] ?? 0);
            
            // Get account details
            $fromAccount = $this->accountModel->findById($fromAccountId);
            $toAccount = $this->accountModel->findById($toAccountId);
            
            if (!$fromAccount || !$toAccount) {
                throw new \Exception('Invalid account specified');
            }
            
            // Update balances
            $this->accountModel->updateBalance($fromAccountId, -$amount);
            $this->accountModel->updateBalance($toAccountId, $amount);
            
            // Record transaction for source account (outgoing)
            $this->rechargeHistoryModel->create([
                'acc_id' => $fromAccountId,
                'account_id' => $fromAccountId,
                'user_id' => $userId,
                'amount' => -$amount,
                'transaction_type' => 'transfer_out',
                'description' => "Transfer to {$toAccount['account_name']}: {$description}",
                'reference_account_id' => $toAccountId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Record transaction for destination account (incoming)
            $this->rechargeHistoryModel->create([
                'acc_id' => $toAccountId,
                'account_id' => $toAccountId,
                'user_id' => $userId,
                'amount' => $amount,
                'transaction_type' => 'transfer_in',
                'description' => "Transfer from {$fromAccount['account_name']}: {$description}",
                'reference_account_id' => $fromAccountId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $db->commit();
            
            if ($request->isAjax()) {
                $response->success([
                    'from_account' => $fromAccount['account_name'],
                    'to_account' => $toAccount['account_name'],
                    'amount' => number_format($amount, 2)
                ], 'Transfer completed successfully');
            } else {
                $_SESSION['flash_success'] = 'Transfer completed successfully';
                $response->redirect(APP_URL . '/finance/transfer-to-account');
            }
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Display global transfer page (any account to any account)
     */
    public function globalTransfer(Request $request, Response $response): void
    {
        try {
            $user = $_SESSION['user'] ?? [];
            
            // Get all active accounts for both source and destination
            $allAccounts = $this->accountModel->getActive();
            
            // Get available currencies
            $currencyTypeModel = new CurrencyType();
            $currencies = $currencyTypeModel->getAll();
            
            // Get CurrencyExchangeRate model for later use
            $currencyModel = new CurrencyExchangeRate();
            
            $this->view('finance/global-transfer/index', [
                'accounts' => $allAccounts,
                'currencies' => $currencies,
                'user' => $user,
                'pageTitle' => 'Global Transfer'
            ], 'main');
        } catch (\Exception $e) {
            error_log("Global Transfer Page Error: " . $e->getMessage());
            $_SESSION['errors'] = ['general' => 'Failed to load global transfer page'];
            $response->redirect(APP_URL . '/finance/global-transfer');
        }
    }

    /**
     * Process global transfer (any account to any account)
     */
    public function processGlobalTransfer(Request $request, Response $response): void
    {
        try {
            $data = $request->getBody();
            
            // Validate global transfer data
            $errors = $this->validateGlobalTransferData($data);

            if (!empty($errors)) {
                if ($request->isAjax()) {
                    $response->error('Validation failed', 422, $errors);
                    return;
                }
                
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $data;
                $response->redirect(APP_URL . '/finance/global-transfer');
                return;
            }

            // Process the global transfer
            $this->processGlobalTransferTransaction($data, $request, $response);

        } catch (\Exception $e) {
            // Log the detailed error for debugging
            error_log("Global Transfer Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if ($request->isAjax()) {
                $response->error('Failed to process global transfer: ' . $e->getMessage(), 500);
                return;
            }
            
            $_SESSION['errors'] = ['general' => 'Failed to process global transfer: ' . $e->getMessage()];
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/finance/global-transfer');
        }
    }

    /**
     * Validate global transfer data
     */
    protected function validateGlobalTransferData(array $data): array
    {
        $errors = [];

        if (empty($data['from_account_id'])) {
            $errors['from_account_id'] = 'Source account is required';
        }

        if (empty($data['to_account_id'])) {
            $errors['to_account_id'] = 'Destination account is required';
        }

        if (empty($data['amount']) || !is_numeric($data['amount']) || floatval($data['amount']) <= 0) {
            $errors['amount'] = 'Valid transfer amount is required';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Transfer description is required';
        }

        if (!empty($data['from_account_id']) && !empty($data['to_account_id'])) {
            if ($data['from_account_id'] === $data['to_account_id']) {
                $errors['to_account_id'] = 'Source and destination accounts cannot be the same';
            }
        }

        // Check if source account exists and has sufficient balance
        if (!empty($data['from_account_id']) && !empty($data['amount'])) {
            $sourceAccount = $this->accountModel->findById((int)$data['from_account_id']);
            if (!$sourceAccount) {
                $errors['from_account_id'] = 'Source account not found';
            } elseif (floatval($sourceAccount['balance']) < floatval($data['amount'])) {
                $errors['amount'] = 'Insufficient balance in source account';
            }
        }

        // Check if destination account exists
        if (!empty($data['to_account_id'])) {
            $destAccount = $this->accountModel->findById((int)$data['to_account_id']);
            if (!$destAccount) {
                $errors['to_account_id'] = 'Destination account not found';
            }
        }

        return $errors;
    }

    /**
     * Process global transfer transaction
     */
    protected function processGlobalTransferTransaction(array $data, Request $request, Response $response): void
    {
        $db = Database::getInstance();
        
        try {
            // Debug logging
            error_log("Starting global transfer - Data: " . json_encode($data));
            
            $db->beginTransaction();
            
            $fromAccountId = (int) $data['from_account_id'];
            $toAccountId = (int) $data['to_account_id'];
            $amount = (float) $data['amount'];
            $description = $data['description'];
            $user = $_SESSION['user'] ?? [];
            $userId = (int) ($user['id'] ?? 0);
            
            error_log("Transfer details - From: $fromAccountId, To: $toAccountId, Amount: $amount, User: $userId");
            
            // Get account details with currency information
            $fromAccount = $this->accountModel->findById($fromAccountId);
            $toAccount = $this->accountModel->findById($toAccountId);
            
            error_log("From Account: " . ($fromAccount ? json_encode($fromAccount) : 'NOT FOUND'));
            error_log("To Account: " . ($toAccount ? json_encode($toAccount) : 'NOT FOUND'));
            
            if (!$fromAccount || !$toAccount) {
                throw new \Exception('Invalid account specified');
            }
            
            // Check if source account has sufficient balance
            if ($fromAccount['balance'] < $amount) {
                throw new \Exception('Insufficient balance in source account. Available: ' . number_format($fromAccount['balance'], 2) . ', Required: ' . number_format($amount, 2));
            }
            
            error_log("Balance check passed");
            
            // Get CurrencyExchangeRate model
            $currencyModel = new CurrencyExchangeRate();
            
            // Check if currency conversion is needed
            $fromCurrencyId = $fromAccount['currency_type_id'] ?? 1;
            $toCurrencyId = $toAccount['currency_type_id'] ?? 1;
            
            $convertedAmount = $amount;
            $exchangeRate = 1.0;
            $conversionNote = '';
            
            // Check if user provided exchange rate - if so, always use it for conversion
            if (!empty($data['exchange_rate'])) {
                $exchangeRate = (float) $data['exchange_rate'];
                $convertedAmount = $amount * $exchangeRate;
                $conversionNote = " (Rate: 1 {$fromAccount['currency_sign']} = {$exchangeRate} {$toAccount['currency_sign']})";
                
                // Save the exchange rate to database for future use
                $currencyModel->setExchangeRate($fromCurrencyId, $toCurrencyId, $exchangeRate);
            } elseif ($fromCurrencyId != $toCurrencyId) {
                // Different currencies - need conversion
                $exchangeRate = null;
                
                // Try to get from database
                $exchangeRate = $currencyModel->getExchangeRate($fromCurrencyId, $toCurrencyId);
                
                if ($exchangeRate === null) {
                    // Ask user for exchange rate
                    $db->rollBack();
                    
                    $responseData = [
                        'needs_exchange_rate' => true,
                        'from_currency' => [
                            'id' => $fromCurrencyId,
                            'name' => $fromAccount['currency_name'] ?? 'Unknown',
                            'sign' => $fromAccount['currency_sign'] ?? ''
                        ],
                        'to_currency' => [
                            'id' => $toCurrencyId,
                            'name' => $toAccount['currency_name'] ?? 'Unknown', 
                            'sign' => $toAccount['currency_sign'] ?? ''
                        ],
                        'transfer_data' => $data
                    ];
                    
                    if ($request->isAjax()) {
                        $response->success($responseData, 'Exchange rate required for currency conversion');
                    } else {
                        $_SESSION['exchange_rate_request'] = $responseData;
                        $_SESSION['old'] = $data;
                        $response->redirect(APP_URL . '/finance/global-transfer');
                    }
                    return;
                }
                
                $convertedAmount = $amount * $exchangeRate;
                $conversionNote = " (Rate: 1 {$fromAccount['currency_sign']} = {$exchangeRate} {$toAccount['currency_sign']})";
                
                // Save the exchange rate to database for future use
                $currencyModel->setExchangeRate($fromCurrencyId, $toCurrencyId, $exchangeRate);
            }
            
            error_log("Currency conversion completed - Rate: $exchangeRate, Converted Amount: $convertedAmount");
            
            // Update balances
            error_log("Starting balance updates");
            $deductResult = $this->accountModel->updateBalance($fromAccountId, -$amount); // Deduct original amount
            if (!$deductResult) {
                throw new \Exception('Failed to deduct amount from source account');
            }
            error_log("Deducted $amount from account $fromAccountId");
            
            $addResult = $this->accountModel->updateBalance($toAccountId, $convertedAmount); // Add converted amount
            if (!$addResult) {
                throw new \Exception('Failed to add converted amount to destination account');
            }
            error_log("Added $convertedAmount to account $toAccountId");
            
            // Record transaction for source account (outgoing)
            $outgoingRecord = $this->rechargeHistoryModel->create([
                'acc_id' => $fromAccountId,
                'account_id' => $fromAccountId,
                'user_id' => $userId,
                'amount' => -$amount,
                'transaction_type' => 'global_transfer_out',
                'description' => "Global transfer to {$toAccount['account_name']}: {$description}" . ($exchangeRate != 1.0 ? " - Sent: {$amount} {$fromAccount['currency_sign']}, Received: {$convertedAmount} {$toAccount['currency_sign']}{$conversionNote}" : ""),
                'reference_account_id' => $toAccountId,
                'exchange_rate' => $exchangeRate != 1.0 ? $exchangeRate : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$outgoingRecord) {
                throw new \Exception('Failed to record outgoing transaction history');
            }
            
            // Record transaction for destination account (incoming)
            $incomingRecord = $this->rechargeHistoryModel->create([
                'acc_id' => $toAccountId,
                'account_id' => $toAccountId,
                'user_id' => $userId,
                'amount' => $convertedAmount,
                'transaction_type' => 'global_transfer_in',
                'description' => "Global transfer from {$fromAccount['account_name']}: {$description}" . ($exchangeRate != 1.0 ? " - Sent: {$amount} {$fromAccount['currency_sign']}, Received: {$convertedAmount} {$toAccount['currency_sign']}{$conversionNote}" : ""),
                'reference_account_id' => $fromAccountId,
                'exchange_rate' => $exchangeRate != 1.0 ? $exchangeRate : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$incomingRecord) {
                throw new \Exception('Failed to record incoming transaction history');
            }
            
            error_log("All operations completed successfully, committing transaction");
            
            $db->commit();
            
            if ($request->isAjax()) {
                $responseData = [
                    'from_account' => $fromAccount['account_name'],
                    'to_account' => $toAccount['account_name'],
                    'amount' => number_format($amount, 2),
                    'from_currency' => $fromAccount['currency_sign'],
                    'converted_amount' => number_format($convertedAmount, 2),
                    'to_currency' => $toAccount['currency_sign'],
                    'exchange_rate' => $exchangeRate,
                    'currency_conversion' => $exchangeRate != 1.0
                ];
                
                $message = $exchangeRate != 1.0 ? 
                    "Global transfer completed with currency conversion: {$amount} {$fromAccount['currency_sign']} → {$convertedAmount} {$toAccount['currency_sign']}" :
                    'Global transfer completed successfully';
                    
                $response->success($responseData, $message);
            } else {
                $message = $exchangeRate != 1.0 ? 
                    "Global transfer completed with currency conversion: {$amount} {$fromAccount['currency_sign']} converted to {$convertedAmount} {$toAccount['currency_sign']}" :
                    'Global transfer completed successfully';
                    
                $_SESSION['flash_success'] = $message;
                $response->redirect(APP_URL . '/finance/global-transfer');
            }
            
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Get exchange rate between two currencies via AJAX
     */
    public function getExchangeRate(Request $request, Response $response): void
    {
        try {
            $fromCurrencyId = $request->query('from_currency_id');
            $toCurrencyId = $request->query('to_currency_id');

            if (empty($fromCurrencyId) || empty($toCurrencyId)) {
                $response->error('From and to currency IDs are required', 400);
                return;
            }

            // Get models
            $currencyModel = new CurrencyExchangeRate();
            $currencyTypeModel = new CurrencyType();

            if ($fromCurrencyId == $toCurrencyId) {
                // Same currency, no conversion needed
                $response->success([
                    'exchange_rate' => 1.0,
                    'needs_conversion' => false,
                    'from_currency' => $currencyTypeModel->getById($fromCurrencyId),
                    'to_currency' => $currencyTypeModel->getById($toCurrencyId)
                ], 'Same currency selected');
                return;
            }

            $exchangeRate = $currencyModel->getExchangeRate($fromCurrencyId, $toCurrencyId);
            
            if ($exchangeRate === null) {
                $fromCurrency = $currencyTypeModel->getById($fromCurrencyId);
                $toCurrency = $currencyTypeModel->getById($toCurrencyId);
                
                $response->error("Exchange rate not available for {$fromCurrency['curre_name']} to {$toCurrency['curre_name']}", 404, [
                    'needs_setup' => true,
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency
                ]);
                return;
            }

            
            $response->success([
                'exchange_rate' => $exchangeRate,
                'needs_conversion' => true,
                'from_currency' => $currencyTypeModel->getById($fromCurrencyId),
                'to_currency' => $currencyTypeModel->getById($toCurrencyId)
            ], 'Exchange rate retrieved successfully');

        } catch (\Exception $e) {
            error_log("Exchange Rate Error: " . $e->getMessage());
            $response->error('Failed to get exchange rate: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Convert amount between currencies via AJAX
     */
    public function convertAmount(Request $request, Response $response): void
    {
        try {
            $amount = (float) $request->query('amount');
            $fromCurrencyId = $request->query('from_currency_id');
            $toCurrencyId = $request->query('to_currency_id');

            if ($amount <= 0 || empty($fromCurrencyId) || empty($toCurrencyId)) {
                $response->error('Valid amount and currency IDs are required', 400);
                return;
            }

            // Load models
            $currencyModel = new CurrencyExchangeRate();
            $currencyTypeModel = new CurrencyType();

            if ($fromCurrencyId == $toCurrencyId) {
                // Same currency, no conversion needed
                $response->success([
                    'original_amount' => $amount,
                    'converted_amount' => $amount,
                    'exchange_rate' => 1.0,
                    'needs_conversion' => false
                ], 'Same currency - no conversion needed');
                return;
            }

            $exchangeRate = $currencyModel->getExchangeRate($fromCurrencyId, $toCurrencyId);
            
            if ($exchangeRate === null) {
                $response->error('Exchange rate not available', 404);
                return;
            }

            $convertedAmount = $amount * $exchangeRate;

            $response->success([
                'original_amount' => $amount,
                'converted_amount' => $convertedAmount,
                'exchange_rate' => $exchangeRate,
                'needs_conversion' => true,
                'from_currency' => $currencyTypeModel->getById($fromCurrencyId),
                'to_currency' => $currencyTypeModel->getById($toCurrencyId)
            ], 'Amount converted successfully');

        } catch (\Exception $e) {
            error_log("Amount Conversion Error: " . $e->getMessage());
            $response->error('Failed to convert amount: ' . $e->getMessage(), 500);
        }
    }
}