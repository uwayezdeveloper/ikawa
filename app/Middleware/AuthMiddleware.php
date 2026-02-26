<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\User;

/**
 * Authentication Middleware
 * Protects routes that require authentication
 */
class AuthMiddleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request): bool|string
    {
        // Check if user is logged in via session
        if (isset($_SESSION['user'])) {
            // Refresh permissions from database on every request
            $this->refreshUserPermissions();
            return true;
        }
        
        // Check for refresh token in cookie
        if (isset($_COOKIE['refresh_token'])) {
            $authService = new \App\Services\AuthService();
            $result = $authService->refreshToken($_COOKIE['refresh_token']);
            
            if ($result) {
                $_SESSION['user'] = $result['user'];
                $_SESSION['access_token'] = $result['tokens']['access_token'];
                $_SESSION['refresh_token'] = $result['tokens']['refresh_token'];
                
                // Update cookie
                setcookie('refresh_token', $result['tokens']['refresh_token'], [
                    'expires' => time() + JWT_REFRESH_EXPIRY,
                    'path' => '/',
                    'httponly' => true,
                    'secure' => isset($_SERVER['HTTPS']),
                    'samesite' => 'Strict'
                ]);
                
                return true;
            }
        }
        
        // Not authenticated
        if ($request->isAjax()) {
            $response = new Response();
            $response->unauthorized('Authentication required');
            return false;
        }
        
        // Redirect to login
        $response = new Response();
        $_SESSION['intended_url'] = $request->getPath();
        $response->redirect(APP_URL . '/login');
        return false;
    }

    /**
     * Refresh user permissions from database
     * This ensures permission changes take effect immediately without logout
     */
    protected function refreshUserPermissions(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) return;

        $userModel = new User();
        
        $menuIdentifiers = $userModel->getUserMenuIdentifiers($userId);
        $_SESSION['user']['menu_identifiers'] = $menuIdentifiers;
        $_SESSION['user']['permissions'] = $userModel->getLegacyPermissionsFromMenus($menuIdentifiers);
        
        // Also refresh role info
        $userWithRole = $userModel->getUserWithRole($userId);
        if ($userWithRole) {
            $_SESSION['user']['role_id'] = $userWithRole['role_id'];
            $_SESSION['user']['role_name'] = $userWithRole['role_name'] ?? null;
        }
    }
}
