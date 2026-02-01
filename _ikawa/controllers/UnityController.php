<?php
namespace Controllers;

require_once __DIR__ . '/../models/Unity.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Unity;
use Config\Response;

class UnityController
{
    private $unityModel;

    public function __construct()
    {
        $this->unityModel = new Unity();
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

        if (empty($input['unit_name'])) {
            Response::error("Missing field: unit_name", 400);
            return;
        }

        $unit_name = trim($input['unit_name']);

        if ($this->unityModel->exists($unit_name)) {
            Response::error('Unit name already exists', 409);
            return;
        }

        if ($this->unityModel->createUnity($unit_name)) {
            Response::success('Unit created successfully', [
                'unit_name' => $unit_name
            ]);
        } else {
            Response::error('Failed to create unit', 500);
        }
    }

    public function getAllUnity()
    {
        $unities = $this->unityModel->getAllUnity();

        if ($unities !== false) {
            Response::success('Units retrieved successfully!', $unities);
        } else {
            Response::error('Failed to retrieve units', 500);
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

        $required = ['unit_id', 'unit_name'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $unit_id = trim($input['unit_id']);
        $unit_name = trim($input['unit_name']);

        if ($this->unityModel->existsUpdate($unit_name, $unit_id)) {
            Response::error('Unit name already exists', 409);
            return;
        }

        $data = [
            'unit_id' => $unit_id,
            'unit_name' => $unit_name
        ];

        if ($this->unityModel->updateUnity($data)) {
            Response::success('Unit updated successfully', [
                'unit_name' => $unit_name
            ]);
        } else {
            Response::error('Failed to update unit', 500);
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['unit_id'])) {
            Response::error('Invalid request. Unit ID required', 400);
            return;
        }

        $unit_id = trim($input['unit_id']);

        if ($this->unityModel->deleteUnity($unit_id)) {
            Response::success('Unit deleted successfully');
        } else {
            Response::error('Failed to delete unit', 500);
        }
    }
}
