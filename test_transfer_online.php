<?php
/**
 * Simple Transfer Page Diagnostic
 * Upload to: https://gihangacoffe.itectab.rw/check_transfer.php
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Transfer Check</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;} .box{background:white;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #2196F3;} .ok{color:green;} .fail{color:red;} .warn{color:orange;} h2{color:#333;}</style>";
echo "</head><body>";
echo "<h1>🔍 Transfer to Warehouse - Diagnostic Check</h1>";
echo "<p style='color:#666;'>Testing: " . date('Y-m-d H:i:s') . "</p><hr>";

// Test 1: Basic PHP
echo "<div class='box'><h2>1. PHP Version</h2>";
echo "<p class='ok'>✓ PHP " . phpversion() . "</p>";
echo "</div>";

// Test 2: Autoloader
echo "<div class='box'><h2>2. Autoloader</h2>";
try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "<p class='ok'>✓ Composer autoload found</p>";
    } else {
        echo "<p class='fail'>✗ Composer autoload missing</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Autoload error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 3: Config
echo "<div class='box'><h2>3. Configuration</h2>";
try {
    if (file_exists(__DIR__ . '/config/config.php')) {
        require_once __DIR__ . '/config/config.php';
        echo "<p class='ok'>✓ Config loaded</p>";
        echo "<p>DB Host: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "</p>";
    } else {
        echo "<p class='fail'>✗ Config file not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Config error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 4: Database Class
echo "<div class='box'><h2>4. Database Class</h2>";
try {
    if (file_exists(__DIR__ . '/app/Core/Database.php')) {
        require_once __DIR__ . '/app/Core/Database.php';
        echo "<p class='ok'>✓ Database.php exists</p>";
        
        // Check critical methods
        $methods = ['getInstance', 'query', 'fetchAll', 'execute'];
        foreach ($methods as $method) {
            if (method_exists('App\Core\Database', $method)) {
                echo "<p class='ok'>✓ Database::$method() exists</p>";
            } else {
                echo "<p class='fail'>✗ Database::$method() MISSING ❌</p>";
            }
        }
        
        // Try connection
        try {
            $pdo = App\Core\Database::getInstance();
            echo "<p class='ok'>✓ Database connection successful</p>";
        } catch (Exception $e) {
            echo "<p class='fail'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p class='fail'>✗ Database.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Database class error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 5: StockTransfer Model
echo "<div class='box'><h2>5. StockTransfer Model</h2>";
try {
    if (file_exists(__DIR__ . '/app/Models/StockTransfer.php')) {
        require_once __DIR__ . '/app/Core/Model.php';
        require_once __DIR__ . '/app/Models/StockTransfer.php';
        echo "<p class='ok'>✓ StockTransfer.php exists</p>";
        
        $transfer = new App\Models\StockTransfer();
        echo "<p class='ok'>✓ StockTransfer object created</p>";
        
        // Check critical methods
        $methods = ['createTransfer', 'approveTransfer', 'receiveTransfer', 'cancelTransfer'];
        foreach ($methods as $method) {
            if (method_exists($transfer, $method)) {
                echo "<p class='ok'>✓ $method() exists</p>";
            } else {
                echo "<p class='fail'>✗ $method() missing</p>";
            }
        }
    } else {
        echo "<p class='fail'>✗ StockTransfer.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ StockTransfer error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 6: StockSummary Model
echo "<div class='box'><h2>6. StockSummary Model</h2>";
try {
    if (file_exists(__DIR__ . '/app/Models/StockSummary.php')) {
        require_once __DIR__ . '/app/Models/StockSummary.php';
        echo "<p class='ok'>✓ StockSummary.php exists</p>";
        
        $summary = new App\Models\StockSummary();
        echo "<p class='ok'>✓ StockSummary object created</p>";
        
        // Check critical methods
        $methods = ['getByLocation', 'getByLocationWithSuppliers', 'deductFromLocation', 'addToLocation'];
        foreach ($methods as $method) {
            if (method_exists($summary, $method)) {
                echo "<p class='ok'>✓ $method() exists</p>";
            } else {
                echo "<p class='fail'>✗ $method() missing</p>";
            }
        }
    } else {
        echo "<p class='fail'>✗ StockSummary.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ StockSummary error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 7: Check Locations & Warehouses
echo "<div class='box'><h2>7. Database Tables Test</h2>";
try {
    // Check location_types
    $locationTypes = App\Core\Database::fetchAll("SELECT id, name FROM location_types WHERE status = 'active' ORDER BY name");
    echo "<p class='ok'>✓ Location Types: " . count($locationTypes) . " found</p>";
    
    // Check warehouses
    $warehouses = App\Core\Database::fetchAll("SELECT l.id, l.name FROM locations l 
        JOIN location_types lt ON l.location_type_id = lt.id 
        WHERE lt.name = 'Warehouse' AND l.status = 'active' 
        ORDER BY l.name");
    echo "<p class='ok'>✓ Warehouses: " . count($warehouses) . " found</p>";
    
    // Check transfers
    $transfers = App\Core\Database::fetchAll("SELECT COUNT(*) as cnt FROM stock_transfers");
    echo "<p class='ok'>✓ Stock Transfers table accessible</p>";
    
} catch (Exception $e) {
    echo "<p class='fail'>✗ Database query error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 8: JavaScript File
echo "<div class='box'><h2>8. JavaScript File</h2>";
if (file_exists(__DIR__ . '/assets/js/pages/stock-transfer.js')) {
    echo "<p class='ok'>✓ stock-transfer.js exists</p>";
    $size = filesize(__DIR__ . '/assets/js/pages/stock-transfer.js');
    echo "<p>File size: " . number_format($size) . " bytes</p>";
} else {
    echo "<p class='warn'>⚠ stock-transfer.js not found</p>";
}
echo "</div>";

// Summary
echo "<hr><div class='box'>";
echo "<h2>📋 Summary</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If Database::execute() is missing → Upload app/Core/Database.php</li>";
echo "<li>If StockSummary methods missing → Upload app/Models/StockSummary.php</li>";
echo "<li>If all checks pass → Check browser console for JavaScript errors</li>";
echo "</ol>";
echo "<p><a href='/stock/transfers'>→ Go to Transfer Page</a></p>";
echo "</div>";

echo "<p style='color:#999;font-size:12px;margin-top:30px;'>Delete this file after diagnosis:check_transfer.php</p>";
echo "</body></html>";
