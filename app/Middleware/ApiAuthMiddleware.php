<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

/**
 * API Authentication Middleware
 * Validates JWT tokens for API requests
 */
class ApiAuthMiddleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request): bool
    {
        $token = $request->getBearerToken();
        
        if (!$token) {
            $response = new Response();
            $response->unauthorized('Access token required');
            return false;
        }
        
        $authService = new AuthService();
        $user = $authService->validateAccessToken($token);
        
        if (!$user) {
            $response = new Response();
            $response->unauthorized('Invalid or expired token');
            return false;
        }
        
        // Store user in request for later use
        $_REQUEST['auth_user'] = $user;
        
        return true;
    }
}
