<?php
namespace Controllers;

require_once __DIR__ . '/../models/CategoryType.php';
require_once __DIR__ . '/../config/Response.php';

use Models\CategoryType;
use Config\Response;

class CategoryTypeController
{
    private $categoryTypeModel;

    public function __construct()
    {
        $this->categoryTypeModel = new CategoryType();
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

        $required = ['category_id', 'type_name', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $category_id = trim($input['category_id']);
        $type_name = trim($input['type_name']);
        $status = trim($input['status']);
        
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active, inactive, or pending', 400);
            return;
        }

        if ($this->categoryTypeModel->exists($type_name, $category_id)) {
            Response::error('Type name already exists for this category', 409);
            return;
        }

        $data = [
            'category_id' => $category_id,
            'type_name' => $type_name,
            'description' => trim($input['description'] ?? ''),
            'status' => $status
        ];

        if ($this->categoryTypeModel->createCategoryType($data)) {
            Response::success('Category type created successfully', [
                'type_name' => $type_name,
                'status' => $status
            ]);
        } else {
            Response::error('Failed to create category type', 500);
        }
    }

    public function getAllCategoryTypes()
    {
        $categoryTypes = $this->categoryTypeModel->getAllCategoryTypes();

        if ($categoryTypes !== false) {
            Response::success('Category types retrieved successfully!', $categoryTypes);
        } else {
            Response::error('Failed to retrieve category types', 500);
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

        $required = ['type_id', 'category_id', 'type_name', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $type_id = trim($input['type_id']);
        $category_id = trim($input['category_id']);
        $type_name = trim($input['type_name']);
        $status = trim($input['status']);

        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active, inactive, or pending', 400);
            return;
        }

        if ($this->categoryTypeModel->existsUpdate($type_name, $category_id, $type_id)) {
            Response::error('Type name already exists for this category', 409);
            return;
        }

        $data = [
            'type_id' => $type_id,
            'category_id' => $category_id,
            'type_name' => $type_name,
            'description' => trim($input['description'] ?? ''),
            'status' => $status
        ];

        if ($this->categoryTypeModel->updateCategoryType($data)) {
            Response::success('Category type updated successfully', [
                'type_name' => $type_name,
                'status' => $status
            ]);
        } else {
            Response::error('Failed to update category type', 500);
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['type_id'])) {
            Response::error('Invalid request. Type ID required', 400);
            return;
        }

        $type_id = trim($input['type_id']);

        if ($this->categoryTypeModel->deleteCategoryType($type_id)) {
            Response::success('Category type deleted successfully');
        } else {
            Response::error('Failed to delete category type', 500);
        }
    }

    public function getActiveCategories()
    {
        $categories = $this->categoryTypeModel->getActiveCategories();

        if ($categories !== false) {
            Response::success('Active categories retrieved successfully!', $categories);
        } else {
            Response::error('Failed to retrieve active categories', 500);
        }
    }
}
