<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

/**
 * Dashboard Controller
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index(Request $request, Response $response): void
    {
        $user = $_SESSION['user'] ?? null;
        
        $this->view('dashboard/index', [
            'user' => $user,
            'pageTitle' => 'Dashboard'
        ], 'main');
    }
}
