<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class ProductMixing
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'MIX';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $date . $random;
    }

    public function getAvailableStockForMixing(int $loc_id)
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
            error_log("Error fetching available stock for mixing: " . $e->getMessage());
            return false;
        }
    }

    public function createMixing(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $reference_no = $this->generateReferenceNo();
            $loc_id = $data['loc_id'];
            $user_id = $data['user_id'];
            $input_items = $data['input_items'];
            $output = $data['output'];
            $mixing_date = $data['mixing_date'];
            $notes = $data['notes'] ?? null;

            // Get output assignment_id
            $assignmentSql = "
                SELECT assignment_id 
                FROM tbl_category_type_units 
                WHERE type_id = :type_id AND unit_id = :unit_id AND status = 'active'
                LIMIT 1
            ";
            $assignmentStmt = $this->conn->prepare($assignmentSql);
            $assignmentStmt->execute([
                ':type_id' => $output['type_id'],
                ':unit_id' => $output['unit_id']
            ]);
            $outputAssignment = $assignmentStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$outputAssignment) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Invalid output product type/unit combination'];
            }

            $output_assignment_id = $outputAssignment['assignment_id'];

            // Calculate total input quantity
            $total_input_qty = 0;
            foreach ($input_items as $item) {
                $total_input_qty += floatval($item['quantity']);
            }

            // Insert mixing record
            $mixingSql = "
                INSERT INTO tbl_product_mixing 
                (reference_no, loc_id, user_id, output_assignment_id, output_quantity, total_input_quantity, mixing_date, notes, created_at)
                VALUES 
                (:reference_no, :loc_id, :user_id, :output_assignment_id, :output_quantity, :total_input_quantity, :mixing_date, :notes, CURRENT_TIMESTAMP)
            ";
            $mixingStmt = $this->conn->prepare($mixingSql);
            $mixingStmt->execute([
                ':reference_no' => $reference_no,
                ':loc_id' => $loc_id,
                ':user_id' => $user_id,
                ':output_assignment_id' => $output_assignment_id,
                ':output_quantity' => $output['quantity'],
                ':total_input_quantity' => $total_input_qty,
                ':mixing_date' => $mixing_date,
                ':notes' => $notes
            ]);

            $mixing_id = $this->conn->lastInsertId();

            // Process input items - deduct from stock
            foreach ($input_items as $item) {
                $assignment_id = $item['assignment_id'];
                $quantity = floatval($item['quantity']);

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
                    return ['success' => false, 'message' => 'Insufficient stock for one or more input items'];
                }

                // Insert mixing input detail
                $inputSql = "
                    INSERT INTO tbl_mixing_inputs 
                    (mixing_id, assignment_id, quantity, created_at)
                    VALUES 
                    (:mixing_id, :assignment_id, :quantity, CURRENT_TIMESTAMP)
                ";
                $inputStmt = $this->conn->prepare($inputSql);
                $inputStmt->execute([
                    ':mixing_id' => $mixing_id,
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
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $assignment_id
                ]);
            }

            // Add output to stock summary
            $checkOutputSql = "
                SELECT stock_summary_id, total_quantity 
                FROM tbl_stock_summary 
                WHERE loc_id = :loc_id AND assignment_id = :assignment_id
            ";
            $checkOutputStmt = $this->conn->prepare($checkOutputSql);
            $checkOutputStmt->execute([
                ':loc_id' => $loc_id,
                ':assignment_id' => $output_assignment_id
            ]);
            $existingOutput = $checkOutputStmt->fetch(\PDO::FETCH_ASSOC);

            if ($existingOutput) {
                $updateOutputSql = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity + :quantity, updated_at = CURRENT_TIMESTAMP
                    WHERE stock_summary_id = :stock_summary_id
                ";
                $updateOutputStmt = $this->conn->prepare($updateOutputSql);
                $updateOutputStmt->execute([
                    ':quantity' => $output['quantity'],
                    ':stock_summary_id' => $existingOutput['stock_summary_id']
                ]);
            } else {
                $insertOutputSql = "
                    INSERT INTO tbl_stock_summary 
                    (loc_id, assignment_id, total_quantity, created_at, updated_at)
                    VALUES 
                    (:loc_id, :assignment_id, :quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ";
                $insertOutputStmt = $this->conn->prepare($insertOutputSql);
                $insertOutputStmt->execute([
                    ':loc_id' => $loc_id,
                    ':assignment_id' => $output_assignment_id,
                    ':quantity' => $output['quantity']
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'reference_no' => $reference_no, 'mixing_id' => $mixing_id];
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating product mixing: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getMixingHistory(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    pm.mixing_id,
                    pm.reference_no,
                    pm.output_quantity,
                    pm.total_input_quantity,
                    pm.mixing_date,
                    pm.notes,
                    pm.created_at,
                    ct.type_name as output_type_name,
                    u.unit_name as output_unit_name,
                    usr.username as created_by,
                    (SELECT COUNT(*) FROM tbl_mixing_inputs WHERE mixing_id = pm.mixing_id) as input_count
                FROM tbl_product_mixing pm
                LEFT JOIN tbl_category_type_units ctu ON pm.output_assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                LEFT JOIN tbl_users usr ON pm.user_id = usr.user_id
                WHERE pm.loc_id = :loc_id
                ORDER BY pm.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching mixing history: " . $e->getMessage());
            return false;
        }
    }

    public function getMixingDetails(int $mixing_id)
    {
        try {
            // Get mixing record
            $mixingSql = "
                SELECT 
                    pm.mixing_id,
                    pm.reference_no,
                    pm.output_quantity,
                    pm.total_input_quantity,
                    pm.mixing_date,
                    pm.notes,
                    ct.type_name as output_type_name,
                    u.unit_name as output_unit_name
                FROM tbl_product_mixing pm
                LEFT JOIN tbl_category_type_units ctu ON pm.output_assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE pm.mixing_id = :mixing_id
            ";
            $mixingStmt = $this->conn->prepare($mixingSql);
            $mixingStmt->execute([':mixing_id' => $mixing_id]);
            $mixing = $mixingStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$mixing) {
                return false;
            }

            // Get input items
            $inputsSql = "
                SELECT 
                    mi.input_id,
                    mi.quantity,
                    ct.type_name,
                    u.unit_name
                FROM tbl_mixing_inputs mi
                LEFT JOIN tbl_category_type_units ctu ON mi.assignment_id = ctu.assignment_id
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE mi.mixing_id = :mixing_id
            ";
            $inputsStmt = $this->conn->prepare($inputsSql);
            $inputsStmt->execute([':mixing_id' => $mixing_id]);
            $inputs = $inputsStmt->fetchAll(\PDO::FETCH_ASSOC);

            $mixing['inputs'] = $inputs;
            return $mixing;
        } catch (\PDOException $e) {
            error_log("Error fetching mixing details: " . $e->getMessage());
            return false;
        }
    }
}
