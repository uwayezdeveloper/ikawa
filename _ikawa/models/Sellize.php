<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Sellize
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllSallize()
    {
        try {
            $query = 'SELECT * FROM tbl_sallize ORDER BY created_at DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching sallize: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $sallize_name): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_sallize WHERE sallize_name = :sallize_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':sallize_name' => $sallize_name]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking sallize existence: " . $e->getMessage());
            return false;
        }
    }

    public function createSallize(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_sallize (
                    sallize_name,
                    description,
                    status
                ) VALUES (
                    :sallize_name,
                    :description,
                    :status
                )
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':sallize_name' => $data['sallize_name'],
                ':description' => $data['description'],
                ':status' => $data['status']
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating sallize: " . $e->getMessage());
            return false;
        }
    }

    public function existsUpdate(string $sallize_name, string $sallize_id): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM tbl_sallize
                WHERE sallize_name = :sallize_name 
                AND sallize_id != :sallize_id
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':sallize_name' => $sallize_name,
                ':sallize_id' => $sallize_id
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking sallize update: " . $e->getMessage());
            return false;
        }
    }

    public function updateSallize(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_sallize 
                SET sallize_name = :sallize_name,
                    description = :description,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE sallize_id = :sallize_id
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':sallize_name' => $data['sallize_name'],
                ':description' => $data['description'],
                ':status' => $data['status'],
                ':sallize_id' => $data['sallize_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating sallize: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSallize(string $sallize_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_sallize WHERE sallize_id = :sallize_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':sallize_id' => $sallize_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting sallize: " . $e->getMessage());
            return false;
        }
    }

    public function getSallizeById(string $sallize_id)
    {
        try {
            $sql = "SELECT * FROM tbl_sallize WHERE sallize_id = :sallize_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':sallize_id' => $sallize_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching sallize by ID: " . $e->getMessage());
            return false;
        }
    }
}
