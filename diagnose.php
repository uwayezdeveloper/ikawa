<?php
/**
 * Diagnostic script for online debugging
 * Access via: https://gihangacoffe.itectab.rw/diagnose.php
 * DELETE THIS FILE AFTER DEBUGGING!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Gihanga Coffee - Online Diagnostics</h2>";
echo "<hr>";

// 1. PHP Version
echo "<h3>1. PHP Version</h3>";
echo "<p>PHP: " . phpversion() . "</p>";

// 2. Check config
echo "<h3>2. Config</h3>";
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
echo "<p>APP_URL: " . APP_URL . "</p>";
echo "<p>DB_HOST: " . DB_HOST . "</p>";
echo "<p>DB_NAME: " . DB_NAME . "</p>";

// 3. Database connection
echo "<h3>3. Database Connection</h3>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p style='color:green'>✅ Database connected successfully</p>";
    
    // 4. Check users table structure
    echo "<h3>4. Users Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $hasLocationId = false;
    foreach ($columns as $col) {
        $highlight = ($col['Field'] === 'location_id') ? "style='background:yellow'" : "";
        if ($col['Field'] === 'location_id') $hasLocationId = true;
        echo "<tr $highlight><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    if (!$hasLocationId) {
        echo "<p style='color:red; font-weight:bold'>❌ Column 'location_id' is MISSING from users table!</p>";
        echo "<p>Run this SQL on your online database:</p>";
        echo "<pre style='background:#f0f0f0;padding:10px'>ALTER TABLE users ADD COLUMN location_id INT(11) NULL AFTER updated_at;</pre>";
    } else {
        echo "<p style='color:green'>✅ Column 'location_id' exists</p>";
    }
    
    // 5. Check locations table
    echo "<h3>5. Locations Table</h3>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM locations");
        $row = $stmt->fetch();
        echo "<p style='color:green'>✅ Locations table exists with {$row['cnt']} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Locations table error: " . $e->getMessage() . "</p>";
    }

    // 6. Check non_exploitable_types table
    echo "<h3>6. Non-Exploitable Types Table</h3>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM non_exploitable_types");
        $row = $stmt->fetch();
        echo "<p style='color:green'>✅ non_exploitable_types table exists with {$row['cnt']} records</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ non_exploitable_types table error: " . $e->getMessage() . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// 7. Check critical files exist
echo "<h3>7. Critical Files</h3>";
$files = [
    'app/Models/Location.php',
    'app/Models/User.php',
    'app/Models/NonExploitableType.php',
    'app/controllers/UserController.php',
    'app/controllers/NonExploitableCategoryController.php',
    'app/Views/users/create.php',
    'app/Views/users/edit.php',
    'app/Views/non-exploitable/categories/index.php',
    'app/Views/non-exploitable/categories/create.php',
    'app/Views/non-exploitable/categories/edit.php',
];
foreach ($files as $f) {
    $path = BASE_PATH . '/' . $f;
    if (file_exists($path)) {
        echo "<p style='color:green'>✅ $f</p>";
    } else {
        echo "<p style='color:red'>❌ $f — MISSING!</p>";
    }
}

// 8. Check error log
echo "<h3>8. Recent Errors (error.log)</h3>";
$logFile = BASE_PATH . '/error.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    echo "<pre style='background:#f0f0f0;padding:10px;max-height:300px;overflow:auto'>";
    echo htmlspecialchars(implode("", $lastLines));
    echo "</pre>";
} else {
    echo "<p>No error.log file found</p>";
}

echo "<hr><p style='color:red;font-weight:bold'>⚠️ DELETE this file (diagnose.php) after debugging!</p>";
