<?php
namespace App\Core;

/**
 * Request Class
 * Handles HTTP request data
 */
class Request
{
    /**
     * Get the request path
     */
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove base path if exists
        $basePath = parse_url(APP_URL, PHP_URL_PATH) ?? '';
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        $position = strpos($path, '?');
        if ($position === false) {
            return $path ?: '/';
        }
        
        return substr($path, 0, $position) ?: '/';
    }

    /**
     * Get the request method
     */
    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Check if request is GET
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Check if request is POST
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get request body data
     */
    public function getBody(): array
    {
        $body = [];
        
        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = $this->sanitize($value);
            }
        }
        
        if ($this->getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                $body[$key] = $this->sanitize($value);
            }
        }
        
        // Handle JSON input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $jsonInput = file_get_contents('php://input');
            $jsonData = json_decode($jsonInput, true);
            if (is_array($jsonData)) {
                foreach ($jsonData as $key => $value) {
                    $body[$key] = $this->sanitize($value);
                }
            }
        }
        
        return $body;
    }

    /**
     * Get a specific input value
     */
    public function input(string $key, $default = null)
    {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }

    /**
     * Get query parameter
     */
    public function query(string $key, $default = null)
    {
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    /**
     * Get all query parameters
     */
    public function queryAll(): array
    {
        $query = [];
        foreach ($_GET as $key => $value) {
            $query[$key] = $this->sanitize($value);
        }
        return $query;
    }

    /**
     * Get authorization header
     */
    public function getAuthorizationHeader(): ?string
    {
        $headers = $this->getHeaders();
        return $headers['Authorization'] ?? $headers['authorization'] ?? null;
    }

    /**
     * Get bearer token from Authorization header
     */
    public function getBearerToken(): ?string
    {
        $header = $this->getAuthorizationHeader();
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
        
        return $headers;
    }

    /**
     * Get client IP address
     */
    public function getClientIp(): string
    {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Sanitize input value
     */
    protected function sanitize($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
