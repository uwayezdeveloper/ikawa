<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class ExpenseConsumer
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllConsumers()
    {
        try {
            $query = 'SELECT * FROM tbl_expenseconsumer 
                      WHERE sts = 1 
                      ORDER BY cons_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getConsumerById($cons_id)
    {
        try {
            $query = 'SELECT * FROM tbl_expenseconsumer 
                      WHERE cons_id = :cons_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['cons_id' => $cons_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function consumerExists($cons_name, $phone)
    {
        try {
            $query = 'SELECT cons_id FROM tbl_expenseconsumer 
                      WHERE (cons_name = :cons_name OR phone = :phone) AND sts = 1 
                      LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['cons_name' => $cons_name, 'phone' => $phone]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['cons_id'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function isConsumerInUse($cons_id)
    {
        try {
            $query = 'SELECT COUNT(*) as count FROM tbl_expenseconsume 
                      WHERE payer_name = :cons_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['cons_id' => $cons_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result && $result['count'] > 0;
        } catch (\PDOException $e) {
            return true; // Safe default - prevent deletion if there's an error
        }
    }

    public function createConsumer($data)
    {
        try {
            $query = 'INSERT INTO tbl_expenseconsumer (cons_name, phone, sts) 
                      VALUES (:cons_name, :phone, 1)';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function updateConsumer($data)
    {
        try {
            $query = 'UPDATE tbl_expenseconsumer 
                      SET cons_name = :cons_name, 
                          phone = :phone 
                      WHERE cons_id = :cons_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function deleteConsumer($cons_id)
    {
        try {
            // Soft delete - set sts to 0
            $query = 'UPDATE tbl_expenseconsumer 
                      SET sts = 0 
                      WHERE cons_id = :cons_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['cons_id' => $cons_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }
}
