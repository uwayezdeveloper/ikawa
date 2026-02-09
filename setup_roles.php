<?php
/**
 * Add Roles Table to Database
 * Run this once to add the roles table
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>Setting up Roles Table</h2>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<p style='color: green;'>✓ Connected to database: " . DB_NAME . "</p>";
    
    // Check if roles table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'roles'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: orange;'>⚠ Roles table already exists</p>";
    } else {
        // Create roles table
        $sql = "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Roles table created</p>";
    }
    
    // Check if default roles exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Insert default roles
        $sql = "INSERT INTO roles (name, description, status) VALUES
            ('Administrator', 'Full system access with all permissions', 'active'),
            ('Manager', 'Can manage staff and view reports', 'active'),
            ('Staff', 'Basic operational access', 'active'),
            ('Customer', 'Customer account access', 'active')";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Default roles inserted</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Roles already exist ($count roles found)</p>";
    }
    
    // Show current roles
    echo "<h3>Current Roles:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Status</th><th>Created</th></tr>";
    
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
    while ($role = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>{$role['id']}</td>";
        echo "<td>{$role['name']}</td>";
        echo "<td>{$role['description']}</td>";
        echo "<td>{$role['status']}</td>";
        echo "<td>{$role['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p style='color: green; font-weight: bold;'>✓ Setup complete!</p>";
    echo "<p><a href='" . APP_URL . "/roles'>Go to Roles Management →</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
