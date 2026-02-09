<?php
/**
 * Database Setup Script
 * Run this script to create the database and tables
 * 
 * Usage: php setup.php
 * Or visit: http://localhost/gihangacoffee/setup.php
 */

// Prevent accidental execution in production
$allowedHosts = ['localhost', '127.0.0.1', '::1'];
$host = $_SERVER['HTTP_HOST'] ?? php_sapi_name();

// Define base path and load config
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';

// Output header
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Gihanga Coffee - Database Setup</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body class="bg-light"><div class="container py-5"><div class="card"><div class="card-body">';
    echo '<h2 class="card-title mb-4">🗄️ Gihanga Coffee - Database Setup</h2>';
}

function output($message, $type = 'info') {
    $colors = [
        'success' => 'text-success',
        'error' => 'text-danger',
        'info' => 'text-info',
        'warning' => 'text-warning'
    ];
    $color = $colors[$type] ?? 'text-dark';
    
    if (php_sapi_name() === 'cli') {
        echo "[$type] $message\n";
    } else {
        echo "<p class='$color mb-2'>$message</p>";
    }
}

try {
    // Connect to MySQL without selecting a database
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    output("✅ Connected to MySQL server successfully", 'success');
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    output("✅ Database '" . DB_NAME . "' created or already exists", 'success');
    
    // Select the database
    $pdo->exec("USE `" . DB_NAME . "`");
    output("✅ Selected database: " . DB_NAME, 'success');
    
    // Create Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(36) NOT NULL UNIQUE,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NULL,
            avatar VARCHAR(255) DEFAULT 'default-avatar.png',
            role ENUM('admin', 'manager', 'staff', 'customer') DEFAULT 'customer',
            status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
            email_verified_at TIMESTAMP NULL,
            last_login_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    output("✅ Users table created", 'success');
    
    // Create JWT Tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jwt_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash VARCHAR(64) NOT NULL UNIQUE,
            type ENUM('access', 'refresh') DEFAULT 'access',
            expires_at TIMESTAMP NOT NULL,
            revoked_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token_hash (token_hash),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    output("✅ JWT Tokens table created", 'success');
    
    // Create Password Resets table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    output("✅ Password Resets table created", 'success');
    
    // Create User Activity Logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(100) NOT NULL,
            description TEXT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    output("✅ User Activity Logs table created", 'success');
    
    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@gihangacoffee.com'");
    $adminExists = $stmt->fetchColumn() > 0;
    
    if (!$adminExists) {
        // Create default admin user (password: Admin@123)
        $adminPassword = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
        $adminUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, first_name, last_name, email, password, role, status, email_verified_at) 
            VALUES (:uuid, 'Admin', 'User', 'admin@gihangacoffee.com', :password, 'admin', 'active', NOW())
        ");
        $stmt->execute([
            'uuid' => $adminUuid,
            'password' => $adminPassword
        ]);
        output("✅ Default admin user created", 'success');
        output("📧 Email: admin@gihangacoffee.com", 'info');
        output("🔑 Password: Admin@123", 'info');
    } else {
        output("ℹ️ Admin user already exists", 'warning');
    }
    
    echo "\n";
    output("🎉 Database setup completed successfully!", 'success');
    output("🌐 You can now access the application at: " . APP_URL, 'info');
    output("🔐 Login with: admin@gihangacoffee.com / Admin@123", 'info');
    
} catch (PDOException $e) {
    output("❌ Database Error: " . $e->getMessage(), 'error');
}

if (php_sapi_name() !== 'cli') {
    echo '<hr class="my-4">';
    echo '<a href="' . APP_URL . '/login" class="btn btn-primary">Go to Login</a>';
    echo '</div></div></div></body></html>';
}
