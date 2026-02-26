<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\SupplierType;
use App\Models\SupplierTypeIdentifier;

class SupplierTypeController extends Controller
{
    protected SupplierType $supplierTypeModel;
    protected SupplierTypeIdentifier $identifierModel;

    public function __construct()
    {
        parent::__construct();
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
     * Display supplier types list
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-supplier-types')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $supplierTypes = $this->supplierTypeModel->getAllWithIdentifierCount();

        return View::render('suppliers/types/index', [
            'title' => 'Supplier Types',
            'user' => $user,
            'supplierTypes' => $supplierTypes,
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle supplier type actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-supplier-types')) {
                    $_SESSION['flash_error'] = 'You do not have permission to create supplier types';
                    return $response->redirect(APP_URL . '/suppliers/types');
                }
                return $this->createType($request, $response);
            case 'update':
            case 'toggle_status':
                if (!$this->hasPermission('edit-supplier-types')) {
                    $_SESSION['flash_error'] = 'You do not have permission to edit supplier types';
                    return $response->redirect(APP_URL . '/suppliers/types');
                }
                return $action === 'update' ? $this->updateType($request, $response) : $this->toggleStatus($request, $response);
            case 'delete':
                if (!$this->hasPermission('delete-supplier-types')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete supplier types';
                    return $response->redirect(APP_URL . '/suppliers/types');
                }
                return $this->deleteType($request, $response);
            // Identifier actions (AJAX)
            case 'get_identifiers':
                return $this->getIdentifiers($request, $response);
            case 'add_identifier':
                if (!$this->hasPermission('edit-supplier-types')) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
                }
                return $this->addIdentifier($request, $response);
            case 'update_identifier':
                if (!$this->hasPermission('edit-supplier-types')) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
                }
                return $this->updateIdentifier($request, $response);
            case 'toggle_identifier_status':
                if (!$this->hasPermission('edit-supplier-types')) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
                }
                return $this->toggleIdentifierStatus($request, $response);
            case 'delete_identifier':
                if (!$this->hasPermission('edit-supplier-types')) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
                }
                return $this->deleteIdentifier($request, $response);
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/suppliers/types');
        }
    }

    /**
     * Create new supplier type
     */
    private function createType($request, $response)
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Type name is required';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        if ($this->supplierTypeModel->nameExists($name)) {
            $_SESSION['flash_error'] = 'A supplier type with this name already exists';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        $result = $this->supplierTypeModel->createType([
            'name' => $name,
            'description' => $description,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Supplier type created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create supplier type';
        }

        return $response->redirect(APP_URL . '/suppliers/types');
    }

    /**
     * Update supplier type
     */
    private function updateType($request, $response)
    {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid supplier type';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        // Validation
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Type name is required';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        if ($this->supplierTypeModel->nameExists($name, (int)$id)) {
            $_SESSION['flash_error'] = 'A supplier type with this name already exists';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        $result = $this->supplierTypeModel->updateType((int)$id, [
            'name' => $name,
            'description' => $description,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Supplier type updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update supplier type';
        }

        return $response->redirect(APP_URL . '/suppliers/types');
    }

    /**
     * Delete supplier type
     */
    private function deleteType($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid supplier type';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        // Check if type exists
        $type = $this->supplierTypeModel->findById((int)$id);
        if (!$type) {
            $_SESSION['flash_error'] = 'Supplier type not found';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        // TODO: Check if type is used by any suppliers before deleting

        $result = $this->supplierTypeModel->deleteType((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Supplier type deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete supplier type';
        }

        return $response->redirect(APP_URL . '/suppliers/types');
    }

    /**
     * Toggle supplier type status
     */
    private function toggleStatus($request, $response)
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid supplier type';
            return $response->redirect(APP_URL . '/suppliers/types');
        }

        $result = $this->supplierTypeModel->toggleStatus((int)$id);

        if ($result) {
            $_SESSION['flash_success'] = 'Supplier type status updated';
        } else {
            $_SESSION['flash_error'] = 'Failed to update status';
        }

        return $response->redirect(APP_URL . '/suppliers/types');
    }

    // ==========================================
    // Identifier Management Methods (AJAX)
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
     * Get identifiers for a supplier type
     */
    private function getIdentifiers($request, $response): void
    {
        $supplierTypeId = (int)($_POST['supplier_type_id'] ?? 0);
        
        if (!$supplierTypeId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid supplier type']);
        }

        $identifiers = $this->identifierModel->getBySupplierType($supplierTypeId);
        $this->jsonResponse(['success' => true, 'identifiers' => $identifiers]);
    }

    /**
     * Add a new identifier
     */
    private function addIdentifier($request, $response): void
    {
        $supplierTypeId = (int)($_POST['supplier_type_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $isRequired = (int)($_POST['is_required'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (!$supplierTypeId || empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Supplier type and name are required']);
        }

        // Check if name exists for this type
        if ($this->identifierModel->nameExists($name, $supplierTypeId)) {
            $this->jsonResponse(['success' => false, 'message' => 'An identifier with this name already exists for this type']);
        }

        $result = $this->identifierModel->createIdentifier([
            'supplier_type_id' => $supplierTypeId,
            'name' => $name,
            'is_required' => $isRequired,
            'status' => $status
        ]);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Identifier added successfully', 'id' => $result]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to add identifier']);
        }
    }

    /**
     * Update an identifier
     */
    private function updateIdentifier($request, $response): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $isRequired = (int)($_POST['is_required'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID and name are required']);
        }

        // Get the identifier to check supplier_type_id
        $identifier = $this->identifierModel->findById($id);
        if (!$identifier) {
            $this->jsonResponse(['success' => false, 'message' => 'Identifier not found']);
        }

        // Check if name exists for this type (excluding current)
        if ($this->identifierModel->nameExists($name, $identifier['supplier_type_id'], $id)) {
            $this->jsonResponse(['success' => false, 'message' => 'An identifier with this name already exists for this type']);
        }

        $result = $this->identifierModel->updateIdentifier($id, [
            'name' => $name,
            'is_required' => $isRequired,
            'status' => $status
        ]);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Identifier updated successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update identifier']);
        }
    }

    /**
     * Toggle identifier status
     */
    private function toggleIdentifierStatus($request, $response): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid identifier']);
        }

        $result = $this->identifierModel->toggleStatus($id);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update status']);
        }
    }

    /**
     * Delete an identifier
     */
    private function deleteIdentifier($request, $response): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid identifier']);
        }

        $result = $this->identifierModel->deleteIdentifier($id);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Identifier deleted successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete identifier']);
        }
    }
}
