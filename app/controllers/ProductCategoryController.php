<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ProductCategory;

/**
 * ProductCategory Controller
 * Handles product category management
 */
class ProductCategoryController extends Controller
{
    protected ProductCategory $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new ProductCategory();
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
     * Show product categories list
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-product-categories')) {
            $_SESSION['flash_error'] = 'You do not have permission to view product categories';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('products/categories/index', [
            'categories' => $categories,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Product Categories',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle category actions (create, update, delete)
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        // Check permission based on action
        $permissionMap = [
            'create' => 'create-product-categories',
            'update' => 'edit-product-categories',
            'delete' => 'delete-product-categories'
        ];
        
        $requiredPermission = $permissionMap[$action] ?? null;
        if (!$requiredPermission || !$this->hasPermission($requiredPermission)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/products/categories');
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
                $response->redirect(APP_URL . '/products/categories');
        }
    }

    /**
     * Create category
     */
    protected function createCategory(array $data, Response $response): void
    {
        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'Category name is required';
            $response->redirect(APP_URL . '/products/categories');
            return;
        }
        
        // Check if name exists
        if ($this->categoryModel->nameExists($data['name'])) {
            $_SESSION['flash_error'] = 'Category with this name already exists';
            $response->redirect(APP_URL . '/products/categories');
            return;
        }
        
        $id = $this->categoryModel->createCategory([
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($id) {
            $_SESSION['flash_success'] = 'Product category created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create product category';
        }
        
        $response->redirect(APP_URL . '/products/categories');
    }

    /**
     * Update category
     */
    protected function updateCategory(array $data, Response $response): void
    {
        if (empty($data['id']) || empty($data['name'])) {
            $_SESSION['flash_error'] = 'ID and Category name are required';
            $response->redirect(APP_URL . '/products/categories');
            return;
        }
        
        $id = (int) $data['id'];
        
        // Check if name exists for other records
        if ($this->categoryModel->nameExists($data['name'], $id)) {
            $_SESSION['flash_error'] = 'Category with this name already exists';
            $response->redirect(APP_URL . '/products/categories');
            return;
        }
        
        $updated = $this->categoryModel->updateCategory($id, [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Product category updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update product category';
        }
        
        $response->redirect(APP_URL . '/products/categories');
    }

    /**
     * Delete category
     */
    protected function deleteCategory(array $data, Response $response): void
    {
        if (empty($data['id'])) {
            $_SESSION['flash_error'] = 'ID is required';
            $response->redirect(APP_URL . '/products/categories');
            return;
        }
        
        $id = (int) $data['id'];
        $deleted = $this->categoryModel->deleteCategory($id);
        
        if ($deleted) {
            $_SESSION['flash_success'] = 'Product category deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete product category';
        }
        
        $response->redirect(APP_URL . '/products/categories');
    }
}
