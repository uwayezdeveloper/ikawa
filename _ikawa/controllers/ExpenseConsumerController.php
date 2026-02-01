<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/ExpenseConsumer.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ExpenseConsumer;
use Config\Response;

class ExpenseConsumerController
{
    private $consumerModel;

    public function __construct()
    {
        $this->consumerModel = new ExpenseConsumer();
    }

    public function getAllConsumers()
    {
        $consumers = $this->consumerModel->getAllConsumers();

        if ($consumers !== false) {
            Response::success('Expense consumers retrieved successfully!', $consumers);
        } else {
            Response::error('Failed to retrieve expense consumers', 500);
        }
    }

    public function getConsumerById($cons_id)
    {
        $consumer = $this->consumerModel->getConsumerById($cons_id);

        if ($consumer !== false) {
            Response::success('Expense consumer retrieved successfully!', $consumer);
        } else {
            Response::error('Expense consumer not found', 404);
        }
    }

    public function checkConsumerInUse($cons_id)
    {
        $inUse = $this->consumerModel->isConsumerInUse($cons_id);
        
        Response::success('Consumer usage checked', ['in_use' => $inUse]);
    }

    public function createConsumer()
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

        $required = ['cons_name', 'phone'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $cons_name = trim($input['cons_name']);
        $phone = trim($input['phone']);
        
        // DUPLICATE CHECK
        $duplicate = $this->consumerModel->consumerExists($cons_name, $phone);

        if ($duplicate !== null) {
            Response::error('Consumer name or phone already exists', 409);
            return;
        }

        $data = [
            'cons_name' => $cons_name,
            'phone' => $phone
        ];

        $result = $this->consumerModel->createConsumer($data);

        if ($result) {
            Response::success('Expense consumer created successfully!', null, 201);
        } else {
            Response::error('Failed to create expense consumer', 500);
        }
    }

    public function updateConsumer()
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

        $required = ['cons_id', 'cons_name', 'phone'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $cons_id = $input['cons_id'];
        $cons_name = trim($input['cons_name']);
        $phone = trim($input['phone']);

        $data = [
            'cons_id' => $cons_id,
            'cons_name' => $cons_name,
            'phone' => $phone
        ];

        $result = $this->consumerModel->updateConsumer($data);

        if ($result) {
            Response::success('Expense consumer updated successfully!', null, 200);
        } else {
            Response::error('Failed to update expense consumer', 500);
        }
    }

    public function deleteConsumer()
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

        if (empty($input['cons_id'])) {
            Response::error('Missing field: cons_id', 400);
            return;
        }

        $cons_id = $input['cons_id'];

        // Check if consumer is in use
        $inUse = $this->consumerModel->isConsumerInUse($cons_id);
        
        if ($inUse) {
            Response::error('Cannot delete this consumer because it is being used in expense records', 400);
            return;
        }

        $result = $this->consumerModel->deleteConsumer($cons_id);

        if ($result) {
            Response::success('Expense consumer deleted successfully!', null, 200);
        } else {
            Response::error('Failed to delete expense consumer', 500);
        }
    }
}
