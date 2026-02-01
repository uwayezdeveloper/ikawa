<?php
namespace Controllers;

require_once __DIR__ . '/../models/ProductMixing.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ProductMixing;
use Config\Response;

class ProductMixingController
{
    private $mixingModel;

    public function __construct()
    {
        $this->mixingModel = new ProductMixing();
    }

    public function getAvailableStock()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $loc_id = intval($_SESSION['loc_id']);
        $stock = $this->mixingModel->getAvailableStockForMixing($loc_id);

        if ($stock !== false) {
            Response::success('Available stock retrieved', $stock);
        } else {
            Response::error('Failed to retrieve stock', 500);
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        // Validation
        if (!isset($input['input_items']) || !is_array($input['input_items']) || count($input['input_items']) < 2) {
            Response::error('At least 2 input items are required for mixing', 400);
            return;
        }

        if (!isset($input['output']) || !isset($input['output']['type_id']) || !isset($input['output']['unit_id'])) {
            Response::error('Output product information is required', 400);
            return;
        }

        if (!isset($input['output']['quantity']) || floatval($input['output']['quantity']) <= 0) {
            Response::error('Valid output quantity is required', 400);
            return;
        }

        if (!isset($input['mixing_date']) || empty($input['mixing_date'])) {
            Response::error('Mixing date is required', 400);
            return;
        }

        $data = [
            'loc_id' => $_SESSION['loc_id'],
            'user_id' => $_SESSION['user_id'],
            'input_items' => $input['input_items'],
            'output' => $input['output'],
            'mixing_date' => $input['mixing_date'],
            'notes' => $input['notes'] ?? null
        ];

        $result = $this->mixingModel->createMixing($data);

        if ($result['success']) {
            Response::success('Product mixing created successfully', [
                'reference_no' => $result['reference_no'],
                'mixing_id' => $result['mixing_id']
            ]);
        } else {
            Response::error($result['message'] ?? 'Failed to create product mixing', 500);
        }
    }

    public function getHistory()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $loc_id = intval($_SESSION['loc_id']);
        $history = $this->mixingModel->getMixingHistory($loc_id);

        if ($history !== false) {
            Response::success('Mixing history retrieved', $history);
        } else {
            Response::error('Failed to retrieve mixing history', 500);
        }
    }

    public function getDetails($mixing_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $details = $this->mixingModel->getMixingDetails(intval($mixing_id));

        if ($details !== false) {
            Response::success('Mixing details retrieved', $details);
        } else {
            Response::error('Failed to retrieve mixing details', 500);
        }
    }
}
