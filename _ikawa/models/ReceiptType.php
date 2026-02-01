<?php
require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class ReceiptType
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all active receipt types
     */
    public function getAllReceiptTypes()
    {
        try {
            $sql = "SELECT rec_id, rec_name, rec_desc, sts 
                    FROM tbl_receipttype 
                    WHERE sts = 1 
                    ORDER BY rec_name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching receipt types: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get receipt type by ID
     */
    public function getReceiptTypeById($rec_id)
    {
        try {
            $sql = "SELECT rec_id, rec_name, rec_desc, sts 
                    FROM tbl_receipttype 
                    WHERE rec_id = :rec_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':rec_id', $rec_id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching receipt type: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new receipt type
     */
    public function createReceiptType($data)
    {
        try {
            $sql = "INSERT INTO tbl_receipttype (rec_name, rec_desc, sts) 
                    VALUES (:rec_name, :rec_desc, :sts)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':rec_name', $data['rec_name']);
            $stmt->bindParam(':rec_desc', $data['rec_desc']);
            $stmt->bindValue(':sts', $data['sts'] ?? 1, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error creating receipt type: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update receipt type
     */
    public function updateReceiptType($data)
    {
        try {
            $sql = "UPDATE tbl_receipttype 
                    SET rec_name = :rec_name, 
                        rec_desc = :rec_desc, 
                        sts = :sts 
                    WHERE rec_id = :rec_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':rec_id', $data['rec_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':rec_name', $data['rec_name']);
            $stmt->bindParam(':rec_desc', $data['rec_desc']);
            $stmt->bindParam(':sts', $data['sts'], \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error updating receipt type: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete receipt type (soft delete by setting sts to 0)
     */
    public function deleteReceiptType($rec_id)
    {
        try {
            $sql = "UPDATE tbl_receipttype SET sts = 0 WHERE rec_id = :rec_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':rec_id', $rec_id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error deleting receipt type: " . $e->getMessage());
            return false;
        }
    }
}
