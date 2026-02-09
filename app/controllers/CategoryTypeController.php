<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\CategoryType;
use App\Models\ProductCategory;

/**
 * CategoryType Controller
 * Handles category type management
 */
class CategoryTypeController extends Controller
{
    protected CategoryType $typeModel;
    protected ProductCategory $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->typeModel = new CategoryType();
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
     * Show category types list
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-category-types')) {
            $_SESSION['flash_error'] = 'You do not have permission to view category types';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $categoryTypes = $this->typeModel->getAll();
        $categories = $this->categoryModel->getActive();
        
        $this->view('products/category-types/index', [
            'categoryTypes' => $categoryTypes,
            'categories' => $categories,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Category Types',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle category type actions (create, update, delete)
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        // Check permission based on action
        $permissionMap = [
            'create' => 'create-category-types',
            'update' => 'edit-category-types',
            'delete' => 'delete-category-types'
        ];
        
        $requiredPermission = $permissionMap[$action] ?? null;
        if (!$requiredPermission || !$this->hasPermission($requiredPermission)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createType($data, $response);
                break;
            case 'update':
                $this->updateType($data, $response);
                break;
            case 'delete':
                $this->deleteType($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/products/category-types');
        }
    }

    /**
     * Create category type
     */
    protected function createType(array $data, Response $response): void
    {
        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'Type name is required';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        if (empty($data['category_id'])) {
            $_SESSION['flash_error'] = 'Please select a category';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        $categoryId = (int) $data['category_id'];
        
        // Check if name exists in this category
        if ($this->typeModel->nameExists($data['name'], $categoryId)) {
            $_SESSION['flash_error'] = 'A type with this name already exists in the selected category';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        $id = $this->typeModel->createType([
            'category_id' => $categoryId,
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($id) {
            $_SESSION['flash_success'] = 'Category type created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create category type';
        }
        
        $response->redirect(APP_URL . '/products/category-types');
    }

    /**
     * Update category type
     */
    protected function updateType(array $data, Response $response): void
    {
        if (empty($data['id']) || empty($data['name'])) {
            $_SESSION['flash_error'] = 'ID and Type name are required';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        if (empty($data['category_id'])) {
            $_SESSION['flash_error'] = 'Please select a category';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        $id = (int) $data['id'];
        $categoryId = (int) $data['category_id'];
        
        // Check if name exists for other records in this category
        if ($this->typeModel->nameExists($data['name'], $categoryId, $id)) {
            $_SESSION['flash_error'] = 'A type with this name already exists in the selected category';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        $updated = $this->typeModel->updateType($id, [
            'category_id' => $categoryId,
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Category type updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update category type';
        }
        
        $response->redirect(APP_URL . '/products/category-types');
    }

    /**
     * Delete category type
     */
    protected function deleteType(array $data, Response $response): void
    {
        if (empty($data['id'])) {
            $_SESSION['flash_error'] = 'ID is required';
            $response->redirect(APP_URL . '/products/category-types');
            return;
        }
        
        $id = (int) $data['id'];
        $deleted = $this->typeModel->deleteType($id);
        
        if ($deleted) {
            $_SESSION['flash_success'] = 'Category type deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete category type';
        }
        
        $response->redirect(APP_URL . '/products/category-types');
    }
}
