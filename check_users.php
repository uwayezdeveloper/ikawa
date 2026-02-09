<?php
require_once 'config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<h2>Users Table Structure:</h2>";
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "<h2>Testing Fixed Query:</h2>";
    $sql = "SELECT d.*, u.first_name, u.last_name 
            FROM tbl_expense_conume_details d
            LEFT JOIN users u ON d.created_by = u.id
            WHERE d.trans_code = 'EXP20260207090806557'
            ORDER BY d.action ASC";
            
    $result = $pdo->query($sql)->fetchAll();
    echo "Query executed successfully. Results:<br>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>