<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Menu extends Model
{
    protected string $table = 'tbl_menus';
    protected string $primaryKey = 'menu_id';

    protected array $fillable = [
        'menu_name',
        'menu_identifier',
        'status'
    ];

    public function findByIdentifier(string $identifier): ?array
    {
        return $this->findBy('menu_identifier', $identifier);
    }

    public function getActiveMenus(): array
    {
        $sql = "SELECT DISTINCT * FROM {$this->table} WHERE status = 1 ORDER BY menu_name ASC";
        return Database::fetchAll($sql);
    }

    public function getAllPaginated(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = (int) (Database::fetch($countSql)['total'] ?? 0);

        $sql = "SELECT * FROM {$this->table} ORDER BY menu_name ASC LIMIT :limit OFFSET :offset";
        $results = Database::fetchAll($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);

        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage)
        ];
    }

    public function identifierExists(string $identifier, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE menu_identifier = :identifier";
        $params = ['identifier' => $identifier];

        if ($excludeId !== null) {
            $sql .= " AND {$this->primaryKey} != :id";
            $params['id'] = $excludeId;
        }

        $result = Database::fetch($sql, $params);
        return (int) ($result['count'] ?? 0) > 0;
    }

    public function getByRoleId(int $roleId): array
    {
        $sql = "SELECT m.*
                FROM {$this->table} m
                INNER JOIN tbl_roles_to_menu rm ON m.menu_id = rm.menu_id
                WHERE rm.role_id = :role_id
                ORDER BY m.menu_name ASC";
        return Database::fetchAll($sql, ['role_id' => $roleId]);
    }

    public function getMenuIdsByRole(int $roleId): array
    {
        $sql = "SELECT menu_id FROM tbl_roles_to_menu WHERE role_id = :role_id";
        $results = Database::fetchAll($sql, ['role_id' => $roleId]);
        return array_map('intval', array_column($results, 'menu_id'));
    }

    public function assignToRole(int $roleId, array $menuIds): bool
    {
        try {
            Database::beginTransaction();

            Database::query("DELETE FROM tbl_roles_to_menu WHERE role_id = :role_id", ['role_id' => $roleId]);

            if (!empty($menuIds)) {
                $insertSql = "INSERT INTO tbl_roles_to_menu (role_id, menu_id) VALUES (:role_id, :menu_id)";
                foreach ($menuIds as $menuId) {
                    Database::query($insertSql, [
                        'role_id' => $roleId,
                        'menu_id' => (int) $menuId
                    ]);
                }
            }

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            return false;
        }
    }

    public function getCountByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $results = Database::fetchAll($sql);

        $counts = ['active' => 0, 'inactive' => 0, 'total' => 0];
        foreach ($results as $row) {
            if ((int) $row['status'] === 1) {
                $counts['active'] = (int) $row['count'];
            } else {
                $counts['inactive'] = (int) $row['count'];
            }
            $counts['total'] += (int) $row['count'];
        }

        return $counts;
    }

    public static function generateIdentifier(string $name): string
    {
        $identifier = strtolower(trim($name));
        $identifier = preg_replace('/[^a-z0-9]+/', '-', $identifier);
        $identifier = preg_replace('/-+/', '-', $identifier);
        return trim($identifier, '-');
    }
}
