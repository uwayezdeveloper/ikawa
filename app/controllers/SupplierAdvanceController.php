<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\SupplierAdvance;
use App\Models\Account;

class SupplierAdvanceController extends Controller
{
    protected SupplierAdvance $advanceModel;
    protected Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->advanceModel = new SupplierAdvance();
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
     * Display supplier advances list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-supplier-advances')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $advances = $this->advanceModel->getAll();
        $locations = $this->getActiveLocations();
        $supplierTypes = $this->getActiveSupplierTypes();

        return View::render('suppliers/advances/index', [
            'title' => 'Supplier Advances',
            'user' => $user,
            'advances' => $advances,
            'locations' => $locations,
            'supplierTypes' => $supplierTypes,
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Get active locations
     */
    private function getActiveLocations(): array
    {
        $sql = "SELECT l.*, lt.name as location_type_name 
                FROM locations l 
                LEFT JOIN location_types lt ON l.location_type_id = lt.id
                WHERE l.status = 'active' 
                ORDER BY l.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get active supplier types
     */
    private function getActiveSupplierTypes(): array
    {
        $sql = "SELECT * FROM supplier_types WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get suppliers by type (AJAX)
     */
    private function getSuppliersByType($request, $response)
    {
        $typeId = $_POST['supplier_type_id'] ?? null;
        
        if (!$typeId) {
            return $response->json(['success' => false, 'message' => 'Supplier Type ID required']);
        }

        $sql = "SELECT id, name FROM suppliers WHERE supplier_type_id = :type_id AND status = 'active' ORDER BY name ASC";
        $suppliers = Database::fetchAll($sql, ['type_id' => $typeId]);
        
        return $response->json(['success' => true, 'suppliers' => $suppliers]);
    }

    /**
     * Get accounts by location (AJAX)
     */
    public function getAccountsByLocation($request, $response)
    {
        $locationId = $_POST['location_id'] ?? null;
        
        if (!$locationId) {
            return $response->json(['success' => false, 'message' => 'Location ID required']);
        }

        $accounts = $this->accountModel->getByLocation((int) $locationId);
        return $response->json(['success' => true, 'accounts' => $accounts]);
    }

    /**
     * Handle actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                return $this->create($request, $response);
            case 'update':
                return $this->update($request, $response);
            case 'delete':
                return $this->delete($request, $response);
            case 'approve':
                return $this->approve($request, $response);
            case 'cancel':
                return $this->cancel($request, $response);
            case 'settle':
                return $this->settle($request, $response);
            case 'get_accounts':
                return $this->getAccountsByLocation($request, $response);
            case 'get_suppliers':
                return $this->getSuppliersByType($request, $response);
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/suppliers/advances');
        }
    }

    /**
     * Create advance
     */
    private function create($request, $response)
    {
        if (!$this->hasPermission('create-supplier-advances')) {
            $_SESSION['flash_error'] = 'You do not have permission to create advances';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $supplierId = $_POST['supplier_id'] ?? null;
        $locationId = $_POST['location_id'] ?? null;
        $accountId = $_POST['account_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $advanceDate = $_POST['advance_date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';

        if (!$supplierId || !$locationId || !$accountId || !$amount) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        // Check account balance
        $account = $this->accountModel->findById((int) $accountId);
        if (!$account) {
            $_SESSION['flash_error'] = 'Account not found';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        if ($account['balance'] < $amount) {
            $_SESSION['flash_error'] = 'Insufficient account balance. Available: ' . number_format($account['balance'], 2) . ' RWF';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $user = $_SESSION['user'] ?? null;

        $this->advanceModel->createAdvance([
            'supplier_id' => $supplierId,
            'location_id' => $locationId,
            'account_id' => $accountId,
            'amount' => $amount,
            'advance_date' => $advanceDate,
            'description' => $description,
            'status' => 'pending',
            'created_by' => $user['id'] ?? null
        ]);

        $_SESSION['flash_success'] = 'Supplier advance created successfully';
        return $response->redirect(APP_URL . '/suppliers/advances');
    }

    /**
     * Update advance
     */
    private function update($request, $response)
    {
        if (!$this->hasPermission('edit-supplier-advances')) {
            $_SESSION['flash_error'] = 'You do not have permission to edit advances';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $id = $_POST['id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;
        $locationId = $_POST['location_id'] ?? null;
        $accountId = $_POST['account_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $advanceDate = $_POST['advance_date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? '';

        if (!$id || !$supplierId || !$locationId || !$accountId || !$amount) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        // Check if advance is still pending
        $advance = $this->advanceModel->findById((int) $id);
        if (!$advance || $advance['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Only pending advances can be edited';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $this->advanceModel->updateAdvance((int) $id, [
            'supplier_id' => $supplierId,
            'location_id' => $locationId,
            'account_id' => $accountId,
            'amount' => $amount,
            'advance_date' => $advanceDate,
            'description' => $description
        ]);

        $_SESSION['flash_success'] = 'Supplier advance updated successfully';
        return $response->redirect(APP_URL . '/suppliers/advances');
    }

    /**
     * Delete advance
     */
    private function delete($request, $response)
    {
        if (!$this->hasPermission('delete-supplier-advances')) {
            $_SESSION['flash_error'] = 'You do not have permission to delete advances';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid advance ID';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $advance = $this->advanceModel->findById((int) $id);
        if (!$advance || $advance['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Only pending advances can be deleted';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $this->advanceModel->deleteAdvance((int) $id);

        $_SESSION['flash_success'] = 'Supplier advance deleted successfully';
        return $response->redirect(APP_URL . '/suppliers/advances');
    }

    /**
     * Approve advance
     */
    private function approve($request, $response)
    {
        if (!$this->hasPermission('approve-supplier-advances')) {
            $_SESSION['flash_error'] = 'You do not have permission to approve advances';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid advance ID';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $advance = $this->advanceModel->findById((int) $id);
        if (!$advance) {
            $_SESSION['flash_error'] = 'Advance not found';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        // Check account balance before approving
        $account = $this->accountModel->findById($advance['account_id']);
        if (!$account || $account['balance'] < $advance['amount']) {
            $_SESSION['flash_error'] = 'Insufficient account balance to approve this advance';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $user = $_SESSION['user'] ?? null;
        $userId = $user['id'] ?? null;

        if ($this->advanceModel->approveAdvance((int) $id, $userId)) {
            $_SESSION['flash_success'] = 'Advance approved and amount deducted from account';
        } else {
            $_SESSION['flash_error'] = 'Failed to approve advance';
        }

        return $response->redirect(APP_URL . '/suppliers/advances');
    }

    /**
     * Cancel advance
     */
    private function cancel($request, $response)
    {
        if (!$this->hasPermission('approve-supplier-advances')) {
            $_SESSION['flash_error'] = 'You do not have permission to cancel advances';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid advance ID';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $user = $_SESSION['user'] ?? null;
        $userId = $user['id'] ?? null;

        if ($this->advanceModel->cancelAdvance((int) $id, $userId)) {
            $_SESSION['flash_success'] = 'Advance cancelled successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel advance';
        }

        return $response->redirect(APP_URL . '/suppliers/advances');
    }

    /**
     * Mark advance as settled
     */
    private function settle($request, $response)
    {
        if (!$this->hasPermission('approve-supplier-advances')) {
            $_SESSION['flash_error'] = 'You do not have permission to settle advances';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid advance ID';
            return $response->redirect(APP_URL . '/suppliers/advances');
        }

        if ($this->advanceModel->settleAdvance((int) $id)) {
            $_SESSION['flash_success'] = 'Advance marked as settled';
        } else {
            $_SESSION['flash_error'] = 'Failed to settle advance. Only approved advances can be settled.';
        }

        return $response->redirect(APP_URL . '/suppliers/advances');
    }
}