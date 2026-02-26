<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Certification;

class CertificationController extends Controller
{
    protected Certification $certificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->certificationModel = new Certification();
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
            $_SESSION['flash_error'] = 'You do not have permission to view certification categories';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }

        $categories = $this->certificationModel->getAll();

        $this->view('certification/categories/index', [
            'categories' => $categories,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Certification Categories',
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
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        switch ($action) {
            case 'create':
                $this->createCategory($data, $response);
                break;
            case 'update':
                $this->updateCategory($data, $response);
                break;
            case 'delete':
                $this->deleteCategory($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/certification/categories');
        }
    }

    protected function createCategory(array $data, Response $response): void
    {
        $name = trim($data['cert_name'] ?? '');
        $description = trim($data['cert_desc'] ?? '');

        if ($name === '') {
            $_SESSION['flash_error'] = 'Certification name is required';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        if (mb_strlen($name) > 50) {
            $_SESSION['flash_error'] = 'Certification name must not exceed 50 characters';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        if ($description !== '' && mb_strlen($description) > 50) {
            $_SESSION['flash_error'] = 'Certification description must not exceed 50 characters';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        if ($this->certificationModel->nameExists($name)) {
            $_SESSION['flash_error'] = 'Certification with this name already exists';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        $createdId = $this->certificationModel->createCategory([
            'cert_name' => $name,
            'cert_desc' => $description === '' ? null : $description,
            'status' => (int) ($data['status'] ?? 1)
        ]);

        if ($createdId) {
            $_SESSION['flash_success'] = 'Certification category created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create certification category';
        }

        $response->redirect(APP_URL . '/certification/categories');
    }

    protected function updateCategory(array $data, Response $response): void
    {
        $id = (int) ($data['cert_id'] ?? 0);
        $name = trim($data['cert_name'] ?? '');
        $description = trim($data['cert_desc'] ?? '');

        if ($id <= 0 || $name === '') {
            $_SESSION['flash_error'] = 'Certification ID and name are required';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        if (mb_strlen($name) > 50) {
            $_SESSION['flash_error'] = 'Certification name must not exceed 50 characters';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        if ($description !== '' && mb_strlen($description) > 50) {
            $_SESSION['flash_error'] = 'Certification description must not exceed 50 characters';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        if ($this->certificationModel->nameExists($name, $id)) {
            $_SESSION['flash_error'] = 'Certification with this name already exists';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        $updated = $this->certificationModel->updateCategory($id, [
            'cert_name' => $name,
            'cert_desc' => $description === '' ? null : $description,
            'status' => (int) ($data['status'] ?? 1)
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = 'Certification category updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update certification category';
        }

        $response->redirect(APP_URL . '/certification/categories');
    }

    protected function deleteCategory(array $data, Response $response): void
    {
        $id = (int) ($data['cert_id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Certification ID is required';
            $response->redirect(APP_URL . '/certification/categories');
            return;
        }

        $deleted = $this->certificationModel->deleteCategory($id);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Certification category deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete certification category';
        }

        $response->redirect(APP_URL . '/certification/categories');
    }
}
