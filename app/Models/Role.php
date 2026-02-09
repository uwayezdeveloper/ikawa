<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Role Model
 */
class Role extends Model
{
    protected string $table = 'roles';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'description',
        'status'
    ];

    /**
     * Find role by name
     */
    public function findByName(string $name): ?array
    {
        return $this->findBy('name', $name);
    }

    /**
     * Get all active roles
     */
    public function getActiveRoles(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Alias for getActiveRoles
     */
    public function getActive(): array
    {
        return $this->getActiveRoles();
    }

    /**
     * Get all roles with pagination
     */
    public function getAllPaginated(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = Database::fetch($countSql)['total'];
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
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
     * Search roles
     */
    public function search(string $keyword): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE :keyword OR description LIKE :keyword 
                ORDER BY name ASC";
        return Database::fetchAll($sql, ['keyword' => "%{$keyword}%"]);
    }

    /**
     * Check if role name exists (excluding specific id for updates)
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Toggle role status
     */
    public function toggleStatus(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET 
                status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END,
                updated_at = NOW() 
                WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Get roles count by status
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
}
