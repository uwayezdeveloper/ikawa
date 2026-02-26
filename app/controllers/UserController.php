<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Role;
use App\Models\Location;
use Exception;

/**
 * User Controller
 * Handles user management
 */
class UserController extends Controller
{
    protected User $userModel;
    protected Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    /**
     * Get locations safely
     */
    protected function getLocations(): array
    {
        try {
            $locationModel = new Location();
            return $locationModel->getActive();
        } catch (Exception $e) {
            error_log("Location model error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has menu access
     */
    protected function hasMenuAccess(string $menuIdentifier): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        // Admin always has access
        $isSuperAdmin = ((int)($user['role_id'] ?? 0) === 1);
        if ($isSuperAdmin) return true;
        
        $menuIdentifiers = $user['menu_identifiers'] ?? [];
        return in_array($menuIdentifier, $menuIdentifiers);
    }

    /**
     * List all users (web view)
     */
    public function index(Request $request, Response $response): void
    {
        $page = (int) $request->query('page', 1);
        $users = $this->userModel->paginateWithRoles($page, 10);
        
        $this->view('users/index', [
            'users' => $users,
            'pageTitle' => 'Users'
        ], 'main');
    }

    /**
     * Show single user
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $viewUser = $this->userModel->findWithRole((int) $params['id']);
        
        if (!$viewUser) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $this->view('users/show', [
            'viewUser' => $viewUser,
            'pageTitle' => 'User Details'
        ], 'main');
    }

    /**
     * Show create user form
     */
    public function create(Request $request, Response $response): void
    {
        if (!$this->hasMenuAccess('users')) {
            $_SESSION['flash_error'] = 'You do not have permission to create users';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $roles = $this->roleModel->getActive();
        $locations = $this->getLocations();
        
        $this->view('users/create', [
            'roles' => $roles,
            'locations' => $locations,
            'pageTitle' => 'Add User'
        ], 'main');
    }

    /**
     * Store new user
     */
    public function store(Request $request, Response $response): void
    {
        if (!$this->hasMenuAccess('users')) {
            $_SESSION['flash_error'] = 'You do not have permission to create users';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/users/create');
            return;
        }
        
        // Check if email exists
        if ($this->userModel->findByEmail($data['email'])) {
            $_SESSION['flash_error'] = 'Email already exists';
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/users/create');
            return;
        }
        
        // Prepare user data
        $userData = [
            'uuid' => $this->generateUUID(),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => trim($data['email']),
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]),
            'phone' => trim($data['phone'] ?? ''),
            'role_id' => (int) $data['role_id'],
            'location_id' => !empty($data['location_id']) ? (int) $data['location_id'] : null,
            'status' => $data['status'] ?? 'active'
        ];
        
        $userId = $this->userModel->create($userData);
        
        if (!$userId) {
            $_SESSION['flash_error'] = 'Failed to create user';
            $response->redirect(APP_URL . '/users/create');
            return;
        }
        
        $_SESSION['flash_success'] = 'User created successfully';
        $response->redirect(APP_URL . '/users');
    }

    /**
     * Show edit user form
     */
    public function edit(Request $request, Response $response, array $params): void
    {
        if (!$this->hasMenuAccess('users')) {
            $_SESSION['flash_error'] = 'You do not have permission to edit users';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $viewUser = $this->userModel->findWithRole((int) $params['id']);
        
        if (!$viewUser) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $roles = $this->roleModel->getActive();
        $locations = $this->getLocations();
        
        $this->view('users/edit', [
            'viewUser' => $viewUser,
            'roles' => $roles,
            'locations' => $locations,
            'pageTitle' => 'Edit User'
        ], 'main');
    }

    /**
     * Update user
     */
    public function update(Request $request, Response $response, array $params): void
    {
        if (!$this->hasMenuAccess('users')) {
            $_SESSION['flash_error'] = 'You do not have permission to edit users';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $userId = (int) $params['id'];
        $existingUser = $this->userModel->find($userId);
        
        if (!$existingUser) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'role_id' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = 'Validation failed: ' . implode(', ', $errors);
            $response->redirect(APP_URL . '/users/' . $userId . '/edit');
            return;
        }
        
        // Check if email exists (exclude current user)
        $emailUser = $this->userModel->findByEmail($data['email']);
        if ($emailUser && $emailUser['id'] != $userId) {
            $_SESSION['flash_error'] = 'Email already exists';
            $response->redirect(APP_URL . '/users/' . $userId . '/edit');
            return;
        }
        
        // Prepare update data
        $updateData = [
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => trim($data['email']),
            'phone' => trim($data['phone'] ?? ''),
            'role_id' => (int) $data['role_id'],
            'location_id' => !empty($data['location_id']) ? (int) $data['location_id'] : null,
            'status' => $data['status'] ?? 'active'
        ];
        
        // Update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        }
        
        $updated = $this->userModel->update($userId, $updateData);
        
        if (!$updated) {
            $_SESSION['flash_error'] = 'Failed to update user';
            $response->redirect(APP_URL . '/users/' . $userId . '/edit');
            return;
        }
        
        $_SESSION['flash_success'] = 'User updated successfully';
        $response->redirect(APP_URL . '/users');
    }

    /**
     * Delete user
     */
    public function delete(Request $request, Response $response, array $params): void
    {
        if (!$this->hasMenuAccess('users')) {
            $_SESSION['flash_error'] = 'You do not have permission to delete users';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $userId = (int) $params['id'];
        $existingUser = $this->userModel->find($userId);
        
        if (!$existingUser) {
            $_SESSION['flash_error'] = 'User not found';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        // Don't allow deleting yourself
        if ($userId === ($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['flash_error'] = 'You cannot delete your own account';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $deleted = $this->userModel->softDelete($userId);
        
        if (!$deleted) {
            $_SESSION['flash_error'] = 'Failed to delete user';
            $response->redirect(APP_URL . '/users');
            return;
        }
        
        $_SESSION['flash_success'] = 'User deleted successfully';
        $response->redirect(APP_URL . '/users');
    }

    /**
     * Generate UUID
     */
    protected function generateUUID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}