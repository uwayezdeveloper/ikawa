<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Models\StockReceive;
use App\Models\StockSummary;
use App\Models\ProcessingStep;
use App\Models\AccountTransaction;

class WarehouseReceiveController extends Controller
{
    private $stockReceiveModel;
    private $processingStepModel;

    public function __construct()
    {
        parent::__construct();
        $this->stockReceiveModel = new StockReceive();
        $this->processingStepModel = new ProcessingStep();
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
     * Display warehouse receive page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$this->hasPermission('create-stock-transfers')) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        // Get warehouse location type ID
        $warehouseType = Database::fetch(
            "SELECT id FROM location_types WHERE name = 'Warehouse' AND status = 'active' LIMIT 1"
        );
        $warehouseTypeId = $warehouseType['id'] ?? 0;

        // Get warehouses
        $warehouses = Database::fetchAll(
            "SELECT l.id, l.name 
             FROM locations l 
             WHERE l.location_type_id = :type_id AND l.status = 'active' 
             ORDER BY l.name",
            ['type_id' => $warehouseTypeId]
        );

        // Get categories
        $categories = Database::fetchAll(
            "SELECT id, name FROM product_categories WHERE status = 'active' ORDER BY name"
        );

        // Get suppliers
        $suppliers = Database::fetchAll(
            "SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name"
        );

        // Get processing steps
        $processingSteps = $this->processingStepModel->getActive();

        // Get warehouse receives (from stock_receives where location is warehouse)
        $receives = Database::fetchAll(
            "SELECT sr.*, 
                    lt.name as location_type_name,
                    l.name as location_name,
                    s.name as supplier_name,
                    pc.name as category_name,
                    ct.name as type_name,
                    mu.name as unit_name,
                    mu.symbol as unit_symbol,
                    ps.name as step_name,
                    CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                    a.account_name
             FROM stock_receives sr
             JOIN location_types lt ON sr.location_type_id = lt.id
             JOIN locations l ON sr.location_id = l.id
             LEFT JOIN suppliers s ON sr.supplier_id = s.id
             LEFT JOIN product_categories pc ON sr.product_category_id = pc.id
             LEFT JOIN category_type_units ctu ON sr.category_type_unit_id = ctu.id
             LEFT JOIN category_types ct ON ctu.category_type_id = ct.id
             LEFT JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             LEFT JOIN processing_steps ps ON sr.processing_step_id = ps.id
             LEFT JOIN users u ON sr.created_by = u.id
             LEFT JOIN accounts a ON sr.account_id = a.id
             WHERE lt.name = 'Warehouse'
             ORDER BY sr.created_at DESC
             LIMIT 100"
        );

