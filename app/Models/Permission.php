<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Permission Model
 */
class Permission extends Model
{
    protected string $table = 'permissions';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'status'
    ];

    /**
     * Find permission by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Get all active permissions
     */
    public function getActivePermissions(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY module, name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get permissions grouped by module
     */
    public function getGroupedByModule(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY module, name ASC";
        $permissions = Database::fetchAll($sql);
        
        $grouped = [];
        foreach ($permissions as $permission) {
            $module = $permission['module'] ?? 'general';
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }

    /**
     * Get all permissions with pagination
     */
    public function getAllPaginated(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = Database::fetch($countSql)['total'];
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} ORDER BY module, name ASC LIMIT :limit OFFSET :offset";
        $results = Database::fetchAll($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Check if permission slug exists (excluding specific id for updates)
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = :slug";
        $params = ['slug' => $slug];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Get permissions for a specific role
     */
    public function getByRoleId(int $roleId): array
    {
        $sql = "SELECT p.* FROM {$this->table} p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id AND p.status = 'active'
                ORDER BY p.module, p.name ASC";
        return Database::fetchAll($sql, ['role_id' => $roleId]);
    }

    /**
     * Get permission IDs for a specific role
     */
    public function getPermissionIdsByRole(int $roleId): array
    {
        $sql = "SELECT permission_id FROM role_permissions WHERE role_id = :role_id";
        $results = Database::fetchAll($sql, ['role_id' => $roleId]);
        return array_map('intval', array_column($results, 'permission_id'));
    }

    /**
     * Assign permissions to a role
     */
    public function assignToRole(int $roleId, array $permissionIds): bool
    {
        try {
            // Start transaction
            Database::beginTransaction();
            
            // Remove existing permissions for the role
            $deleteSql = "DELETE FROM role_permissions WHERE role_id = :role_id";
            Database::query($deleteSql, ['role_id' => $roleId]);
            
            // Insert new permissions
            if (!empty($permissionIds)) {
                $insertSql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
                foreach ($permissionIds as $permissionId) {
                    Database::query($insertSql, [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                }
            }
            
            // Commit transaction
            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            return false;
        }
    }

    /**
     * Check if a role has a specific permission
     */
    public function roleHasPermission(int $roleId, string $permissionSlug): bool
    {
        $sql = "SELECT COUNT(*) as count FROM role_permissions rp
                INNER JOIN {$this->table} p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id AND p.slug = :slug AND p.status = 'active'";
        $result = Database::fetch($sql, [
            'role_id' => $roleId,
            'slug' => $permissionSlug
        ]);
        return $result['count'] > 0;
    }

    /**
     * Get distinct modules
     */
    public function getModules(): array
    {
        $sql = "SELECT DISTINCT module FROM {$this->table} ORDER BY module ASC";
        $results = Database::fetchAll($sql);
        return array_column($results, 'module');
    }

    /**
     * Get permissions count by status
     */
    public function getCountByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $results = Database::fetchAll($sql);
        
        $counts = ['active' => 0, 'inactive' => 0, 'total' => 0];
        foreach ($results as $row) {
            $counts[$row['status']] = $row['count'];
            $counts['total'] += $row['count'];
        }
        
        return $counts;
    }

    /**
     * Generate slug from name
     */
    public static function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
