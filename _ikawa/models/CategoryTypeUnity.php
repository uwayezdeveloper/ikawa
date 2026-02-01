<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class CategoryTypeUnity
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllAssignments()
    {
        try {
            $query = '
                SELECT 
                    ctu.assignment_id,
                    ctu.type_id,
                    ctu.unit_id,
                    ctu.status,
                    c.category_name,
                    ct.type_name,
                    u.unit_name,
                    ctu.created_at
                FROM tbl_category_type_units ctu
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_categories c ON ct.category_id = c.category_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                ORDER BY ctu.created_at DESC
            ';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching assignments: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $type_id, string $unit_id): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_category_type_units WHERE type_id = :type_id AND unit_id = :unit_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_id' => $type_id,
                ':unit_id' => $unit_id
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking assignment existence: " . $e->getMessage());
            return false;
        }
    }

    public function existsUpdate(string $type_id, string $unit_id, string $assignment_id): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM tbl_category_type_units
                WHERE type_id = :type_id 
                AND unit_id = :unit_id
                AND assignment_id != :assignment_id
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_id' => $type_id,
                ':unit_id' => $unit_id,
                ':assignment_id' => $assignment_id
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking assignment update: " . $e->getMessage());
            return false;
        }
    }

    public function createAssignment(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_category_type_units (type_id, unit_id, status) 
                VALUES (:type_id, :unit_id, 'active')
            ";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':type_id' => $data['type_id'],
                ':unit_id' => $data['unit_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating assignment: " . $e->getMessage());
            return false;
        }
    }

    public function updateAssignment(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_category_type_units 
                SET type_id = :type_id,
                    unit_id = :unit_id,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE assignment_id = :assignment_id
            ";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':type_id' => $data['type_id'],
                ':unit_id' => $data['unit_id'],
                ':status' => $data['status'],
                ':assignment_id' => $data['assignment_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating assignment: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAssignment(string $assignment_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_category_type_units WHERE assignment_id = :assignment_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':assignment_id' => $assignment_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting assignment: " . $e->getMessage());
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
            error_log("Error fetching units by type: " . $e->getMessage());
            return false;
        }
    }

    public function getTypesWithUnits()
    {
        try {
            $sql = "
                SELECT DISTINCT ct.type_id, ct.type_name, c.category_name
                FROM tbl_category_types ct
                INNER JOIN tbl_category_type_units ctu ON ct.type_id = ctu.type_id
                LEFT JOIN tbl_categories c ON ct.category_id = c.category_id
                WHERE ctu.status = 'active'
                ORDER BY c.category_name, ct.type_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching types with units: " . $e->getMessage());
            return false;
        }
    }

    public function getTypesByCategory(string $category_id)
    {
        try {
            $sql = "
                SELECT DISTINCT ct.type_id, ct.type_name
                FROM tbl_category_types ct
                INNER JOIN tbl_category_type_units ctu ON ct.type_id = ctu.type_id
                WHERE ct.category_id = :category_id 
                AND ctu.status = 'active'
                ORDER BY ct.type_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':category_id' => $category_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching types by category: " . $e->getMessage());
            return false;
        }
    }

    public function getTypeUnityByCategory(string $category_id)
    {
        try {
            $sql = "
                SELECT 
                    ct.type_id, 
                    ct.type_name,
                    u.unit_id,
                    u.unit_name,
                    CONCAT(ct.type_name, ' / ', u.unit_name) as type_unit_name
                FROM tbl_category_types ct
                INNER JOIN tbl_category_type_units ctu ON ct.type_id = ctu.type_id
                INNER JOIN tbl_units u ON ctu.unit_id = u.unit_id
                WHERE ct.category_id = :category_id 
                AND ctu.status = 'active'
                ORDER BY ct.type_name, u.unit_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':category_id' => $category_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching type-unity by category: " . $e->getMessage());
            return false;
        }
    }
}
