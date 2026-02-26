<?php
/**
 * Gihanga Coffee - Main Entry Point
 * PHP MVC Framework with JWT Authentication
 */

// Start session
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Autoload classes
require_once BASE_PATH . '/app/Core/Autoloader.php';

// Load configuration
require_once BASE_PATH . '/config/config.php';

// Initialize the application
$app = new App\Core\Application();

// Load routes (after application is created)
require_once BASE_PATH . '/routes/web.php';

// Run the application
$app->run();
