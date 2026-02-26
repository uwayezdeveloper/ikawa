<?php
/**
 * Quick Test - Place at: https://gihangacoffe.itectab.rw/quick_test.php
 * This will show you exactly what's failing
 */

// Start output
header('Content-Type: text/html; charset=utf-8');
session_start();

echo "<!DOCTYPE html><html><head><title>Quick Test</title>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .fail{color:red;} .warn{color:orange;} h2{border-bottom:2px solid #333;}</style>";
echo "</head><body>";
echo "<h1>🔍 Quick Diagnostic Test</h1>";

// Test 1: Session
echo "<h2>1. User Session</h2>";
if (isset($_SESSION['user'])) {
    echo "<p class='ok'>✓ Logged in as: " . htmlspecialchars($_SESSION['user']['username'] ?? 'unknown') . "</p>";
    echo "<p>Role: " . htmlspecialchars($_SESSION['user']['role_name'] ?? 'N/A') . "</p>";
    
    // Check permission
    $perms = $_SESSION['user']['permissions'] ?? [];
    if (in_array('approve-stock-receives', $perms)) {
        echo "<p class='ok'>✓ Has approve-stock-receives permission</p>";
    } else {
        echo "<p class='fail'>✗ MISSING approve-stock-receives permission</p>";
        echo "<p class='warn'>Fix: Go to Users → Roles → Assign this permission to your role</p>";
    }
} else {
    echo "<p class='fail'>✗ NOT LOGGED IN</p>";
    echo "<p class='warn'>Fix: <a href='/auth/login'>Log in first</a>, then come back here</p>";
}

// Test 2: Database
echo "<h2>2. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='ok'>✓ Database connected</p>";
    
    // Count pending
    $stmt = $pdo->query("SELECT COUNT(*) FROM stock_receives WHERE status = 'pending'");
    $pending = $stmt->fetchColumn();
    echo "<p>Pending stock receives: <strong>$pending</strong></p>";
    
} catch (Exception $e) {
    echo "<p class='fail'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: PHP Info
echo "<h2>3. Server Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>POST Max Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Upload Max: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>Session Timeout: " . ini_get('session.gc_maxlifetime') . " seconds</p>";

// Test 4: Error Log Location
echo "<h2>4. Error Logging</h2>";
$error_log = ini_get('error_log');
echo "<p>Error log: " . ($error_log ?: 'Default location') . "</p>";
echo "<p class='warn'>Check this file for \"StockReceiveController\" or \"Approve\" messages</p>";

// Test 5: Quick Test Button
echo "<h2>5. Test Approve Action</h2>";
if (isset($pdo) && isset($_SESSION['user'])) {
    $stmt = $pdo->query("SELECT id, receive_number, status FROM stock_receives WHERE status = 'pending' LIMIT 1");
    $receive = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($receive) {
        echo "<p>Found pending receive: <strong>#" . $receive['id'] . " - " . htmlspecialchars($receive['receive_number']) . "</strong></p>";
        echo "<form method='POST' action='/stock/receives/action' onsubmit='return confirm(\"Test approve this receive?\");'>";
        echo "<input type='hidden' name='action' value='approve'>";
        echo "<input type='hidden' name='id' value='" . $receive['id'] . "'>";
        echo "<button type='submit' style='padding:10px 20px; background:#28a745; color:white; border:none; cursor:pointer; font-size:16px;'>🚀 Test Approve</button>";
        echo "</form>";
        echo "<p class='warn'>After clicking, check if it redirects and shows success message</p>";
    } else {
        echo "<p class='warn'>No pending receives to test. Create one first.</p>";
    }
}

// Test 6: Server Variables
echo "<h2>6. Request Info</h2>";
echo "<p>Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . "</p>";
echo "<p>Remote IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "</p>";
echo "<p>User Agent: " . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

// Footer
echo "<hr>";
echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>If you see any <span class='fail'>red errors</span> above, fix those first</li>";
echo "<li>Check your server error log for detailed messages</li>";
echo "<li>Try the Test Approve button above</li>";
echo "<li>Open browser console (F12) when clicking approve on the actual page</li>";
echo "<li>If still failing, check .htaccess or ModSecurity blocking POST requests</li>";
echo "</ol>";

echo "<p><a href='/stock/receives'>← Go to Stock Receives Page</a></p>";
echo "<p style='color:#999; font-size:12px;'>Delete this file after testing: quick_test.php</p>";
echo "</body></html>";
