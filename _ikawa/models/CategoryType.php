<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class CategoryType
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllCategoryTypes()
    {
        try {
            $query = '
                SELECT ct.*, c.category_name 
                FROM tbl_category_types ct
                LEFT JOIN tbl_categories c ON ct.category_id = c.category_id
                ORDER BY ct.created_at DESC
            ';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching category types: " . $e->getMessage());
            return false;
        }
    }

    public function getActiveCategories()
    {
        try {
            $query = "SELECT category_id, category_name FROM tbl_categories WHERE status = 'active' ORDER BY category_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching active categories: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $type_name, string $category_id): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_category_types WHERE type_name = :type_name AND category_id = :category_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_name' => $type_name,
                ':category_id' => $category_id
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking category type existence: " . $e->getMessage());
            return false;
        }
    }

    public function createCategoryType(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_category_types (
                    category_id,
                    type_name,
                    description,
                    status
                ) VALUES (
                    :category_id,
                    :type_name,
                    :description,
                    :status
                )
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':category_id' => $data['category_id'],
                ':type_name' => $data['type_name'],
                ':description' => $data['description'],
                ':status' => $data['status']
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating category type: " . $e->getMessage());
            return false;
        }
    }

    public function existsUpdate(string $type_name, string $category_id, string $type_id): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM tbl_category_types
                WHERE type_name = :type_name 
                AND category_id = :category_id
                AND type_id != :type_id
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_name' => $type_name,
                ':category_id' => $category_id,
                ':type_id' => $type_id
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking category type update: " . $e->getMessage());
            return false;
        }
    }

    public function updateCategoryType(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_category_types 
                SET category_id = :category_id,
                    type_name = :type_name,
                    description = :description,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE type_id = :type_id
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':category_id' => $data['category_id'],
                ':type_name' => $data['type_name'],
                ':description' => $data['description'],
                ':status' => $data['status'],
                ':type_id' => $data['type_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating category type: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategoryType(string $type_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_category_types WHERE type_id = :type_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':type_id' => $type_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting category type: " . $e->getMessage());
            return false;
        }
    }
}
