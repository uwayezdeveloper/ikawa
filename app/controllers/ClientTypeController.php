<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\ClientType;

class ClientTypeController extends Controller
{
    private $clientTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientTypeModel = new ClientType();
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

    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$this->hasPermission('view-suppliers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $types = $this->clientTypeModel->getAllWithIdentifiers();
        $stats = $this->clientTypeModel->getStats();

        return View::render('clients/types', [
            'title' => 'Client Types',
            'user' => $user,
            'clientTypes' => $types,
            'stats' => $stats,
            'permissions' => $user['permissions'] ?? []
        ], 'main');
    }

    public function handleAction($request, $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/client-types');
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                return $this->create();
            case 'update':
                return $this->update();
            case 'delete':
                return $this->delete();
            case 'toggle_status':
                return $this->toggleStatus();
            case 'get_type':
                return $this->getType();
            case 'get_identifiers':
                return $this->getIdentifiers();
            case 'add_identifier':
                return $this->addIdentifier();
            case 'update_identifier':
                return $this->updateIdentifier();
            case 'delete_identifier':
                return $this->deleteIdentifier();
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid action']);
        }
    }

    private function create()
    {
        if (!$this->hasPermission('create-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');

        if (empty($name) || empty($identifier)) {
            $this->jsonResponse(['success' => false, 'message' => 'Name and identifier are required']);
            return;
        }

        // Validate identifier format (lowercase, no spaces)
        $identifier = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier));

        if ($this->clientTypeModel->identifierExists($identifier)) {
            $this->jsonResponse(['success' => false, 'message' => 'Identifier already exists']);
            return;
        }

        $result = $this->clientTypeModel->createType([
            'name' => $name,
            'description' => $description,
            'identifier' => $identifier
        ]);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Client type created successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to create client type']);
        }
    }

    private function update()
    {
        if (!$this->hasPermission('edit-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($name) || empty($identifier)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID, name and identifier are required']);
            return;
        }

        // Validate identifier format
        $identifier = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier));

        if ($this->clientTypeModel->identifierExists($identifier, $id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Identifier already exists']);
            return;
        }

        $result = $this->clientTypeModel->updateType($id, [
            'name' => $name,
            'description' => $description,
            'identifier' => $identifier,
            'status' => $status
        ]);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Client type updated successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update client type']);
        }
    }

    private function delete()
    {
        if (!$this->hasPermission('delete-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Type ID is required']);
            return;
        }

        $result = $this->clientTypeModel->deleteType($id);
        $this->jsonResponse($result);
    }

    private function toggleStatus()
    {
        if (!$this->hasPermission('edit-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Type ID is required']);
            return;
        }

        $result = $this->clientTypeModel->toggleStatus($id);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update status']);
        }
    }

    private function getType()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Type ID is required']);
            return;
        }

        $type = $this->clientTypeModel->getById($id);

        if ($type) {
            $this->jsonResponse(['success' => true, 'type' => $type]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Type not found']);
        }
    }

    // ==================== IDENTIFIER METHODS ====================

    private function getIdentifiers()
    {
        $typeId = $_POST['type_id'] ?? null;

        if (!$typeId) {
            $this->jsonResponse(['success' => false, 'message' => 'Type ID is required']);
            return;
        }

        $identifiers = $this->clientTypeModel->getIdentifiers($typeId);
        $this->jsonResponse(['success' => true, 'identifiers' => $identifiers]);
    }

    private function addIdentifier()
    {
        if (!$this->hasPermission('edit-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $typeId = $_POST['type_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $isRequired = $_POST['is_required'] ?? 0;
        $status = $_POST['status'] ?? 'active';

        if (!$typeId || empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Type ID and name are required']);
            return;
        }

        $result = $this->clientTypeModel->addIdentifier($typeId, [
            'name' => $name,
            'is_required' => $isRequired,
            'status' => $status
        ]);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Identifier added successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to add identifier']);
        }
    }

    private function updateIdentifier()
    {
        if (!$this->hasPermission('edit-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $isRequired = $_POST['is_required'] ?? 0;
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID and name are required']);
            return;
        }

        $result = $this->clientTypeModel->updateIdentifier($id, [
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

    private function deleteIdentifier()
    {
        if (!$this->hasPermission('delete-suppliers')) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            return;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Identifier ID is required']);
            return;
        }

        $result = $this->clientTypeModel->deleteIdentifier($id);

        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Identifier deleted successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete identifier']);
        }
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}