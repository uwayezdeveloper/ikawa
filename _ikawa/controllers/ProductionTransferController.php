<?php
namespace Controllers;

require_once __DIR__ . '/../models/ProductionTransfer.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ProductionTransfer;
use Config\Response;

class ProductionTransferController
{
    private $transferModel;

    public function __construct()
    {
        $this->transferModel = new ProductionTransfer();
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
        $stock = $this->transferModel->getAvailableStock($loc_id);

        if ($stock !== false) {
            Response::success('Available stock retrieved', $stock);
        } else {
            Response::error('Failed to retrieve stock', 500);
        }
    }

    public function createMultiple()
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

        if (!is_array($input) || !isset($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
            Response::error('No items to transfer', 400);
            return;
        }

        $transfer_date = $input['transfer_date'] ?? date('Y-m-d');
        $notes = trim($input['notes'] ?? '');

        // Use user's loc_id as both from and to location
        $loc_id = $_SESSION['loc_id'];

        $data = [
            'from_loc_id' => $loc_id,
            'to_loc_id' => $loc_id,  // Same as from_loc_id (user's station)
            'transfer_date' => $transfer_date,
            'notes' => $notes,
            'user_id' => $_SESSION['user_id'],
            'items' => $input['items']
        ];

        $result = $this->transferModel->createTransferBatch($data);

        if ($result['success']) {
            Response::success('Transfer created successfully', ['reference_no' => $result['reference_no']]);
        } else {
            Response::error($result['message'] ?? 'Failed to create transfer', 500);
        }
    }

    public function getTransfers()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug: Log session info
        error_log("getTransfers - SESSION loc_id: " . ($_SESSION['loc_id'] ?? 'NOT SET'));
        error_log("getTransfers - GET loc_id: " . ($_GET['loc_id'] ?? 'NOT SET'));
        
        // Try to get loc_id from session first, then from query parameter
        $loc_id = null;
        
        if (isset($_SESSION['loc_id'])) {
            $loc_id = intval($_SESSION['loc_id']);
        } elseif (isset($_GET['loc_id'])) {
            $loc_id = intval($_GET['loc_id']);
        }
        
        error_log("getTransfers - Final loc_id: " . ($loc_id ?? 'NULL'));
        
        // If no loc_id available, return empty data
        if (!$loc_id) {
            Response::success('Transfers retrieved successfully', []);
            return;
        }

        $transfers = $this->transferModel->getTransfersByLocation($loc_id);

        error_log("getTransfers - transfers count: " . ($transfers !== false ? count($transfers) : 'FALSE'));

        if ($transfers !== false) {
            Response::success('Transfers retrieved successfully', $transfers);
        } else {
            Response::error('Failed to retrieve transfers', 500);
        }
    }

    public function getTransferDetails($tracking_id)
    {
        session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $details = $this->transferModel->getTransferDetails($tracking_id);

        if ($details !== false) {
            Response::success('Transfer details retrieved successfully', $details);
        } else {
            Response::error('Failed to retrieve transfer details', 500);
        }
    }

    public function approveTransfer()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        // Check if approving by transfer_detail_id (individual) or tracking_id (batch)
        if (isset($input['transfer_detail_id'])) {
            $transfer_detail_id = intval($input['transfer_detail_id']);
            $result = $this->transferModel->approveTransferDetail($transfer_detail_id);
        } elseif (isset($input['tracking_id'])) {
            $tracking_id = intval($input['tracking_id']);
            $result = $this->transferModel->approveTransfer($tracking_id);
        } else {
            Response::error('Tracking ID or Transfer Detail ID is required', 400);
            return;
        }

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message'], 400);
        }
    }

    public function returnTransfer()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method: ' . $_SERVER['REQUEST_METHOD'], 405);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['tracking_id']) || !isset($input['items']) || count($input['items']) === 0) {
            Response::error('Invalid return data', 400);
            return;
        }

        $data = [
            'tracking_id' => intval($input['tracking_id']),
            'return_date' => $input['return_date'] ?? date('Y-m-d'),
            'return_reason' => trim($input['return_reason'] ?? ''),
            'items' => $input['items']
        ];

        if (empty($data['return_reason'])) {
            Response::error('Return reason is required', 400);
            return;
        }

        $result = $this->transferModel->returnTransfer($data);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message'], 500);
        }
    }

    public function receiveProduction()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['tracking_id']) || !isset($input['items']) || count($input['items']) === 0) {
            Response::error('Invalid receive data', 400);
            return;
        }

        $data = [
            'tracking_id' => intval($input['tracking_id']),
            'transfer_detail_id' => isset($input['transfer_detail_id']) ? intval($input['transfer_detail_id']) : null,
            'receive_date' => $input['receive_date'] ?? date('Y-m-d'),
            'notes' => trim($input['notes'] ?? ''),
            'items' => $input['items'],
            'loc_id' => $_SESSION['loc_id'],
            'user_id' => $_SESSION['user_id']
        ];

        $result = $this->transferModel->receiveProduction($data);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message'], 500);
        }
    }

    public function getTransferInfo($tracking_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $info = $this->transferModel->getTransferInfo(intval($tracking_id));

        if ($info !== false) {
            Response::success('Transfer info retrieved successfully', $info);
        } else {
            Response::error('Failed to retrieve transfer info', 500);
        }
    }

    public function getTransferDetailInfo($transfer_detail_id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $info = $this->transferModel->getTransferDetailInfo(intval($transfer_detail_id));

        if ($info !== false) {
            Response::success('Transfer detail info retrieved successfully', $info);
        } else {
            Response::error('Failed to retrieve transfer detail info', 500);
        }
    }
}
