<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Supplier;
use App\Models\SupplierType;
use App\Models\SupplierTypeIdentifier;

class SupplierController extends Controller
{
    protected Supplier $supplierModel;
    protected SupplierType $supplierTypeModel;
    protected SupplierTypeIdentifier $identifierModel;

    public function __construct()
    {
        parent::__construct();
        $this->supplierModel = new Supplier();
        $this->supplierTypeModel = new SupplierType();
        $this->identifierModel = new SupplierTypeIdentifier();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Display suppliers list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-suppliers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $suppliers = $this->supplierModel->getAll();
        $supplierTypes = $this->supplierTypeModel->getActive();

        return View::render('suppliers/index', [
            'title' => 'Suppliers',
            'user' => $user,
            'suppliers' => $suppliers,
            'supplierTypes' => $supplierTypes,
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle supplier actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-suppliers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create suppliers';
                    return $response->redirect(APP_URL . '/suppliers');
                }
                return $this->createSupplier($request, $response);
            case 'update':
            case 'toggle_status':
                if (!$this->hasPermission('edit-suppliers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit suppliers';
                    return $response->redirect(APP_URL . '/suppliers');
                }
                return $action === 'update' ? $this->updateSupplier($request, $response) : $this->toggleStatus($request, $response);
            case 'delete':
                if (!$this->hasPermission('delete-suppliers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete suppliers';
                    return $response->redirect(APP_URL . '/suppliers');
                }
                return $this->deleteSupplier($request, $response);
            // AJAX actions
            case 'get_type_identifiers':
                return $this->getTypeIdentifiers($request, $response);
            case 'get_supplier_identifiers':
                return $this->getSupplierIdentifiers($request, $response);
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/suppliers');
        }
    }

    /**
     * Create new supplier
     */
    private function createSupplier($request, $response)
    {
        $name = trim($_POST['name'] ?? '');
        $supplierTypeId = $_POST['supplier_type_id'] ?? null;
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $status = $_POST['status'] ?? 'active';

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Supplier name is required';
            return $response->redirect(APP_URL . '/suppliers');
        }

        if (empty($supplierTypeId)) {
            $_SESSION['flash_error'] = 'Supplier type is required';
            return $response->redirect(APP_URL . '/suppliers');
        }

        if ($this->supplierModel->nameExists($name)) {
            $_SESSION['flash_error'] = 'A supplier with this name already exists';
            return $response->redirect(APP_URL . '/suppliers');
        }

        $result = $this->supplierModel->createSupplier([
            'name' => $name,
            'supplier_type_id' => $supplierTypeId,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'contact_person' => $contactPerson,
            'contact_phone' => $contactPhone,
            'status' => $status
        ]);

        if ($result) {
            // Save identifiers
            $identifiers = $_POST['identifiers'] ?? [];
            if (!empty($identifiers)) {
                $this->supplierModel->saveIdentifiers($result, $identifiers);
            }
            $_SESSION['flash_success'] = 'Supplier created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create supplier';
        }

        return $response->redirect(APP_URL . '/suppliers');
    }

    /**
     * Update supplier
     */
    private function updateSupplier($request, $response)
    {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $supplierTypeId = $_POST['supplier_type_id'] ?? null;
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid supplier';
            return $response->redirect(APP_URL . '/suppliers');
        }

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Supplier name is required';
            return $response->redirect(APP_URL . '/suppliers');
        }

        if (empty($supplierTypeId)) {
            $_SESSION['flash_error'] = 'Supplier type is required';
            return $response->redirect(APP_URL . '/suppliers');
        }

        if ($this->supplierModel->nameExists($name, (int)$id)) {
            $_SESSION['flash_error'] = 'A supplier with this name already exists';
            return $response->redirect(APP_URL . '/suppliers');
        }

        $result = $this->supplierModel->updateSupplier((int)$id, [
            'name' => $name,
            'supplier_type_id' => $supplierTypeId,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'contact_person' => $contactPerson,
            'contact_phone' => $contactPhone,
            'status' => $status
        ]);

        if ($result) {
            // Save identifiers
            $identifiers = $_POST['identifiers'] ?? [];
            $this->supplierModel->saveIdentifiers((int)$id, $identifiers);
            $_SESSION['flash_success'] = 'Supplier updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update supplier';
        }

        return $response->redirect(APP_URL . '/suppliers');
    }

    /**
     * Delete supplier
     */
    private function deleteSupplier($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid supplier';
            return $response->redirect(APP_URL . '/suppliers');
        }

        $supplier = $this->supplierModel->findById((int)$id);
        if (!$supplier) {
            $_SESSION['flash_error'] = 'Supplier not found';
            return $response->redirect(APP_URL . '/suppliers');
        }

        $result = $this->supplierModel->deleteSupplier((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Supplier deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete supplier';
        }

        return $response->redirect(APP_URL . '/suppliers');
    }

    /**
     * Toggle supplier status
     */
    private function toggleStatus($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid supplier';
            return $response->redirect(APP_URL . '/suppliers');
        }

        $result = $this->supplierModel->toggleStatus((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Supplier status updated';
        } else {
            $_SESSION['flash_error'] = 'Failed to update status';
        }

        return $response->redirect(APP_URL . '/suppliers');
    }

    // ==========================================
    // AJAX Methods
    // ==========================================

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get identifiers for a supplier type (AJAX)
     */
    private function getTypeIdentifiers($request, $response): void
    {
        $supplierTypeId = (int)($_POST['supplier_type_id'] ?? 0);
        
        if (!$supplierTypeId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid supplier type']);
        }

        $identifiers = $this->identifierModel->getActiveBySupplierType($supplierTypeId);
        $this->jsonResponse(['success' => true, 'identifiers' => $identifiers]);
    }

    /**
     * Get identifiers for a specific supplier (AJAX)
     */
    private function getSupplierIdentifiers($request, $response): void
    {
        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        
        if (!$supplierId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid supplier']);
        }

        $identifiersMap = $this->supplierModel->getIdentifiersMap($supplierId);
        $this->jsonResponse(['success' => true, 'identifiers' => $identifiersMap]);
    }
}
