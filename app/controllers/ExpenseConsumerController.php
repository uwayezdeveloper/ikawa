<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExpenseConsumer;

class ExpenseConsumerController extends Controller
{
    protected ExpenseConsumer $expenseConsumer;

    public function __construct()
    {
        parent::__construct();
        $this->expenseConsumer = new ExpenseConsumer();
    }

    /**
     * Display expense consumers list
     */
    public function index(Request $request, Response $response)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        
        $result = $this->expenseConsumer->getPaginatedConsumers($page, $perPage, $search);
        
        $data = [
            'consumers' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['totalPages'],
                'per_page' => $result['perPage'],
                'total' => $result['total']
            ],
            'search' => $search,
            'title' => 'Expense Consumers',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/consumers/index', $data, 'main');
    }

    /**
     * Show create form
     */
    public function create(Request $request, Response $response)
    {
        $data = [
            'title' => 'Add New Expense Consumer',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/consumers/create', $data, 'main');
    }

    /**
     * Store new expense consumer
     */
    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-consumers');
        }

        $data = [
            'cons_name' => trim($_POST['cons_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'sts' => (int) ($_POST['sts'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['cons_name'])) {
            $errors[] = 'Consumer name is required';
        } elseif (strlen($data['cons_name']) > 50) {
            $errors[] = 'Consumer name must not exceed 50 characters';
        } elseif ($this->expenseConsumer->consumerNameExists($data['cons_name'])) {
            $errors[] = 'Consumer name already exists';
        }

        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        } elseif (strlen($data['phone']) > 50) {
            $errors[] = 'Phone number must not exceed 50 characters';
        } elseif (!$this->validatePhone($data['phone'])) {
            $errors[] = 'Please enter a valid phone number';
        } elseif ($this->expenseConsumer->phoneExists($data['phone'])) {
            $errors[] = 'Phone number already exists';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . '/finance/expense-consumers/create');
        }

        try {
            $success = $this->expenseConsumer->create($data);
            
            if ($success) {
                $_SESSION['success'] = 'Expense consumer created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create expense consumer';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-consumers');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, Response $response, array $params)
    {
        $id = (int) $params['id'];
        $consumer = $this->expenseConsumer->findById($id);
        
        if (!$consumer) {
            $_SESSION['error'] = 'Consumer not found';
            return $response->redirect(APP_URL . '/finance/expense-consumers');
        }

        $data = [
            'consumer' => $consumer,
            'title' => 'Edit Expense Consumer',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/expenses/consumers/edit', $data, 'main');
    }

    /**
     * Update expense consumer
     */
    public function update(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-consumers');
        }

        $id = (int) $params['id'];
        $consumer = $this->expenseConsumer->findById($id);
        
        if (!$consumer) {
            $_SESSION['error'] = 'Consumer not found';
            return $response->redirect(APP_URL . '/finance/expense-consumers');
        }

        $data = [
            'cons_name' => trim($_POST['cons_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'sts' => (int) ($_POST['sts'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['cons_name'])) {
            $errors[] = 'Consumer name is required';
        } elseif (strlen($data['cons_name']) > 50) {
            $errors[] = 'Consumer name must not exceed 50 characters';
        } elseif ($this->expenseConsumer->consumerNameExists($data['cons_name'], $id)) {
            $errors[] = 'Consumer name already exists';
        }

        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        } elseif (strlen($data['phone']) > 50) {
            $errors[] = 'Phone number must not exceed 50 characters';
        } elseif (!$this->validatePhone($data['phone'])) {
            $errors[] = 'Please enter a valid phone number';
        } elseif ($this->expenseConsumer->phoneExists($data['phone'], $id)) {
            $errors[] = 'Phone number already exists';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . "/finance/expense-consumers/{$id}/edit");
        }

        try {
            $success = $this->expenseConsumer->update($id, $data);
            
            if ($success) {
                $_SESSION['success'] = 'Expense consumer updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update expense consumer';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-consumers');
    }

    /**
     * Delete expense consumer
     */
    public function delete(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/expense-consumers');
        }

        $id = (int) $params['id'];
        $consumer = $this->expenseConsumer->findById($id);
        
        if (!$consumer) {
            $_SESSION['error'] = 'Consumer not found';
            return $response->redirect(APP_URL . '/finance/expense-consumers');
        }

        try {
            $success = $this->expenseConsumer->delete($id);
            
            if ($success) {
                $_SESSION['success'] = 'Expense consumer deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete expense consumer';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/expense-consumers');
    }

    /**
     * Toggle consumer status
     */
    public function toggleStatus(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->json(['success' => false, 'message' => 'Invalid request method']);
        }

        $id = (int) $params['id'];

        try {
            $success = $this->expenseConsumer->toggleStatus($id);
            
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
     * Get active consumers for AJAX requests
     */
    public function getActive(Request $request, Response $response)
    {
        try {
            $consumers = $this->expenseConsumer->getActiveConsumers();
            return $response->json(['success' => true, 'data' => $consumers]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Search consumers for AJAX requests
     */
    public function search(Request $request, Response $response)
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query) || strlen($query) < 2) {
            return $response->json(['success' => false, 'message' => 'Query must be at least 2 characters']);
        }

        try {
            $consumers = $this->expenseConsumer->searchConsumers($query);
            return $response->json(['success' => true, 'data' => $consumers]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Validate phone number format
     */
    private function validatePhone(string $phone): bool
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if phone has at least 8 digits and no more than 15
        if (strlen($cleanPhone) < 8 || strlen($cleanPhone) > 15) {
            return false;
        }
        
        // Basic phone number pattern validation
        return preg_match('/^[\d\+\-\(\)\s]{8,20}$/', $phone);
    }
}