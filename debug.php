<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

echo "<h1>Debug Information</h1>";
echo "<h2>1. PHP Version: " . PHP_VERSION . "</h2>";

// Load config
try {
    require_once BASE_PATH . '/config/config.php';
    echo "<h2>2. Configuration loaded successfully</h2>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
} catch (Exception $e) {
    echo "<h2>2. Configuration Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// Test database connection
try {
    echo "<h2>3. Testing Database Connection</h2>";
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Database connection successful<br>";
    
    // Check if expense tables exist
    $tables = [
        'tbl_expensecategories',
        'tbl_expenses', 
        'tbl_expenseconsumer',
        'tbl_expenseconsume',
        'tbl_expense_conume_details',
        'tbl_receipttype',
        'accounts',
        'locations',
        'users'
    ];
    
    echo "<h3>Table Status:</h3>";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "✅ $table exists<br>";
                
                // Show column structure for expense tables
                if (strpos($table, 'expense') !== false || $table === 'accounts' || $table === 'locations') {
                    $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll();
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;Columns: ";
                    foreach ($columns as $col) {
                        echo $col['Field'] . " (" . $col['Type'] . "), ";
                    }
                    echo "<br>";
                }
            } else {
                echo "❌ $table missing<br>";
            }
        } catch (Exception $e) {
            echo "❌ $table error: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>3. Database Connection Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// Test autoloader
try {
    echo "<h2>4. Testing Autoloader</h2>";
    require_once BASE_PATH . '/app/Core/Autoloader.php';
    echo "✅ Autoloader loaded<br>";
    
    // Try to load some classes
    $classes = [
        'App\Core\Application',
        'App\Core\Router', 
        'App\Core\Request',
        'App\Core\Response',
        'App\Controllers\ExpenseTransactionController'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ $class loaded<br>";
        } else {
            echo "❌ $class not found<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>4. Autoloader Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

echo "<h2>5. File System Check</h2>";
$files = [
    '/app/Controllers/ExpenseTransactionController.php',
    '/app/Models/ExpenseTransaction.php',
    '/app/Views/finance/expenses/transactions/index.php'
];

foreach ($files as $file) {
    if (file_exists(BASE_PATH . $file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

echo "<h2>6. Memory and Execution Info</h2>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Current Memory Usage: " . memory_get_usage(true) / 1024 / 1024 . " MB<br>";

echo "<h2>7. Testing ExpenseTransactionController Directly</h2>";
try {
    // Simulate the actual route call
    require_once BASE_PATH . '/app/Core/Application.php';
    require_once BASE_PATH . '/app/Core/Request.php';
    require_once BASE_PATH . '/app/Core/Response.php';
    
    $request = new App\Core\Request();
    $response = new App\Core\Response();
    
    $controller = new App\Controllers\ExpenseTransactionController();
    
    echo "✅ ExpenseTransactionController instantiated successfully<br>";
    
    // Test the create method specifically
    echo "<h3>Testing create() method:</h3>";
    
    // Set up a fake session for testing
    $_SESSION['user'] = ['id' => 3];
    
    // Test individual model methods
    $expenseTransaction = new App\Models\ExpenseTransaction();
    $accounts = $expenseTransaction->getAccountsByUserLocation(3);
    echo "Accounts found: " . count($accounts) . "<br>";
    if (count($accounts) > 0) {
        echo "First account: " . print_r($accounts[0], true) . "<br>";
    } else {
        echo "❌ No accounts found - check accounts table data<br>";
    }
    
    $result = $controller->create($request, $response);
    
    if ($result) {
        echo "✅ ExpenseTransactionController create method executed<br>";
    }
    
} catch (Error $e) {
    echo "❌ Fatal Error in ExpenseTransactionController:<br>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>File: " . $e->getFile() . "</pre>";
    echo "<pre>Line: " . $e->getLine() . "</pre>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "❌ Exception in ExpenseTransactionController:<br>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>File: " . $e->getFile() . "</pre>";
    echo "<pre>Line: " . $e->getLine() . "</pre>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}

echo "<h2>8. Testing Models Directly</h2>";
try {
    $expenseTransaction = new App\Models\ExpenseTransaction();
    echo "✅ ExpenseTransaction model loaded<br>";
    
    $account = new App\Models\Account();
    echo "✅ Account model loaded<br>";
    
} catch (Error $e) {
    echo "❌ Fatal Error in Models:<br>";
    echo "<pre>" . $e->getMessage() . "</pre>";
} catch (Exception $e) {
    echo "❌ Exception in Models:<br>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>