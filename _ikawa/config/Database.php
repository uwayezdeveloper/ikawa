<?php
namespace Config;

use PDO;
use PDOException;

class Database
 {
    // db variables
    private $host = 'localhost';
    private $db_name = 'tabrw_ikawa_db';
    private $username = 'root';
    private $password = '';
    public $conn;

    // database connection

    public function getConnection()
 {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password
            );

            // Set error mode to exceptions
            $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        } catch ( PDOException $exception ) {
            echo 'Connection error: ' . $exception->getMessage();
            exit;
        }

        return $this->conn;
    }
}
