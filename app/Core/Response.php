<?php
namespace App\Core;

/**
 * Response Class
 * Handles HTTP responses
 */
class Response
{
    /**
     * Set HTTP status code
     */
    public function setStatusCode(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    /**
     * Set response header
     */
    public function setHeader(string $name, string $value): self
    {
        header("$name: $value");
        return $this;
    }

    /**
     * Redirect to URL
     */
    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Send JSON response
     */
    public function json(array $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success JSON response
     */
    public function success(array $data = [], string $message = 'Success', int $statusCode = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send error JSON response
     */
    public function error(string $message = 'Error', int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $statusCode);
    }

    /**
     * Send unauthorized response
     */
    public function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, 401);
    }

    /**
     * Send forbidden response
     */
    public function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403);
    }

    /**
     * Send not found response
     */
    public function notFound(string $message = 'Not Found'): void
    {
        $this->error($message, 404);
    }
}
