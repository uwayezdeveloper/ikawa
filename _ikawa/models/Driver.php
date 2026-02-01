<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use \PDOException;

class Driver
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all drivers
     */
    public function getAllDrivers()
    {
        try {
            $query = "SELECT * FROM tbl_drivers ORDER BY first_name, last_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching drivers: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get driver by ID
     */
    public function getDriverById(string $driver_id)
    {
        try {
            $query = "SELECT * FROM tbl_drivers WHERE driver_id = :driver_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':driver_id' => $driver_id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching driver: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if driver exists by phone, email or license
     */
    public function exists(string $phone, string $email, string $license_number): ?string
    {
        $sql = "
            SELECT 
                CASE 
                    WHEN phone = :phone THEN 'phone'
                    WHEN email = :email THEN 'email'
                    WHEN license_number = :license_number THEN 'license_number'
                END AS field
            FROM tbl_drivers
            WHERE phone = :phone OR email = :email OR license_number = :license_number
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':phone' => $phone,
            ':email' => $email,
            ':license_number' => $license_number
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['field'] ?? null;
    }

    /**
     * Check if driver exists for update (excluding current record)
     */
    public function existsUpdate(string $phone, string $email, string $license_number, string $driver_id): ?string
    {
        $sql = "
            SELECT 
                CASE 
                    WHEN phone = :phone THEN 'phone'
                    WHEN email = :email THEN 'email'
                    WHEN license_number = :license_number THEN 'license_number'
                END AS field
            FROM tbl_drivers
            WHERE (phone = :phone OR email = :email OR license_number = :license_number)
            AND driver_id != :driver_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':phone' => $phone,
            ':email' => $email,
            ':license_number' => $license_number,
            ':driver_id' => $driver_id
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['field'] ?? null;
    }

    /**
     * Create new driver
     */
    public function createDriver(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_drivers (
                    first_name,
                    last_name,
                    phone,
                    email,
                    license_number
                ) VALUES (
                    :first_name,
                    :last_name,
                    :phone,
                    :email,
                    :license_number
                )
            ";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':license_number' => $data['license_number']
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating driver: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update driver
     */
    public function updateDriver(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_drivers 
                SET first_name = :first_name,
                    last_name = :last_name,
                    phone = :phone,
                    email = :email,
                    license_number = :license_number
                WHERE driver_id = :driver_id
            ";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':license_number' => $data['license_number'],
                ':driver_id' => $data['driver_id']
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating driver: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete driver
     */
    public function deleteDriver(string $driver_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_drivers WHERE driver_id = :driver_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':driver_id' => $driver_id]);
        } catch (\PDOException $e) {
            error_log("Error deleting driver: " . $e->getMessage());
            return false;
        }
    }
}
