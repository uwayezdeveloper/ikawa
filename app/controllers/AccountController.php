<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\Account;
use App\Models\PaymentMode;

class AccountController extends Controller
{
    protected Account $accountModel;
    protected PaymentMode $paymentModeModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
        $this->paymentModeModel = new PaymentMode();
    }

    /**
     * Get currency types
     */
    private function getCurrencyTypes(): array
    {
        $sql = "SELECT currency_id, curre_name, sign FROM tbl_currency_type ORDER BY curre_name ASC";
        return Database::fetchAll($sql);
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
     * Display accounts list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-accounts')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $accounts = $this->accountModel->getAll();
        $locationTypes = $this->getLocationTypes();
        $paymentModes = $this->paymentModeModel->getActive();
        $currencyTypes = $this->getCurrencyTypes();

        return View::render('finance/accounts/index', [
            'title' => 'Accounts',
            'user' => $user,
            'accounts' => $accounts,
            'locationTypes' => $locationTypes,
            'paymentModes' => $paymentModes,
            'currencyTypes' => $currencyTypes,
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Get location types
     */
    private function getLocationTypes(): array
    {
        $sql = "SELECT * FROM location_types WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get locations by type (AJAX)
     */
    public function getLocationsByType($request, $response)
    {
        $typeId = $_POST['location_type_id'] ?? null;

        if (!$typeId) {
            return $response->json(['success' => false, 'message' => 'Location type is required']);
        }

        $sql = "SELECT id, name FROM locations WHERE location_type_id = :type_id AND status = 'active' ORDER BY name ASC";
        $locations = Database::fetchAll($sql, ['type_id' => $typeId]);

        return $response->json(['success' => true, 'locations' => $locations]);
    }

    /**
     * Handle account actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-accounts')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create accounts';
                    return $response->redirect(APP_URL . '/finance/accounts');
                }
                return $this->createAccount($request, $response);

            case 'update':
                if (!$this->hasPermission('edit-accounts')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit accounts';
                    return $response->redirect(APP_URL . '/finance/accounts');
                }
                return $this->updateAccount($request, $response);

            case 'delete':
                if (!$this->hasPermission('delete-accounts')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete accounts';
                    return $response->redirect(APP_URL . '/finance/accounts');
                }
                return $this->deleteAccount($request, $response);

            case 'toggle_status':
                if (!$this->hasPermission('edit-accounts')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit accounts';
                    return $response->redirect(APP_URL . '/finance/accounts');
                }
                return $this->toggleStatus($request, $response);

            case 'get_locations':
                return $this->getLocationsByType($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/finance/accounts');
        }
    }

    /**
     * Create new account
     */
    private function createAccount($request, $response)
    {
        $locationTypeId = $_POST['location_type_id'] ?? null;
        $locationId = $_POST['location_id'] ?? null;
        $paymentModeId = $_POST['payment_mode_id'] ?? null;
        $accountName = trim($_POST['account_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $bankName = trim($_POST['bank_name'] ?? '');
        $currencyType = $_POST['currency_type'] ?? null;
        $balance = floatval($_POST['balance'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        // Validation
        if (empty($locationTypeId)) {
            $_SESSION['flash_error'] = 'Location type is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($locationId)) {
            $_SESSION['flash_error'] = 'Location is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($paymentModeId)) {
            $_SESSION['flash_error'] = 'Payment mode is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($accountName)) {
            $_SESSION['flash_error'] = 'Account name is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($currencyType)) {
            $_SESSION['flash_error'] = 'Currency type is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        $result = $this->accountModel->createAccount([
            'location_type_id' => $locationTypeId,
            'location_id' => $locationId,
            'payment_mode_id' => $paymentModeId,
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'bank_name' => $bankName,
            'currency_type' => $currencyType,
            'balance' => $balance,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Account created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create account';
        }

        return $response->redirect(APP_URL . '/finance/accounts');
    }

    /**
     * Update account
     */
    private function updateAccount($request, $response)
    {
        $id = $_POST['id'] ?? null;
        $locationTypeId = $_POST['location_type_id'] ?? null;
        $locationId = $_POST['location_id'] ?? null;
        $paymentModeId = $_POST['payment_mode_id'] ?? null;
        $accountName = trim($_POST['account_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $bankName = trim($_POST['bank_name'] ?? '');
        $currencyType = $_POST['currency_type'] ?? null;
        $balance = floatval($_POST['balance'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid account';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        // Validation
        if (empty($locationTypeId)) {
            $_SESSION['flash_error'] = 'Location type is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($locationId)) {
            $_SESSION['flash_error'] = 'Location is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($paymentModeId)) {
            $_SESSION['flash_error'] = 'Payment mode is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($accountName)) {
            $_SESSION['flash_error'] = 'Account name is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        if (empty($currencyType)) {
            $_SESSION['flash_error'] = 'Currency type is required';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        $result = $this->accountModel->updateAccount((int)$id, [
            'location_type_id' => $locationTypeId,
            'location_id' => $locationId,
            'payment_mode_id' => $paymentModeId,
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'bank_name' => $bankName,
            'currency_type' => $currencyType,
            'balance' => $balance,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Account updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update account';
        }

        return $response->redirect(APP_URL . '/finance/accounts');
    }

    /**
     * Delete account
     */
    private function deleteAccount($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid account';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        $result = $this->accountModel->deleteAccount((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Account deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete account';
        }

        return $response->redirect(APP_URL . '/finance/accounts');
    }

    /**
     * Toggle account status
     */
    private function toggleStatus($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid account';
            return $response->redirect(APP_URL . '/finance/accounts');
        }

        $result = $this->accountModel->toggleStatus((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Account status updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update account status';
        }

        return $response->redirect(APP_URL . '/finance/accounts');
    }
}
