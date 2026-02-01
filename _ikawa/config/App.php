<?php
class App {
    public static function baseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $folder = '/ikawa.itectab.rw';
        } else {
            $folder = '';
        }
        
        return $protocol . '://' . $host . $folder;
    }
}

// Add global helper function
function fetchApiData($url) {
    // Try file_get_contents first
    $context = stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        'http' => ['timeout' => 30, 'ignore_errors' => true]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    // Fallback to CURL
    if ($response === FALSE && function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
    return $response;
}