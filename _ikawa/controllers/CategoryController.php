<?php
namespace Controllers;

require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Category;
use Config\Response;

class CategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['category_name', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $category_name = trim($input['category_name']);
        $status = trim($input['status']);
        
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active, inactive, or pending', 400);
            return;
        }

        if ($this->categoryModel->exists($category_name)) {
            Response::error('Category name already exists', 409);
            return;
        }

        $data = [
            'category_name' => $category_name,
            'description' => trim($input['description'] ?? ''),
            'status' => $status
        ];

        if ($this->categoryModel->createCategory($data)) {
            Response::success('Category created successfully', [
                'category_name' => $category_name,
                'status' => $status
            ]);
        } else {
            Response::error('Failed to create category', 500);
        }
    }

    public function getAllCategories()
    {
        $categories = $this->categoryModel->getAllCategories();

        if ($categories !== false) {
            Response::success('Categories retrieved successfully!', $categories);
        } else {
            Response::error('Failed to retrieve categories', 500);
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['category_id', 'category_name', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $category_id = trim($input['category_id']);
        $category_name = trim($input['category_name']);
        $status = trim($input['status']);

        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active, inactive, or pending', 400);
            return;
        }

        if ($this->categoryModel->existsUpdate($category_name, $category_id)) {
            Response::error('Category name already exists', 409);
            return;
        }

        $data = [
            'category_id' => $category_id,
            'category_name' => $category_name,
            'description' => trim($input['description'] ?? ''),
            'status' => $status
        ];

        if ($this->categoryModel->updateCategory($data)) {
            Response::success('Category updated successfully', [
                'category_name' => $category_name,
                'status' => $status
            ]);
        } else {
            Response::error('Failed to update category', 500);
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['category_id'])) {
            Response::error('Invalid request. Category ID required', 400);
            return;
        }

        $category_id = trim($input['category_id']);

        if ($this->categoryModel->deleteCategory($category_id)) {
            Response::success('Category deleted successfully');
        } else {
            Response::error('Failed to delete category', 500);
        }
    }
}
