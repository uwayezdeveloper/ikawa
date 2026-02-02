<?php
$action = $_POST[ 'action' ] ?? $_GET[ 'page' ] ?? 'dashboard';

switch ( $action ) {
    case 'login':
    case 'dashboard':
        require __DIR__ . '/views/layout.php';
        break;

    default:
        // Route all other pages through load.php
        require __DIR__ . '/load.php';
        break;
}
?>