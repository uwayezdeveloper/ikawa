<?php
namespace App\Services;

use App\Core\Database;
use App\Models\User;

/**
 * Authentication Service
 * Handles user authentication logic
 */
class AuthService
{
    protected JWTService $jwtService;
    protected User $userModel;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->userModel = new User();
    }

    /**
     * Authenticate user with email and password
     */
    public function attempt(string $email, string $password): ?array
    {
        // Find user by email (with password for verification)
        $user = $this->userModel->findByWithPassword('email', $email);
        
        if (!$user) {
            return null;
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            return null;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return null;
        }
        
        // Generate tokens
        $tokens = $this->jwtService->generateTokenPair($user);
        
        // Store refresh token hash
        $this->storeToken($user['id'], $tokens['refresh_token'], 'refresh');
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Log activity
        $this->logActivity($user['id'], 'login', 'User logged in successfully');
        
        // Get user with role details
        $userWithRole = $this->userModel->getUserWithRole($user['id']);
        
        // Get user permissions
        $permissions = $this->userModel->getUserPermissions($user['id']);
        $permissionSlugs = array_column($permissions, 'slug');
        
        // Add permissions to user data
        $userWithRole['permissions'] = $permissionSlugs;
        
        return [
            'user' => $userWithRole,
            'tokens' => $tokens
        ];
    }

    /**
     * Register a new user
     */
    public function register(array $data): ?array
    {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        $data['uuid'] = $this->generateUUID();
        $data['status'] = 'active'; // Or 'pending' if email verification is required
        
        try {
            // Create user
            $userId = $this->userModel->create($data);
            
            // Get created user
            $user = $this->userModel->find($userId);
            
            // Generate tokens
            $tokens = $this->jwtService->generateTokenPair([
                'id' => $userId,
                'email' => $data['email'],
                'role' => $data['role'] ?? 'customer'
            ]);
            
            // Store refresh token
            $this->storeToken($userId, $tokens['refresh_token'], 'refresh');
            
            // Log activity
            $this->logActivity($userId, 'register', 'User registered successfully');
            
            return [
                'user' => $user,
                'tokens' => $tokens
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        // Validate refresh token
        $payload = $this->jwtService->validateToken($refreshToken);
        
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            return null;
        }
        
        // Check if token is stored and not revoked
        $tokenHash = $this->jwtService->hashToken($refreshToken);
        $storedToken = Database::fetch(
            "SELECT * FROM jwt_tokens WHERE token_hash = :hash AND revoked_at IS NULL AND expires_at > NOW()",
            ['hash' => $tokenHash]
        );
        
        if (!$storedToken) {
            return null;
        }
        
        // Get user
        $user = $this->userModel->findByWithPassword('id', $payload['sub']);
        
        if (!$user || $user['status'] !== 'active') {
            return null;
        }
        
        // Revoke old refresh token
        $this->revokeToken($refreshToken);
        
        // Generate new tokens
        $tokens = $this->jwtService->generateTokenPair($user);
        
        // Store new refresh token
        $this->storeToken($user['id'], $tokens['refresh_token'], 'refresh');
        
        unset($user['password']);
        
        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }

    /**
     * Logout user (revoke tokens)
     */
    public function logout(string $token, ?int $userId = null): bool
    {
        // Revoke the token
        $this->revokeToken($token);
        
        if ($userId) {
            $this->logActivity($userId, 'logout', 'User logged out');
        }
        
        return true;
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(int $userId): bool
    {
        Database::query(
            "UPDATE jwt_tokens SET revoked_at = NOW() WHERE user_id = :user_id AND revoked_at IS NULL",
            ['user_id' => $userId]
        );
        
        $this->logActivity($userId, 'logout_all', 'User logged out from all devices');
        
        return true;
    }

    /**
     * Validate access token and get user
     */
    public function validateAccessToken(string $token): ?array
    {
        $payload = $this->jwtService->validateToken($token);
        
        if (!$payload || ($payload['type'] ?? '') !== 'access') {
            return null;
        }
        
        // Get user
        $user = $this->userModel->find($payload['sub']);
        
        if (!$user || $user['status'] !== 'active') {
            return null;
        }
        
        return $user;
    }

    /**
     * Store token hash in database
     */
    protected function storeToken(int $userId, string $token, string $type): void
    {
        $hash = $this->jwtService->hashToken($token);
        $expiry = $this->jwtService->getTokenExpiry($token);
        
        Database::insert('jwt_tokens', [
            'user_id' => $userId,
            'token_hash' => $hash,
            'type' => $type,
            'expires_at' => date('Y-m-d H:i:s', $expiry)
        ]);
    }

    /**
     * Revoke a token
     */
    protected function revokeToken(string $token): void
    {
        $hash = $this->jwtService->hashToken($token);
        
        Database::query(
            "UPDATE jwt_tokens SET revoked_at = NOW() WHERE token_hash = :hash",
            ['hash' => $hash]
        );
    }

    /**
     * Log user activity
     */
    protected function logActivity(?int $userId, string $action, string $description): void
    {
        $request = new \App\Core\Request();
        
        Database::insert('user_activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->getClientIp(),
            'user_agent' => $request->getUserAgent()
        ]);
    }

    /**
     * Generate UUID v4
     */
    protected function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Clean expired tokens
     */
    public function cleanExpiredTokens(): int
    {
        $stmt = Database::query("DELETE FROM jwt_tokens WHERE expires_at < NOW()");
        return $stmt->rowCount();
    }
}
