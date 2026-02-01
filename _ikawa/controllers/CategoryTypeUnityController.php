<?php
namespace Controllers;

require_once __DIR__ . '/../models/CategoryTypeUnity.php';
require_once __DIR__ . '/../config/Response.php';

use Models\CategoryTypeUnity;
use Config\Response;

class CategoryTypeUnityController
{
    private $categoryTypeUnityModel;

    public function __construct()
    {
        $this->categoryTypeUnityModel = new CategoryTypeUnity();
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

        $required = ['type_id', 'unit_id'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $type_id = trim($input['type_id']);
        $unit_id = trim($input['unit_id']);

        if ($this->categoryTypeUnityModel->exists($type_id, $unit_id)) {
            Response::error('This unit is already assigned to this category type', 409);
            return;
        }

        $data = [
            'type_id' => $type_id,
            'unit_id' => $unit_id
        ];

        if ($this->categoryTypeUnityModel->createAssignment($data)) {
            Response::success('Unit assigned successfully');
        } else {
            Response::error('Failed to assign unit', 500);
        }
    }

    public function getAllAssignments()
    {
        $assignments = $this->categoryTypeUnityModel->getAllAssignments();

        if ($assignments !== false) {
            Response::success('Assignments retrieved successfully!', $assignments);
        } else {
            Response::error('Failed to retrieve assignments', 500);
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

        $required = ['assignment_id', 'type_id', 'unit_id', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $assignment_id = trim($input['assignment_id']);
        $type_id = trim($input['type_id']);
        $unit_id = trim($input['unit_id']);
        $status = trim($input['status']);

        $validStatuses = ['active', 'inactive'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active or inactive', 400);
            return;
        }

        // Check for duplicate (excluding current assignment)
        if ($this->categoryTypeUnityModel->existsUpdate($type_id, $unit_id, $assignment_id)) {
            Response::error('This unit is already assigned to this category type', 409);
            return;
        }

        $data = [
            'assignment_id' => $assignment_id,
            'type_id' => $type_id,
            'unit_id' => $unit_id,
            'status' => $status
        ];

        if ($this->categoryTypeUnityModel->updateAssignment($data)) {
            Response::success('Assignment updated successfully', [
                'status' => $status
            ]);
        } else {
            Response::error('Failed to update assignment', 500);
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['assignment_id'])) {
            Response::error('Invalid request. Assignment ID required', 400);
            return;
        }

        $assignment_id = trim($input['assignment_id']);

        if ($this->categoryTypeUnityModel->deleteAssignment($assignment_id)) {
            Response::success('Assignment deleted successfully');
        } else {
            Response::error('Failed to delete assignment', 500);
        }
    }

    public function getUnitsByType($type_id)
    {
        if (empty($type_id)) {
            Response::error('Type ID is required', 400);
            return;
        }

        $units = $this->categoryTypeUnityModel->getUnitsByType($type_id);

        if ($units !== false) {
            Response::success('Units retrieved successfully', $units);
        } else {
            Response::error('Failed to retrieve units', 500);
        }
    }

    public function getTypesWithUnits()
    {
        $types = $this->categoryTypeUnityModel->getTypesWithUnits();

        if ($types !== false) {
            Response::success('Category types retrieved successfully', $types);
        } else {
            Response::error('Failed to retrieve category types', 500);
        }
    }

    public function getTypesByCategory($category_id)
    {
        if (empty($category_id)) {
            Response::error('Category ID is required', 400);
            return;
        }

        $types = $this->categoryTypeUnityModel->getTypesByCategory($category_id);

        if ($types !== false) {
            Response::success('Category types retrieved successfully', $types);
        } else {
            Response::error('Failed to retrieve category types', 500);
        }
    }

    public function getTypeUnityByCategory($category_id)
    {
        if (empty($category_id)) {
            Response::error('Category ID is required', 400);
            return;
        }

        $data = $this->categoryTypeUnityModel->getTypeUnityByCategory($category_id);

        if ($data !== false) {
            Response::success('Type-Unity combinations retrieved successfully', $data);
        } else {
            Response::error('Failed to retrieve data', 500);
        }
    }
}
