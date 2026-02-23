<?php
/**
 * Check Accounts Table Structure
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h2>Accounts Table Structure Check</h2>';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<p style="color:green">✅ Database connection successful!</p>';
    
    // Check if accounts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'accounts'");
    if ($stmt->rowCount() > 0) {
        echo '<p style="color:green">✅ accounts table exists</p>';
        
        // Show table structure
        echo '<h3>Accounts Table Structure:</h3>';
        $stmt = $pdo->query("DESCRIBE accounts");
        $columns = $stmt->fetchAll();
        
        echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
        echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
        
        $statusColumn = null;
        foreach ($columns as $column) {
            echo '<tr>';
            echo '<td><strong>' . $column['Field'] . '</strong></td>';
            echo '<td>' . $column['Type'] . '</td>';
            echo '<td>' . $column['Null'] . '</td>';
            echo '<td>' . $column['Key'] . '</td>';
            echo '<td>' . ($column['Default'] ?? 'NULL') . '</td>';
            echo '</tr>';
            
            // Look for status-related columns
            if (in_array(strtolower($column['Field']), ['status', 'is_active', 'active', 'enabled'])) {
                $statusColumn = $column['Field'];
            }
        }
        echo '</table>';
        
        // Show sample data
        echo '<h3>Sample Accounts Data:</h3>';
        $stmt = $pdo->query("SELECT * FROM accounts LIMIT 5");
        $accounts = $stmt->fetchAll();
        
        if ($accounts) {
            echo '<table border="1" style="border-collapse: collapse; margin: 10px 0; font-size: 12px;">';
            echo '<tr>';
            foreach (array_keys($accounts[0]) as $key) {
                echo '<th>' . $key . '</th>';
            }
            echo '</tr>';
            
            foreach ($accounts as $account) {
                echo '<tr>';
                foreach ($account as $value) {
                    echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }
        
        // Show correct query
        echo '<h3>Correct Query for Active Accounts:</h3>';
        if ($statusColumn) {
            echo '<p style="color:green">Status column found: <strong>' . $statusColumn . '</strong></p>';
            
            // Try to determine active status values
            $stmt = $pdo->prepare("SELECT DISTINCT $statusColumn FROM accounts");
            $stmt->execute();
            $statusValues = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<p>Possible status values: <strong>' . implode(', ', $statusValues) . '</strong></p>';
            
            // Guess the active condition
            $activeCondition = "$statusColumn = 'active'";
            if (in_array('1', $statusValues)) {
                $activeCondition = "$statusColumn = '1'";
            } elseif (in_array(1, $statusValues)) {
                $activeCondition = "$statusColumn = 1";
            }
            
            echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">';
            echo '<strong>Recommended Query:</strong><br>';
            echo '<code>SELECT * FROM accounts WHERE ' . $activeCondition . ' AND location_id = ?</code>';
            echo '</div>';
            
        } else {
            echo '<p style="color:orange">⚠️ No obvious status column found. Using all records.</p>';
            echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">';
            echo '<strong>Fallback Query:</strong><br>';
            echo '<code>SELECT * FROM accounts WHERE location_id = ?</code>';
            echo '</div>';
        }
        
    } else {
        echo '<p style="color:red">❌ accounts table does NOT exist</p>';
    }
    
} catch (Exception $e) {
    echo '<p style="color:red">❌ Error: ' . $e->getMessage() . '</p>';
}
?>

<style>
body { font-family: Arial; margin: 20px; }
table { border-collapse: collapse; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
code { background: #e8e8e8; padding: 2px 4px; font-family: monospace; }
</style>