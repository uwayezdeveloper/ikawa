<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    protected ExpenseCategory $expenseCategory;

    public function __construct()
    {
        parent::__construct();
        $this->expenseCategory = new ExpenseCategory();
    }

    /**
     * Display expense categories list
     */
    public function index(Request $request, Response $response)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        
        $result = $this->expenseCategory->getPaginatedCategories($page, $perPage, $search);
        
        $data = [
            'categories' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['totalPages'],
                'per_page' => $result['perPage'],
                'total' => $result['total']
            ],
            'search' => $search,
            'title' => 'Expense Categories',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/categories/index', $data, 'main');
    }

    /**
     * Show create form
     */
    public function create(Request $request, Response $response)
    {
        $data = [
            'title' => 'Add New Expense Category',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/categories/create', $data, 'main');
    }

    /**
     * Store new expense category
     */
    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-categories');
        }

        $data = [
            'categ_name' => trim($_POST['categ_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['categ_name'])) {
            $errors[] = 'Category name is required';
        } elseif (strlen($data['categ_name']) > 50) {
            $errors[] = 'Category name must not exceed 50 characters';
        } elseif ($this->expenseCategory->categoryNameExists($data['categ_name'])) {
            $errors[] = 'Category name already exists';
        }

        if (!empty($data['description']) && strlen($data['description']) > 50) {
            $errors[] = 'Description must not exceed 50 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . '/finance/expense-categories/create');
        }

        try {
            $success = $this->expenseCategory->create($data);
            
            if ($success) {
                $_SESSION['success'] = 'Expense category created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create expense category';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-categories');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, Response $response, array $params)
    {
        $id = (int) $params['id'];
        $category = $this->expenseCategory->findById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Category not found';
            return $response->redirect(APP_URL . '/finance/expense-categories');
        }

        $data = [
            'category' => $category,
            'title' => 'Edit Expense Category',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/categories/edit', $data, 'main');
    }

    /**
     * Update expense category
     */
    public function update(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-categories');
        }

        $id = (int) $params['id'];
        $category = $this->expenseCategory->findById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Category not found';
            return $response->redirect(APP_URL . '/finance/expense-categories');
        }

        $data = [
            'categ_name' => trim($_POST['categ_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['categ_name'])) {
            $errors[] = 'Category name is required';
        } elseif (strlen($data['categ_name']) > 50) {
            $errors[] = 'Category name must not exceed 50 characters';
        } elseif ($this->expenseCategory->categoryNameExists($data['categ_name'], $id)) {
            $errors[] = 'Category name already exists';
        }

        if (!empty($data['description']) && strlen($data['description']) > 50) {
            $errors[] = 'Description must not exceed 50 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . "/finance/expense-categories/{$id}/edit");
        }

        try {
            $success = $this->expenseCategory->update($id, $data);
            
            if ($success) {
                $_SESSION['success'] = 'Expense category updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update expense category';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-categories');
    }

    /**
     * Delete expense category
     */
    public function delete(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-categories');
        }

        $id = (int) $params['id'];
        $category = $this->expenseCategory->findById($id);
        
        if (!$category) {
            $_SESSION['error'] = 'Category not found';
            return $response->redirect(APP_URL . '/finance/expense-categories');
        }

        try {
            $success = $this->expenseCategory->delete($id);
            
            if ($success) {
                $_SESSION['success'] = 'Expense category deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete expense category';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-categories');
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->json(['success' => false, 'message' => 'Invalid request method']);
        }

        $id = (int) $params['id'];

        try {
            $success = $this->expenseCategory->toggleStatus($id);
            
            if ($success) {
                return $response->json(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to update status']);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get active categories for AJAX requests
     */
    public function getActive(Request $request, Response $response)
    {
        try {
            $categories = $this->expenseCategory->getActiveCategories();
            return $response->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}