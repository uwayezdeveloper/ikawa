<?php
$action = $_POST[ 'action' ] ?? $_GET[ 'page' ] ?? 'dashboard';

switch ( $action ) {

    case 'login':
    case 'dashboard':
    require __DIR__ . '/views/layout.php';
    break;

    default:
    http_response_code( 404 );
    echo 'Page not found';
}
?>