<?php
/**
 * Setup menu access tables and seed global menus
 * Run once: http://localhost/gihnew/setup_menus.php
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h2>Setup Menu Access</h2><hr>';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_menus (
        menu_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        menu_name VARCHAR(50) NOT NULL,
        menu_identifier VARCHAR(50) NOT NULL,
        status INT(11) NOT NULL DEFAULT 1,
        UNIQUE KEY uq_menu_identifier (menu_identifier)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_roles_to_menu (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        role_id INT(11) NOT NULL,
        menu_id INT(11) NOT NULL,
        UNIQUE KEY uq_role_menu (role_id, menu_id),
        KEY idx_role_id (role_id),
        KEY idx_menu_id (menu_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $menus = [
        ['Dashboard', 'dashboard'],
        ['Users', 'users'],
        ['Settings', 'settings'],
        ['Products', 'products'],
        ['Suppliers', 'suppliers'],
        ['Clients', 'clients'],
        ['Finance', 'finance'],
        ['Stock', 'stock'],
        ['Warehouse', 'warehouse'],
        ['Production', 'production'],
        ['Expense Management', 'expenses'],
        ['Non-Exploitable Mgmt', 'non-exploitable'],
        ['Certification', 'certification'],
        ['Property Management', 'properties']
    ];

    $insertMenu = $pdo->prepare("INSERT INTO tbl_menus (menu_name, menu_identifier, status)
                                 VALUES (:menu_name, :menu_identifier, 1)
                                 ON DUPLICATE KEY UPDATE menu_name = VALUES(menu_name)");

    foreach ($menus as $menu) {
        $insertMenu->execute([
            'menu_name' => $menu[0],
            'menu_identifier' => $menu[1]
        ]);
    }

    $adminRole = $pdo->query("SELECT id FROM roles WHERE name = 'Administrator' LIMIT 1")->fetch();
    if ($adminRole) {
        $adminRoleId = (int) $adminRole['id'];
        $menuIds = $pdo->query("SELECT menu_id FROM tbl_menus WHERE status = 1")->fetchAll();

        $insertRoleMenu = $pdo->prepare("INSERT IGNORE INTO tbl_roles_to_menu (role_id, menu_id) VALUES (:role_id, :menu_id)");
        foreach ($menuIds as $row) {
            $insertRoleMenu->execute([
                'role_id' => $adminRoleId,
                'menu_id' => (int) $row['menu_id']
            ]);
        }
    }

    echo '<p style="color:green;">✅ Menu tables are ready and default global menus are registered.</p>';
    echo '<p><a href="' . APP_URL . '/permissions">Open Manage Menus</a></p>';
    echo '<p><a href="' . APP_URL . '/permissions/roles">Open Role Menus</a></p>';
} catch (Throwable $e) {
    echo '<p style="color:red;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
