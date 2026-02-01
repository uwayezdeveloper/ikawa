<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Unity
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllUnity()
    {
        try {
            $query = 'SELECT unit_id, unit_name FROM tbl_units ORDER BY unit_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching unity: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $unit_name): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_units WHERE unit_name = :unit_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':unit_name' => $unit_name]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking unity existence: " . $e->getMessage());
            return false;
        }
    }

    public function createUnity(string $unit_name): bool
    {
        try {
            $sql = "INSERT INTO tbl_units (unit_name) VALUES (:unit_name)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':unit_name' => $unit_name]);
        } catch (\PDOException $e) {
            error_log("Error creating unity: " . $e->getMessage());
            return false;
        }
    }

    public function existsUpdate(string $unit_name, string $unit_id): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM tbl_units
                WHERE unit_name = :unit_name 
                AND unit_id != :unit_id
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':unit_name' => $unit_name,
                ':unit_id' => $unit_id
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error checking unity update: " . $e->getMessage());
            return false;
        }
    }

    public function updateUnity(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_units 
                SET unit_name = :unit_name
                WHERE unit_id = :unit_id
            ";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':unit_name' => $data['unit_name'],
                ':unit_id' => $data['unit_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating unity: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUnity(string $unit_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_units WHERE unit_id = :unit_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':unit_id' => $unit_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting unity: " . $e->getMessage());
            return false;
        }
    }
}
