<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Account;

class AccountReconciliationController extends Controller
{
    protected Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
    }

    /**
     * Display account reconciliation main page
     */
    public function index(Request $request, Response $response): void
    {
        try {
            $user = $_SESSION['user'] ?? [];
            
            $this->view('reconciliation/index', [
                'user' => $user,
                'pageTitle' => 'Account Reconciliation'
            ], 'main');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to load account reconciliation page');
        }
    }

    /**
     * View All Accounts - first sub-menu
     */
    public function viewAllAccounts(Request $request, Response $response): void
    {
        try {
            $user = $_SESSION['user'] ?? [];
            
            // Get all accounts with pagination
            $page = max(1, (int) $request->query('page', 1));
            $perPage = 20;
            
            // For now, we'll get basic account information
            // This will be expanded later as requested
            $accounts = $this->accountModel->getAll();
            
            // Calculate some basic reconciliation info for each account
            $reconciliationData = [];
            foreach ($accounts as $account) {
                $reconciliationData[] = [
                    'account_id' => $account['id'],
                    'account_name' => $account['account_name'],
                    'account_number' => $account['account_number'] ?? 'N/A',
                    'current_balance' => $account['balance'],
                    'location' => $account['location_name'] ?? 'N/A',
                    'currency' => $account['currency_sign'] ?? '$',
                    'status' => $account['status'],
                    'last_updated' => $account['updated_at'] ?? 'N/A',
                    // Placeholder for reconciliation status - to be implemented later
                    'reconciliation_status' => 'Pending', 
                    'last_reconciled' => 'Never' // To be implemented later
                ];
            }
            
            $this->view('reconciliation/view-all-accounts', [
                'user' => $user,
                'accounts' => $reconciliationData,
                'pageTitle' => 'View All Accounts - Reconciliation'
            ], 'main');
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to load accounts for reconciliation');
        }
    }

    /**
     * Handle errors consistently
     */
    private function handleError(\Exception $e, string $userMessage): void
    {
        error_log("AccountReconciliationController Error: " . $e->getMessage());
        
        if (APP_ENV === 'development') {
            throw $e;
        }
        
        $_SESSION['errors'] = ['general' => $userMessage];
        (new Response())->redirect(APP_URL . '/reconciliation');
    }
}