<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Role;

/**
 * Role Controller
 * Handles role management
 */
class RoleController extends Controller
{
    protected Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->roleModel = new Role();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        // Only check actual permissions from the database
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * List all roles (web view)
     */
    public function index(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        $page = (int) $request->query('page', 1);
        $roles = $this->roleModel->getAllPaginated($page, 10);
        $stats = $this->roleModel->getCountByStatus();
        
        $this->view('roles/index', [
            'user' => $user,
            'roles' => $roles,
            'stats' => $stats,
            'pageTitle' => 'Manage Roles',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Store new role (web form submission)
     */
    public function store(Request $request, Response $response): void
    {
        // Check permission
        if (!$this->hasPermission('create-roles')) {
            $_SESSION['flash_error'] = 'You do not have permission to create roles';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:100',
            'description' => 'max:500'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        // Check if name exists
        if ($this->roleModel->nameExists($data['name'])) {
            $_SESSION['flash_error'] = 'Role name already exists';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        // Prepare data
        $roleData = [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ];
        
        // Create role
        $roleId = $this->roleModel->create($roleData);
        
        if (!$roleId) {
            $_SESSION['flash_error'] = 'Failed to create role';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $_SESSION['flash_success'] = 'Role created successfully';
        $response->redirect(APP_URL . '/roles');
    }

    /**
     * Update role (web form submission)
     */
    public function update(Request $request, Response $response, array $params): void
    {
        // Check permission
        if (!$this->hasPermission('edit-roles')) {
            $_SESSION['flash_error'] = 'You do not have permission to edit roles';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $roleId = (int) $params['id'];
        $role = $this->roleModel->find($roleId);
        
        if (!$role) {
            $_SESSION['flash_error'] = 'Role not found';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:100',
            'description' => 'max:500'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        // Check if name exists (excluding current role)
        if ($this->roleModel->nameExists($data['name'], $roleId)) {
            $_SESSION['flash_error'] = 'Role name already exists';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        // Prepare data
        $roleData = [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ];
        
        // Update role
        $updated = $this->roleModel->update($roleId, $roleData);
        
        if (!$updated) {
            $_SESSION['flash_error'] = 'Failed to update role';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $_SESSION['flash_success'] = 'Role updated successfully';
        $response->redirect(APP_URL . '/roles');
    }

    /**
     * Delete role
     */
    public function delete(Request $request, Response $response, array $params): void
    {
        // Check permission
        if (!$this->hasPermission('delete-roles')) {
            $_SESSION['flash_error'] = 'You do not have permission to delete roles';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $roleId = (int) $params['id'];
        $role = $this->roleModel->find($roleId);
        
        if (!$role) {
            $_SESSION['flash_error'] = 'Role not found';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        // Delete role
        $deleted = $this->roleModel->delete($roleId);
        
        if (!$deleted) {
            $_SESSION['flash_error'] = 'Failed to delete role';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $_SESSION['flash_success'] = 'Role deleted successfully';
        $response->redirect(APP_URL . '/roles');
    }

    /**
     * Toggle role status
     */
    public function toggleStatus(Request $request, Response $response, array $params): void
    {
        // Check permission (edit permission required to toggle status)
        if (!$this->hasPermission('edit-roles')) {
            $_SESSION['flash_error'] = 'You do not have permission to edit roles';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $roleId = (int) $params['id'];
        $role = $this->roleModel->find($roleId);
        
        if (!$role) {
            $_SESSION['flash_error'] = 'Role not found';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        // Toggle status
        $toggled = $this->roleModel->toggleStatus($roleId);
        
        if (!$toggled) {
            $_SESSION['flash_error'] = 'Failed to update role status';
            $response->redirect(APP_URL . '/roles');
            return;
        }
        
        $_SESSION['flash_success'] = 'Role status updated';
        $response->redirect(APP_URL . '/roles');
    }

    // ==================== API Methods ====================

    /**
     * API: Get all roles
     */
    public function apiIndex(Request $request, Response $response): void
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);
        
        $roles = $this->roleModel->getAllPaginated($page, $perPage);
        
        $response->success($roles, 'Roles retrieved');
    }

    /**
     * API: Get single role
     */
    public function apiShow(Request $request, Response $response, array $params): void
    {
        $role = $this->roleModel->find((int) $params['id']);
        
        if (!$role) {
            $response->notFound('Role not found');
            return;
        }
        
        $response->success(['role' => $role], 'Role retrieved');
    }

    /**
     * API: Create role
     */
    public function apiStore(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:100',
            'description' => 'max:500'
        ]);
        
        if (!empty($errors)) {
            $response->error('Validation failed', 422, $errors);
            return;
        }
        
        // Check if name exists
        if ($this->roleModel->nameExists($data['name'])) {
            $response->error('Role name already exists', 409);
            return;
        }
        
        // Prepare data
        $roleData = [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ];
        
        // Create role
        $roleId = $this->roleModel->create($roleData);
        
        if (!$roleId) {
            $response->error('Failed to create role', 500);
            return;
        }
        
        $role = $this->roleModel->find($roleId);
        
        $response->success(['role' => $role], 'Role created', 201);
    }

    /**
     * API: Update role
     */
    public function apiUpdate(Request $request, Response $response, array $params): void
    {
        $roleId = (int) $params['id'];
        $role = $this->roleModel->find($roleId);
        
        if (!$role) {
            $response->notFound('Role not found');
            return;
        }
        
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'min:2|max:100',
            'description' => 'max:500'
        ]);
        
        if (!empty($errors)) {
            $response->error('Validation failed', 422, $errors);
            return;
        }
        
        // Check if name exists (excluding current role)
        if (isset($data['name']) && $this->roleModel->nameExists($data['name'], $roleId)) {
            $response->error('Role name already exists', 409);
            return;
        }
        
        // Update role
        $updated = $this->roleModel->update($roleId, $data);
        
        if (!$updated) {
            $response->error('Update failed', 500);
            return;
        }
        
        $role = $this->roleModel->find($roleId);
        
        $response->success(['role' => $role], 'Role updated');
    }

    /**
     * API: Delete role
     */
    public function apiDelete(Request $request, Response $response, array $params): void
    {
        $roleId = (int) $params['id'];
        $role = $this->roleModel->find($roleId);
        
        if (!$role) {
            $response->notFound('Role not found');
            return;
        }
        
        // Delete role
        $deleted = $this->roleModel->delete($roleId);
        
        if (!$deleted) {
            $response->error('Delete failed', 500);
            return;
        }
        
        $response->success([], 'Role deleted');
    }

    /**
     * API: Get active roles list
     */
    public function apiActiveRoles(Request $request, Response $response): void
    {
        $roles = $this->roleModel->getActiveRoles();
        $response->success(['roles' => $roles], 'Active roles retrieved');
    }
}
