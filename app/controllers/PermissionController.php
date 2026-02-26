<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Menu;
use App\Models\Role;

/**
 * Menu Controller (kept on Permission routes for compatibility)
 * Handles system menus and role-menu access management
 */
class PermissionController extends Controller
{
    protected Menu $menuModel;
    protected Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuModel = new Menu();
        $this->roleModel = new Role();
    }

    /**
     * List all menus (web view)
     */
    public function index(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        $page = (int) $request->query('page', 1);
        $menus = $this->menuModel->getAllPaginated($page, 15);
        $stats = $this->menuModel->getCountByStatus();
        
        $this->view('permissions/index', [
            'user' => $user,
            'menus' => $menus,
            'stats' => $stats,
            'pageTitle' => 'Manage Menus'
        ], 'main');
    }

    /**
     * Store new menu (web form submission)
     */
    public function store(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'menu_name' => 'required|min:2|max:50',
            'menu_identifier' => 'max:50'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $identifier = !empty($data['menu_identifier'])
            ? Menu::generateIdentifier($data['menu_identifier'])
            : Menu::generateIdentifier($data['menu_name']);
        
        if ($this->menuModel->identifierExists($identifier)) {
            $_SESSION['flash_error'] = 'Menu identifier already exists';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $menuData = [
            'menu_name' => trim($data['menu_name']),
            'menu_identifier' => $identifier,
            'status' => isset($data['status']) && (int) $data['status'] === 1 ? 1 : 0
        ];
        
        $menuId = $this->menuModel->create($menuData);
        
        if (!$menuId) {
            $_SESSION['flash_error'] = 'Failed to create menu';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $_SESSION['flash_success'] = 'Menu created successfully';
        $response->redirect(APP_URL . '/permissions');
    }

    /**
     * Update menu (web form submission)
     */
    public function update(Request $request, Response $response, array $params): void
    {
        $menuId = (int) $params['id'];
        $menu = $this->menuModel->find($menuId);
        
        if (!$menu) {
            $_SESSION['flash_error'] = 'Menu not found';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $data = $request->getBody();
        
        $errors = $this->validate($data, [
            'menu_name' => 'required|min:2|max:50',
            'menu_identifier' => 'max:50'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $identifier = !empty($data['menu_identifier'])
            ? Menu::generateIdentifier($data['menu_identifier'])
            : Menu::generateIdentifier($data['menu_name']);
        
        if ($this->menuModel->identifierExists($identifier, $menuId)) {
            $_SESSION['flash_error'] = 'Menu identifier already exists';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $menuData = [
            'menu_name' => trim($data['menu_name']),
            'menu_identifier' => $identifier,
            'status' => isset($data['status']) && (int) $data['status'] === 1 ? 1 : 0
        ];
        
        $updated = $this->menuModel->update($menuId, $menuData);
        
        if (!$updated) {
            $_SESSION['flash_error'] = 'Failed to update menu';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $_SESSION['flash_success'] = 'Menu updated successfully';
        $response->redirect(APP_URL . '/permissions');
    }

    /**
     * Delete menu
     */
    public function delete(Request $request, Response $response, array $params): void
    {
        $menuId = (int) $params['id'];
        $menu = $this->menuModel->find($menuId);
        
        if (!$menu) {
            $_SESSION['flash_error'] = 'Menu not found';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $deleted = $this->menuModel->delete($menuId);
        
        if (!$deleted) {
            $_SESSION['flash_error'] = 'Failed to delete menu';
            $response->redirect(APP_URL . '/permissions');
            return;
        }
        
        $_SESSION['flash_success'] = 'Menu deleted successfully';
        $response->redirect(APP_URL . '/permissions');
    }

    /**
     * Show role menu access page
     */
    public function rolePermissions(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        $roles = $this->roleModel->getActiveRoles();
        $menus = $this->menuModel->getActiveMenus();
        
        $selectedRoleId = (int) $request->query('role_id', $roles[0]['id'] ?? 0);
        $selectedRole = null;
        
        foreach ($roles as $role) {
            if ($role['id'] == $selectedRoleId) {
                $selectedRole = $role;
                break;
            }
        }
        
        $roleMenuIds = $this->menuModel->getMenuIdsByRole($selectedRoleId);
        
        $this->view('permissions/role-permissions', [
            'user' => $user,
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'selectedRoleId' => $selectedRoleId,
            'menus' => $menus,
            'roleMenuIds' => $roleMenuIds,
            'pageTitle' => 'Role Menus'
        ], 'main');
    }

    /**
     * Save role menus
     */
    public function saveRolePermissions(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $roleId = (int) ($data['role_id'] ?? 0);
        $menuIds = $data['menus'] ?? [];
        
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
        
        $menuIds = array_map('intval', $menuIds);
        
        $success = $this->menuModel->assignToRole($roleId, $menuIds);
        
        if (!$success) {
            $_SESSION['flash_error'] = 'Failed to update role menus';
            $response->redirect(APP_URL . '/permissions/roles?role_id=' . $roleId);
            return;
        }
        
        $_SESSION['flash_success'] = 'Role menus updated successfully';
        $response->redirect(APP_URL . '/permissions/roles?role_id=' . $roleId);
    }

    // ==================== API Methods ====================

    /**
     * API: Get all menus
     */
    public function apiIndex(Request $request, Response $response): void
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);
        
        $menus = $this->menuModel->getAllPaginated($page, $perPage);
        
        $response->success($menus, 'Menus retrieved');
    }

    /**
     * API: Get active menus
     */
    public function apiGrouped(Request $request, Response $response): void
    {
        $menus = $this->menuModel->getActiveMenus();
        $response->success(['menus' => $menus], 'Menus retrieved');
    }

    /**
     * API: Get role menus
     */
    public function apiRolePermissions(Request $request, Response $response, array $params): void
    {
        $roleId = (int) $params['id'];
        $menus = $this->menuModel->getByRoleId($roleId);
        
        $response->success(['menus' => $menus], 'Role menus retrieved');
    }

    /**
     * API: Update role menus
     */
    public function apiUpdateRolePermissions(Request $request, Response $response, array $params): void
    {
        $roleId = (int) $params['id'];
        $data = $request->getBody();
        $menuIds = $data['menus'] ?? [];
        
        $role = $this->roleModel->find($roleId);
        if (!$role) {
            $response->notFound('Role not found');
            return;
        }
        
        $menuIds = array_map('intval', $menuIds);
        
        $success = $this->menuModel->assignToRole($roleId, $menuIds);
        
        if (!$success) {
            $response->error('Failed to update role menus', 500);
            return;
        }
        
        $updatedMenus = $this->menuModel->getByRoleId($roleId);
        
        $response->success(['menus' => $updatedMenus], 'Role menus updated');
    }
}
