<?php
namespace Config;

require_once __DIR__ . '/Database.php';

class MenuHelper {
    public static function getMenus($roleId) {
        $database = new Database();
        $conn = $database->getConnection();
        
        $sql = "SELECT m.* FROM menus m 
                INNER JOIN role_menus rm ON m.menu_id = rm.menu_id 
                WHERE rm.role_id = :role_id AND m.is_active = 1 
                ORDER BY m.sort_order";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':role_id' => $roleId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}