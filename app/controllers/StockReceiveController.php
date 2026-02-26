<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\StockReceive;
use App\Models\StockSummary;

class StockReceiveController extends Controller
{
    protected StockReceive $stockReceiveModel;
    protected StockSummary $stockSummaryModel;

    public function __construct()
    {
        parent::__construct();
        $this->stockReceiveModel = new StockReceive();
        $this->stockSummaryModel = new StockSummary();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Display stock receives list (Receive Stock page)
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-stock-receives')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $receives = $this->stockReceiveModel->getAll();
        $locationTypes = Database::fetchAll("SELECT id, name FROM location_types WHERE status = 'active' ORDER BY name");
        $suppliers = Database::fetchAll("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name");

        return View::render('stock/receive-stock', [
            'title' => 'Receive Stock',
            'user' => $user,
            'receives' => $receives,
            'locationTypes' => $locationTypes,
            'suppliers' => $suppliers,
            'scripts' => ['js/pages/stock-receive.js']
        ], 'main');
    }

    /**
     * Display stock summary page
     */
    public function summary($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        if (!$this->hasPermission('view-stock-receives')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $locationTypes = Database::fetchAll("SELECT id, name FROM location_types WHERE status = 'active' ORDER BY name");
        $summaries = $this->stockSummaryModel->getAll();
        $valueByLocation = $this->stockSummaryModel->getValueByLocation();

        return View::render('stock/stock-summary', [
            'title' => 'Stock Summary',
            'user' => $user,
            'locationTypes' => $locationTypes,
            'summaries' => $summaries,
            'valueByLocation' => $valueByLocation,
            'scripts' => ['js/pages/stock-summary.js']
        ], 'main');
    }

    /**
     * Handle stock receive actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';
        
        // Log all action requests
        error_log("StockReceiveController::handleAction - Action: '$action', IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ", User: " . ($_SESSION['user']['username'] ?? 'not logged in'));

        switch ($action) {
            case 'create':
                if (!$this->hasPermission('create-stock-receives')) {
                    error_log("Permission denied: create-stock-receives");
                    $_SESSION['flash_error'] = 'You do not have permission to create stock receives';
                    return $response->redirect(APP_URL . '/stock/receives');
                }
                return $this->createReceive($request, $response);

            case 'approve':
                if (!$this->hasPermission('approve-stock-receives')) {
                    error_log("Permission denied: approve-stock-receives for user " . ($_SESSION['user']['username'] ?? 'unknown'));
                    $_SESSION['flash_error'] = 'You do not have permission to approve stock receives';
                    return $response->redirect(APP_URL . '/stock/receives');
                }
                return $this->approveReceive($request, $response);

            case 'cancel':
                if (!$this->hasPermission('approve-stock-receives')) {
                    $_SESSION['flash_error'] = 'You do not have permission to cancel stock receives';
                    return $response->redirect(APP_URL . '/stock/receives');
                }
                return $this->cancelReceive($request, $response);

            case 'delete':
                if (!$this->hasPermission('delete-stock-receives')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete stock receives';
                    return $response->redirect(APP_URL . '/stock/receives');
                }
                return $this->deleteReceive($request, $response);

            // AJAX Cascade Actions
            case 'get_locations':
                return $this->getLocations($request, $response);
            
            case 'get_categories':
                return $this->getCategories($request, $response);
            
            case 'get_category_types':
                return $this->getCategoryTypes($request, $response);
            
            case 'get_type_units':
                return $this->getTypeUnits($request, $response);

            case 'get_accounts':
                return $this->getAccounts($request, $response);

            case 'check_supplier_advance':
                return $this->checkSupplierAdvance($request, $response);

            // AJAX for Summary
            case 'get_summary_by_location':
                return $this->getSummaryByLocation($request, $response);

            case 'get_summary_by_location_type':
                return $this->getSummaryByLocationType($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/stock/receives');
        }
    }

    /**
     * Create new stock receive
     */
    private function createReceive($request, $response)
    {
        $locationTypeId = $_POST['location_type_id'] ?? null;
        $locationId = $_POST['location_id'] ?? null;
        $supplierId = $_POST['supplier_id'] ?? null;
        $categoryId = $_POST['product_category_id'] ?? null;
        $categoryTypeUnitId = $_POST['category_type_unit_id'] ?? null;
        $accountId = $_POST['account_id'] ?? null;
        $quantity = floatval($_POST['quantity'] ?? 0);
        $unitPrice = floatval($_POST['unit_price'] ?? 0);
        $receiveDate = $_POST['receive_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');

        // Validation
        if (empty($locationTypeId) || empty($locationId) || empty($supplierId) || 
            empty($categoryId) || empty($categoryTypeUnitId)) {
            $_SESSION['flash_error'] = 'All dropdown fields are required';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        if ($quantity <= 0) {
            $_SESSION['flash_error'] = 'Quantity must be greater than zero';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        if ($unitPrice <= 0) {
            $_SESSION['flash_error'] = 'Unit price must be greater than zero';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        $user = $_SESSION['user'] ?? null;
        $result = $this->stockReceiveModel->createReceive([
            'location_type_id' => $locationTypeId,
            'location_id' => $locationId,
            'supplier_id' => $supplierId,
            'product_category_id' => $categoryId,
            'category_type_unit_id' => $categoryTypeUnitId,
            'account_id' => $accountId ?: null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'receive_date' => $receiveDate,
            'notes' => $notes,
            'status' => 'pending',
            'created_by' => $user['id'] ?? null
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Stock receive created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create stock receive';
        }

        return $response->redirect(APP_URL . '/stock/receives');
    }

    /**
     * Approve stock receive
     */
    private function approveReceive($request, $response)
    {
        $id = $_POST['id'] ?? null;
        
        // Log the approval attempt
        error_log("Approve stock receive attempt - ID: " . ($id ?? 'null') . ", User: " . ($_SESSION['user']['username'] ?? 'unknown'));
        
        if (!$id) {
            error_log("Approve failed: Invalid receive ID");
            $_SESSION['flash_error'] = 'Invalid receive ID';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        $user = $_SESSION['user'] ?? null;
        
        try {
            $result = $this->stockReceiveModel->approveReceive($id, $user['id'] ?? 0);

            if ($result) {
                error_log("Stock receive #$id approved successfully");
                $_SESSION['flash_success'] = 'Stock receive approved and summary updated';
            } else {
                error_log("Stock receive #$id approval failed - approveReceive returned false");
                $_SESSION['flash_error'] = 'Failed to approve stock receive';
            }
        } catch (\Exception $e) {
            error_log("Stock receive #$id approval exception: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error approving stock receive: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/stock/receives');
    }

    /**
     * Cancel stock receive
     */
    private function cancelReceive($request, $response)
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid receive ID';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        $result = $this->stockReceiveModel->cancelReceive($id);

        if ($result) {
            $_SESSION['flash_success'] = 'Stock receive cancelled';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel stock receive';
        }

        return $response->redirect(APP_URL . '/stock/receives');
    }

    /**
     * Delete stock receive (only pending)
     */
    private function deleteReceive($request, $response)
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid receive ID';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        $receive = $this->stockReceiveModel->find($id);
        if (!$receive || $receive['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Only pending receives can be deleted';
            return $response->redirect(APP_URL . '/stock/receives');
        }

        $result = $this->stockReceiveModel->delete($id);

        if ($result) {
            $_SESSION['flash_success'] = 'Stock receive deleted';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete stock receive';
        }

        return $response->redirect(APP_URL . '/stock/receives');
    }

    // ==================== AJAX Cascade Handlers ====================

    /**
     * Get locations by location type (AJAX)
     */
    private function getLocations($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationTypeId = $_POST['location_type_id'] ?? null;
        if (!$locationTypeId) {
            echo json_encode(['success' => false, 'message' => 'Location type ID required']);
            exit;
        }

        $locations = $this->stockReceiveModel->getLocationsByType($locationTypeId);
        echo json_encode(['success' => true, 'data' => $locations]);
        exit;
    }

    /**
     * Get product categories by location type (AJAX)
     */
    private function getCategories($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationTypeId = $_POST['location_type_id'] ?? null;
        if (!$locationTypeId) {
            echo json_encode(['success' => false, 'message' => 'Location type ID required']);
            exit;
        }

        $categories = $this->stockReceiveModel->getCategoriesByLocationType($locationTypeId);
        echo json_encode(['success' => true, 'data' => $categories]);
        exit;
    }

    /**
     * Get category types by category (AJAX)
     */
    private function getCategoryTypes($request, $response)
    {
        header('Content-Type: application/json');
        
        $categoryId = $_POST['category_id'] ?? null;
        if (!$categoryId) {
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit;
        }

        $types = $this->stockReceiveModel->getCategoryTypes($categoryId);
        echo json_encode(['success' => true, 'data' => $types]);
        exit;
    }

    /**
     * Get type-unit assignments by category type (AJAX)
     */
    private function getTypeUnits($request, $response)
    {
        header('Content-Type: application/json');
        
        $categoryTypeId = $_POST['category_type_id'] ?? null;
        if (!$categoryTypeId) {
            echo json_encode(['success' => false, 'message' => 'Category type ID required']);
            exit;
        }

        $typeUnits = $this->stockReceiveModel->getTypeUnitsByCategoryType($categoryTypeId);
        echo json_encode(['success' => true, 'data' => $typeUnits]);
        exit;
    }

    /**
     * Get stock summary by location (AJAX)
     */
    private function getSummaryByLocation($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationId = $_POST['location_id'] ?? null;
        if (!$locationId) {
            echo json_encode(['success' => false, 'message' => 'Location ID required']);
            exit;
        }

        $summary = $this->stockSummaryModel->getByLocation($locationId);
        echo json_encode(['success' => true, 'data' => $summary]);
        exit;
    }

    /**
     * Get stock summary by location type (AJAX)
     */
    private function getSummaryByLocationType($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationTypeId = $_POST['location_type_id'] ?? null;
        if (!$locationTypeId) {
            echo json_encode(['success' => false, 'message' => 'Location type ID required']);
            exit;
        }

        $summary = $this->stockSummaryModel->getByLocationType($locationTypeId);
        echo json_encode(['success' => true, 'data' => $summary]);
        exit;
    }

    /**
     * Get accounts by location (AJAX)
     */
    private function getAccounts($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationId = $_POST['location_id'] ?? null;
        if (!$locationId) {
            echo json_encode(['success' => false, 'message' => 'Location ID required']);
            exit;
        }

        $accounts = $this->stockReceiveModel->getAccountsByLocation($locationId);
        echo json_encode(['success' => true, 'data' => $accounts]);
        exit;
    }

    /**
     * Check if supplier has available advance (AJAX)
     */
    private function checkSupplierAdvance($request, $response)
    {
        header('Content-Type: application/json');
        
        $supplierId = $_POST['supplier_id'] ?? null;
        $amount = floatval($_POST['amount'] ?? 0);
        
        if (!$supplierId) {
            echo json_encode(['success' => false, 'message' => 'Supplier ID required']);
            exit;
        }

        // Get total available advance
        $totalAdvance = $this->stockReceiveModel->getTotalAvailableAdvance($supplierId);
        $advances = $this->stockReceiveModel->getApprovedAdvances($supplierId);
        $hasAdvance = $totalAdvance > 0;
        
        // Calculate how much will need to be paid from account
        $remainingAfterAdvance = max(0, $amount - $totalAdvance);
        
        echo json_encode([
            'success' => true, 
            'data' => [
                'has_advance' => $hasAdvance,
                'total_advance' => $totalAdvance,
                'advances' => $advances,
                'amount_requested' => $amount,
                'remaining_after_advance' => $remainingAfterAdvance,
                'fully_covered' => $totalAdvance >= $amount
            ]
        ]);
        exit;
    }
}