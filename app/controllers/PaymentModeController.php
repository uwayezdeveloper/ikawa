<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\PaymentMode;

class PaymentModeController extends Controller
{
    protected PaymentMode $paymentModeModel;

    public function __construct()
    {
        parent::__construct();
        $this->paymentModeModel = new PaymentMode();
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
     * Display payment modes list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-payment-modes')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $paymentModes = $this->paymentModeModel->getAll();

        return View::render('finance/payment-modes/index', [
            'title' => 'Payment Modes',
            'user' => $user,
            'paymentModes' => $paymentModes,
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle payment mode actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-payment-modes')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create payment modes';
                    return $response->redirect(APP_URL . '/finance/payment-modes');
                }
                return $this->createMode($request, $response);

            case 'update':
                if (!$this->hasPermission('edit-payment-modes')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit payment modes';
                    return $response->redirect(APP_URL . '/finance/payment-modes');
                }
                return $this->updateMode($request, $response);

            case 'delete':
                if (!$this->hasPermission('delete-payment-modes')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete payment modes';
                    return $response->redirect(APP_URL . '/finance/payment-modes');
                }
                return $this->deleteMode($request, $response);

            case 'toggle_status':
                if (!$this->hasPermission('edit-payment-modes')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit payment modes';
                    return $response->redirect(APP_URL . '/finance/payment-modes');
                }
                return $this->toggleStatus($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/finance/payment-modes');
        }
    }

    /**
     * Create new payment mode
     */
    private function createMode($request, $response)
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Payment mode name is required';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        if ($this->paymentModeModel->nameExists($name)) {
            $_SESSION['flash_error'] = 'A payment mode with this name already exists';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        $result = $this->paymentModeModel->createMode([
            'name' => $name,
            'description' => $description,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Payment mode created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create payment mode';
        }

        return $response->redirect(APP_URL . '/finance/payment-modes');
    }

    /**
     * Update payment mode
     */
    private function updateMode($request, $response)
    {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid payment mode';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Payment mode name is required';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        if ($this->paymentModeModel->nameExists($name, (int)$id)) {
            $_SESSION['flash_error'] = 'A payment mode with this name already exists';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        $result = $this->paymentModeModel->updateMode((int)$id, [
            'name' => $name,
            'description' => $description,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Payment mode updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update payment mode';
        }

        return $response->redirect(APP_URL . '/finance/payment-modes');
    }

    /**
     * Delete payment mode
     */
    private function deleteMode($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid payment mode';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        $result = $this->paymentModeModel->deleteMode((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Payment mode deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete payment mode';
        }

        return $response->redirect(APP_URL . '/finance/payment-modes');
    }

    /**
     * Toggle payment mode status
     */
    private function toggleStatus($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid payment mode';
            return $response->redirect(APP_URL . '/finance/payment-modes');
        }

        $result = $this->paymentModeModel->toggleStatus((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Payment mode status updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update payment mode status';
        }

        return $response->redirect(APP_URL . '/finance/payment-modes');
    }
}
