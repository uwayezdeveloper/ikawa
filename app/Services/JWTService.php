<?php
namespace App\Services;

/**
 * JWT Service Class
 * Handles JWT token generation and validation
 */
class JWTService
{
    /**
     * Generate a JWT token
     */
    public function generateToken(array $payload, int $expiry = null): string
    {
        $expiry = $expiry ?? JWT_EXPIRY;
        
        $header = [
            'alg' => JWT_ALGORITHM,
            'typ' => 'JWT'
        ];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        $payload['iss'] = APP_URL;
        
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->sign("$headerEncoded.$payloadEncoded", JWT_SECRET);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Generate access and refresh tokens
     */
    public function generateTokenPair(array $userData): array
    {
        $accessPayload = [
            'sub' => $userData['id'],
            'email' => $userData['email'],
            'role' => $userData['role'] ?? $userData['role_name'] ?? 'user',
            'type' => 'access'
        ];
        
        $refreshPayload = [
            'sub' => $userData['id'],
            'type' => 'refresh'
        ];
        
        return [
            'access_token' => $this->generateToken($accessPayload, JWT_EXPIRY),
            'refresh_token' => $this->generateToken($refreshPayload, JWT_REFRESH_EXPIRY),
            'token_type' => 'Bearer',
            'expires_in' => JWT_EXPIRY
        ];
    }

    /**
     * Validate and decode a JWT token
     */
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        
        // Verify signature
        $signature = $this->base64UrlDecode($signatureEncoded);
        $expectedSignature = $this->sign("$headerEncoded.$payloadEncoded", JWT_SECRET);
        
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }
        
        // Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        if (!$payload) {
            return null;
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }

    /**
     * Decode token without validation (for inspection)
     */
    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        
        return $payload ?: null;
    }

    /**
     * Get token expiry time
     */
    public function getTokenExpiry(string $token): ?int
    {
        $payload = $this->decodeToken($token);
        
        return $payload['exp'] ?? null;
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(string $token): bool
    {
        $expiry = $this->getTokenExpiry($token);
        
        return $expiry === null || $expiry < time();
    }

    /**
     * Get user ID from token
     */
    public function getUserIdFromToken(string $token): ?int
    {
        $payload = $this->validateToken($token);
        
        return $payload['sub'] ?? null;
    }

    /**
     * Hash token for storage
     */
    public function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Sign data with secret
     */
    protected function sign(string $data, string $secret): string
    {
        return hash_hmac('sha256', $data, $secret, true);
    }
}