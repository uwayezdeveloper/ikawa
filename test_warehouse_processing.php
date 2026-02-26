<!DOCTYPE html>
<html>
<head>
    <title>APP_URL Test</title>
</head>
<body>
    <h1>Testing window.APP_URL</h1>
    
    <h2>PHP Constants:</h2>
    <pre>
<?php
// Bootstrap
define('BASE_PATH', __DIR__);
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}
require_once BASE_PATH . '/app/Core/Autoloader.php';
require_once BASE_PATH . '/config/config.php';

echo "APP_URL constant: " . APP_URL . "\n";
echo "Defined: " . (defined('APP_URL') ? 'YES' : 'NO') . "\n";
?>
    </pre>
    
    <h2>JavaScript Test:</h2>
    <div id="result"></div>
    
    <!-- Set window.APP_URL like main.php -->
    <script>
        console.log("Before setting - window.APP_URL:", window.APP_URL);
        window.APP_URL = '<?= APP_URL ?>';
        console.log("After setting - window.APP_URL:", window.APP_URL);
        console.log("Type:", typeof window.APP_URL);
        
        document.getElementById('result').innerHTML = 
            '<p><strong>window.APP_URL:</strong> ' + window.APP_URL + '</p>' +
            '<p><strong>Type:</strong> ' + typeof window.APP_URL + '</p>';
    </script>
    
    <h3>View Page Source</h3>
    <p>Right-click → View Page Source to see the actual rendered JavaScript</p>
    <p>Look for the line: <code>window.APP_URL = '...';</code></p>
</body>
</html>
