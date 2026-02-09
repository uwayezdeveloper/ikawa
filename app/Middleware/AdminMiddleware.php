<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Admin Middleware
 * Restricts access to admin users only (users with Administrator role)
 */
class AdminMiddleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request): bool
    {
        $user = $_SESSION['user'] ?? null;
        
        if (!$user) {
            if ($request->isAjax()) {
                $response = new Response();
                $response->forbidden('Admin access required');
                return false;
            }
            
            $response = new Response();
            $response->redirect(APP_URL . '/dashboard');
            return false;
        }
        
        // Check if user has Administrator role (role_id = 1) or role_name = 'Administrator'
        $isAdmin = ($user['role_id'] ?? 0) == 1 || ($user['role_name'] ?? '') === 'Administrator';
        
        // Also check if user has any management permission (more flexible approach)
        $permissions = $user['permissions'] ?? [];
        $hasManagementAccess = !empty($permissions);
        
        if (!$isAdmin && !$hasManagementAccess) {
            if ($request->isAjax()) {
                $response = new Response();
                $response->forbidden('Admin access required');
                return false;
            }
            
            $response = new Response();
            $response->redirect(APP_URL . '/dashboard');
            return false;
        }
        
        return true;
    }
}
