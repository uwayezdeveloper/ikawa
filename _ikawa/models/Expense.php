<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Expense
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllExpenses()
    {
        try {
            $query = 'SELECT e.*, c.categ_name 
                      FROM tbl_expenses e
                      LEFT JOIN tbl_expensecategories c ON e.categ_id = c.categ_id
                      WHERE e.expense_status = 1 
                      ORDER BY e.expense_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getExpenseById($expense_id)
    {
        try {
            $query = 'SELECT * FROM tbl_expenses 
                      WHERE expense_id = :expense_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['expense_id' => $expense_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function expenseExists($expense_name)
    {
        try {
            $query = 'SELECT expense_name FROM tbl_expenses WHERE expense_name = :expense_name LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['expense_name' => $expense_name]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['expense_name'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function createExpense($data)
    {
        try {
            $query = 'INSERT INTO tbl_expenses (categ_id, expense_name, description, expense_status) 
                      VALUES (:categ_id, :expense_name, :description, 1)';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function updateExpense($data)
    {
        try {
            $query = 'UPDATE tbl_expenses 
                      SET categ_id = :categ_id,
                          expense_name = :expense_name,
                          description = :description 
                      WHERE expense_id = :expense_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function deleteExpense($expense_id)
    {
        try {
            $query = 'UPDATE tbl_expenses SET expense_status = 0 WHERE expense_id = :expense_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['expense_id' => $expense_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getExpensesByCategory($categ_id)
    {
        try {
            $query = 'SELECT e.*, c.categ_name 
                      FROM tbl_expenses e
                      LEFT JOIN tbl_expensecategories c ON e.categ_id = c.categ_id
                      WHERE e.categ_id = :categ_id AND e.expense_status = 1 
                      ORDER BY e.expense_name ASC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['categ_id' => $categ_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }
}
