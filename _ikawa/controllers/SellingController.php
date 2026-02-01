<?php
namespace Controllers;

require_once __DIR__ . '/../models/Selling.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Selling;
use Config\Response;

class SellingController
{
    private $sellingModel;

    public function __construct()
    {
        $this->sellingModel = new Selling();
    }

    public function getAvailableStock()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Debug logging
            error_log("SellingController::getAvailableStock - Session loc_id: " . ($_SESSION['loc_id'] ?? 'NOT SET'));
            error_log("SellingController::getAvailableStock - Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
            
            if (!isset($_SESSION['loc_id'])) {
                Response::error('User session not found. Please log in again.', 401);
                return;
            }

            $loc_id = intval($_SESSION['loc_id']);
            error_log("SellingController::getAvailableStock - Using loc_id: " . $loc_id);
            
            $stock = $this->sellingModel->getAvailableStockForSelling($loc_id);

            error_log("SellingController::getAvailableStock - Stock result: " . ($stock !== false ? count($stock) . ' items' : 'FALSE'));

            if ($stock !== false) {
                Response::success('Available stock retrieved', $stock);
            } else {
                Response::error('Failed to retrieve stock', 500);
            }
        } catch (Exception $e) {
            error_log("SellingController::getAvailableStock - Exception: " . $e->getMessage());
            Response::error('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function createSale()
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

        if (!is_array($input) || !isset($input['items']) || count($input['items']) === 0) {
            Response::error('No items to sell', 400);
            return;
        }

        $hasPrice = isset($input['has_price']) ? $input['has_price'] : true;
        
        // Only require payment if has_price is true
        if ($hasPrice) {
            if (!isset($input['payment']) || !isset($input['payment']['mode_id']) || !isset($input['payment']['acc_id'])) {
                Response::error('Payment information is required', 400);
                return;
            }
        }

        $data = [
            'loc_id' => $_SESSION['loc_id'],
            'user_id' => $_SESSION['user_id'],
            'items' => $input['items'],
            'has_price' => $hasPrice,
            'client_id' => $input['client_id'] ?? null,
            'notes' => $input['notes'] ?? null
        ];
        
        if ($hasPrice && isset($input['payment'])) {
            $data['payment'] = $input['payment'];
        }

        $result = $this->sellingModel->createSale($data);

        if ($result['success']) {
            Response::success('Sale recorded successfully', ['invoice_no' => $result['invoice_no'], 'sale_id' => $result['sale_id']]);
        } else {
            Response::error($result['message'] ?? 'Failed to record sale', 500);
        }
    }

    // ==================== CLIENT ENDPOINTS ====================

    public function getClients()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $clients = $this->sellingModel->getClients();

        if ($clients !== false) {
            Response::success('Clients retrieved', $clients);
        } else {
            Response::error('Failed to retrieve clients', 500);
        }
    }

    public function createClient()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['client_name']) || empty(trim($input['client_name']))) {
            Response::error('Client name is required', 400);
            return;
        }

        // Check for duplicate
        $exists = $this->sellingModel->clientExists(trim($input['client_name']), $input['phone'] ?? null);
        if ($exists) {
            Response::error('Client with this name or phone already exists', 409);
            return;
        }

        $data = [
            'client_name' => trim($input['client_name']),
            'phone' => $input['phone'] ?? null,
            'email' => $input['email'] ?? null,
            'address' => $input['address'] ?? null
        ];

        $client_id = $this->sellingModel->createClient($data);

        if ($client_id) {
            Response::success('Client created successfully', ['client_id' => $client_id, 'client_name' => $data['client_name']]);
        } else {
            Response::error('Failed to create client', 500);
        }
    }

    public function getSalesHistory()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $loc_id = intval($_SESSION['loc_id']);
        $history = $this->sellingModel->getSalesHistory($loc_id);

        if ($history !== false) {
            Response::success('Sales history retrieved', $history);
        } else {
            Response::error('Failed to retrieve sales history', 500);
        }
    }

    public function getSaleDetails($sale_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $details = $this->sellingModel->getSaleDetails(intval($sale_id));

        if ($details !== false) {
            Response::success('Sale details retrieved', $details);
        } else {
            Response::error('Failed to retrieve sale details', 500);
        }
    }

    // Get pending sales (sales without prices)
    public function getPendingSales()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $loc_id = intval($_SESSION['loc_id']);
        $pendingSales = $this->sellingModel->getPendingSales($loc_id);

        if ($pendingSales !== false) {
            Response::success('Pending sales retrieved', $pendingSales);
        } else {
            Response::error('Failed to retrieve pending sales', 500);
        }
    }

    // Update sale prices and complete the sale
    public function updateSalePrices()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $sale_id = isset($_POST['sale_id']) ? intval($_POST['sale_id']) : 0;
        $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
        $grand_total = isset($_POST['grand_total']) ? floatval($_POST['grand_total']) : 0;
        $payment_mode = isset($_POST['payment_mode']) ? intval($_POST['payment_mode']) : 0;
        $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
        $amount_received = isset($_POST['amount_received']) ? floatval($_POST['amount_received']) : 0;
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

        // Validation
        if ($sale_id <= 0) {
            Response::error('Invalid sale ID', 400);
            return;
        }

        if (empty($items)) {
            Response::error('No items provided', 400);
            return;
        }

        if ($grand_total <= 0) {
            Response::error('Invalid total amount', 400);
            return;
        }

        if ($payment_mode <= 0) {
            Response::error('Payment mode is required', 400);
            return;
        }

        if ($account_id <= 0) {
            Response::error('Account is required', 400);
            return;
        }

        if ($amount_received <= 0) {
            Response::error('Amount received is required', 400);
            return;
        }

        $user_id = intval($_SESSION['user_id']);
        $result = $this->sellingModel->updateSalePrices($sale_id, $items, $grand_total, $payment_mode, $account_id, $amount_received, $notes, $user_id);

        if ($result) {
            Response::success('Sale prices updated successfully');
        } else {
            Response::error('Failed to update sale prices', 500);
        }
    }
}