        return View::render('stock/warehouse-receive', [
            'title' => 'Warehouse Receive',
            'user' => $user,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'processingSteps' => $processingSteps,
            'receives' => $receives,
            'warehouseTypeId' => $warehouseTypeId
        ], 'main');
    }

    /**
     * Handle actions
     */
    public function handleAction($request, $response)
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'receive_stock':
                if (!$this->hasPermission('create-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to receive stock';
                    return $response->redirect(APP_URL . '/warehouse/receive');
                }
                return $this->receiveStock($request, $response);

            case 'approve':
                if (!$this->hasPermission('approve-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to approve receives';
                    return $response->redirect(APP_URL . '/warehouse/receive');
                }
                return $this->approveReceive($request, $response);

            case 'cancel':
                if (!$this->hasPermission('approve-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to cancel receives';
                    return $response->redirect(APP_URL . '/warehouse/receive');
                }
                return $this->cancelReceive($request, $response);

            case 'delete':
                if (!$this->hasPermission('delete-stock-transfers')) {
                    $_SESSION['flash_error'] = 'You do not have permission to delete receives';
                    return $response->redirect(APP_URL . '/warehouse/receive');
                }
                return $this->deleteReceive($request, $response);

            case 'get_category_types':
                return $this->getCategoryTypes($request, $response);

            case 'get_type_units':
                return $this->getTypeUnits($request, $response);

            case 'get_accounts':
                return $this->getAccounts($request, $response);

            case 'check_supplier_advance':
                return $this->checkSupplierAdvance($request, $response);

            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/warehouse/receive');
        }
    }

    /**
     * Create pending stock receive at warehouse
     */
    private function receiveStock($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        
        $locationId = intval($_POST['location_id'] ?? 0);
        $categoryId = intval($_POST['product_category_id'] ?? 0);
        $categoryTypeUnitId = intval($_POST['category_type_unit_id'] ?? 0);
        $supplierId = intval($_POST['supplier_id'] ?? 0);
        $quantity = floatval($_POST['quantity'] ?? 0);
        $unitPrice = floatval($_POST['unit_price'] ?? 0);
        $processingStepId = intval($_POST['processing_step_id'] ?? 0) ?: null;
        $accountId = intval($_POST['account_id'] ?? 0) ?: null;
        $receiveDate = $_POST['receive_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');

        // Validation
        if (!$locationId || !$categoryId || !$categoryTypeUnitId || !$supplierId || $quantity <= 0) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        if ($unitPrice <= 0) {
            $_SESSION['flash_error'] = 'Price per kg must be greater than zero';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        // Get location type
        $location = Database::fetch(
            "SELECT location_type_id FROM locations WHERE id = :id",
            ['id' => $locationId]
        );
        
        if (!$location) {
            $_SESSION['flash_error'] = 'Invalid warehouse location';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        // Create receive using the model
        $result = $this->stockReceiveModel->createReceive([
            'location_type_id' => $location['location_type_id'],
            'location_id' => $locationId,
            'supplier_id' => $supplierId,
            'product_category_id' => $categoryId,
            'category_type_unit_id' => $categoryTypeUnitId,
            'processing_step_id' => $processingStepId,
            'account_id' => $accountId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'receive_date' => $receiveDate,
            'notes' => $notes,
            'status' => 'pending',
            'created_by' => $user['id'] ?? null
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Stock receive created successfully. Pending approval.';
        } else {
            $_SESSION['flash_error'] = 'Failed to create stock receive';
        }

        return $response->redirect(APP_URL . '/warehouse/receive');
    }

    /**
     * Approve stock receive - handles advance and account payments
     */
    private function approveReceive($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid receive ID';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        $user = $_SESSION['user'] ?? null;
        
        // Get the receive record
        $receive = $this->stockReceiveModel->find($id);
        if (!$receive || $receive['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Invalid or already processed receive';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        try {
            Database::query("START TRANSACTION");
            
            $totalPrice = floatval($receive['total_price']);
            $remainingAmount = $totalPrice;
            $advanceAmount = 0;
            $accountAmount = 0;
            $payableAmount = 0;
            $paymentMethod = null;
            $accountId = $receive['account_id'] ?? null;
            $advanceId = null;
            
            // Step 1: Check for approved advances and use them
            $advances = $this->getApprovedAdvances($receive['supplier_id']);
            
            foreach ($advances as $advance) {
                if ($remainingAmount <= 0) break;
                
                $advanceAmt = floatval($advance['amount']);
                $useAmount = min($advanceAmt, $remainingAmount);
                
                $advanceAmount += $useAmount;
                $remainingAmount -= $useAmount;
                $advanceId = $advance['id'];
                
                // Mark advance as settled
                Database::query("UPDATE supplier_advances SET status = 'settled' WHERE id = :id", ['id' => $advance['id']]);
            }
            
            // Step 2: Use account if there's remaining amount and account is selected
            if ($remainingAmount > 0 && $accountId) {
                $account = Database::fetch("SELECT balance FROM accounts WHERE id = :id", ['id' => $accountId]);
                $accountBalance = floatval($account['balance'] ?? 0);
                
                $accountAmount = min($accountBalance, $remainingAmount);
                $remainingAmount -= $accountAmount;
                
                if ($accountAmount > 0) {
                    $transactionModel = new AccountTransaction();
                    $description = "Warehouse Stock Payment: " . $receive['quantity'] . " units to supplier";
                    $transactionModel->createDebit(
                        $accountId,
                        $accountAmount,
                        'stock_receive',
                        $id,
                        $description,
                        $user['id'] ?? 0
                    );
                }
            }
            
            // Step 3: Record remaining as payable
            if ($remainingAmount > 0) {
                $payableAmount = $remainingAmount;
                
                $payableNumber = 'PAY-' . date('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $sql = "INSERT INTO supplier_payables 
                        (payable_number, stock_receive_id, supplier_id, location_id, amount, created_by)
                        VALUES (:payable_number, :stock_receive_id, :supplier_id, :location_id, :amount, :created_by)";
                Database::query($sql, [
                    'payable_number' => $payableNumber,
                    'stock_receive_id' => $id,
                    'supplier_id' => $receive['supplier_id'],
                    'location_id' => $receive['location_id'],
                    'amount' => $payableAmount,
                    'created_by' => $user['id'] ?? 0
                ]);
            }
            
            // Determine payment method
            if ($advanceAmount > 0 && $accountAmount > 0) {
                $paymentMethod = 'advance';
            } elseif ($advanceAmount > 0) {
                $paymentMethod = 'advance';
            } elseif ($accountAmount > 0) {
                $paymentMethod = 'account';
            }
            
            // Update receive status
            $this->stockReceiveModel->update($id, [
                'status' => 'approved',
                'payment_method' => $paymentMethod,
                'advance_id' => $advanceId,
                'advance_amount' => $advanceAmount,
                'account_amount' => $accountAmount,
                'payable_amount' => $payableAmount,
                'approved_by' => $user['id'] ?? 0,
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update stock_summary using smart unit conversion
            $this->stockReceiveModel->updateWarehouseStock(
                $receive['location_id'],
                $receive['supplier_id'],
                $receive['product_category_id'],
                $receive['category_type_unit_id'],
                $receive['processing_step_id'],
                floatval($receive['quantity']),
                floatval($receive['total_price']),
                $receive['receive_date']
            );
            
            Database::query("COMMIT");
            $_SESSION['flash_success'] = 'Stock receive approved and added to warehouse stock';
            
        } catch (\Exception $e) {
            Database::query("ROLLBACK");
            $_SESSION['flash_error'] = 'Failed to approve: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/warehouse/receive');
    }

    /**
     * Cancel stock receive
     */
    private function cancelReceive($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid receive ID';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        $result = $this->stockReceiveModel->cancelReceive($id);

        if ($result) {
            $_SESSION['flash_success'] = 'Stock receive cancelled';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel stock receive';
        }

        return $response->redirect(APP_URL . '/warehouse/receive');
    }

    /**
     * Delete stock receive (only pending)
     */
    private function deleteReceive($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid receive ID';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        $receive = $this->stockReceiveModel->find($id);
        if (!$receive || $receive['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Only pending receives can be deleted';
            return $response->redirect(APP_URL . '/warehouse/receive');
        }

        $result = $this->stockReceiveModel->delete($id);

        if ($result) {
            $_SESSION['flash_success'] = 'Stock receive deleted';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete stock receive';
        }

        return $response->redirect(APP_URL . '/warehouse/receive');
    }

    /**
     * Get category types for a category (AJAX)
     */
    private function getCategoryTypes($request, $response)
    {
        $categoryId = intval($_POST['category_id'] ?? 0);

        if (!$categoryId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit;
        }

        $types = Database::fetchAll(
            "SELECT id, name FROM category_types WHERE category_id = :cat AND status = 'active' ORDER BY name",
            ['cat' => $categoryId]
        );

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $types]);
        exit;
    }

    /**
     * Get units for a category type (AJAX)
     */
    private function getTypeUnits($request, $response)
    {
        $categoryTypeId = intval($_POST['category_type_id'] ?? 0);

        if (!$categoryTypeId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category Type ID required']);
            exit;
        }

        $units = Database::fetchAll(
            "SELECT ctu.id, mu.name, mu.symbol
             FROM category_type_units ctu
             JOIN measurement_units mu ON ctu.measurement_unit_id = mu.id
             WHERE ctu.category_type_id = :type
             ORDER BY mu.conversion_factor ASC",
            ['type' => $categoryTypeId]
        );

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $units]);
        exit;
    }

    /**
     * Get accounts by warehouse location (AJAX)
     */
    private function getAccounts($request, $response)
    {
        header('Content-Type: application/json');
        
        $locationId = intval($_POST['location_id'] ?? 0);
        if (!$locationId) {
            echo json_encode(['success' => false, 'message' => 'Location ID required']);
            exit;
        }

        $accounts = Database::fetchAll(
            "SELECT id, account_name, balance 
             FROM accounts 
             WHERE location_id = :loc AND status = 'active' 
             ORDER BY account_name",
            ['loc' => $locationId]
        );

        echo json_encode(['success' => true, 'data' => $accounts]);
        exit;
    }

    /**
     * Check supplier advance balance (AJAX)
     */
    private function checkSupplierAdvance($request, $response)
    {
        header('Content-Type: application/json');
        
        $supplierId = intval($_POST['supplier_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        
        if (!$supplierId) {
            echo json_encode(['success' => false, 'message' => 'Supplier ID required']);
            exit;
        }

        $totalAdvance = $this->getTotalAvailableAdvance($supplierId);
        $advances = $this->getApprovedAdvances($supplierId);
        $hasAdvance = $totalAdvance > 0;
        
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

    /**
     * Get total available advance for a supplier
     */
    private function getTotalAvailableAdvance(int $supplierId): float
    {
        $result = Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM supplier_advances 
             WHERE supplier_id = :supplier_id AND status = 'approved'",
            ['supplier_id' => $supplierId]
        );
        return floatval($result['total'] ?? 0);
    }

    /**
     * Get approved advances for a supplier
     */
    private function getApprovedAdvances(int $supplierId): array
    {
        return Database::fetchAll(
            "SELECT id, advance_number, amount, created_at 
             FROM supplier_advances 
             WHERE supplier_id = :supplier_id AND status = 'approved'
             ORDER BY created_at ASC",
            ['supplier_id' => $supplierId]
        );
    }
}
