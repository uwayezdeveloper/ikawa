<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Stock
{
    private $conn;
    private $lastError = '';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function createStock(array $data, string $recordType = 'purchase'): bool
    {
        try {
            $this->conn->beginTransaction();

            // Get assignment_id from tbl_category_type_units
            $assignmentSql = "
                SELECT assignment_id 
                FROM tbl_category_type_units 
                WHERE type_id = :type_id AND unit_id = :unit_id AND status = 'active'
                LIMIT 1
            ";
            $assignmentStmt = $this->conn->prepare($assignmentSql);
            $assignmentStmt->execute([
                ':type_id' => $data['type_id'],
                ':unit_id' => $data['unit_id']
            ]);
            $assignmentResult = $assignmentStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$assignmentResult) {
                error_log("Assignment not found for type_id: {$data['type_id']} and unit_id: {$data['unit_id']}");
                $this->conn->rollBack();
                return false;
            }

            $assignment_id = $assignmentResult['assignment_id'];

            // Insert stock detail WITHOUT stock_summary_id (will be set when approved)
            $sql = "
                INSERT INTO tbl_stock_details 
                (assignment_id, sup_id, quantity, unit_price, total_price, loc_id, user_id, record_type, price_status) 
                VALUES 
                (:assignment_id, :sup_id, :quantity, 0, 0, :loc_id, :user_id, :record_type, 'pending')
            ";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':assignment_id' => $assignment_id,
                ':sup_id' => $data['sup_id'],
                ':quantity' => $data['quantity'],
                ':loc_id' => $data['loc_id'],
                ':user_id' => $data['user_id'],
                ':record_type' => $recordType
            ]);

            if (!$result) {
                error_log("Failed to insert stock detail: " . print_r($stmt->errorInfo(), true));
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating stock: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function approveStockPrice(int $stockDetailId, float $unitPrice, int $approvedBy): bool
    {
        try {
            $this->conn->beginTransaction();

            // Get stock detail info
            $detailSql = "SELECT * FROM tbl_stock_details WHERE stock_detail_id = :stock_detail_id AND price_status = 'pending'";
            $detailStmt = $this->conn->prepare($detailSql);
            $detailStmt->execute([':stock_detail_id' => $stockDetailId]);
            $detail = $detailStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$detail) {
                throw new \PDOException("Stock detail not found or already approved");
            }

            $totalPrice = $detail['quantity'] * $unitPrice;

            // Check if summary exists for this loc_id and assignment_id
            $checkSql = "
                SELECT stock_summary_id, total_quantity 
                FROM tbl_stock_summary 
                WHERE loc_id = :loc_id AND assignment_id = :assignment_id
            ";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([
                ':loc_id' => $detail['loc_id'],
                ':assignment_id' => $detail['assignment_id']
            ]);
            $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            $stock_summary_id = null;

            if ($existing) {
                $updateSql = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity + :quantity,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE stock_summary_id = :stock_summary_id
                ";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->execute([
                    ':quantity' => $detail['quantity'],
                    ':stock_summary_id' => $existing['stock_summary_id']
                ]);
                $stock_summary_id = $existing['stock_summary_id'];
            } else {
                $insertSql = "
                    INSERT INTO tbl_stock_summary 
                    (loc_id, assignment_id, total_quantity) 
                    VALUES 
                    (:loc_id, :assignment_id, :quantity)
                ";
                $insertStmt = $this->conn->prepare($insertSql);
                $insertStmt->execute([
                    ':loc_id' => $detail['loc_id'],
                    ':assignment_id' => $detail['assignment_id'],
                    ':quantity' => $detail['quantity']
                ]);
                $stock_summary_id = $this->conn->lastInsertId();
            }

            // Update stock detail with price and approved status
            $updateDetailSql = "
                UPDATE tbl_stock_details 
                SET unit_price = :unit_price,
                    total_price = :total_price,
                    stock_summary_id = :stock_summary_id,
                    price_status = 'approved',
                    approved_by = :approved_by,
                    approved_at = CURRENT_TIMESTAMP
                WHERE stock_detail_id = :stock_detail_id
            ";
            $updateDetailStmt = $this->conn->prepare($updateDetailSql);
            $updateDetailStmt->execute([
                ':unit_price' => $unitPrice,
                ':total_price' => $totalPrice,
                ':stock_summary_id' => $stock_summary_id,
                ':approved_by' => $approvedBy,
                ':stock_detail_id' => $stockDetailId
            ]);

            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error approving stock price: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getPendingStockForApproval()
    {
        try {
            $sql = "
                SELECT 
                    sd.stock_detail_id,
                    sd.assignment_id,
                    sd.sup_id,
                    sd.quantity,
                    sd.loc_id,
                    sd.user_id,
                    sd.created_at,
                    ct.type_name,
                    u.unit_name,
                    s.full_name as supplier_name,
                    l.location_name as station_name,
                    usr.first_name,
                    usr.last_name
                FROM tbl_stock_details sd
                LEFT JOIN tbl_category_type_units ctu ON sd.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                LEFT JOIN tbl_suppliers s ON sd.sup_id = s.sup_id
                LEFT JOIN tbl_location l ON sd.loc_id = l.loc_id
                LEFT JOIN tbl_users usr ON sd.user_id = usr.user_id
                WHERE sd.price_status = 'pending'
                ORDER BY sd.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching pending stock: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getUnitsByType(string $type_id)
    {
        try {
            $sql = "
                SELECT u.unit_id, u.unit_name 
                FROM tbl_units u
                INNER JOIN tbl_category_type_units ctu ON u.unit_id = ctu.unit_id
                WHERE ctu.type_id = :type_id AND ctu.status = 'active'
                ORDER BY u.unit_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':type_id' => $type_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching units: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getDetailedStock(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    sd.stock_detail_id,
                    sd.assignment_id,
                    sd.sup_id,
                    sd.quantity,
                    sd.unit_price,
                    sd.total_price,
                    sd.loc_id,
                    sd.user_id,
                    sd.stock_summary_id,
                    sd.record_type,
                    sd.price_status,
                    sd.created_at,
                    ct.type_name,
                    u.unit_name,
                    s.full_name as supplier_name
                FROM tbl_stock_details sd
                LEFT JOIN tbl_category_type_units ctu ON sd.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                LEFT JOIN tbl_suppliers s ON sd.sup_id = s.sup_id
                WHERE sd.loc_id = :loc_id
                ORDER BY sd.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching detailed stock: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getSummaryStock(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    ss.stock_summary_id,
                    ss.loc_id,
                    ss.assignment_id,
                    ss.total_quantity,
                    ss.created_at,
                    ss.updated_at,
                    l.location_name as station_name,
                    ct.type_name,
                    u.unit_name
                FROM tbl_stock_summary ss
                LEFT JOIN tbl_category_type_units ctu ON ss.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_location l ON ss.loc_id = l.loc_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE ss.loc_id = :loc_id
                ORDER BY ct.type_name, u.unit_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching summary stock: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getSupplierStatement(int $sup_id, ?string $start = null, ?string $end = null, ?int $loc_id = null)
    {
        try {
            $where = 'WHERE sd.sup_id = :sup_id';
            $params = [':sup_id' => $sup_id];

            if ($loc_id) {
                $where .= ' AND sd.loc_id = :loc_id';
                $params[':loc_id'] = $loc_id;
            }

            if ($start) {
                $where .= ' AND sd.created_at >= :start';
                $params[':start'] = $start . ' 00:00:00';
            }
            if ($end) {
                $where .= ' AND sd.created_at <= :end';
                $params[':end'] = $end . ' 23:59:59';
            }

            // Determine if payments table has payed_amount column
            $colCheckSql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_supplier_payment' AND COLUMN_NAME = 'payed_amount'";
            $colStmt = $this->conn->query($colCheckSql);
            $hasAmount = false;
            if ($colStmt) {
                $rowCol = $colStmt->fetch(\PDO::FETCH_ASSOC);
                $hasAmount = intval($rowCol['cnt'] ?? 0) > 0;
            }

            if ($hasAmount) {
                // aggregate payments by stock_detail_id summing payed_amount
                $rowsSql = "
                    SELECT sd.stock_detail_id, sd.assignment_id, sd.sup_id, sd.quantity, sd.unit_price, sd.total_price, sd.loc_id, sd.user_id, sd.record_type, sd.created_at,
                           ct.type_name, u.unit_name, s.full_name AS supplier_name,
                           COALESCE(pv.paid_amount, 0) AS paid_amount,
                           pv.payment_status
                    FROM tbl_stock_details sd
                    LEFT JOIN tbl_category_type_units ctu ON sd.assignment_id = ctu.assignment_id
                    LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                    LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                    LEFT JOIN tbl_suppliers s ON sd.sup_id = s.sup_id
                    LEFT JOIN (
                        SELECT stock_detail_id, SUM(payed_amount) AS paid_amount, MAX(status) AS payment_status
                        FROM tbl_supplier_payment
                        GROUP BY stock_detail_id
                    ) pv ON sd.stock_detail_id = pv.stock_detail_id
                    $where
                    ORDER BY sd.created_at DESC
                ";

                $stmt = $this->conn->prepare($rowsSql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $totalsSql = "
                    SELECT
                        COALESCE(SUM(sd.total_price),0) AS payable,
                        COALESCE(SUM(pv.paid_amount),0) AS paid
                    FROM tbl_stock_details sd
                    LEFT JOIN (
                        SELECT stock_detail_id, SUM(payed_amount) AS paid_amount
                        FROM tbl_supplier_payment
                        GROUP BY stock_detail_id
                    ) pv ON sd.stock_detail_id = pv.stock_detail_id
                    $where
                ";
                $totStmt = $this->conn->prepare($totalsSql);
                $totStmt->execute($params);
                $totals = $totStmt->fetch(\PDO::FETCH_ASSOC);

                $payable = floatval($totals['payable'] ?? 0);
                $paid = floatval($totals['paid'] ?? 0);
                $remaining = $payable - $paid;
            } else {
                // fallback: no amount column — treat a payment with status=1 as full payment of the stock row
                $rowsSql = "
                    SELECT sd.stock_detail_id, sd.assignment_id, sd.sup_id, sd.quantity, sd.unit_price, sd.total_price, sd.loc_id, sd.user_id, sd.record_type, sd.created_at,
                           ct.type_name, u.unit_name, s.full_name AS supplier_name,
                           MAX(p.status) AS payment_status
                    FROM tbl_stock_details sd
                    LEFT JOIN tbl_category_type_units ctu ON sd.assignment_id = ctu.assignment_id
                    LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                    LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                    LEFT JOIN tbl_suppliers s ON sd.sup_id = s.sup_id
                    LEFT JOIN tbl_supplier_payment p ON sd.stock_detail_id = p.stock_detail_id
                    $where
                    GROUP BY sd.stock_detail_id
                    ORDER BY sd.created_at DESC
                ";

                $stmt = $this->conn->prepare($rowsSql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $totalsSql = "
                    SELECT
                        COALESCE(SUM(sd.total_price),0) AS payable,
                        COALESCE(SUM(CASE WHEN p.status = 1 THEN sd.total_price ELSE 0 END),0) AS paid
                    FROM tbl_stock_details sd
                    LEFT JOIN tbl_supplier_payment p ON sd.stock_detail_id = p.stock_detail_id
                    $where
                ";
                $totStmt = $this->conn->prepare($totalsSql);
                $totStmt->execute($params);
                $totals = $totStmt->fetch(\PDO::FETCH_ASSOC);

                $payable = floatval($totals['payable'] ?? 0);
                $paid = floatval($totals['paid'] ?? 0);
                $remaining = $payable - $paid;
                // ensure rows have paid_amount field for UI
                foreach ($rows as &$r) {
                    $r['paid_amount'] = ($r['payment_status'] == 1) ? floatval($r['total_price']) : 0.0;
                }
                unset($r);
            }
            return [
                'supplier_id' => $sup_id,
                'start' => $start,
                'end' => $end,
                'loc_id' => $loc_id,
                'payable' => $payable,
                'paid' => $paid,
                'remaining' => $remaining,
                'rows' => $rows
            ];
        } catch (\PDOException $e) {
            error_log('Error fetching supplier statement: ' . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getDistinctSuppliers()
    {
        try {
            $sql = "
                SELECT sup_id, full_name, email, phone, type, status
                FROM tbl_suppliers
                WHERE status = 'active'
                ORDER BY full_name ASC
            ";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error fetching suppliers: ' . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getUnpaidStockItems($sup_id)
    {
        try {
            $sql = "
                SELECT 
                    sd.stock_detail_id,
                    sd.total_price,
                    COALESCE(SUM(sp.payed_amount), 0) as paid_amount,
                    (sd.total_price - COALESCE(SUM(sp.payed_amount), 0)) as remaining
                FROM tbl_stock_details sd
                LEFT JOIN tbl_supplier_payment sp ON sd.stock_detail_id = sp.stock_detail_id
                WHERE sd.sup_id = :sup_id
                GROUP BY sd.stock_detail_id, sd.total_price, sd.created_at
                HAVING remaining > 0
                ORDER BY sd.created_at ASC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':sup_id' => $sup_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error fetching unpaid stock items: ' . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function recordSupplierPayment($payment_data)
    {
        try {
            $sql = "
                INSERT INTO tbl_supplier_payment 
                (stock_detail_id, pay_modes, payed_amount, status)
                VALUES
                (:stock_detail_id, :pay_modes, :payed_amount, :status)
            ";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':stock_detail_id' => $payment_data['stock_detail_id'],
                ':pay_modes' => $payment_data['pay_modes'], // JSON string
                ':payed_amount' => $payment_data['payed_amount'], // amount paid in this transaction
                ':status' => $payment_data['status'] // int: 1=partial, 2=paid
            ]);
        } catch (\PDOException $e) {
            error_log('Error recording supplier payment: ' . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollBack();
    }

    public function getLastError(): string
    {
        return $this->lastError ?? '';
    }
}
