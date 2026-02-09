<?php
/**
 * Application Configuration
 */

// Application settings
define('APP_NAME', 'Gihanga Coffee');
define('APP_URL', 'http://localhost/gihanga');
define('APP_ENV', 'production'); // development or production

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tabrw_gihangacoffee');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// JWT Configuration
define('JWT_SECRET', 'your-super-secret-key-change-this-in-production-gihanga-coffee-2024');
define('JWT_EXPIRY', 3600); // 1 hour in seconds
define('JWT_REFRESH_EXPIRY', 86400 * 7); // 7 days in seconds
define('JWT_ALGORITHM', 'HS256');

// Security settings
define('HASH_COST', 12); // bcrypt cost factor

// Timezone
date_default_timezone_set('Africa/Kigali');


// Change this temporarily:
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);      // Changed
    ini_set('display_errors', 1); // Changed
}