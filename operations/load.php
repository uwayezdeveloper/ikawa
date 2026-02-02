<?php
require __DIR__ . '/../_ikawa/config/App.php';
require __DIR__ . '/../_ikawa/middleware/auth.php';

$page = $_GET[ 'page' ] ?? 'dashboard';

// whitelist of allowed pages
$allowed_pages = [
    'dashboard' => 'views/dashboard.php',
    'manage-users' => 'views/manage-users.php',
    'manage-roles' => 'views/manage-roles.php',
    'permissions' => 'views/permissions-data.php',
    'suppliers'=>'views/suppliers-data.php',
    'profile'=>'views/profile-data.php',
    'stations'=>'views/stations-data.php',
    'manage-accounts' => 'views/manage-accounts.php',
    'account-recharge' => 'views/manage-account-recharge.php',
    'approve-investments' => 'views/approve-investments.php',
    'my-rejected-investments' => 'views/my-rejected-investments.php',
    'manage-expense-categories' => 'views/manage-expense-categories.php',
    'manage-expense-consumers' => 'views/manage-expense-consumers.php',
    'manage-expenses' => 'views/manage-expenses.php',
    'manage-expense-consume' => 'views/manage-expense-consume.php',
    'expense-consumed-statement' => 'views/expense-consumed-statement.php',
    'sellize-management'=>'views/sellize-management.php',
    'coffee-categories'=>'views/coffee-categories.php',
    'coffee-types'=>'views/coffee-types.php',
    'coffee-types-assign-unity'=>'views/coffee-types-assign-unity.php',
    'unity'=>'views/unity.php',
    'company-clients'=>'views/company-clients-data.php',
    'accounts-transfer'=>'views/FinancialManagement/accounts-transfer-data.php',
    'request-Advance'=> 'views/AdvanceManagment/request-Advance-data.php',
    'pending-request-Advance'=>'views/AdvanceManagment/pending-request-Advance-data.php',
    'disburse-approved-Advance'=>'views/AdvanceManagment/disburse-approved-Advance-data.php',
    'manage-access'=>'views/ManageAccess/manage-access-data.php',
    'Station-to-warehouse'=>'views/station-to-warehouse.php',
    'pending-transaction'=>'views/pending-transaction.php',
    'disburse-Advance-report'=>'views/AdvanceManagment/disburse-Advance-report-data.php',
    'station-manage-stock'=>'views/station-manage-stock.php',
    'transfer-to-production'=>'views/transfer-to-production.php',
    'supplier-statement' => 'views/supplier-statement.php',
    'finance-stock-approval'=>'views/finance-stock-approval.php',
    'product-mixing'=>'views/product-mixing.php',
    'manage-selling'=>'views/manage-selling.php',
    'sales-price-approval'=>'views/sales-price-approval.php',
    'general-items-sales-report'=>'views/general-items-sales-report.php',
    'general-items-stock-report'=>'views/general-items-stock-report.php',
    'general-items-prices-report'=>'views/general-items-prices-report.php',

];

if ( isset( $allowed_pages[ $page ] ) ) {
    require __DIR__ . '/' . $allowed_pages[ $page ];
} else {
    http_response_code( 404 );
    echo '<h3>Page not found</h3>';
}

