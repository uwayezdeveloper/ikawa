<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Sellize.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Sellize;
use Config\Response;

class SellizeController
{
    private $sellizeModel;

    public function __construct()
    {
        $this->sellizeModel = new Sellize();
    }

    public function create()
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

        $required = ['sallize_name', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $sallize_name = trim($input['sallize_name']);
        $status = trim($input['status']);
        
        // Validate status
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active, inactive, or pending', 400);
            return;
        }

        // DUPLICATE CHECK
        if ($this->sellizeModel->exists($sallize_name)) {
            Response::error('Sallize name already exists', 409);
            return;
        }

        $data = [
            'sallize_name' => $sallize_name,
            'description' => trim($input['description'] ?? ''),
            'status' => $status
        ];

        if ($this->sellizeModel->createSallize($data)) {
            Response::success('Sallize created successfully', [
                'sallize_name' => $sallize_name,
                'status' => $status
            ]);
        } else {
            Response::error('Failed to create sallize', 500);
        }
    }

    public function getAllSallize()
    {
        $sallize = $this->sellizeModel->getAllSallize();

        if ($sallize !== false) {
            Response::success('Sallize retrieved successfully!', $sallize);
        } else {
            Response::error('Failed to retrieve sallize', 500);
        }
    }

    public function update()
    {
        // PUT METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Invalid request method', 405);
            return;
        }

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['sallize_id', 'sallize_name', 'status'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $sallize_id = trim($input['sallize_id']);
        $sallize_name = trim($input['sallize_name']);
        $status = trim($input['status']);

        // Validate status
        $validStatuses = ['active', 'inactive', 'pending'];
        if (!in_array($status, $validStatuses)) {
            Response::error('Invalid status. Must be: active, inactive, or pending', 400);
            return;
        }

        // DUPLICATE CHECK (exclude current record)
        if ($this->sellizeModel->existsUpdate($sallize_name, $sallize_id)) {
            Response::error('Sallize name already exists', 409);
            return;
        }

        $data = [
            'sallize_id' => $sallize_id,
            'sallize_name' => $sallize_name,
            'description' => trim($input['description'] ?? ''),
            'status' => $status
        ];

        if ($this->sellizeModel->updateSallize($data)) {
            Response::success('Sallize updated successfully', [
                'sallize_name' => $sallize_name,
                'status' => $status
            ]);
        } else {
            Response::error('Failed to update sallize', 500);
        }
    }

    public function delete()
    {
        // DELETE METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Invalid request method', 405);
            return;
        }

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['sallize_id'])) {
            Response::error('Invalid request. Sallize ID required', 400);
            return;
        }

        $sallize_id = trim($input['sallize_id']);

        if ($this->sellizeModel->deleteSallize($sallize_id)) {
            Response::success('Sallize deleted successfully');
        } else {
            Response::error('Failed to delete sallize', 500);
        }
    }
}
