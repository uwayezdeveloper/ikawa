<?php
/**
 * Verify Database Methods - https://gihangacoffe.itectab.rw/verify_database.php
 */

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Database Class Check</title>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .fail{color:red;} h2{border-bottom:2px solid #333;}</style>";
echo "</head><body>";
echo "<h1>🔍 Database Class Verification</h1><hr>";

// Load Database class
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

echo "<h2>Database Class Methods Check</h2>";

$requiredMethods = [
    'getInstance',
    'query',
    'fetchAll',
    'fetch',
    'insert',
    'update',
    'delete',
    'execute',        // ← CRITICAL: This is missing on online server
    'beginTransaction',
    'commit',
    'rollback'
];

$allOk = true;
foreach ($requiredMethods as $method) {
    if (method_exists('App\Core\Database', $method)) {
        echo "<p class='ok'>✓ $method() - EXISTS</p>";
    } else {
        echo "<p class='fail'>✗ $method() - MISSING ❌</p>";
        $allOk = false;
    }
}

echo "<hr>";

if ($allOk) {
    echo "<h2 class='ok'>✅ ALL METHODS EXIST</h2>";
    echo "<p>Database class is up to date.</p>";
} else {
    echo "<h2 class='fail'>❌ MISSING METHODS DETECTED</h2>";
    echo "<p><strong>ACTION REQUIRED:</strong> Upload the complete Database.php file from your local machine.</p>";
    echo "<p><strong>File path:</strong> app/Core/Database.php</p>";
    echo "<hr>";
    echo "<h3>Where Database::execute() is used:</h3>";
    echo "<ul>";
    echo "<li>app/Models/Production.php (production completion)</li>";
    echo "<li>app/Models/StockSummary.php (stock operations)</li>";
    echo "<li>app/Models/StockReceive.php (stock receives)</li>";
    echo "<li>app/Models/StockTransfer.php (stock transfers)</li>";
    echo "<li>app/controllers/WarehouseProcessingController.php (warehouse processing)</li>";
    echo "</ul>";
    echo "<p class='fail'><strong>Without this method, these features will fail with fatal errors!</strong></p>";
}

// Test Database connection
echo "<hr>";
echo "<h2>Database Connection Test</h2>";
try {
    $pdo = \App\Core\Database::getInstance();
    echo "<p class='ok'>✓ Database connection successful</p>";
    
    // Test execute() method if it exists
    if (method_exists('App\Core\Database', 'execute')) {
        echo "<h3>Testing execute() method:</h3>";
        // Test with a harmless query
        $testResult = \App\Core\Database::execute("SELECT 1", []);
        if ($testResult === true) {
            echo "<p class='ok'>✓ execute() method works correctly</p>";
        } else {
            echo "<p class='fail'>✗ execute() method returned false</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Show file info
echo "<hr>";
echo "<h2>File Information</h2>";
$dbFile = __DIR__ . '/app/Core/Database.php';
if (file_exists($dbFile)) {
    echo "<p>Path: $dbFile</p>";
    echo "<p>Size: " . filesize($dbFile) . " bytes</p>";
    echo "<p>Last Modified: " . date('Y-m-d H:i:s', filemtime($dbFile)) . "</p>";
} else {
    echo "<p class='fail'>✗ Database.php file not found!</p>";
}

echo "<hr>";
echo "<p><a href='/production'>← Back to Production</a></p>";
echo "<p style='color:#999;font-size:12px;'>Delete this file after verification: verify_database.php</p>";
echo "</body></html>";
