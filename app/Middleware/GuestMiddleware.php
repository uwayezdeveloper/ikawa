<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Guest Middleware
 * Redirects authenticated users away from guest-only pages
 */
class GuestMiddleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request): bool
    {
        if (isset($_SESSION['user'])) {
            $response = new Response();
            $response->redirect(APP_URL . '/dashboard');
            return false;
        }
        
        return true;
    }
}
