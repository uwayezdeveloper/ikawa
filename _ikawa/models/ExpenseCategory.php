<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class ExpenseCategory
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllCategories()
    {
        try {
            $query = 'SELECT * FROM tbl_expensecategories 
                      WHERE status = 1 
                      ORDER BY categ_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getCategoryById($categ_id)
    {
        try {
            $query = 'SELECT * FROM tbl_expensecategories 
                      WHERE categ_id = :categ_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['categ_id' => $categ_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function categoryExists($categ_name)
    {
        try {
            $query = 'SELECT categ_name FROM tbl_expensecategories 
                      WHERE categ_name = :categ_name AND status = 1 
                      LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['categ_name' => $categ_name]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['categ_name'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function isCategoryInUse($categ_id)
    {
        try {
            $query = 'SELECT COUNT(*) as count FROM tbl_expenses 
                      WHERE categ_id = :categ_id AND expense_status = 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['categ_id' => $categ_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result && $result['count'] > 0;
        } catch (\PDOException $e) {
            return true; // Safe default - prevent deletion if there's an error
        }
    }

    public function createCategory($data)
    {
        try {
            $query = 'INSERT INTO tbl_expensecategories (categ_name, description, status) 
                      VALUES (:categ_name, :description, 1)';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function updateCategory($data)
    {
        try {
            $query = 'UPDATE tbl_expensecategories 
                      SET categ_name = :categ_name, 
                          description = :description 
                      WHERE categ_id = :categ_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function deleteCategory($categ_id)
    {
        try {
            // Soft delete - set status to 0
            $query = 'UPDATE tbl_expensecategories 
                      SET status = 0 
                      WHERE categ_id = :categ_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['categ_id' => $categ_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }
}
