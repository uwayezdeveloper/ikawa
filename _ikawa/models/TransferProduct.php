<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class TransferProduct
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all stations (type = 'Station')
     */
    public function getStations()
    {
        try {
            $query = "SELECT loc_id, location_name FROM tbl_location WHERE type = 'Station' ORDER BY location_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching stations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get location by ID
     */
    public function getLocationById(string $loc_id)
    {
        try {
            $query = "SELECT loc_id, location_name, type FROM tbl_location WHERE loc_id = :loc_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching location: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all warehouses (type = 'Warehouse')
     */
    public function getWarehouses()
    {
        try {
            $query = "SELECT loc_id, location_name FROM tbl_location WHERE type = 'Warehouse' ORDER BY location_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching warehouses: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active categories
     */
    public function getActiveCategories()
    {
        try {
            $query = "SELECT category_id, category_name FROM tbl_categories WHERE status = 'active' ORDER BY category_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get category types by category ID with their units
     */
    public function getCategoryTypesByCategory(string $category_id)
    {
        try {
            $query = "
                SELECT ct.type_id, ct.type_name 
                FROM tbl_category_types ct 
                WHERE ct.category_id = :category_id AND ct.status = 'active'
                ORDER BY ct.type_name
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':category_id' => $category_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching category types: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get units assigned to a category type
     */
    public function getUnitsByTypeId(string $type_id)
    {
        try {
            $query = "
                SELECT u.unit_id, u.unit_name 
                FROM tbl_category_type_units ctu
                JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE ctu.type_id = :type_id AND ctu.status = 'active'
                ORDER BY u.unit_name
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':type_id' => $type_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching units: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get category types available in stock at a specific location
     */
    public function getStockCategoryTypes(string $loc_id, string $category_id)
    {
        try {
            $query = "
                SELECT DISTINCT 
                    ct.type_id, 
                    ct.type_name,
                    c.category_id,
                    c.category_name
                FROM tbl_stock_summary ss
                JOIN tbl_category_type_units ctu ON ss.assignment_id = ctu.assignment_id
                JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                JOIN tbl_categories c ON ct.category_id = c.category_id
                WHERE ss.loc_id = :loc_id 
                  AND ct.category_id = :category_id
                  AND ct.status = 'active'
                  AND ss.total_quantity > 0
                ORDER BY ct.type_name
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':loc_id' => $loc_id, ':category_id' => $category_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching stock category types: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get units available in stock for a specific type at a location
     * Returns assignment_id for each unit to use in transfers
     */
    public function getStockUnits(string $loc_id, string $type_id)
    {
        try {
            $query = "
                SELECT DISTINCT 
                    u.unit_id, 
                    u.unit_name,
                    ctu.assignment_id,
                    ss.total_quantity
                FROM tbl_stock_summary ss
                JOIN tbl_category_type_units ctu ON ss.assignment_id = ctu.assignment_id
                JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE ss.loc_id = :loc_id 
                  AND ctu.type_id = :type_id
                  AND ss.total_quantity > 0
                ORDER BY u.unit_name
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':loc_id' => $loc_id, ':type_id' => $type_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching stock units: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available quantity for a specific assignment_id at a location
     */
    public function getAvailableQuantity(string $loc_id, string $assignment_id)
    {
        try {
            $query = "
                SELECT COALESCE(SUM(total_quantity), 0) as available_quantity
                FROM tbl_stock_summary
                WHERE loc_id = :loc_id 
                  AND assignment_id = :assignment_id
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':loc_id' => $loc_id,
                ':assignment_id' => $assignment_id
            ]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? (float)$result['available_quantity'] : 0;
        } catch (\PDOException $e) {
            error_log("Error fetching available quantity: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Deduct stock from source location when creating transfer
     */
    public function deductStock(string $loc_id, string $assignment_id, float $amount): bool
    {
        try {
            $query = "
                UPDATE tbl_stock_summary 
                SET total_quantity = total_quantity - :amount,
                    updated_at = CURRENT_TIMESTAMP
                WHERE loc_id = :loc_id 
                  AND assignment_id = :assignment_id
                  AND total_quantity >= :amount_check
            ";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':loc_id' => $loc_id,
                ':assignment_id' => $assignment_id,
                ':amount' => $amount,
                ':amount_check' => $amount
            ]);
        } catch (\PDOException $e) {
            error_log("Error deducting stock: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add stock to destination location when receiving transfer
     */
    public function addStock(string $loc_id, string $assignment_id, string $sup_id, float $amount): bool
    {
        try {
            // Check if record exists
            $checkQuery = "
                SELECT stock_summary_id, total_quantity 
                FROM tbl_stock_summary 
                WHERE loc_id = :loc_id 
                  AND assignment_id = :assignment_id
                  AND sup_id = :sup_id
                  AND sup_type = 'station'
            ";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([
                ':loc_id' => $loc_id,
                ':assignment_id' => $assignment_id,
                ':sup_id' => $sup_id
            ]);
            $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing record - add to total_quantity
                $updateQuery = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity + :amount,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE stock_summary_id = :stock_summary_id
                ";
                $updateStmt = $this->conn->prepare($updateQuery);
                return $updateStmt->execute([
                    ':amount' => $amount,
                    ':stock_summary_id' => $existing['stock_summary_id']
                ]);
            } else {
                // Insert new record
                $insertQuery = "
                    INSERT INTO tbl_stock_summary (loc_id, assignment_id, sup_id, sup_type, total_quantity)
                    VALUES (:loc_id, :assignment_id, :sup_id, 'station', :amount)
                ";
                $insertStmt = $this->conn->prepare($insertQuery);
                return $insertStmt->execute([
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $assignment_id,
                    ':sup_id' => $sup_id,
                    ':amount' => $amount
                ]);
            }
        } catch (\PDOException $e) {
            error_log("Error adding stock: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create transfer product with items (transaction)
     */
    public function createTransfer(array $productData, array $items): bool
    {
        try {
            $this->conn->beginTransaction();

            // Insert into tbl_transifer_products
            $sql = "
                INSERT INTO tbl_transifer_products (
                    categories_id,
                    supporting_documents,
                    additional_info,
                    station_id,
                    warehouse_id,
                    driver_id,
                    created_by
                ) VALUES (
                    :categories_id,
                    :supporting_documents,
                    :additional_info,
                    :station_id,
                    :warehouse_id,
                    :driver_id,
                    :created_by
                )
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':categories_id' => $productData['categories_id'],
                ':supporting_documents' => $productData['supporting_documents'],
                ':additional_info' => $productData['additional_info'],
                ':station_id' => $productData['station_id'],
                ':warehouse_id' => $productData['warehouse_id'],
                ':driver_id' => $productData['driver_id'],
                ':created_by' => $productData['created_by']
            ]);

            $transferId = $this->conn->lastInsertId();

            // Insert items into tbl_transifer_items
            $itemSql = "
                INSERT INTO tbl_transifer_items (
                    transifer_product_id,
                    assignment_id,
                    amount,
                    unit_price,
                    total_price
                ) VALUES (
                    :transifer_product_id,
                    :assignment_id,
                    :amount,
                    :unit_price,
                    :total_price
                )
            ";

            $itemStmt = $this->conn->prepare($itemSql);

            foreach ($items as $item) {
                // Insert into tbl_transifer_items
                $itemStmt->execute([
                    ':transifer_product_id' => $transferId,
                    ':assignment_id' => $item['assignment_id'],
                    ':amount' => $item['amount'],
                    ':unit_price' => $item['unit_price'],
                    ':total_price' => $item['total_price']
                ]);
            }

            $this->conn->commit();
            return true;

        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating transfer: " . $e->getMessage());
            return false;
        }
    }

    /**
         * Get all transfers with details
     * @param string|null $station_id Filter by station (shows transfers where station is source OR destination)
     */
    public function getAllTransfers($station_id = null)
    {
        try {
            $query = "
                SELECT 
                    tp.id,
                    tp.categories_id,
                    c.category_name,
                    tp.supporting_documents,
                    tp.additional_info,
                    tp.station_id,
                    s.location_name as station_name,
                    tp.warehouse_id,
                    w.location_name as warehouse_name,
                    tp.driver_id,
                    d.first_name as driver_first_name,
                    d.last_name as driver_last_name,
                    d.phone as driver_phone,
                    d.license_number as driver_license,
                    CONCAT(d.first_name, ' ', d.last_name) as driver_name,
                    tp.created_at,
                    tp.created_by,
                    CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name,
                    tp.received_at,
                    tp.received_by,
                    CONCAT(ur.first_name, ' ', ur.last_name) as received_by_name,
                    tp.receive_note,
                    tp.receive_comment,
                    tp.rejected_at,
                    tp.rejected_by,
                    CONCAT(uj.first_name, ' ', uj.last_name) as rejected_by_name,
                    tp.rejected_reason,
                    CASE 
                        WHEN tp.rejected_at IS NOT NULL THEN 'Rejected'
                        WHEN tp.received_at IS NOT NULL THEN 'Received'
                        ELSE 'Pending'
                    END as status
                FROM tbl_transifer_products tp
                LEFT JOIN tbl_categories c ON tp.categories_id = c.category_id
                LEFT JOIN tbl_location s ON tp.station_id = s.loc_id
                LEFT JOIN tbl_location w ON tp.warehouse_id = w.loc_id
                LEFT JOIN tbl_drivers d ON tp.driver_id = d.driver_id
                LEFT JOIN tbl_users uc ON tp.created_by = uc.user_id
                LEFT JOIN tbl_users ur ON tp.received_by = ur.user_id
                LEFT JOIN tbl_users uj ON tp.rejected_by = uj.user_id
            ";
            
            // Add filter if station_id provided (show transfers where user's station is source OR destination)
            if ($station_id) {
                $query .= " WHERE tp.station_id = :station_id OR tp.warehouse_id = :station_id2";
            }
            
            $query .= " ORDER BY tp.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($station_id) {
                $stmt->execute([':station_id' => $station_id, ':station_id2' => $station_id]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfers: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transfer items by transfer ID
     */
    public function getTransferItems(string $transfer_id)
    {
        try {
            $query = "
                SELECT 
                    ti.id,
                    ti.transifer_product_id,
                    ti.assignment_id,
                    ctu.type_id,
                    ct.type_name,
                    ctu.unit_id,
                    u.unit_name,
                    ti.amount,
                    ti.received_amount,
                    ti.unit_price,
                    ti.total_price,
                    ti.item_status,
                    ti.received_at,
                    ti.received_by,
                    CONCAT(ur.first_name, ' ', ur.last_name) as received_by_name
                FROM tbl_transifer_items ti
                LEFT JOIN tbl_category_type_units ctu ON ti.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                LEFT JOIN tbl_users ur ON ti.received_by = ur.user_id
                WHERE ti.transifer_product_id = :transfer_id
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':transfer_id' => $transfer_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfer items: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Receive transfer items (supports partial receiving)
     * @param string $transfer_id Transfer ID
     * @param string $received_by User ID who is receiving
     * @param array $items Array of items to receive: [['item_id' => x, 'received_amount' => y], ...]
     * @param string $receive_note Optional document path
     * @param string $receive_comment Optional comment
     */
    public function receiveTransfer(string $transfer_id, string $received_by, array $items = [], string $receive_note = '', string $receive_comment = ''): bool
    {
        try {
            $this->conn->beginTransaction();

            // Get transfer details (source station and destination)
            $transferQuery = "SELECT station_id, warehouse_id, status FROM tbl_transifer_products WHERE id = :transfer_id";
            $transferStmt = $this->conn->prepare($transferQuery);
            $transferStmt->execute([':transfer_id' => $transfer_id]);
            $transfer = $transferStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$transfer) {
                $this->conn->rollBack();
                return false;
            }

            // Check if transfer is already fully received or rejected
            if ($transfer['status'] === 'Received' || $transfer['status'] === 'Rejected') {
                $this->conn->rollBack();
                return false;
            }

            $source_station_id = $transfer['station_id'];  // Where product is FROM (sup_id)
            $destination_id = $transfer['warehouse_id'];    // Where product is TO (loc_id)

            // Prepare statements
            $updateItemSql = "
                UPDATE tbl_transifer_items 
                SET received_amount = :received_amount,
                    item_status = :item_status,
                    received_at = CURRENT_TIMESTAMP,
                    received_by = :received_by
                WHERE id = :item_id AND transifer_product_id = :transfer_id
            ";
            $updateItemStmt = $this->conn->prepare($updateItemSql);

            // Get item details query
            $getItemSql = "SELECT assignment_id, amount, unit_price FROM tbl_transifer_items WHERE id = :item_id";
            $getItemStmt = $this->conn->prepare($getItemSql);

            // Prepare insert for tbl_stock_details
            $stockDetailSql = "
                INSERT INTO tbl_stock_details (
                    assignment_id,
                    sup_id,
                    quantity,
                    unit_price,
                    total_price,
                    loc_id,
                    user_id,
                    record_type
                ) VALUES (
                    :assignment_id,
                    :sup_id,
                    :quantity,
                    :unit_price,
                    :total_price,
                    :loc_id,
                    :user_id,
                    'transfer'
                )
            ";
            $stockDetailStmt = $this->conn->prepare($stockDetailSql);

            // Process each selected item
            foreach ($items as $itemData) {
                $item_id = $itemData['item_id'];
                $received_amount = (float)$itemData['received_amount'];

                // Get item details
                $getItemStmt->execute([':item_id' => $item_id]);
                $item = $getItemStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$item) continue;

                $assignment_id = $item['assignment_id'];
                $sent_amount = (float)$item['amount'];
                $unit_price = (float)$item['unit_price'];
                
                // Item status is always 'received' - items can only be received once
                // Note: received_amount can be less than, equal to, or greater than sent_amount
                $item_status = 'received';

                // Calculate total price for received amount
                $received_total_price = $received_amount * $unit_price;

                // Update item with received amount and status
                $updateItemStmt->execute([
                    ':received_amount' => $received_amount,
                    ':item_status' => $item_status,
                    ':received_by' => $received_by,
                    ':item_id' => $item_id,
                    ':transfer_id' => $transfer_id
                ]);

                // Insert into tbl_stock_details (only for received amount)
                $stockDetailStmt->execute([
                    ':assignment_id' => $assignment_id,
                    ':sup_id' => $source_station_id,
                    ':quantity' => $received_amount,
                    ':unit_price' => $unit_price,
                    ':total_price' => $received_total_price,
                    ':loc_id' => $destination_id,
                    ':user_id' => $received_by
                ]);

                // Update stock summary at destination
                $this->updateStockSummary($destination_id, $assignment_id, $source_station_id, $received_amount);
            }

            // Determine overall transfer status
            $statusCheckQuery = "
                SELECT 
                    COUNT(*) as total_items,
                    SUM(CASE WHEN item_status = 'received' THEN 1 ELSE 0 END) as received_items,
                    SUM(CASE WHEN item_status = 'pending' THEN 1 ELSE 0 END) as pending_items
                FROM tbl_transifer_items 
                WHERE transifer_product_id = :transfer_id
            ";
            $statusCheckStmt = $this->conn->prepare($statusCheckQuery);
            $statusCheckStmt->execute([':transfer_id' => $transfer_id]);
            $statusResult = $statusCheckStmt->fetch(\PDO::FETCH_ASSOC);

            $totalItems = (int)$statusResult['total_items'];
            $receivedItems = (int)$statusResult['received_items'];
            $pendingItems = (int)$statusResult['pending_items'];

            // Determine overall status
            $overallStatus = 'Pending';
            if ($pendingItems === 0) {
                $overallStatus = 'Received'; // All items received
            } elseif ($receivedItems > 0) {
                $overallStatus = 'Partial'; // Some items received, some pending
            }

            // Update transfer status and receive info
            $sql = "
                UPDATE tbl_transifer_products 
                SET status = :status,
                    received_at = CASE WHEN :status2 = 'Received' THEN CURRENT_TIMESTAMP ELSE received_at END,
                    received_by = CASE WHEN received_by IS NULL THEN :received_by ELSE received_by END,
                    receive_note = CASE WHEN :receive_note != '' THEN :receive_note2 ELSE receive_note END,
                    receive_comment = CASE WHEN :receive_comment != '' THEN :receive_comment2 ELSE receive_comment END
                WHERE id = :transfer_id AND rejected_at IS NULL
            ";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':status' => $overallStatus,
                ':status2' => $overallStatus,
                ':transfer_id' => $transfer_id,
                ':received_by' => $received_by,
                ':receive_note' => $receive_note,
                ':receive_note2' => $receive_note,
                ':receive_comment' => $receive_comment,
                ':receive_comment2' => $receive_comment
            ]);

            if ($result) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error receiving transfer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper function to update stock summary
     */
    private function updateStockSummary(string $loc_id, string $assignment_id, string $sup_id, float $amount): bool
    {
        try {
            // Check if record exists
            $checkQuery = "
                SELECT stock_summary_id, total_quantity 
                FROM tbl_stock_summary 
                WHERE loc_id = :loc_id 
                  AND assignment_id = :assignment_id
                  AND sup_id = :sup_id
                  AND sup_type = 'station'
            ";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([
                ':loc_id' => $loc_id,
                ':assignment_id' => $assignment_id,
                ':sup_id' => $sup_id
            ]);
            $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing record
                $updateQuery = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity + :amount,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE stock_summary_id = :stock_summary_id
                ";
                $updateStmt = $this->conn->prepare($updateQuery);
                return $updateStmt->execute([
                    ':amount' => $amount,
                    ':stock_summary_id' => $existing['stock_summary_id']
                ]);
            } else {
                // Insert new record
                $insertQuery = "
                    INSERT INTO tbl_stock_summary (loc_id, assignment_id, sup_id, sup_type, total_quantity)
                    VALUES (:loc_id, :assignment_id, :sup_id, 'station', :amount)
                ";
                $insertStmt = $this->conn->prepare($insertQuery);
                return $insertStmt->execute([
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $assignment_id,
                    ':sup_id' => $sup_id,
                    ':amount' => $amount
                ]);
            }
        } catch (\PDOException $e) {
            error_log("Error updating stock summary: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject transfer
     */
    public function rejectTransfer(string $transfer_id, string $rejected_by, string $reason): bool
    {
        try {
            $sql = "
                UPDATE tbl_transifer_products 
                SET rejected_at = CURRENT_TIMESTAMP,
                    rejected_by = :rejected_by,
                    rejected_reason = :rejected_reason
                WHERE id = :transfer_id AND received_at IS NULL AND rejected_at IS NULL
            ";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':transfer_id' => $transfer_id,
                ':rejected_by' => $rejected_by,
                ':rejected_reason' => $reason
            ]);
        } catch (\PDOException $e) {
            error_log("Error rejecting transfer: " . $e->getMessage());
            return false;
        }
    }

    /**
         * Get transfer by ID
     */
    public function getTransferById(string $transfer_id)
    {
        try {
            $query = "
                SELECT 
                    tp.*,
                    c.category_name,
                    s.location_name as station_name,
                    w.location_name as warehouse_name,
                    d.first_name as driver_first_name,
                    d.last_name as driver_last_name,
                    d.phone as driver_phone,
                    d.license_number as driver_license,
                    CONCAT(d.first_name, ' ', d.last_name) as driver_name,
                    CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name,
                    CONCAT(ur.first_name, ' ', ur.last_name) as received_by_name,
                    CONCAT(uj.first_name, ' ', uj.last_name) as rejected_by_name
                FROM tbl_transifer_products tp
                LEFT JOIN tbl_categories c ON tp.categories_id = c.category_id
                LEFT JOIN tbl_location s ON tp.station_id = s.loc_id
                LEFT JOIN tbl_location w ON tp.warehouse_id = w.loc_id
                LEFT JOIN tbl_drivers d ON tp.driver_id = d.driver_id
                LEFT JOIN tbl_users uc ON tp.created_by = uc.user_id
                LEFT JOIN tbl_users ur ON tp.received_by = ur.user_id
                LEFT JOIN tbl_users uj ON tp.rejected_by = uj.user_id
                WHERE tp.id = :transfer_id
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':transfer_id' => $transfer_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching transfer: " . $e->getMessage());
            return false;
        }
    }
}
