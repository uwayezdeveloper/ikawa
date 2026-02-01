<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/ExpenseCategory.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ExpenseCategory;
use Config\Response;

class ExpenseCategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ExpenseCategory();
    }

    public function getAllCategories()
    {
        $categories = $this->categoryModel->getAllCategories();

        if ($categories !== false) {
            Response::success('Expense categories retrieved successfully!', $categories);
        } else {
            Response::error('Failed to retrieve expense categories', 500);
        }
    }

    public function getCategoryById($categ_id)
    {
        $category = $this->categoryModel->getCategoryById($categ_id);

        if ($category !== false) {
            Response::success('Expense category retrieved successfully!', $category);
        } else {
            Response::error('Expense category not found', 404);
        }
    }

    public function checkCategoryInUse($categ_id)
    {
        $inUse = $this->categoryModel->isCategoryInUse($categ_id);
        
        Response::success('Category usage checked', ['in_use' => $inUse]);
    }

    public function createCategory()
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

        $required = ['categ_name'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $categ_name = trim($input['categ_name']);
        $description = isset($input['description']) ? trim($input['description']) : '';
        
        // DUPLICATE CHECK
        $duplicate = $this->categoryModel->categoryExists($categ_name);

        if ($duplicate !== null) {
            Response::error('Expense category name already exists', 409);
            return;
        }

        $data = [
            'categ_name' => $categ_name,
            'description' => $description
        ];

        $result = $this->categoryModel->createCategory($data);

        if ($result) {
            Response::success('Expense category created successfully!', null, 201);
        } else {
            Response::error('Failed to create expense category', 500);
        }
    }

    public function updateCategory()
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

        $required = ['categ_id', 'categ_name'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $categ_id = $input['categ_id'];
        $categ_name = trim($input['categ_name']);
        $description = isset($input['description']) ? trim($input['description']) : '';

        $data = [
            'categ_id' => $categ_id,
            'categ_name' => $categ_name,
            'description' => $description
        ];

        $result = $this->categoryModel->updateCategory($data);

        if ($result) {
            Response::success('Expense category updated successfully!', null, 200);
        } else {
            Response::error('Failed to update expense category', 500);
        }
    }

    public function deleteCategory()
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

        if (empty($input['categ_id'])) {
            Response::error('Missing field: categ_id', 400);
            return;
        }

        $categ_id = $input['categ_id'];

        // Check if category is in use
        $inUse = $this->categoryModel->isCategoryInUse($categ_id);
        
        if ($inUse) {
            Response::error('Cannot delete this category because it is being used in expenses', 400);
            return;
        }

        $result = $this->categoryModel->deleteCategory($categ_id);

        if ($result) {
            Response::success('Expense category deleted successfully!', null, 200);
        } else {
            Response::error('Failed to delete expense category', 500);
        }
    }
}
