<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Expense;
use Config\Response;

class ExpenseController
{
    private $expenseModel;

    public function __construct()
    {
        $this->expenseModel = new Expense();
    }

    public function getAllExpenses()
    {
        $expenses = $this->expenseModel->getAllExpenses();

        if ($expenses !== false) {
            Response::success('Expenses retrieved successfully!', $expenses);
        } else {
            Response::error('Failed to retrieve expenses', 500);
        }
    }

    public function getExpenseById($expense_id)
    {
        $expense = $this->expenseModel->getExpenseById($expense_id);

        if ($expense !== false) {
            Response::success('Expense retrieved successfully!', $expense);
        } else {
            Response::error('Expense not found', 404);
        }
    }

    public function createExpense()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['expense_name', 'categ_id'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $expense_name = trim($input['expense_name']);
        $categ_id = $input['categ_id'];
        $description = isset($input['description']) ? trim($input['description']) : '';
        
        // DUPLICATE CHECK
        $duplicate = $this->expenseModel->expenseExists($expense_name);

        if ($duplicate !== null) {
            Response::error('Expense name already exists', 409);
            return;
        }

        $data = [
            'categ_id' => $categ_id,
            'expense_name' => $expense_name,
            'description' => $description
        ];

        $result = $this->expenseModel->createExpense($data);

        if ($result) {
            Response::success('Expense created successfully!', null, 201);
        } else {
            Response::error('Failed to create expense', 500);
        }
    }

    public function updateExpense()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['expense_id', 'expense_name', 'categ_id'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $data = [
            'expense_id' => $input['expense_id'],
            'categ_id' => $input['categ_id'],
            'expense_name' => trim($input['expense_name']),
            'description' => isset($input['description']) ? trim($input['description']) : ''
        ];

        $result = $this->expenseModel->updateExpense($data);

        if ($result) {
            Response::success('Expense updated successfully!');
        } else {
            Response::error('Failed to update expense', 500);
        }
    }

    public function deleteExpense($expense_id)
    {
        if (empty($expense_id)) {
            Response::error('Expense ID is required', 400);
            return;
        }

        $result = $this->expenseModel->deleteExpense($expense_id);

        if ($result) {
            Response::success('Expense deleted successfully!');
        } else {
            Response::error('Failed to delete expense', 500);
        }
    }

    public function getExpensesByCategory($categ_id)
    {
        if (empty($categ_id)) {
            Response::error('Category ID is required', 400);
            return;
        }

        $expenses = $this->expenseModel->getExpensesByCategory($categ_id);

        if ($expenses !== false) {
            Response::success('Expenses retrieved successfully!', $expenses);
        } else {
            Response::error('Failed to retrieve expenses', 500);
        }
    }
}
