<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Class
 * Handles database connections and operations
 */
class Database
{
    protected static ?PDO $instance = null;

    /**
     * Get database connection instance (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];
                
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die("Database connection failed: " . $e->getMessage());
                } else {
                    die("Database connection failed. Please try again later.");
                }
            }
        }
        
        return self::$instance;
    }

    /**
     * Execute a query with parameters
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get all results
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Get single result
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Insert data and return last insert ID
     */
    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        self::query($sql, $data);
        
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Update data
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = :$column";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
        $stmt = self::query($sql, array_merge($data, $whereParams));
        
        return $stmt->rowCount();
    }

    /**
     * Delete data
     */
    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = self::query($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }
}
