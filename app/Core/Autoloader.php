<?php
/**
 * Class Autoloader
 * Automatically loads classes based on namespace
 */

spl_autoload_register(function ($class) {
    // Project namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $baseDir = BASE_PATH . '/app/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Not using our namespace, move to next autoloader
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace namespace separator with directory separator
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
        return;
    }
    
    // Try lowercase first directory (for case-sensitive systems)
    // Convert Controllers -> controllers, Models -> models, etc.
    $parts = explode('/', str_replace('\\', '/', $relativeClass));
    if (count($parts) > 0) {
        $parts[0] = lcfirst($parts[0]); // lowercase first letter of directory
        $altFile = $baseDir . implode('/', $parts) . '.php';
        if (file_exists($altFile)) {
            require $altFile;
        }
    }
});