<?php
/**
 * Database Test Script
 * Tests database connection and verifies admin user
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h2>Database Connection Test</h2>';

try {
    // Test connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<p style="color:green">✅ Database connection successful!</p>';
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo '<p style="color:green">✅ Users table exists</p>';
        
        // Get admin user
        $stmt = $pdo->query("SELECT * FROM users WHERE email = 'admin@gihangacoffee.com'");
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo '<p style="color:green">✅ Admin user found!</p>';
            echo '<h3>Admin User Details:</h3>';
            echo '<pre>';
            echo 'ID: ' . $admin['id'] . "\n";
            echo 'Email: ' . $admin['email'] . "\n";
            echo 'Name: ' . $admin['first_name'] . ' ' . $admin['last_name'] . "\n";
            echo 'Role: ' . $admin['role'] . "\n";
            echo 'Status: ' . $admin['status'] . "\n";
            echo 'Password Hash: ' . $admin['password'] . "\n";
            echo '</pre>';
            
            // Test password verification
            $testPassword = 'Admin@123';
            echo '<h3>Password Verification Test:</h3>';
            
            if (password_verify($testPassword, $admin['password'])) {
                echo '<p style="color:green">✅ Password "Admin@123" is CORRECT!</p>';
            } else {
                echo '<p style="color:red">❌ Password verification FAILED!</p>';
                echo '<p>The stored hash does not match "Admin@123"</p>';
                
                // Generate correct hash
                $correctHash = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                echo '<p>Generating new correct hash...</p>';
                
                // Update the password
                $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = 'admin@gihangacoffee.com'");
                $updateStmt->execute(['password' => $correctHash]);
                
                echo '<p style="color:green">✅ Password has been updated! Try logging in again.</p>';
            }
        } else {
            echo '<p style="color:red">❌ Admin user NOT found!</p>';
            echo '<p>Creating admin user...</p>';
            
            $password = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
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
                'uuid' => $uuid,
                'password' => $password
            ]);
            
            echo '<p style="color:green">✅ Admin user created!</p>';
        }
    } else {
        echo '<p style="color:red">❌ Users table does not exist!</p>';
        echo '<p><a href="setup.php">Run setup.php to create tables</a></p>';
    }
    
} catch (PDOException $e) {
    echo '<p style="color:red">❌ Database Error: ' . $e->getMessage() . '</p>';
}

echo '<hr>';
echo '<p><a href="' . APP_URL . '/login">Go to Login</a></p>';
