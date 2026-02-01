<?php
namespace Controllers;

require_once __DIR__ . '/../models/TransferProduct.php';
require_once __DIR__ . '/../config/Response.php';

use Models\TransferProduct;
use Config\Response;

class TransferProductController
{
    private $transferModel;

    public function __construct()
    {
        $this->transferModel = new TransferProduct();
    }

    /**
     * Get all stations
     */
    public function getStations()
    {
        $stations = $this->transferModel->getStations();

        if ($stations !== false) {
            Response::success('Stations retrieved successfully!', $stations);
        } else {
            Response::error('Failed to retrieve stations', 500);
        }
    }

    /**
     * Get all warehouses
     */
    public function getWarehouses()
    {
        $warehouses = $this->transferModel->getWarehouses();

        if ($warehouses !== false) {
            Response::success('Warehouses retrieved successfully!', $warehouses);
        } else {
            Response::error('Failed to retrieve warehouses', 500);
        }
    }

    /**
     * Get active categories
     */
    public function getActiveCategories()
    {
        $categories = $this->transferModel->getActiveCategories();

        if ($categories !== false) {
            Response::success('Categories retrieved successfully!', $categories);
        } else {
            Response::error('Failed to retrieve categories', 500);
        }
    }

    /**
     * Get category types by category ID
     */
    public function getCategoryTypesByCategory($category_id)
    {
        if (empty($category_id)) {
            Response::error('Category ID is required', 400);
            return;
        }

        $types = $this->transferModel->getCategoryTypesByCategory($category_id);

        if ($types !== false) {
            Response::success('Category types retrieved successfully!', $types);
        } else {
            Response::error('Failed to retrieve category types', 500);
        }
    }

    /**
     * Get units by category type ID
     */
    public function getUnitsByTypeId($type_id)
    {
        if (empty($type_id)) {
            Response::error('Type ID is required', 400);
            return;
        }

        $units = $this->transferModel->getUnitsByTypeId($type_id);

        if ($units !== false) {
            Response::success('Units retrieved successfully!', $units);
        } else {
            Response::error('Failed to retrieve units', 500);
        }
    }

    /**
     * Get category types available in stock at user's station
     */
    public function getStockCategoryTypes($category_id)
    {
        if (empty($category_id)) {
            Response::error('Category ID is required', 400);
            return;
        }

        $loc_id = $_SESSION['loc_id'] ?? null;
        if (!$loc_id) {
            Response::error('User station not found. Please log in again.', 400);
            return;
        }

        $types = $this->transferModel->getStockCategoryTypes($loc_id, $category_id);

        if ($types !== false) {
            Response::success('Stock category types retrieved successfully!', $types);
        } else {
            Response::error('Failed to retrieve stock category types', 500);
        }
    }

    /**
     * Get units available in stock for a specific type at user's station
     */
    public function getStockUnits($type_id)
    {
        if (empty($type_id)) {
            Response::error('Type ID is required', 400);
            return;
        }

        $loc_id = $_SESSION['loc_id'] ?? null;
        if (!$loc_id) {
            Response::error('User station not found. Please log in again.', 400);
            return;
        }

        $units = $this->transferModel->getStockUnits($loc_id, $type_id);

        if ($units !== false) {
            Response::success('Stock units retrieved successfully!', $units);
        } else {
            Response::error('Failed to retrieve stock units', 500);
        }
    }

    /**
     * Get available quantity for a specific assignment_id at user's station
     */
    public function getAvailableQuantity($assignment_id)
    {
        if (empty($assignment_id)) {
            Response::error('Assignment ID is required', 400);
            return;
        }

        $loc_id = $_SESSION['loc_id'] ?? null;
        if (!$loc_id) {
            Response::error('User station not found. Please log in again.', 400);
            return;
        }

        $quantity = $this->transferModel->getAvailableQuantity($loc_id, $assignment_id);

        Response::success('Available quantity retrieved successfully!', ['available_quantity' => $quantity]);
    }

