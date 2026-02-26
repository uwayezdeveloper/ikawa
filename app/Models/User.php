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
        'location_id',
        'status',
        'email_verified_at'
    ];
    
    protected array $hidden = ['password'];

    protected array $menuPermissionMap = [
        'dashboard' => ['dashboard'],
        'users' => ['view-users', 'create-users', 'edit-users', 'delete-users', 'view-roles', 'create-roles', 'edit-roles', 'delete-roles', 'view-permissions', 'manage-permissions'],
        'settings' => ['view-settings', 'manage-settings', 'view-company', 'edit-company', 'view-location-types', 'create-location-types', 'edit-location-types', 'delete-location-types', 'view-locations', 'create-locations', 'edit-locations', 'delete-locations', 'view-location-categories', 'manage-location-categories'],
        'products' => ['view-product-categories', 'create-product-categories', 'edit-product-categories', 'delete-product-categories', 'view-category-types', 'create-category-types', 'edit-category-types', 'delete-category-types', 'view-measurement-units', 'create-measurement-units', 'edit-measurement-units', 'delete-measurement-units', 'view-type-unit-assignments', 'manage-type-unit-assignments'],
        'suppliers' => ['view-supplier-types', 'create-supplier-types', 'edit-supplier-types', 'delete-supplier-types', 'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers', 'view-supplier-advances', 'create-supplier-advances', 'edit-supplier-advances', 'delete-supplier-advances', 'approve-supplier-advances', 'view-supplier-records'],
        'clients' => ['view-clients', 'create-clients', 'edit-clients', 'delete-clients', 'view-client-types', 'create-client-types', 'edit-client-types', 'delete-client-types', 'view-suppliers'],
        'finance' => ['view-payment-modes', 'create-payment-modes', 'edit-payment-modes', 'delete-payment-modes', 'view-account-transactions', 'view-station-finances', 'pay-supplier-payables', 'view-accounts', 'create-accounts', 'edit-accounts', 'delete-accounts'],
        'stock' => ['view-stock-receives', 'create-stock-receives', 'approve-stock-receives', 'delete-stock-receives', 'view-stock-transfers', 'create-stock-transfers', 'approve-stock-transfers', 'delete-stock-transfers'],
        'warehouse' => ['view-warehouse-stock', 'view-warehouse-incoming', 'receive-warehouse-transfers', 'view-stock-transfers', 'create-stock-transfers', 'approve-stock-transfers'],
        'production' => ['view-production', 'create-production', 'complete-production'],
        'expenses' => ['view-expenses', 'manage-expenses', 'view-accounts', 'view-payment-modes'],
        'non-exploitable' => ['view-non-exploitable', 'manage-non-exploitable', 'view-accounts', 'view-payment-modes'],
        'certification' => ['view-certification', 'manage-certification', 'view-accounts', 'view-payment-modes'],
        'properties' => ['properties', 'view-properties', 'create-properties', 'edit-properties', 'delete-properties', 'view-accounts', 'create-accounts', 'edit-accounts', 'delete-accounts']
    ];

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
     * Get user menu identifiers from menu-role mapping
     */
    public function getUserMenuIdentifiers(int $userId): array
    {
        $sql = "SELECT DISTINCT m.menu_identifier
                FROM tbl_menus m
                INNER JOIN tbl_roles_to_menu rm ON m.menu_id = rm.menu_id
                INNER JOIN users u ON u.role_id = rm.role_id
                WHERE u.id = :user_id AND m.status = 1
                ORDER BY m.menu_identifier ASC";
        $results = Database::fetchAll($sql, ['user_id' => $userId]);
        return array_column($results, 'menu_identifier');
    }

    /**
     * Get legacy-style permission strings expanded from menu identifiers
     */
    public function getUserPermissions(int $userId): array
    {
        $menuIdentifiers = $this->getUserMenuIdentifiers($userId);
        return $this->getLegacyPermissionsFromMenus($menuIdentifiers);
    }

    /**
     * Build compatible permissions list from menu identifiers
     */
    public function getLegacyPermissionsFromMenus(array $menuIdentifiers): array
    {
        $permissions = [];

        foreach ($menuIdentifiers as $identifier) {
            $permissions[] = $identifier;
            if (isset($this->menuPermissionMap[$identifier])) {
                $permissions = array_merge($permissions, $this->menuPermissionMap[$identifier]);
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Check if user has a specific permission (compatible check)
     */
    public function hasPermission(int $userId, string $permissionSlug): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permissionSlug, $permissions, true);
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
