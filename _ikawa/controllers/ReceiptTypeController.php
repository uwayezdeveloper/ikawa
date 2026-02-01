<?php
require_once __DIR__ . '/../models/ReceiptType.php';
require_once __DIR__ . '/../config/Response.php';

use Config\Response;

class ReceiptTypeController
{
    private $receiptTypeModel;

    public function __construct()
    {
        $this->receiptTypeModel = new ReceiptType();
    }

    /**
     * Get all receipt types
     */
    public function getAllReceiptTypes()
    {
        $receiptTypes = $this->receiptTypeModel->getAllReceiptTypes();
        Response::success('Receipt types retrieved successfully', $receiptTypes);
    }

    /**
     * Get receipt type by ID
     */
    public function getReceiptTypeById()
    {
        $rec_id = $_GET['rec_id'] ?? null;

        if (!$rec_id) {
            Response::error('Receipt type ID is required', 400);
            return;
        }

        $receiptType = $this->receiptTypeModel->getReceiptTypeById($rec_id);

        if ($receiptType) {
            Response::success('Receipt type retrieved successfully', $receiptType);
        } else {
            Response::error('Receipt type not found', 404);
        }
    }

    /**
     * Create new receipt type
     */
    public function createReceiptType()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['rec_name'])) {
            Response::error('Receipt type name is required', 400);
            return;
        }

        $data = [
            'rec_name' => trim($input['rec_name']),
            'rec_desc' => isset($input['rec_desc']) ? trim($input['rec_desc']) : '',
            'sts' => $input['sts'] ?? 1
        ];

        if ($this->receiptTypeModel->createReceiptType($data)) {
            Response::success('Receipt type created successfully', $data);
        } else {
            Response::error('Failed to create receipt type', 500);
        }
    }

    /**
     * Update receipt type
     */
    public function updateReceiptType()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['rec_id']) || !isset($input['rec_name'])) {
            Response::error('Receipt type ID and name are required', 400);
            return;
        }

        $data = [
            'rec_id' => (int)$input['rec_id'],
            'rec_name' => trim($input['rec_name']),
            'rec_desc' => isset($input['rec_desc']) ? trim($input['rec_desc']) : '',
            'sts' => $input['sts'] ?? 1
        ];

        if ($this->receiptTypeModel->updateReceiptType($data)) {
            Response::success('Receipt type updated successfully', $data);
        } else {
            Response::error('Failed to update receipt type', 500);
        }
    }

    /**
     * Delete receipt type
     */
    public function deleteReceiptType()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['rec_id'])) {
            Response::error('Receipt type ID is required', 400);
            return;
        }

        $rec_id = (int)$input['rec_id'];

        if ($this->receiptTypeModel->deleteReceiptType($rec_id)) {
            Response::success('Receipt type deleted successfully');
        } else {
            Response::error('Failed to delete receipt type', 500);
        }
    }
}
