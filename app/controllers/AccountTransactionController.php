<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\AccountTransaction;
use App\Models\Account;

class AccountTransactionController extends Controller
{
    protected AccountTransaction $transactionModel;
    protected Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new AccountTransaction();
        $this->accountModel = new Account();
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
     * Display transactions list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-account-transactions')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $accountId = $_GET['account_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        if ($accountId) {
            $transactions = $this->transactionModel->getByAccount((int) $accountId);
            $selectedAccount = $this->accountModel->findById((int) $accountId);
        } else {
            $transactions = $this->transactionModel->getByDateRange($startDate, $endDate);
            $selectedAccount = null;
        }

        $accounts = $this->accountModel->getActive();

        // Calculate totals
        $totalCredits = 0;
        $totalDebits = 0;
        foreach ($transactions as $t) {
            if ($t['transaction_type'] === 'credit') {
                $totalCredits += $t['amount'];
            } else {
                $totalDebits += $t['amount'];
            }
        }

        return View::render('finance/transactions/index', [
            'title' => 'Account Transactions',
            'user' => $user,
            'transactions' => $transactions,
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'accountId' => $accountId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalCredits' => $totalCredits,
            'totalDebits' => $totalDebits,
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * View transaction details (AJAX)
     */
    public function show($request, $response)
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            return $response->json(['success' => false, 'message' => 'Transaction ID required']);
        }

        $transaction = $this->transactionModel->findById((int) $id);
        
        if (!$transaction) {
            return $response->json(['success' => false, 'message' => 'Transaction not found']);
        }

        return $response->json(['success' => true, 'transaction' => $transaction]);
    }
}
