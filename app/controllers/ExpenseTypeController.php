<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExpenseType;
use App\Models\ExpenseCategory;

class ExpenseTypeController extends Controller
{
    protected ExpenseType $expenseType;
    protected ExpenseCategory $expenseCategory;

    public function __construct()
    {
        parent::__construct();
        $this->expenseType = new ExpenseType();
        $this->expenseCategory = new ExpenseCategory();
    }

    /**
     * Display expense types list
     */
    public function index(Request $request, Response $response)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        
        $result = $this->expenseType->getPaginatedExpenseTypes($page, $perPage, $search);
        
        $data = [
            'expenseTypes' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['totalPages'],
                'per_page' => $result['perPage'],
                'total' => $result['total']
            ],
            'search' => $search,
            'title' => 'Expense Types',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/types/index', $data, 'main');
    }

    /**
     * Show create form
     */
    public function create(Request $request, Response $response)
    {
        $categories = $this->expenseCategory->getActiveCategories();
        
        $data = [
            'categories' => $categories,
            'title' => 'Add New Expense Type',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/types/create', $data, 'main');
    }

    /**
     * Store new expense type
     */
    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-types');
        }

        $data = [
            'categ_id' => !empty($_POST['categ_id']) ? (int) $_POST['categ_id'] : null,
            'expense_name' => trim($_POST['expense_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'expense_status' => (int) ($_POST['expense_status'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['expense_name'])) {
            $errors[] = 'Expense name is required';
        } elseif (strlen($data['expense_name']) > 50) {
            $errors[] = 'Expense name must not exceed 50 characters';
        } elseif ($this->expenseType->expenseNameExists($data['expense_name'])) {
            $errors[] = 'Expense name already exists';
        }

        if (!empty($data['description']) && strlen($data['description']) > 55) {
            $errors[] = 'Description must not exceed 55 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . '/finance/expense-types/create');
        }

        try {
            $success = $this->expenseType->create($data);
            
            if ($success) {
                $_SESSION['success'] = 'Expense type created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create expense type';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-types');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, Response $response, array $params)
    {
        $id = (int) $params['id'];
        $expenseType = $this->expenseType->findById($id);
        
        if (!$expenseType) {
            $_SESSION['error'] = 'Expense type not found';
            return $response->redirect(APP_URL . '/finance/expense-types');
        }

        $categories = $this->expenseCategory->getActiveCategories();

        $data = [
            'expenseType' => $expenseType,
            'categories' => $categories,
            'title' => 'Edit Expense Type',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/types/edit', $data, 'main');
    }

    /**
     * Update expense type
     */
    public function update(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-types');
        }

        $id = (int) $params['id'];
        $expenseType = $this->expenseType->findById($id);
        
        if (!$expenseType) {
            $_SESSION['error'] = 'Expense type not found';
            return $response->redirect(APP_URL . '/finance/expense-types');
        }

        $data = [
            'categ_id' => !empty($_POST['categ_id']) ? (int) $_POST['categ_id'] : null,
            'expense_name' => trim($_POST['expense_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'expense_status' => (int) ($_POST['expense_status'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['expense_name'])) {
            $errors[] = 'Expense name is required';
        } elseif (strlen($data['expense_name']) > 50) {
            $errors[] = 'Expense name must not exceed 50 characters';
        } elseif ($this->expenseType->expenseNameExists($data['expense_name'], $id)) {
            $errors[] = 'Expense name already exists';
        }

        if (!empty($data['description']) && strlen($data['description']) > 55) {
            $errors[] = 'Description must not exceed 55 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . "/finance/expense-types/{$id}/edit");
        }

        try {
            $success = $this->expenseType->update($id, $data);
            
            if ($success) {
                $_SESSION['success'] = 'Expense type updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update expense type';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-types');
    }

    /**
     * Delete expense type
     */
    public function delete(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-types');
        }

        $id = (int) $params['id'];
        $expenseType = $this->expenseType->findById($id);
        
        if (!$expenseType) {
            $_SESSION['error'] = 'Expense type not found';
            return $response->redirect(APP_URL . '/finance/expense-types');
        }

        try {
            $success = $this->expenseType->delete($id);
            
            if ($success) {
                $_SESSION['success'] = 'Expense type deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete expense type';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-types');
    }

    /**
     * Toggle expense type status
     */
    public function toggleStatus(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->json(['success' => false, 'message' => 'Invalid request method']);
        }

        $id = (int) $params['id'];

        try {
            $success = $this->expenseType->toggleStatus($id);
            
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
     * Get active expense types for AJAX requests
     */
    public function getActive(Request $request, Response $response)
    {
        try {
            $expenseTypes = $this->expenseType->getActiveExpenseTypes();
            return $response->json(['success' => true, 'data' => $expenseTypes]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get expense types by category for AJAX requests
     */
    public function getByCategory(Request $request, Response $response, array $params)
    {
        $categoryId = (int) $params['id'];
        
        try {
            $expenseTypes = $this->expenseType->getByCategory($categoryId);
            return $response->json(['success' => true, 'data' => $expenseTypes]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}