<?php
/**
 * Quick User Location Fix - No Autoloader
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';

// Start session
session_start();

header('Content-Type: text/html; charset=utf-8');
echo '<h2>User Location Quick Fix</h2>';

try {
    // Database connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<p style="color:green">✅ Database connection successful!</p>';
    
    // Get current user ID from session
    $userId = $_SESSION['user']['id'] ?? null;
    
    if (!$userId) {
        echo '<p style="color:red">❌ No user ID in session</p>';
        exit;
    }
    
    echo '<p>Checking user ID: ' . $userId . '</p>';
    
    // Get user's actual data from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo '<p style="color:red">❌ User not found in database</p>';
        exit;
    }
    
    echo '<h3>User Database Record:</h3>';
    echo '<pre>';
    echo 'ID: ' . $user['id'] . "\n";
    echo 'Email: ' . $user['email'] . "\n";
    echo 'Name: ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";
    echo 'Location ID (DB): ' . ($user['location_id'] ?? 'NULL') . "\n";
    echo 'Role: ' . ($user['role'] ?? 'NULL') . "\n";
    echo '</pre>';
    
    // Update session if location_id exists in DB but not in session
    if (!empty($user['location_id']) && ($_SESSION['user']['location_id'] ?? null) != $user['location_id']) {
        $_SESSION['user']['location_id'] = $user['location_id'];
        $_SESSION['user']['role'] = $user['role'];
        echo '<p style="color:green">✅ Fixed session - location_id updated from database</p>';
    }
    
    // If location_id is still null, show available locations
    if (empty($user['location_id'])) {
        echo '<h3>Available Locations:</h3>';
        $locationStmt = $pdo->query("SELECT * FROM locations WHERE status = 'active' ORDER BY name");
        $locations = $locationStmt->fetchAll();
        
        if ($locations) {
            echo '<form method="POST" action="?action=update_location">';
            echo '<select name="location_id" required>';
            echo '<option value="">Select a location</option>';
            foreach ($locations as $location) {
                echo '<option value="' . $location['id'] . '">' . htmlspecialchars($location['name']) . '</option>';
            }
            echo '</select><br><br>';
            echo '<button type="submit" style="background: green; color: white; padding: 10px;">Update My Location</button>';
            echo '</form>';
        } else {
            echo '<p style="color:red">❌ No locations available</p>';
        }
    }
    
    // Handle location update
    if (isset($_POST['location_id']) && $_GET['action'] == 'update_location') {
        $newLocationId = (int)$_POST['location_id'];
        
        $updateStmt = $pdo->prepare("UPDATE users SET location_id = ? WHERE id = ?");
        if ($updateStmt->execute([$newLocationId, $userId])) {
            $_SESSION['user']['location_id'] = $newLocationId;
            echo '<p style="color:green">✅ Location updated successfully! Refresh page to see changes.</p>';
            echo '<meta http-equiv="refresh" content="2">';
        } else {
            echo '<p style="color:red">❌ Failed to update location</p>';
        }
    }
    
    // Show accounts for current location
    if (!empty($user['location_id']) || !empty($_SESSION['user']['location_id'])) {
        $locId = $user['location_id'] ?? $_SESSION['user']['location_id'];
        echo '<h3>Accounts for Location ID ' . $locId . ':</h3>';
        
        $accountStmt = $pdo->prepare("
            SELECT a.*, pm.name as payment_mode_name 
            FROM accounts a
            LEFT JOIN payment_modes pm ON a.payment_mode_id = pm.id
            WHERE a.location_id = ? AND a.status = 'active'
            ORDER BY a.account_name
        ");
        $accountStmt->execute([$locId]);
        $accounts = $accountStmt->fetchAll();
        
        if ($accounts) {
            echo '<p style="color:green">✅ Found ' . count($accounts) . ' accounts:</p>';
            echo '<ul>';
            foreach ($accounts as $account) {
                echo '<li>' . htmlspecialchars($account['account_name']) . ' (' . htmlspecialchars($account['payment_mode_name'] ?? 'N/A') . ')</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color:orange">⚠️ No accounts found for this location</p>';
        }
    }
    
} catch (Exception $e) {
    echo '<p style="color:red">❌ Error: ' . $e->getMessage() . '</p>';
}
?>

<style>
body { font-family: Arial; margin: 20px; }
form { margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background: #f9f9f9; }
select { padding: 8px; font-size: 14px; width: 200px; }
button { padding: 10px 20px; font-size: 14px; cursor: pointer; border: none; border-radius: 3px; }
</style>