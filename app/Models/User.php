<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * User Model
 */
class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'avatar',
        'role_id',
        'status',
        'email_verified_at'
    ];
    
    protected array $hidden = ['password'];

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find user by UUID
     */
    public function findByUuid(string $uuid): ?array
    {
        return $this->findBy('uuid', $uuid);
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' AND deleted_at IS NULL ORDER BY created_at DESC";
        $results = Database::fetchAll($sql);
        
        return array_map([$this, 'hideFields'], $results);
    }

    /**
     * Get users by role_id
     */
    public function getByRole(int $roleId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE role_id = :role_id AND deleted_at IS NULL ORDER BY created_at DESC";
        $results = Database::fetchAll($sql, ['role_id' => $roleId]);
        
        return array_map([$this, 'hideFields'], $results);
    }

    /**
     * Get users with role names (paginated)
     */
    public function paginateWithRoles(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $total = Database::fetch($countSql)['total'];
        
        // Get paginated data with role names
        $sql = "SELECT u.*, r.name as role_name 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.deleted_at IS NULL 
                ORDER BY u.id DESC 
                LIMIT :limit OFFSET :offset";
        
        $data = Database::fetchAll($sql, ['limit' => $perPage, 'offset' => $offset]);
        
        return [
            'data' => array_map([$this, 'hideFields'], $data),
            'total' => (int) $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Find user by ID with role name
     */
    public function findWithRole(int $id): ?array
    {
        $sql = "SELECT u.*, r.name as role_name 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = :id AND u.deleted_at IS NULL";
        $result = Database::fetch($sql, ['id' => $id]);
        
        return $result ? $this->hideFields($result) : null;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET last_login_at = NOW() WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $userId]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        $sql = "UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id";
        $stmt = Database::query($sql, [
            'password' => $hashedPassword,
            'id' => $userId
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Verify user email
     */
    public function verifyEmail(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET email_verified_at = NOW(), status = 'active' WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $userId]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Update user status
     */
    public function updateStatus(int $userId, string $status): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = Database::query($sql, [
            'status' => $status,
            'id' => $userId
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Search users
     */
    public function search(string $query, int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (first_name LIKE :query OR last_name LIKE :query OR email LIKE :query) 
                AND deleted_at IS NULL 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $results = Database::fetchAll($sql, [
            'query' => "%$query%",
            'limit' => $limit
        ]);
        
        return array_map([$this, 'hideFields'], $results);
    }

    /**
     * Get user stats
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN u.status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN u.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN u.status = 'suspended' THEN 1 ELSE 0 END) as suspended
                FROM {$this->table} u
                WHERE u.deleted_at IS NULL";
        
        return Database::fetch($sql);
    }

    /**
     * Get full name
     */
    public function getFullName(array $user): string
    {
        return trim($user['first_name'] . ' ' . $user['last_name']);
    }

    /**
     * Get user with role details
     */
    public function getUserWithRole(int $userId): ?array
    {
        $sql = "SELECT u.*, r.name as role_name, r.id as role_id 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = :id AND u.deleted_at IS NULL";
        $result = Database::fetch($sql, ['id' => $userId]);
        
        return $result ? $this->hideFields($result) : null;
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(int $userId): array
    {
        $sql = "SELECT p.* FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                INNER JOIN users u ON u.role_id = rp.role_id
                WHERE u.id = :user_id AND p.status = 'active'";
        return Database::fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(int $userId, string $permissionSlug): bool
    {
        $sql = "SELECT COUNT(*) as count FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                INNER JOIN users u ON u.role_id = rp.role_id
                WHERE u.id = :user_id AND p.slug = :slug AND p.status = 'active'";
        $result = Database::fetch($sql, [
            'user_id' => $userId,
            'slug' => $permissionSlug
        ]);
        return $result['count'] > 0;
    }

    /**
     * Hide sensitive fields (override parent)
     */
    protected function hideFields(array $data): array
    {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        
        // Add computed fields
        $data['full_name'] = $this->getFullName($data);
        
        return $data;
    }
}
