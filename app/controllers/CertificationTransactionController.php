<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Account;
use App\Models\Certification;
use App\Models\CertificationTransaction;

class CertificationTransactionController extends Controller
{
    protected CertificationTransaction $transactionModel;
    protected Certification $certificationModel;
    protected Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new CertificationTransaction();
        $this->certificationModel = new Certification();
        $this->accountModel = new Account();
    }

    /**
     * Load active accounts for a location with backward-compatible model methods.
     */
    private function getLocationAccounts(int $locationId): array
    {
        if ($locationId <= 0) {
            return [];
        }

        if (method_exists($this->accountModel, 'getByLocation')) {
            $accounts = $this->accountModel->getByLocation($locationId);

            return array_values(array_filter($accounts, function ($account) {
                $status = $account['status'] ?? null;
                return $status === null || $status === 'active' || (int) $status === 1;
            }));
        }

        return [];
    }

    protected function hasMenuAccess(string $menuIdentifier): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            return false;
        }

        // Admin always has access
        $isSuperAdmin = ((int)($user['role_id'] ?? 0) === 1);
        if ($isSuperAdmin) return true;
        
        $menuIdentifiers = $user['menu_identifiers'] ?? [];
        return in_array($menuIdentifier, $menuIdentifiers);
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->hasMenuAccess('certification')) {
            $_SESSION['flash_error'] = 'You do not have permission to view certification transactions';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }

        $transactions = $this->transactionModel->getAllWithRelations();
        $categories = $this->certificationModel->getAll();
        $accounts = [];
        $user = $_SESSION['user'] ?? [];

        if (!empty($user['location_id'])) {
            $accounts = $this->getLocationAccounts((int) $user['location_id']);
        }

        $this->view('certification/transactions/index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'accounts' => $accounts,
            'user' => $user,
            'pageTitle' => 'Certification Transactions',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';

        // Check menu access for all actions
        if (!$this->hasMenuAccess('certification')) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/certification/transactions');
            return;
        }

        switch ($action) {
            case 'create':
                $this->createTransaction($data, $response);
                break;
            case 'update':
                $this->updateTransaction($data, $response);
                break;
            case 'delete':
                $this->deleteTransaction($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/certification/transactions');
        }
    }

    protected function createTransaction(array $data, Response $response): void
    {
        $validated = $this->validateAndNormalize($data);
        if ($validated === null) {
            $response->redirect(APP_URL . '/certification/transactions');
            return;
        }

        $createdId = $this->transactionModel->createTransaction($validated);

        if ($createdId) {
            $_SESSION['flash_success'] = 'Certification transaction created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create certification transaction';
        }

        $response->redirect(APP_URL . '/certification/transactions');
    }

    protected function updateTransaction(array $data, Response $response): void
    {
        $transId = (int) ($data['trans_id'] ?? 0);
        if ($transId <= 0) {
            $_SESSION['flash_error'] = 'Transaction ID is required';
            $response->redirect(APP_URL . '/certification/transactions');
            return;
        }

        $validated = $this->validateAndNormalize($data);
        if ($validated === null) {
            $response->redirect(APP_URL . '/certification/transactions');
            return;
        }

        $updated = $this->transactionModel->updateTransaction($transId, $validated);

        if ($updated) {
            $_SESSION['flash_success'] = 'Certification transaction updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update certification transaction';
        }

        $response->redirect(APP_URL . '/certification/transactions');
    }

    protected function deleteTransaction(array $data, Response $response): void
    {
        $transId = (int) ($data['trans_id'] ?? 0);
        if ($transId <= 0) {
            $_SESSION['flash_error'] = 'Transaction ID is required';
            $response->redirect(APP_URL . '/certification/transactions');
            return;
        }

        $deleted = $this->transactionModel->deleteTransaction($transId);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Certification transaction deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete certification transaction';
        }

        $response->redirect(APP_URL . '/certification/transactions');
    }

    protected function validateAndNormalize(array $data): ?array
    {
        $user = $_SESSION['user'] ?? [];
        $userLocationId = (int) ($user['location_id'] ?? 0);
        $userId = (int) ($user['id'] ?? 0);

        $certId = (int) ($data['cert_id'] ?? 0);
        $dateDone = trim($data['date_done'] ?? '');
        $paymentAccounts = $data['payment_accounts'] ?? [];
        $accountAmounts = $data['account_amounts'] ?? [];
        $comments = trim($data['coments'] ?? '');
        $amount = (int) ($data['amount'] ?? 0);

        if ($certId <= 0) {
            $_SESSION['flash_error'] = 'Certification category is required';
            return null;
        }

        if ($userId <= 0) {
            $_SESSION['flash_error'] = 'Invalid user session. Please login again';
            return null;
        }

        if ($dateDone === '') {
            $_SESSION['flash_error'] = 'Date done is required';
            return null;
        }

        if (empty($paymentAccounts) || !is_array($paymentAccounts)) {
            $_SESSION['flash_error'] = 'Please select at least one payment account';
            return null;
        }

        if ($userLocationId <= 0) {
            $_SESSION['flash_error'] = 'User location is not set';
            return null;
        }

        if (mb_strlen($comments) > 100) {
            $_SESSION['flash_error'] = 'Comments must not exceed 100 characters';
            return null;
        }

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Amount must be greater than zero';
            return null;
        }

        $userAccounts = $this->getLocationAccounts($userLocationId);
        $userAccountIds = array_column($userAccounts, 'id');
        $accountBalances = [];

        foreach ($userAccounts as $account) {
            $accountBalances[(int) $account['id']] = (float) ($account['balance'] ?? 0);
        }

        $payments = [];
        $totalSelectedAmount = 0;

        foreach ($paymentAccounts as $accountIdRaw) {
            $accountId = (int) $accountIdRaw;

            if (!in_array($accountId, $userAccountIds)) {
                $_SESSION['flash_error'] = 'Invalid account selected. You can only use accounts from your location';
                return null;
            }

            $accountAmount = (float) ($accountAmounts[$accountId] ?? 0);
            if ($accountAmount <= 0) {
                $_SESSION['flash_error'] = 'All selected accounts must have amount greater than 0';
                return null;
            }

            $availableBalance = $accountBalances[$accountId] ?? 0;
            if ($accountAmount > $availableBalance) {
                $_SESSION['flash_error'] = 'One of selected accounts has insufficient balance';
                return null;
            }

            $payments[] = [
                'account_id' => $accountId,
                'amount' => $accountAmount
            ];

            $totalSelectedAmount += $accountAmount;
        }

        if (abs($totalSelectedAmount - $amount) > 0.01) {
            $_SESSION['flash_error'] = 'Sum of account amounts must equal transaction amount';
            return null;
        }

        return [
            'cert_id' => $certId,
            'done_by' => $userId,
            'date_done' => $dateDone,
            'payment_accounts' => $payments,
            'coments' => $comments === '' ? null : $comments,
            'status' => 1,
            'amount' => $amount
        ];
    }
}
