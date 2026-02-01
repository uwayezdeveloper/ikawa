<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class ProductionTransfer
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'TRF';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $date . $random;
    }

    public function getAvailableStock(int $loc_id)
    {
        try {
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
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching available stock: " . $e->getMessage());
            return false;
        }
    }

    public function createTransferBatch(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $reference_no = $this->generateReferenceNo();
            $total_items = count($data['items']);
            $total_quantity = 0;

            foreach ($data['items'] as $item) {
                $total_quantity += floatval($item['quantity']);
            }

            // Insert tracking record
            $trackingSql = "
                INSERT INTO tbl_transfer_tracking 
                (reference_no, from_loc_id, to_loc_id, transfer_date, total_items, total_quantity, notes, user_id, status) 
                VALUES 
                (:reference_no, :from_loc_id, :to_loc_id, :transfer_date, :total_items, :total_quantity, :notes, :user_id, 'pending')
            ";
            $trackingStmt = $this->conn->prepare($trackingSql);
            $trackingStmt->execute([
                ':reference_no' => $reference_no,
                ':from_loc_id' => $data['from_loc_id'],
                ':to_loc_id' => $data['to_loc_id'],
                ':transfer_date' => $data['transfer_date'],
                ':total_items' => $total_items,
                ':total_quantity' => $total_quantity,
                ':notes' => $data['notes'],
                ':user_id' => $data['user_id']
            ]);

            $tracking_id = $this->conn->lastInsertId();

            // Insert each item and deduct from stock
            foreach ($data['items'] as $item) {
                $assignment_id = $item['assignment_id'];
                $quantity = floatval($item['quantity']);

                // Check available stock
                $checkSql = "
                    SELECT total_quantity FROM tbl_stock_summary 
                    WHERE loc_id = :loc_id AND assignment_id = :assignment_id
                ";
                $checkStmt = $this->conn->prepare($checkSql);
                $checkStmt->execute([
                    ':loc_id' => $data['from_loc_id'],
                    ':assignment_id' => $assignment_id
                ]);
                $stock = $checkStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$stock || $stock['total_quantity'] < $quantity) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Insufficient stock for one or more items'];
                }

                // Insert transfer detail (without sup_id since your table has it but we're not using it)
                $detailSql = "
                    INSERT INTO tbl_transfer_details 
                    (tracking_id, assignment_id, quantity) 
                    VALUES 
                    (:tracking_id, :assignment_id, :quantity)
                ";
                $detailStmt = $this->conn->prepare($detailSql);
                $detailStmt->execute([
                    ':tracking_id' => $tracking_id,
                    ':assignment_id' => $assignment_id,
                    ':quantity' => $quantity
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
                    ':loc_id' => $data['from_loc_id'],
                    ':assignment_id' => $assignment_id
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'reference_no' => $reference_no, 'tracking_id' => $tracking_id];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating transfer batch: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
        }
    }

    public function getTransfersByLocation(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    tt.tracking_id,
                    tt.reference_no,
                    tt.from_loc_id,
                    tt.to_loc_id,
                    tt.transfer_date,
                    tt.total_items,
                    tt.total_quantity as tracking_total_quantity,
                    tt.notes,
                    tt.status as tracking_status,
                    tt.created_at,
                    td.transfer_detail_id,
                    td.assignment_id,
                    td.quantity,
                    td.status as detail_status,
                    fl.location_name as from_location,
                    tl.location_name as to_location,
                    u.username as created_by,
                    ct.type_name,
                    un.unit_name
                FROM tbl_transfer_tracking tt
                LEFT JOIN tbl_transfer_details td ON tt.tracking_id = td.tracking_id
                LEFT JOIN tbl_location fl ON tt.from_loc_id = fl.loc_id
                LEFT JOIN tbl_location tl ON tt.to_loc_id = tl.loc_id
                LEFT JOIN tbl_users u ON tt.user_id = u.user_id
                LEFT JOIN tbl_category_type_units ctu ON td.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units un ON ctu.unit_id = un.unit_id
                WHERE tt.from_loc_id = :loc_id
                ORDER BY tt.created_at DESC, td.transfer_detail_id ASC
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfers: " . $e->getMessage());
            return false;
        }
    }

    public function getTransferDetails(int $tracking_id)
    {
        try {
            $sql = "
                SELECT 
                    td.transfer_detail_id,
                    td.tracking_id,
                    td.assignment_id,
                    td.quantity,
                    td.created_at,
                    ctu.type_id,
                    ctu.unit_id,
                    ct.type_name,
                    u.unit_name
                FROM tbl_transfer_details td
                LEFT JOIN tbl_category_type_units ctu ON td.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE td.tracking_id = :tracking_id
                ORDER BY ct.type_name, u.unit_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':tracking_id' => $tracking_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfer details: " . $e->getMessage());
            return false;
        }
    }

    public function updateTransferStatus(int $tracking_id, string $status): bool
    {
        try {
            $sql = "UPDATE tbl_transfer_tracking SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE tracking_id = :tracking_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':status' => $status, ':tracking_id' => $tracking_id]);
        } catch (\PDOException $e) {
            error_log("Error updating transfer status: " . $e->getMessage());
            return false;
        }
    }

    public function approveTransferDetail(int $transfer_detail_id): array
    {
        try {
            $this->conn->beginTransaction();

            // Get the transfer detail info
            $detailSql = "
                SELECT td.*, tt.tracking_id
                FROM tbl_transfer_details td
                INNER JOIN tbl_transfer_tracking tt ON td.tracking_id = tt.tracking_id
                WHERE td.transfer_detail_id = :transfer_detail_id
            ";
            $detailStmt = $this->conn->prepare($detailSql);
            $detailStmt->execute([':transfer_detail_id' => $transfer_detail_id]);
            $detail = $detailStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$detail) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Transfer detail not found'];
            }

            if ($detail['status'] !== 'pending') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'This item is already approved or processed'];
            }

            // Update only this transfer detail status to in_transit
            $updateDetailSql = "
                UPDATE tbl_transfer_details 
                SET status = 'in_transit' 
                WHERE transfer_detail_id = :transfer_detail_id
            ";
            $updateDetailStmt = $this->conn->prepare($updateDetailSql);
            $updateDetailStmt->execute([':transfer_detail_id' => $transfer_detail_id]);

            // Check if all details for this tracking are now in_transit or beyond
            $checkAllSql = "
                SELECT COUNT(*) as pending_count 
                FROM tbl_transfer_details 
                WHERE tracking_id = :tracking_id AND status = 'pending'
            ";
            $checkAllStmt = $this->conn->prepare($checkAllSql);
            $checkAllStmt->execute([':tracking_id' => $detail['tracking_id']]);
            $pendingCount = $checkAllStmt->fetch(\PDO::FETCH_ASSOC)['pending_count'];

            // If no more pending items, update tracking status
            if ($pendingCount == 0) {
                $updateTrackingSql = "
                    UPDATE tbl_transfer_tracking 
                    SET status = 'in_transit', updated_at = CURRENT_TIMESTAMP 
                    WHERE tracking_id = :tracking_id
                ";
                $updateTrackingStmt = $this->conn->prepare($updateTrackingSql);
                $updateTrackingStmt->execute([':tracking_id' => $detail['tracking_id']]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Transfer item approved and marked as in transit'];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error approving transfer detail: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
        }
    }

    public function approveTransfer(int $tracking_id): array
    {
        try {
            $this->conn->beginTransaction();

            $checkSql = "SELECT status FROM tbl_transfer_tracking WHERE tracking_id = :tracking_id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([':tracking_id' => $tracking_id]);
            $transfer = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$transfer) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Transfer not found'];
            }

            // Update all pending details to in_transit
            $updateDetailsSql = "
                UPDATE tbl_transfer_details 
                SET status = 'in_transit' 
                WHERE tracking_id = :tracking_id AND status = 'pending'
            ";
            $updateDetailsStmt = $this->conn->prepare($updateDetailsSql);
            $updateDetailsStmt->execute([':tracking_id' => $tracking_id]);

            // Update tracking status
            $updateTrackingSql = "
                UPDATE tbl_transfer_tracking 
                SET status = 'in_transit', updated_at = CURRENT_TIMESTAMP 
                WHERE tracking_id = :tracking_id
            ";
            $updateTrackingStmt = $this->conn->prepare($updateTrackingSql);
            $updateTrackingStmt->execute([':tracking_id' => $tracking_id]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'All transfer items approved and marked as in transit'];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error approving transfer: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    public function returnTransfer(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $tracking_id = $data['tracking_id'];
            
            $checkSql = "SELECT status, from_loc_id, returned_quantity FROM tbl_transfer_tracking WHERE tracking_id = :tracking_id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([':tracking_id' => $tracking_id]);
            $transfer = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$transfer) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Transfer not found'];
            }

            if ($transfer['status'] === 'returned') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Transfer already returned'];
            }

            $total_returned = 0;

            foreach ($data['items'] as $item) {
                $detail_id = $item['detail_id'];
                $return_qty = floatval($item['return_quantity']);
                $total_returned += $return_qty;

                $detailSql = "SELECT * FROM tbl_transfer_details WHERE transfer_detail_id = :transfer_detail_id";
                $detailStmt = $this->conn->prepare($detailSql);
                $detailStmt->execute([':transfer_detail_id' => $detail_id]);
                $detail = $detailStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$detail || $return_qty > $detail['quantity']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Invalid return quantity'];
                }

                // Check if stock summary record exists
                $checkStockSql = "
                    SELECT stock_summary_id, total_quantity 
                    FROM tbl_stock_summary 
                    WHERE loc_id = :loc_id AND assignment_id = :assignment_id
                ";
                $checkStockStmt = $this->conn->prepare($checkStockSql);
                $checkStockStmt->execute([
                    ':loc_id' => $transfer['from_loc_id'],
                    ':assignment_id' => $detail['assignment_id']
                ]);
                $existingStock = $checkStockStmt->fetch(\PDO::FETCH_ASSOC);

                if ($existingStock) {
                    $updateStockSql = "
                        UPDATE tbl_stock_summary 
                        SET total_quantity = total_quantity + :quantity,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE stock_summary_id = :stock_summary_id
                    ";
                    $updateStockStmt = $this->conn->prepare($updateStockSql);
                    $updateStockStmt->execute([
                        ':quantity' => $return_qty,
                        ':stock_summary_id' => $existingStock['stock_summary_id']
                    ]);
                } else {
                    $insertStockSql = "
                        INSERT INTO tbl_stock_summary 
                        (loc_id, assignment_id, total_quantity, created_at, updated_at)
                        VALUES 
                        (:loc_id, :assignment_id, :quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ";
                    $insertStockStmt = $this->conn->prepare($insertStockSql);
                    $insertStockStmt->execute([
                        ':loc_id' => $transfer['from_loc_id'],
                        ':assignment_id' => $detail['assignment_id'],
                        ':quantity' => $return_qty
                    ]);
                }
            }

            // Update tracking record - use returned_quantity from tbl_transfer_tracking
            $updateTrackingSql = "
                UPDATE tbl_transfer_tracking 
                SET status = 'returned', 
                    return_date = :return_date,
                    return_reason = :return_reason,
                    returned_quantity = COALESCE(returned_quantity, 0) + :returned_quantity,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE tracking_id = :tracking_id
            ";
            $updateTrackingStmt = $this->conn->prepare($updateTrackingSql);
            $updateTrackingStmt->execute([
                ':return_date' => $data['return_date'],
                ':return_reason' => $data['return_reason'],
                ':returned_quantity' => $total_returned,
                ':tracking_id' => $tracking_id
            ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Transfer returned successfully'];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error returning transfer: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getTransferInfo(int $tracking_id)
    {
        try {
            $sql = "
                SELECT 
                    tt.tracking_id,
                    tt.reference_no,
                    tt.total_quantity,
                    tt.received_quantity,
                    tt.status,
                    (tt.total_quantity - COALESCE(tt.received_quantity, 0)) as available_to_receive
                FROM tbl_transfer_tracking tt
                WHERE tt.tracking_id = :tracking_id
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':tracking_id' => $tracking_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfer info: " . $e->getMessage());
            return false;
        }
    }

    public function getTransferDetailInfo(int $transfer_detail_id)
    {
        try {
            $sql = "
                SELECT 
                    td.transfer_detail_id,
                    td.tracking_id,
                    td.assignment_id,
                    td.quantity,
                    td.status,
                    COALESCE(td.received_quantity, 0) as received_quantity,
                    td.created_at,
                    tt.reference_no,
                    tt.transfer_date,
                    ctu.type_id,
                    ctu.unit_id,
                    ct.type_name,
                    u.unit_name
                FROM tbl_transfer_details td
                INNER JOIN tbl_transfer_tracking tt ON td.tracking_id = tt.tracking_id
                LEFT JOIN tbl_category_type_units ctu ON td.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE td.transfer_detail_id = :transfer_detail_id
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':transfer_detail_id' => $transfer_detail_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfer detail info: " . $e->getMessage());
            return false;
        }
    }

    public function receiveProduction(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $tracking_id = $data['tracking_id'];
            $transfer_detail_id = $data['transfer_detail_id'] ?? null;
            $loc_id = $data['loc_id'];
            $user_id = $data['user_id'];
            $receive_date = $data['receive_date'];
            $notes = $data['notes'] ?? '';

            // If specific detail_id provided, validate against that detail
            if ($transfer_detail_id) {
                $checkDetailSql = "
                    SELECT td.*, tt.from_loc_id 
                    FROM tbl_transfer_details td
                    INNER JOIN tbl_transfer_tracking tt ON td.tracking_id = tt.tracking_id
                    WHERE td.transfer_detail_id = :transfer_detail_id
                ";
                $checkDetailStmt = $this->conn->prepare($checkDetailSql);
                $checkDetailStmt->execute([':transfer_detail_id' => $transfer_detail_id]);
                $detail = $checkDetailStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$detail) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Transfer detail not found'];
                }

                // Check if already completed
                if ($detail['status'] === 'completed') {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'This item has already been fully received'];
                }

                if ($detail['status'] !== 'in_transit') {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Only in-transit items can receive production'];
                }

                $total_received = 0;
                foreach ($data['items'] as $item) {
                    $total_received += floatval($item['quantity']);
                }

                $already_received = floatval($detail['received_quantity'] ?? 0);
                $transferred_quantity = floatval($detail['quantity']);
                $available_to_receive = $transferred_quantity - $already_received;

                if ($total_received > $available_to_receive) {
                    $this->conn->rollBack();
                    return [
                        'success' => false, 
                        'message' => "Cannot receive more than transferred. Available: {$available_to_receive}, Trying to receive: {$total_received}"
                    ];
                }

                // Process items
                foreach ($data['items'] as $item) {
                    $type_id = $item['type_id'];
                    $unit_id = $item['unit_id'];
                    $quantity = floatval($item['quantity']);

                    $assignmentSql = "
                        SELECT assignment_id 
                        FROM tbl_category_type_units 
                        WHERE type_id = :type_id AND unit_id = :unit_id AND status = 'active'
                        LIMIT 1
                    ";
                    $assignmentStmt = $this->conn->prepare($assignmentSql);
                    $assignmentStmt->execute([
                        ':type_id' => $type_id,
                        ':unit_id' => $unit_id
                    ]);
                    $assignmentResult = $assignmentStmt->fetch(\PDO::FETCH_ASSOC);

                    if (!$assignmentResult) {
                        $this->conn->rollBack();
                        return ['success' => false, 'message' => 'Invalid type/unit combination'];
                    }

                    $assignment_id = $assignmentResult['assignment_id'];

                    // Insert into production receipt table
                    $receiptSql = "
                        INSERT INTO tbl_production_receipts 
                        (tracking_id, assignment_id, quantity, receive_date, notes, loc_id, user_id, created_at)
                        VALUES 
                        (:tracking_id, :assignment_id, :quantity, :receive_date, :notes, :loc_id, :user_id, CURRENT_TIMESTAMP)
                    ";
                    $receiptStmt = $this->conn->prepare($receiptSql);
                    $receiptStmt->execute([
                        ':tracking_id' => $tracking_id,
                        ':assignment_id' => $assignment_id,
                        ':quantity' => $quantity,
                        ':receive_date' => $receive_date,
                        ':notes' => $notes,
                        ':loc_id' => $loc_id,
                        ':user_id' => $user_id
                    ]);

                    // Update stock summary
                    $checkStockSql = "
                        SELECT stock_summary_id, total_quantity 
                        FROM tbl_stock_summary 
                        WHERE loc_id = :loc_id AND assignment_id = :assignment_id
                    ";
                    $checkStockStmt = $this->conn->prepare($checkStockSql);
                    $checkStockStmt->execute([
                        ':loc_id' => $loc_id,
                        ':assignment_id' => $assignment_id
                    ]);
                    $existingStock = $checkStockStmt->fetch(\PDO::FETCH_ASSOC);

                    if ($existingStock) {
                        $updateStockSql = "
                            UPDATE tbl_stock_summary 
                            SET total_quantity = total_quantity + :quantity,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE stock_summary_id = :stock_summary_id
                        ";
                        $updateStockStmt = $this->conn->prepare($updateStockSql);
                        $updateStockStmt->execute([
                            ':quantity' => $quantity,
                            ':stock_summary_id' => $existingStock['stock_summary_id']
                        ]);
                    } else {
                        $insertStockSql = "
                            INSERT INTO tbl_stock_summary 
                            (loc_id, assignment_id, total_quantity, created_at, updated_at)
                            VALUES 
                            (:loc_id, :assignment_id, :quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                        ";
                        $insertStockStmt = $this->conn->prepare($insertStockSql);
                        $insertStockStmt->execute([
                            ':loc_id' => $loc_id,
                            ':assignment_id' => $assignment_id,
                            ':quantity' => $quantity
                        ]);
                    }
                }

                // Update specific detail received_quantity and status
                $newReceivedQty = $already_received + $total_received;
                
                // Always mark as completed after receiving (since we receive the full amount)
                $newStatus = 'completed';
                
                $updateDetailSql = "
                    UPDATE tbl_transfer_details 
                    SET received_quantity = :received_quantity,
                        status = :status
                    WHERE transfer_detail_id = :transfer_detail_id
                ";
                $updateDetailStmt = $this->conn->prepare($updateDetailSql);
                $updateDetailStmt->execute([
                    ':received_quantity' => $newReceivedQty,
                    ':status' => $newStatus,
                    ':transfer_detail_id' => $transfer_detail_id
                ]);

                // Check if all details are completed to update tracking status
                $checkAllCompletedSql = "
                    SELECT COUNT(*) as incomplete_count 
                    FROM tbl_transfer_details 
                    WHERE tracking_id = :tracking_id AND status != 'completed'
                ";
                $checkAllCompletedStmt = $this->conn->prepare($checkAllCompletedSql);
                $checkAllCompletedStmt->execute([':tracking_id' => $tracking_id]);
                $incompleteCount = $checkAllCompletedStmt->fetch(\PDO::FETCH_ASSOC)['incomplete_count'];

                if ($incompleteCount == 0) {
                    $updateTrackingSql = "
                        UPDATE tbl_transfer_tracking 
                        SET status = 'completed', 
                            received_date = :receive_date,
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE tracking_id = :tracking_id
                    ";
                    $updateTrackingStmt = $this->conn->prepare($updateTrackingSql);
                    $updateTrackingStmt->execute([
                        ':receive_date' => $receive_date,
                        ':tracking_id' => $tracking_id
                    ]);
                }

                $this->conn->commit();
                return ['success' => true, 'message' => 'Production received successfully'];
            }

            // Fallback: Original behavior if no specific detail_id
            $checkSql = "SELECT status, from_loc_id, total_quantity, received_quantity FROM tbl_transfer_tracking WHERE tracking_id = :tracking_id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([':tracking_id' => $tracking_id]);
            $transfer = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$transfer) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Transfer not found'];
            }

            if ($transfer['status'] !== 'in_transit') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Only in-transit transfers can receive production'];
            }

            $total_received = 0;
            foreach ($data['items'] as $item) {
                $total_received += floatval($item['quantity']);
            }

            $already_received = floatval($transfer['received_quantity'] ?? 0);
            $transferred_quantity = floatval($transfer['total_quantity']);
            $available_to_receive = $transferred_quantity - $already_received;

            if ($total_received > $available_to_receive) {
                $this->conn->rollBack();
                return [
                    'success' => false, 
                    'message' => "Cannot receive more than transferred. Available: {$available_to_receive}, Trying to receive: {$total_received}"
                ];
            }

            foreach ($data['items'] as $item) {
                $type_id = $item['type_id'];
                $unit_id = $item['unit_id'];
                $quantity = floatval($item['quantity']);

                $assignmentSql = "
                    SELECT assignment_id 
                    FROM tbl_category_type_units 
                    WHERE type_id = :type_id AND unit_id = :unit_id AND status = 'active'
                    LIMIT 1
                ";
                $assignmentStmt = $this->conn->prepare($assignmentSql);
                $assignmentStmt->execute([
                    ':type_id' => $type_id,
                    ':unit_id' => $unit_id
                ]);
                $assignmentResult = $assignmentStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$assignmentResult) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Invalid type/unit combination'];
                }

                $assignment_id = $assignmentResult['assignment_id'];

                // Insert into production receipt table
                $receiptSql = "
                    INSERT INTO tbl_production_receipts 
                    (tracking_id, assignment_id, quantity, receive_date, notes, loc_id, user_id, created_at)
                    VALUES 
                    (:tracking_id, :assignment_id, :quantity, :receive_date, :notes, :loc_id, :user_id, CURRENT_TIMESTAMP)
                ";
                $receiptStmt = $this->conn->prepare($receiptSql);
                $receiptStmt->execute([
                    ':tracking_id' => $tracking_id,
                    ':assignment_id' => $assignment_id,
                    ':quantity' => $quantity,
                    ':receive_date' => $receive_date,
                    ':notes' => $notes,
                    ':loc_id' => $loc_id,
                    ':user_id' => $user_id
                ]);

                // Update stock summary
                $checkStockSql = "
                    SELECT stock_summary_id, total_quantity 
                    FROM tbl_stock_summary 
                    WHERE loc_id = :loc_id AND assignment_id = :assignment_id
                ";
                $checkStockStmt = $this->conn->prepare($checkStockSql);
                $checkStockStmt->execute([
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $assignment_id
                ]);
                $existingStock = $checkStockStmt->fetch(\PDO::FETCH_ASSOC);

                if ($existingStock) {
                    $updateStockSql = "
                        UPDATE tbl_stock_summary 
                        SET total_quantity = total_quantity + :quantity,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE stock_summary_id = :stock_summary_id
                    ";
                    $updateStockStmt = $this->conn->prepare($updateStockSql);
                    $updateStockStmt->execute([
                        ':quantity' => $quantity,
                        ':stock_summary_id' => $existingStock['stock_summary_id']
                    ]);
                } else {
                    $insertStockSql = "
                        INSERT INTO tbl_stock_summary 
                        (loc_id, assignment_id, total_quantity, created_at, updated_at)
                        VALUES 
                        (:loc_id, :assignment_id, :quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ";
                    $insertStockStmt = $this->conn->prepare($insertStockSql);
                    $insertStockStmt->execute([
                        ':loc_id' => $loc_id,
                        ':assignment_id' => $assignment_id,
                        ':quantity' => $quantity
                    ]);
                }
            }

            // Update tracking status
            $updateTrackingSql = "
                UPDATE tbl_transfer_tracking 
                SET status = 'completed', 
                    received_date = :receive_date,
                    received_quantity = :received_quantity,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE tracking_id = :tracking_id
            ";
            $updateTrackingStmt = $this->conn->prepare($updateTrackingSql);
            $updateTrackingStmt->execute([
                ':receive_date' => $receive_date,
                ':received_quantity' => $already_received + $total_received,
                ':tracking_id' => $tracking_id
            ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Production received successfully'];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error receiving production: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
