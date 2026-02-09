<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Permission;
use App\Models\Role;

/**
 * Permission Controller
 * Handles permission and role-permission management
 */
class PermissionController extends Controller
{
    protected Permission $permissionModel;
    protected Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->permissionModel = new Permission();
        $this->roleModel = new Role();
    }

    /**
     * List all permissions (web view)
     */
    public function index(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        $page = (int) $request->query('page', 1);
        $permissions = $this->permissionModel->getAllPaginated($page, 15);
        $stats = $this->permissionModel->getCountByStatus();
        $modules = $this->permissionModel->getModules();
        
        $this->view('permissions/index', [
            'user' => $user,
            'permissions' => $permissions,
            'stats' => $stats,
            'modules' => $modules,
            'pageTitle' => 'Manage Permissions'
        ], 'main');
    }

    /**
     * Store new permission (web form submission)
     */
    public function store(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:100',
            'description' => 'max:500'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        // Generate slug if not provided
        $slug = !empty($data['slug']) ? Permission::generateSlug($data['slug']) : Permission::generateSlug($data['name']);
        
        // Check if slug exists
        if ($this->permissionModel->slugExists($slug)) {
            $_SESSION['flash_error'] = 'Permission slug already exists';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        // Prepare data
        $permissionData = [
            'name' => trim($data['name']),
            'slug' => $slug,
            'description' => trim($data['description'] ?? ''),
            'module' => trim($data['module'] ?? 'general'),
            'status' => $data['status'] ?? 'active'
        ];
        
        // Create permission
        $permissionId = $this->permissionModel->create($permissionData);
        
        if (!$permissionId) {
            $_SESSION['flash_error'] = 'Failed to create permission';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $_SESSION['flash_success'] = 'Permission created successfully';
        $response->redirect(APP_URL . '/permissions');
    }

    /**
     * Update permission (web form submission)
     */
    public function update(Request $request, Response $response, array $params): void
    {
        $permissionId = (int) $params['id'];
        $permission = $this->permissionModel->find($permissionId);
        
        if (!$permission) {
            $_SESSION['flash_error'] = 'Permission not found';
            $response->redirect(APP_URL . '/permissions');
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
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        // Generate slug if changed
        $slug = !empty($data['slug']) ? Permission::generateSlug($data['slug']) : Permission::generateSlug($data['name']);
        
        // Check if slug exists (excluding current permission)
        if ($this->permissionModel->slugExists($slug, $permissionId)) {
            $_SESSION['flash_error'] = 'Permission slug already exists';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        // Prepare data
        $permissionData = [
            'name' => trim($data['name']),
            'slug' => $slug,
            'description' => trim($data['description'] ?? ''),
            'module' => trim($data['module'] ?? 'general'),
            'status' => $data['status'] ?? 'active'
        ];
        
        // Update permission
        $updated = $this->permissionModel->update($permissionId, $permissionData);
        
        if (!$updated) {
            $_SESSION['flash_error'] = 'Failed to update permission';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $_SESSION['flash_success'] = 'Permission updated successfully';
        $response->redirect(APP_URL . '/permissions');
    }

    /**
     * Delete permission
     */
    public function delete(Request $request, Response $response, array $params): void
    {
        $permissionId = (int) $params['id'];
        $permission = $this->permissionModel->find($permissionId);
        
        if (!$permission) {
            $_SESSION['flash_error'] = 'Permission not found';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        // Delete permission (cascade will remove from role_permissions)
        $deleted = $this->permissionModel->delete($permissionId);
        
        if (!$deleted) {
            $_SESSION['flash_error'] = 'Failed to delete permission';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $_SESSION['flash_success'] = 'Permission deleted successfully';
        $response->redirect(APP_URL . '/permissions');
    }

    /**
     * Show role permissions page
     */
    public function rolePermissions(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        $roles = $this->roleModel->getActiveRoles();
        $permissionsGrouped = $this->permissionModel->getGroupedByModule();
        
        // Get selected role (default to first role)
        $selectedRoleId = (int) $request->query('role_id', $roles[0]['id'] ?? 0);
        $selectedRole = null;
        
        foreach ($roles as $role) {
            if ($role['id'] == $selectedRoleId) {
                $selectedRole = $role;
                break;
            }
        }
        
        // Get current permissions for selected role
        $rolePermissionIds = $this->permissionModel->getPermissionIdsByRole($selectedRoleId);
        
        $this->view('permissions/role-permissions', [
            'user' => $user,
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'selectedRoleId' => $selectedRoleId,
            'permissionsGrouped' => $permissionsGrouped,
            'rolePermissionIds' => $rolePermissionIds,
            'pageTitle' => 'Role Permissions'
        ], 'main');
    }

    /**
     * Save role permissions
     */
    public function saveRolePermissions(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $roleId = (int) ($data['role_id'] ?? 0);
        $permissionIds = $data['permissions'] ?? [];
        
        if (!$roleId) {
            $_SESSION['flash_error'] = 'Please select a role';
            $response->redirect(APP_URL . '/permissions/roles');
            return;
        }
        
        // Verify role exists
        $role = $this->roleModel->find($roleId);
        if (!$role) {
            $_SESSION['flash_error'] = 'Role not found';
            $response->redirect(APP_URL . '/permissions/roles');
            return;
        }
        
        // Convert permission IDs to integers
        $permissionIds = array_map('intval', $permissionIds);
        
        // Assign permissions to role
        $success = $this->permissionModel->assignToRole($roleId, $permissionIds);
        
        if (!$success) {
            $_SESSION['flash_error'] = 'Failed to update role permissions';
            $response->redirect(APP_URL . '/permissions/roles?role_id=' . $roleId);
            return;
        }
        
        $_SESSION['flash_success'] = 'Role permissions updated successfully';
        $response->redirect(APP_URL . '/permissions/roles?role_id=' . $roleId);
    }

    // ==================== API Methods ====================

    /**
     * API: Get all permissions
     */
    public function apiIndex(Request $request, Response $response): void
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);
        
        $permissions = $this->permissionModel->getAllPaginated($page, $perPage);
        
        $response->success($permissions, 'Permissions retrieved');
    }

    /**
     * API: Get permissions grouped by module
     */
    public function apiGrouped(Request $request, Response $response): void
    {
        $permissions = $this->permissionModel->getGroupedByModule();
        $response->success(['permissions' => $permissions], 'Permissions retrieved');
    }

    /**
     * API: Get role permissions
     */
    public function apiRolePermissions(Request $request, Response $response, array $params): void
    {
        $roleId = (int) $params['id'];
        $permissions = $this->permissionModel->getByRoleId($roleId);
        
        $response->success(['permissions' => $permissions], 'Role permissions retrieved');
    }

    /**
     * API: Update role permissions
     */
    public function apiUpdateRolePermissions(Request $request, Response $response, array $params): void
    {
        $roleId = (int) $params['id'];
        $data = $request->getBody();
        $permissionIds = $data['permissions'] ?? [];
        
        // Verify role exists
        $role = $this->roleModel->find($roleId);
        if (!$role) {
            $response->notFound('Role not found');
            return;
        }
        
        // Convert permission IDs to integers
        $permissionIds = array_map('intval', $permissionIds);
        
        // Assign permissions to role
        $success = $this->permissionModel->assignToRole($roleId, $permissionIds);
        
        if (!$success) {
            $response->error('Failed to update role permissions', 500);
            return;
        }
        
        $updatedPermissions = $this->permissionModel->getByRoleId($roleId);
        
        $response->success(['permissions' => $updatedPermissions], 'Role permissions updated');
    }
}
