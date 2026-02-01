<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Selling
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function generateInvoiceNo(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $date . $random;
    }

    // ==================== CLIENT METHODS ====================

    public function getClients()
    {
        try {
            $sql = "SELECT client_id, full_name as client_name, phone, email, address FROM tbl_clients WHERE status = 'active' ORDER BY full_name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching clients: " . $e->getMessage());
            return false;
        }
    }

    public function createClient(array $data)
    {
        try {
            $sql = "
                INSERT INTO tbl_clients (full_name, phone, email, address, status, client_type)
                VALUES (:full_name, :phone, :email, :address, 'active', 'Retailer')
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':full_name' => $data['client_name'],
                ':phone' => $data['phone'] ?? null,
                ':email' => $data['email'] ?? null,
                ':address' => $data['address'] ?? null
            ]);
            return $this->conn->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating client: " . $e->getMessage());
            return false;
        }
    }

    public function clientExists(string $name, ?string $phone = null)
    {
        try {
            if ($phone) {
                $sql = "SELECT client_id FROM tbl_clients WHERE (full_name = :name OR phone = :phone) AND status = 'active'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':name' => $name, ':phone' => $phone]);
            } else {
                $sql = "SELECT client_id FROM tbl_clients WHERE full_name = :name AND status = 'active'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':name' => $name]);
            }
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    // ==================== STOCK METHODS ====================

    public function getAvailableStockForSelling(int $loc_id)
    {
        try {
            error_log("Selling::getAvailableStockForSelling - Starting with loc_id: " . $loc_id);
            
            if (!$this->conn) {
                error_log("Selling::getAvailableStockForSelling - Database connection is null!");
                return false;
            }
            
            $sql = "
                SELECT 
                    ss.stock_summary_id,
                    ss.assignment_id,
                    ss.total_quantity,
                    ctu.type_id,
                    ctu.unit_id,
                    ct.type_name,
                    u.unit_name
                FROM tbl_stock_summary ss
                INNER JOIN tbl_category_type_units ctu ON ss.assignment_id = ctu.assignment_id
                INNER JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                INNER JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE ss.loc_id = :loc_id AND ss.total_quantity > 0
                ORDER BY ct.type_name, u.unit_name
            ";
            
            error_log("Selling::getAvailableStockForSelling - Executing query");
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("Selling::getAvailableStockForSelling - Query completed, rows: " . count($result));
            return $result;
        } catch (\PDOException $e) {
            error_log("Selling::getAvailableStockForSelling - PDO Error: " . $e->getMessage());
            error_log("Selling::getAvailableStockForSelling - Error Code: " . $e->getCode());
            return false;
        } catch (Exception $e) {
            error_log("Selling::getAvailableStockForSelling - General Error: " . $e->getMessage());
            return false;
        }
    }

    public function createSale(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $invoice_no = $this->generateInvoiceNo();
            $loc_id = $data['loc_id'];
            $user_id = $data['user_id'];
            $items = $data['items'];
            $has_price = $data['has_price'] ?? true;
            $client_id = $data['client_id'] ?? null;
            $notes = $data['notes'] ?? null;
            
            // Calculate total only if has_price
            $total_amount = 0;
            if ($has_price) {
                foreach ($items as $item) {
                    $total_amount += floatval($item['total'] ?? 0);
                }
            }

            // Insert sale record - use both client_id and customer_name/phone fields
            $saleSql = "
                INSERT INTO tbl_sales 
                (invoice_no, loc_id, user_id, client_id, total_items, total_amount, customer_name, customer_phone, notes, status, price_status, created_at)
                VALUES 
                (:invoice_no, :loc_id, :user_id, :client_id, :total_items, :total_amount, :customer_name, :customer_phone, :notes, :status, :price_status, CURRENT_TIMESTAMP)
            ";
            $saleStmt = $this->conn->prepare($saleSql);
            $saleStmt->execute([
                ':invoice_no' => $invoice_no,
                ':loc_id' => $loc_id,
                ':user_id' => $user_id,
                ':client_id' => $client_id,
                ':total_items' => count($items),
                ':total_amount' => $total_amount,
                ':customer_name' => $data['customer_name'] ?? null,
                ':customer_phone' => $data['customer_phone'] ?? null,
                ':notes' => $notes,
                ':status' => $has_price ? 'completed' : 'pending',
                ':price_status' => $has_price ? 'completed' : 'pending'
            ]);

            $sale_id = $this->conn->lastInsertId();

            // Insert sale items and deduct from stock
            foreach ($items as $item) {
                $assignment_id = $item['assignment_id'];
                $quantity = floatval($item['quantity']);
                $unit_price = $has_price ? floatval($item['unit_price'] ?? 0) : 0;
                $total = $has_price ? floatval($item['total'] ?? 0) : 0;

                // Check available stock
                $checkSql = "
                    SELECT total_quantity FROM tbl_stock_summary 
                    WHERE loc_id = :loc_id AND assignment_id = :assignment_id
                ";
                $checkStmt = $this->conn->prepare($checkSql);
                $checkStmt->execute([
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $assignment_id
                ]);
                $stock = $checkStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$stock || $stock['total_quantity'] < $quantity) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Insufficient stock for one or more items'];
                }

                // Insert sale item
                $itemSql = "
                    INSERT INTO tbl_sale_items 
                    (sale_id, assignment_id, quantity, unit_price, total_price, created_at)
                    VALUES 
                    (:sale_id, :assignment_id, :quantity, :unit_price, :total_price, CURRENT_TIMESTAMP)
                ";
                $itemStmt = $this->conn->prepare($itemSql);
                $itemStmt->execute([
                    ':sale_id' => $sale_id,
                    ':assignment_id' => $assignment_id,
                    ':quantity' => $quantity,
                    ':unit_price' => $unit_price,
                    ':total_price' => $total
                ]);

                // Deduct from stock summary
                $deductSql = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity - :quantity, updated_at = CURRENT_TIMESTAMP
                    WHERE loc_id = :loc_id AND assignment_id = :assignment_id
                ";
                $deductStmt = $this->conn->prepare($deductSql);
                $deductStmt->execute([
                    ':quantity' => $quantity,
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $assignment_id
                ]);
            }

            // Insert payment record only if has_price
            if ($has_price && isset($data['payment'])) {
                $payment = $data['payment'];
                $paymentSql = "
                    INSERT INTO tbl_sale_payments 
                    (sale_id, mode_id, acc_id, amount, document_ref, created_at)
                    VALUES 
                    (:sale_id, :mode_id, :acc_id, :amount, :document_ref, CURRENT_TIMESTAMP)
                ";
                $paymentStmt = $this->conn->prepare($paymentSql);
                $paymentStmt->execute([
                    ':sale_id' => $sale_id,
                    ':mode_id' => $payment['mode_id'],
                    ':acc_id' => $payment['acc_id'],
                    ':amount' => $payment['amount'],
                    ':document_ref' => $payment['document_ref'] ?? null
                ]);

                // Update account balance
                $updateAccSql = "
                    UPDATE tbl_accounts 
                    SET balance = balance + :amount 
                    WHERE acc_id = :acc_id
                ";
                $updateAccStmt = $this->conn->prepare($updateAccSql);
                $updateAccStmt->execute([
                    ':amount' => $payment['amount'],
                    ':acc_id' => $payment['acc_id']
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'invoice_no' => $invoice_no, 'sale_id' => $sale_id];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating sale: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getSalesHistory(int $loc_id, int $limit = 50)
    {
        try {
            $sql = "
                SELECT 
                    s.sale_id,
                    s.invoice_no,
                    s.total_items,
                    s.total_amount,
                    s.status,
                    s.price_status,
                    s.created_at,
                    s.customer_name,
                    s.customer_phone,
                    COALESCE(c.full_name, s.customer_name, 'Walk-in') as client_name,
                    COALESCE(c.phone, s.customer_phone) as client_phone,
                    pm.Mode_names as mode_name,
                    u.username as created_by
                FROM tbl_sales s
                LEFT JOIN tbl_clients c ON s.client_id = c.client_id
                LEFT JOIN tbl_sale_payments sp ON s.sale_id = sp.sale_id
                LEFT JOIN tbl_paymentmodes pm ON sp.mode_id = pm.Mode_id
                LEFT JOIN tbl_users u ON s.user_id = u.user_id
                WHERE s.loc_id = :loc_id
                ORDER BY s.created_at DESC
                LIMIT :limit
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':loc_id', $loc_id, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching sales history: " . $e->getMessage());
            return false;
        }
    }

    public function getSaleDetails(int $sale_id)
    {
        try {
            // First get the sale info
            $saleSql = "
                SELECT 
                    s.sale_id,
                    s.invoice_no,
                    s.client_id,
                    s.total_amount,
                    s.price_status,
                    s.created_at,
                    s.customer_name,
                    s.customer_phone,
                    COALESCE(c.full_name, s.customer_name, 'Walk-in') as client_name
                FROM tbl_sales s
                LEFT JOIN tbl_clients c ON s.client_id = c.client_id
                WHERE s.sale_id = :sale_id
            ";
            $stmt = $this->conn->prepare($saleSql);
            $stmt->execute([':sale_id' => $sale_id]);
            $sale = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$sale) {
                return false;
            }
            
            // Get sale items with stock names
            $sql = "
                SELECT 
                    si.sale_item_id as item_id,
                    si.quantity,
                    si.unit_price,
                    si.total_price,
                    ct.type_name as stock_name,
                    cat.category_name,
                    u.unit_name as unity_name
                FROM tbl_sale_items si
                LEFT JOIN tbl_category_type_units ctu ON si.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_categories cat ON ct.category_id = cat.category_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE si.sale_id = :sale_id
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':sale_id' => $sale_id]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $sale['items'] = $items;
            return $sale;
        } catch (\PDOException $e) {
            error_log("Error fetching sale details: " . $e->getMessage());
            return false;
        }
    }

    // Get pending sales (sales without prices)
    public function getPendingSales(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    s.sale_id,
                    s.invoice_no,
                    s.client_id,
                    s.total_amount,
                    s.price_status,
                    s.created_at,
                    s.customer_name,
                    COALESCE(c.full_name, s.customer_name, 'Walk-in') as client_name,
                    u.username as created_by_name,
                    (SELECT COUNT(*) FROM tbl_sale_items WHERE sale_id = s.sale_id) as item_count
                FROM tbl_sales s
                LEFT JOIN tbl_clients c ON s.client_id = c.client_id
                LEFT JOIN tbl_users u ON s.user_id = u.user_id
                WHERE s.loc_id = :loc_id AND s.price_status = 'pending'
                ORDER BY s.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("getPendingSales found " . count($result) . " pending sales for loc_id: " . $loc_id);
            return $result;
        } catch (\PDOException $e) {
            error_log("Error fetching pending sales: " . $e->getMessage());
            return [];
        }
    }

    // Update sale prices and complete the sale
    public function updateSalePrices(int $sale_id, array $items, float $grand_total, int $payment_mode, int $account_id, float $amount_received, ?string $notes = null, int $user_id = 1)
    {
        try {
            $this->conn->beginTransaction();

            // Update each sale item with its price
            $updateItemSql = "UPDATE tbl_sale_items SET unit_price = :unit_price, total_price = :total_price WHERE sale_item_id = :item_id";
            $updateItemStmt = $this->conn->prepare($updateItemSql);

            foreach ($items as $item) {
                $updateItemStmt->execute([
                    ':unit_price' => $item['unit_price'],
                    ':total_price' => $item['total'],
                    ':item_id' => $item['item_id']
                ]);
            }

            // Update sale total and status
            $updateSaleSql = "UPDATE tbl_sales SET total_amount = :total_amount, status = 'completed', price_status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE sale_id = :sale_id";
            $updateSaleStmt = $this->conn->prepare($updateSaleSql);
            $updateSaleStmt->execute([
                ':total_amount' => $grand_total,
                ':sale_id' => $sale_id
            ]);

            // Insert payment record
            $paymentSql = "
                INSERT INTO tbl_sale_payments (sale_id, mode_id, acc_id, amount, document_ref, created_at)
                VALUES (:sale_id, :mode_id, :acc_id, :amount, :notes, CURRENT_TIMESTAMP)
            ";
            $paymentStmt = $this->conn->prepare($paymentSql);
            $paymentStmt->execute([
                ':sale_id' => $sale_id,
                ':mode_id' => $payment_mode,
                ':acc_id' => $account_id,
                ':amount' => $amount_received,
                ':notes' => $notes
            ]);

            // Update account balance
            $updateAccSql = "UPDATE tbl_accounts SET balance = balance + :amount WHERE acc_id = :acc_id";
            $updateAccStmt = $this->conn->prepare($updateAccSql);
            $updateAccStmt->execute([
                ':amount' => $amount_received,
                ':acc_id' => $account_id
            ]);

            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating sale prices: " . $e->getMessage());
            return false;
        }
    }
}