    /**
     * Create transfer
     */
    public function createTransfer()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Check if it's a FormData request (file upload) or JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'multipart/form-data') !== false) {
            // Handle FormData (file upload)
            $input = $_POST;
            $input['items'] = json_decode($_POST['items'] ?? '[]', true);
        } else {
            // Handle JSON
            $input = json_decode(file_get_contents('php://input'), true);
        }

        if (!is_array($input)) {
            Response::error('Invalid payload', 400);
            return;
        }

        // station_id comes from session, not from input
        $required = ['warehouse_id', 'categories_id', 'items'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        // Validate items array
        if (!is_array($input['items']) || count($input['items']) === 0) {
            Response::error('At least one item is required', 400);
            return;
        }

        // Validate each item
        foreach ($input['items'] as $index => $item) {
            if (empty($item['assignment_id']) || empty($item['amount'])) {
                Response::error("Invalid item at position " . ($index + 1) . ": assignment_id and amount are required", 400);
                return;
            }
            if (!is_numeric($item['amount']) || $item['amount'] <= 0) {
                Response::error("Invalid amount at position " . ($index + 1) . ": Amount must be a positive number", 400);
                return;
            }
        }

        // Validate driver_id if provided
        $driver_id = isset($input['driver_id']) && !empty($input['driver_id']) ? trim($input['driver_id']) : null;

        // Handle file upload
        $supportingDocumentName = '';
        if (isset($_FILES['supporting_documents']) && $_FILES['supporting_documents']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['supporting_documents'];
            
            // Validate file type
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file['type'], $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
                Response::error('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG', 400);
                return;
            }
            
            // Max file size: 10MB
            if ($file['size'] > 10 * 1024 * 1024) {
                Response::error('File size exceeds 10MB limit', 400);
                return;
            }
            
            // Create upload directory if it doesn't exist (relative to project root)
            $projectRoot = dirname(dirname(__DIR__)); // Go up from controllers to _ikawa, then to project root
            $uploadDir = $projectRoot . '/Doc/product_transifer';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $supportingDocumentName = uniqid('transfer_') . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . '/' . $supportingDocumentName;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                Response::error('Failed to upload file', 500);
                return;
            }
        }

        // Get current user from session
        $created_by = $_SESSION['user_id'] ?? 'Unknown';
        
        // Get user's station from session
        $station_id = $_SESSION['loc_id'] ?? null;
        if (!$station_id) {
            Response::error('User station not found. Please log in again.', 400);
            return;
        }

        // Validate available quantities before creating transfer
        foreach ($input['items'] as $index => $item) {
            $availableQty = $this->transferModel->getAvailableQuantity(
                $station_id,
                trim($item['assignment_id'])
            );
            
            if ((float)$item['amount'] > $availableQty) {
                Response::error("Insufficient stock for item " . ($index + 1) . ". Available: {$availableQty}, Requested: {$item['amount']}", 400);
                return;
            }
        }

        $productData = [
            'categories_id' => trim($input['categories_id']),
            'supporting_documents' => $supportingDocumentName,
            'additional_info' => trim($input['additional_info'] ?? ''),
            'station_id' => $station_id,
            'warehouse_id' => trim($input['warehouse_id']),
            'driver_id' => $driver_id,
            'created_by' => $created_by
        ];

        $items = array_map(function($item) {
            return [
                'assignment_id' => trim($item['assignment_id']),
                'amount' => (float)$item['amount'],
                'unit_price' => (float)($item['unit_price'] ?? 0),
                'total_price' => (float)($item['total_price'] ?? 0)
            ];
        }, $input['items']);

        if ($this->transferModel->createTransfer($productData, $items)) {
            // Deduct stock from source station after successful transfer creation
            foreach ($items as $item) {
                $this->transferModel->deductStock(
                    $station_id,
                    $item['assignment_id'],
                    (float)$item['amount']
                );
            }
            Response::success('Transfer created successfully');
        } else {
            Response::error('Failed to create transfer', 500);
        }
    }

    /**
     * Get all transfers (filtered by user's station)
     */
    public function getAllTransfers()
    {
        // Get station_id from URL parameter first, then fall back to session
        $station_id = $_GET['station_id'] ?? $_SESSION['loc_id'] ?? null;
        
        $transfers = $this->transferModel->getAllTransfers($station_id);

        if ($transfers !== false) {
            Response::success('Transfers retrieved successfully!', $transfers);
        } else {
            Response::error('Failed to retrieve transfers', 500);
        }
    }

    /**
     * Get transfer items
     */
    public function getTransferItems($transfer_id)
    {
        if (empty($transfer_id)) {
            Response::error('Transfer ID is required', 400);
            return;
        }

        $items = $this->transferModel->getTransferItems($transfer_id);

        if ($items !== false) {
            Response::success('Transfer items retrieved successfully!', $items);
        } else {
            Response::error('Failed to retrieve transfer items', 500);
        }
    }

    /**
     * Receive transfer (supports partial receiving)
     */
    public function receiveTransfer()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Check if it's a FormData request (file upload) or JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        $items = [];
        if (strpos($contentType, 'multipart/form-data') !== false) {
            // Handle FormData (file upload)
            $transfer_id = $_POST['transfer_id'] ?? '';
            $receive_comment = $_POST['receive_comment'] ?? '';
            // Parse items from FormData
            $itemsJson = $_POST['items'] ?? '[]';
            $items = json_decode($itemsJson, true) ?? [];
        } else {
            // Handle JSON
            $input = json_decode(file_get_contents('php://input'), true);
            $transfer_id = $input['transfer_id'] ?? '';
            $receive_comment = $input['receive_comment'] ?? '';
            $items = $input['items'] ?? [];
        }

        if (empty($transfer_id)) {
            Response::error('Transfer ID is required', 400);
            return;
        }

        if (empty($items)) {
            Response::error('Please select at least one item to receive', 400);
            return;
        }

        // Validate items array
        foreach ($items as $item) {
            if (!isset($item['item_id']) || !isset($item['received_amount'])) {
                Response::error('Invalid items data', 400);
                return;
            }
            if ((float)$item['received_amount'] <= 0) {
                Response::error('Received amount must be greater than 0', 400);
                return;
            }
        }

        $transfer_id = trim($transfer_id);
        $receive_comment = trim($receive_comment);
        $received_by = $_SESSION['user_id'] ?? 'Unknown';

        // Handle file upload for receive_note
        $receiveNoteName = '';
        if (isset($_FILES['receive_note']) && $_FILES['receive_note']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['receive_note'];
            
            // Validate file type
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file['type'], $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
                Response::error('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG', 400);
                return;
            }
            
            // Max file size: 10MB
            if ($file['size'] > 10 * 1024 * 1024) {
                Response::error('File size exceeds 10MB limit', 400);
                return;
            }
            
            // Create upload directory if it doesn't exist
            $projectRoot = dirname(dirname(__DIR__));
            $uploadDir = $projectRoot . '/Doc/product_transifer/receive_notes';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $receiveNoteName = 'receive_' . $transfer_id . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . '/' . $receiveNoteName;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                Response::error('Failed to upload file', 500);
                return;
            }
        }

        if ($this->transferModel->receiveTransfer($transfer_id, $received_by, $items, $receiveNoteName, $receive_comment)) {
            Response::success('Items received successfully');
        } else {
            Response::error('Failed to receive transfer or transfer already processed', 500);
        }
    }

    /**
     * Reject transfer
     */
    public function rejectTransfer()
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

        if (empty($input['transfer_id'])) {
            Response::error('Transfer ID is required', 400);
            return;
        }

        if (empty($input['rejected_reason'])) {
            Response::error('Rejection reason is required', 400);
            return;
        }

        $transfer_id = trim($input['transfer_id']);
        $rejected_reason = trim($input['rejected_reason']);
        $rejected_by = $_SESSION['user_id'] ?? 'Unknown';

        if ($this->transferModel->rejectTransfer($transfer_id, $rejected_by, $rejected_reason)) {
            Response::success('Transfer rejected successfully');
        } else {
            Response::error('Failed to reject transfer or transfer already processed', 500);
        }
    }

    /**
     * Get transfer by ID with items
     */
    public function getTransferById($transfer_id)
    {
        if (empty($transfer_id)) {
            Response::error('Transfer ID is required', 400);
            return;
        }

        $transfer = $this->transferModel->getTransferById($transfer_id);
        $items = $this->transferModel->getTransferItems($transfer_id);

        if ($transfer !== false) {
            // Return structured data with transfer and items separate
            $responseData = [
                'transfer' => $transfer,
                'items' => $items ?: []
            ];
            Response::success('Transfer retrieved successfully!', $responseData);
        } else {
            Response::error('Failed to retrieve transfer', 500);
        }
    }
}
