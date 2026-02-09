<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Models\User;

/**
 * Authentication Controller
 * Handles login, register, logout
 */
class AuthController extends Controller
{
    protected AuthService $authService;
    protected User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->userModel = new User();
    }

    /**
     * Show login page
     */
    public function showLogin(Request $request, Response $response): void
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user'])) {
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $this->view('auth/login', [], 'auth');
    }

    /**
     * Show register page
     */
    public function showRegister(Request $request, Response $response): void
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user'])) {
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $this->view('auth/register', [], 'auth');
    }

    /**
     * Handle login (form submission)
     */
    public function login(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if (!empty($errors)) {
            if ($request->isAjax()) {
                $response->error('Validation failed', 422, $errors);
                return;
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        // Attempt authentication
        $result = $this->authService->attempt($data['email'], $data['password']);
        
        if (!$result) {
            if ($request->isAjax()) {
                $response->error('Invalid credentials', 401);
                return;
            }
            
            $_SESSION['error'] = 'Invalid email or password.';
            $_SESSION['old'] = ['email' => $data['email']];
            $response->redirect(APP_URL . '/login');
            return;
        }
        
        // Store user in session
        $_SESSION['user'] = $result['user'];
        $_SESSION['access_token'] = $result['tokens']['access_token'];
        $_SESSION['refresh_token'] = $result['tokens']['refresh_token'];
        
        // Set cookie for "remember me"
        if (!empty($data['remember'])) {
            setcookie('refresh_token', $result['tokens']['refresh_token'], [
                'expires' => time() + JWT_REFRESH_EXPIRY,
                'path' => '/',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']),
                'samesite' => 'Strict'
            ]);
        }
        
        if ($request->isAjax()) {
            $response->success([
                'user' => $result['user'],
                'tokens' => $result['tokens'],
                'redirect' => APP_URL . '/dashboard'
            ], 'Login successful');
            return;
        }
        
        $response->redirect(APP_URL . '/dashboard');
    }

    /**
     * Handle registration (form submission)
     */
    public function register(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);
        
        if (!empty($errors)) {
            if ($request->isAjax()) {
                $response->error('Validation failed', 422, $errors);
                return;
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/register');
            return;
        }
        
        // Register user
        $result = $this->authService->register([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer'
        ]);
        
        if (!$result) {
            if ($request->isAjax()) {
                $response->error('Registration failed. Please try again.', 500);
                return;
            }
            
            $_SESSION['error'] = 'Registration failed. Please try again.';
            $_SESSION['old'] = $data;
            $response->redirect(APP_URL . '/register');
            return;
        }
        
        // Store user in session
        $_SESSION['user'] = $result['user'];
        $_SESSION['access_token'] = $result['tokens']['access_token'];
        $_SESSION['refresh_token'] = $result['tokens']['refresh_token'];
        
        if ($request->isAjax()) {
            $response->success([
                'user' => $result['user'],
                'tokens' => $result['tokens'],
                'redirect' => APP_URL . '/dashboard'
            ], 'Registration successful', 201);
            return;
        }
        
        $response->redirect(APP_URL . '/dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request, Response $response): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $token = $_SESSION['access_token'] ?? '';
        
        // Revoke token
        if ($token) {
            $this->authService->logout($token, $userId);
        }
        
        // Clear session
        $_SESSION = [];
        
        // Clear cookie
        if (isset($_COOKIE['refresh_token'])) {
            setcookie('refresh_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']),
                'samesite' => 'Strict'
            ]);
        }
        
        // Destroy session
        session_destroy();
        
        if ($request->isAjax()) {
            $response->success([], 'Logged out successfully');
            return;
        }
        
        $response->redirect(APP_URL . '/login');
    }

    /**
     * API: Login
     */
    public function apiLogin(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $response->error('Validation failed', 422, $errors);
            return;
        }
        
        // Attempt authentication
        $result = $this->authService->attempt($data['email'], $data['password']);
        
        if (!$result) {
            $response->error('Invalid credentials', 401);
            return;
        }
        
        $response->success([
            'user' => $result['user'],
            'tokens' => $result['tokens']
        ], 'Login successful');
    }

    /**
     * API: Register
     */
    public function apiRegister(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        // Validate input
        $errors = $this->validate($data, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);
        
        if (!empty($errors)) {
            $response->error('Validation failed', 422, $errors);
            return;
        }
        
        // Register user
        $result = $this->authService->register([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer'
        ]);
        
        if (!$result) {
            $response->error('Registration failed', 500);
            return;
        }
        
        $response->success([
            'user' => $result['user'],
            'tokens' => $result['tokens']
        ], 'Registration successful', 201);
    }

    /**
     * API: Refresh token
     */
    public function apiRefresh(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $refreshToken = $data['refresh_token'] ?? $request->getBearerToken();
        
        if (!$refreshToken) {
            $response->error('Refresh token required', 400);
            return;
        }
        
        $result = $this->authService->refreshToken($refreshToken);
        
        if (!$result) {
            $response->error('Invalid or expired refresh token', 401);
            return;
        }
        
        $response->success([
            'user' => $result['user'],
            'tokens' => $result['tokens']
        ], 'Token refreshed');
    }

    /**
     * API: Logout
     */
    public function apiLogout(Request $request, Response $response): void
    {
        $token = $request->getBearerToken();
        
        if ($token) {
            $user = $this->authService->validateAccessToken($token);
            $this->authService->logout($token, $user['id'] ?? null);
        }
        
        $response->success([], 'Logged out successfully');
    }

    /**
     * API: Get current user
     */
    public function apiMe(Request $request, Response $response): void
    {
        $token = $request->getBearerToken();
        
        if (!$token) {
            $response->unauthorized('Token required');
            return;
        }
        
        $user = $this->authService->validateAccessToken($token);
        
        if (!$user) {
            $response->unauthorized('Invalid token');
            return;
        }
        
        $response->success(['user' => $user], 'User retrieved');
    }
}
