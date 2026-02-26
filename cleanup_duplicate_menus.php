<?php
// Load config constants
require_once __DIR__ . '/config/config.php';

try {
    // Create direct PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "Checking for duplicate menus...\n";
    
    // Find duplicate menu identifiers
    $stmt = $pdo->query("SELECT menu_identifier, COUNT(*) as count 
                         FROM tbl_menus 
                         GROUP BY menu_identifier 
                         HAVING count > 1");
    $duplicates = $stmt->fetchAll();
    
    if (empty($duplicates)) {
        echo "No duplicate menu identifiers found.\n";
    } else {
        echo "Found " . count($duplicates) . " duplicate menu identifier(s):\n";
        foreach ($duplicates as $duplicate) {
            echo "- '{$duplicate['menu_identifier']}' appears {$duplicate['count']} times\n";
            
            // Keep the first one, delete the others
            $stmt = $pdo->prepare("SELECT menu_id FROM tbl_menus WHERE menu_identifier = ? ORDER BY menu_id ASC LIMIT 1");
            $stmt->execute([$duplicate['menu_identifier']]);
            $keep = $stmt->fetch();
            
            if ($keep) {
                $stmt = $pdo->prepare("DELETE FROM tbl_menus WHERE menu_identifier = ? AND menu_id != ?");
                $stmt->execute([$duplicate['menu_identifier'], $keep['menu_id']]);
                echo "  Kept menu_id {$keep['menu_id']}, deleted duplicates.\n";
            }
        }
    }
    
    // Also check for duplicate name records
    echo "\nChecking for duplicate menu names...\n";
    $stmt = $pdo->query("SELECT menu_name, COUNT(*) as count 
                         FROM tbl_menus 
                         GROUP BY menu_name 
                         HAVING count > 1");
    $nameDuplicates = $stmt->fetchAll();
    
    if (empty($nameDuplicates)) {
        echo "No duplicate menu names found.\n";
    } else {
        echo "Found " . count($nameDuplicates) . " duplicate menu name(s):\n";
        foreach ($nameDuplicates as $duplicate) {
            echo "- '{$duplicate['menu_name']}' appears {$duplicate['count']} times\n";
        }
    }
    
    echo "\nDuplicate cleanup completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}