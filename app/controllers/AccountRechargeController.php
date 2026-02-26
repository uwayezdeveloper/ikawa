<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Account;
use App\Models\RechargeHistory;
use App\Models\ExpenseTransaction;
use App\Models\SourceOfIncome;

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
            // Get accounts that can transfer money (with location_type_id and positive balance)
            $transferAccounts = $this->accountModel->getTransferEnabledAccounts();
            
            // Get all active accounts for receiving transfers
            $receivingAccounts = $this->accountModel->getReceivingAccounts();
            
            // Get all active source of income
            $sourcesOfIncome = $this->sourceOfIncomeModel->getActiveSources();
            
            $this->view('finance/transfer/index', [
                'transferAccounts' => $transferAccounts,
                'receivingAccounts' => $receivingAccounts,
                'sourcesOfIncome' => $sourcesOfIncome,
                'user' => $_SESSION['user'] ?? [],
                'pageTitle' => 'Account Transfer'
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
}