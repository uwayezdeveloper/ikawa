<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Category
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
            $query = 'SELECT * FROM tbl_categories ORDER BY created_at DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $category_name): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_categories WHERE category_name = :category_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':category_name' => $category_name]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking category existence: " . $e->getMessage());
            return false;
        }
    }

    public function createCategory(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_categories (
                    category_name,
                    description,
                    status
                ) VALUES (
                    :category_name,
                    :description,
                    :status
                )
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':category_name' => $data['category_name'],
                ':description' => $data['description'],
                ':status' => $data['status']
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }

    public function existsUpdate(string $category_name, string $category_id): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM tbl_categories
                WHERE category_name = :category_name 
                AND category_id != :category_id
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':category_name' => $category_name,
                ':category_id' => $category_id
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking category update: " . $e->getMessage());
            return false;
        }
    }

    public function updateCategory(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_categories 
                SET category_name = :category_name,
                    description = :description,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE category_id = :category_id
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':category_name' => $data['category_name'],
                ':description' => $data['description'],
                ':status' => $data['status'],
                ':category_id' => $data['category_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory(string $category_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_categories WHERE category_id = :category_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':category_id' => $category_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    public function getCategoryById(string $category_id)
    {
        try {
            $sql = "SELECT * FROM tbl_categories WHERE category_id = :category_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':category_id' => $category_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching category by ID: " . $e->getMessage());
            return false;
        }
    }
}
