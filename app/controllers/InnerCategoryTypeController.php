<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\InnerCategoryType;
use App\Models\ProductCategory;
use App\Models\CategoryType;

class InnerCategoryTypeController extends Controller
{
    protected InnerCategoryType $innerCategoryTypeModel;
    protected ProductCategory $productCategoryModel;
    protected CategoryType $categoryTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->innerCategoryTypeModel = new InnerCategoryType();
        $this->productCategoryModel = new ProductCategory();
        $this->categoryTypeModel = new CategoryType();
    }

    /**
     * Display inner category types management page
     */
    public function index(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? [];
        $innerCategoryTypes = [];
        $categories = [];

        try {
            $innerCategoryTypes = $this->innerCategoryTypeModel->getAllWithRelations();
            if (!is_array($innerCategoryTypes)) {
                $innerCategoryTypes = [];
            }
        } catch (\Throwable $e) {
            error_log("InnerCategoryTypeController index data error: " . $e->getMessage());
            $_SESSION['errors'] = ['general' => 'Failed to load some inner category data'];
        }

        try {
            $categories = $this->productCategoryModel->getAll();
        } catch (\Throwable $e) {
            try {
                $categories = Database::fetchAll("SELECT categ_id, categ_name FROM tbl_product_categories ORDER BY categ_name ASC");
            } catch (\Throwable $e2) {
                try {
                    $categories = Database::fetchAll("SELECT id, name FROM product_categories ORDER BY name ASC");
                } catch (\Throwable $e3) {
                    $categories = [];
                    error_log("InnerCategoryTypeController categories load error: " . $e3->getMessage());
                    $_SESSION['errors'] = ['general' => 'Failed to load categories for inner category types'];
                }
            }
        }

        $categories = array_map(static function (array $category): array {
            return [
                'categ_id' => (int) ($category['categ_id'] ?? $category['id'] ?? 0),
                'categ_name' => (string) ($category['categ_name'] ?? $category['name'] ?? ''),
            ];
        }, is_array($categories) ? $categories : []);

        $this->view('products/inner-category-types/index', [
            'innerCategoryTypes' => $innerCategoryTypes,
            'categories' => $categories,
            'user' => $user,
            'pageTitle' => 'Inner Category Types'
        ], 'main');
    }

    /**
     * Handle form actions (create, update, delete)
     */
    public function handleAction(Request $request, Response $response): void
    {
        try {
            $data = $request->getBody();
            $action = $data['action'] ?? '';

            switch ($action) {
                case 'create':
                    $this->create($data, $request, $response);
                    break;
                case 'update':
                    $this->update($data, $request, $response);
                    break;
                case 'delete':
                    $this->delete($data, $request, $response);
                    break;
                default:
                    $_SESSION['errors'] = ['general' => 'Invalid action specified'];
                    $response->redirect(APP_URL . '/products/inner-category-types');
            }
        } catch (\Exception $e) {
            $this->handleError($e, 'Failed to process action');
        }
    }

    /**
     * Create new inner category type
     */
    private function create(array $data, Request $request, Response $response): void
    {
        // Validate input
        $errors = $this->validateInnerCategoryType($data);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                $response->error('Validation failed', 422, $errors);
                return;
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/products/inner-category-types');
            return;
        }

        $innerCategoryTypeData = [
            'categ_id' => (int) $data['categ_id'],
            'type_id' => (int) $data['type_id'],
            'inner_name' => trim($data['inner_name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'status' => isset($data['status']) ? (int) $data['status'] : 1
        ];

        $result = $this->innerCategoryTypeModel->create($innerCategoryTypeData);

        if ($result) {
            $message = 'Inner category type created successfully';
            if ($request->isAjax()) {
                $response->success(['id' => $result], $message);
                return;
            }
            
            $_SESSION['success'] = $message;
        } else {
            $message = 'Failed to create inner category type';
            if ($request->isAjax()) {
                $response->error($message, 500);
                return;
            }
            
            $_SESSION['errors'] = ['general' => $message];
        }

        $response->redirect(APP_URL . '/products/inner-category-types');
    }

    /**
     * Update inner category type
     */
    private function update(array $data, Request $request, Response $response): void
    {
        if (empty($data['inner_id'])) {
            $_SESSION['errors'] = ['general' => 'Inner category type ID is required'];
            $response->redirect(APP_URL . '/products/inner-category-types');
            return;
        }

        $innerId = (int) $data['inner_id'];

        // Validate input
        $errors = $this->validateInnerCategoryType($data, $innerId);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                $response->error('Validation failed', 422, $errors);
                return;
            }
            
            $_SESSION['errors'] = $errors;
            $response->redirect(APP_URL . '/products/inner-category-types');
            return;
        }

        $innerCategoryTypeData = [
            'categ_id' => (int) $data['categ_id'],
            'type_id' => (int) $data['type_id'],
            'inner_name' => trim($data['inner_name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'status' => isset($data['status']) ? (int) $data['status'] : 1
        ];

        $result = $this->innerCategoryTypeModel->update($innerId, $innerCategoryTypeData);

        if ($result) {
            $message = 'Inner category type updated successfully';
            if ($request->isAjax()) {
                $response->success([], $message);
                return;
            }
            
            $_SESSION['success'] = $message;
        } else {
            $message = 'Failed to update inner category type';
            if ($request->isAjax()) {
                $response->error($message, 500);
                return;
            }
            
            $_SESSION['errors'] = ['general' => $message];
        }

        $response->redirect(APP_URL . '/products/inner-category-types');
    }

    /**
     * Delete inner category type
     */
    private function delete(array $data, Request $request, Response $response): void
    {
        if (empty($data['inner_id'])) {
            if ($request->isAjax()) {
                $response->error('Inner category type ID is required', 400);
                return;
            }
            
            $_SESSION['errors'] = ['general' => 'Inner category type ID is required'];
            $response->redirect(APP_URL . '/products/inner-category-types');
            return;
        }

        $innerId = (int) $data['inner_id'];
        $result = $this->innerCategoryTypeModel->delete($innerId);

        if ($result) {
            $message = 'Inner category type deleted successfully';
            if ($request->isAjax()) {
                $response->success([], $message);
                return;
            }
            
            $_SESSION['success'] = $message;
        } else {
            $message = 'Failed to delete inner category type';
            if ($request->isAjax()) {
                $response->error($message, 500);
                return;
            }
            
            $_SESSION['errors'] = ['general' => $message];
        }

        $response->redirect(APP_URL . '/products/inner-category-types');
    }

    /**
     * Get category types by category ID (AJAX)
     */
    public function getCategoryTypes(Request $request, Response $response): void
    {
        try {
            $categoryId = $request->query('category_id');
            
            if (!$categoryId) {
                $response->error('Category ID is required', 400);
                return;
            }

            $categoryTypes = [];

            try {
                $categoryTypes = $this->categoryTypeModel->getByCategory((int) $categoryId);
            } catch (\Throwable $e) {
                $categoryTypes = [];
            }

            if (empty($categoryTypes)) {
                $categoryTypes = Database::fetchAll(
                    "SELECT type_id, type_name FROM tbl_category_types WHERE categ_id = :category_id ORDER BY type_name ASC",
                    ['category_id' => (int) $categoryId]
                );
            }

            $categoryTypes = array_map(static function (array $type): array {
                return [
                    'type_id' => (int) ($type['type_id'] ?? $type['id'] ?? 0),
                    'type_name' => (string) ($type['type_name'] ?? $type['name'] ?? ''),
                ];
            }, $categoryTypes);
            
            $response->success($categoryTypes, 'Category types retrieved successfully');
        } catch (\Exception $e) {
            $response->error('Failed to get category types: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate inner category type data
     */
    private function validateInnerCategoryType(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // Validate category
        if (empty($data['categ_id'])) {
            $errors['categ_id'] = 'Category is required';
        } elseif (!is_numeric($data['categ_id']) || (int) $data['categ_id'] <= 0) {
            $errors['categ_id'] = 'Please select a valid category';
        }

        // Validate category type
        if (empty($data['type_id'])) {
            $errors['type_id'] = 'Category type is required';
        } elseif (!is_numeric($data['type_id']) || (int) $data['type_id'] <= 0) {
            $errors['type_id'] = 'Please select a valid category type';
        }

        // Validate inner name
        if (empty($data['inner_name'])) {
            $errors['inner_name'] = 'Inner category name is required';
        } elseif (strlen(trim($data['inner_name'])) < 2) {
            $errors['inner_name'] = 'Inner category name must be at least 2 characters';
        } elseif (strlen(trim($data['inner_name'])) > 50) {
            $errors['inner_name'] = 'Inner category name cannot exceed 50 characters';
        }

        // Validate description length
        if (!empty($data['description']) && strlen(trim($data['description'])) > 50) {
            $errors['description'] = 'Description cannot exceed 50 characters';
        }

        // Check for duplicate inner category name within same category and type
        if (!empty($data['categ_id']) && !empty($data['type_id']) && !empty($data['inner_name'])) {
            $exists = $this->innerCategoryTypeModel->nameExistsForCategoryType(
                trim($data['inner_name']),
                (int) $data['categ_id'],
                (int) $data['type_id'],
                $excludeId
            );
            
            if ($exists) {
                $errors['inner_name'] = 'This inner category name already exists for the selected category and type';
            }
        }

        return $errors;
    }

    /**
     * Handle errors consistently
     */
    private function handleError(\Exception $e, string $userMessage): void
    {
        error_log("InnerCategoryTypeController Error: " . $e->getMessage());
        
        if (APP_ENV === 'development') {
            throw $e;
        }
        
        $_SESSION['errors'] = ['general' => $userMessage];
        (new Response())->redirect(APP_URL . '/products/inner-category-types');
    }
}