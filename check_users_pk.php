<?php
require_once __DIR__ . '/config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<h3>Users table primary key:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
    foreach ($columns as $column) {
        if ($column['Key'] === 'PRI') {
            echo "<strong>Primary Key: " . $column['Field'] . "</strong><br>";
            break;
        }
    }
    
    echo "<h3>All columns in users table:</h3>";
    echo "<pre>";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . " (" . $column['Key'] . ")\n";
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>