<?php
require __DIR__ . '/../../_ikawa/config/App.php';
require __DIR__ . '/../../_ikawa/middleware/auth.php';
require __DIR__ . '/../../pages/header.php';
require __DIR__ . '/../../vendor/topcontent.php';
require __DIR__ . '/../../_ikawa/config/MenuHelper.php';
require __DIR__ . '/../../vendor/navbar.php';
require __DIR__ . '/toasters.php';
?>

<div id = 'app'>
<?php require __DIR__ . '/dashboard.php';
?>
</div>

<?php
require __DIR__ . '/../../pages/footer.php';
?>
<?php require __DIR__ . '/getpages.php';
?>
<?php require __DIR__ . '/getStockpages.php';
?>
<?php require __DIR__ . '/scripts/user-scripts.php';
?>
<?php require __DIR__ . '/scripts/roles-scripts.php';
?>
<?php require __DIR__ . '/scripts/supplier-scripts.php';
?>
<?php require __DIR__ . '/scripts/company-scripts.php';
?>

<?php require __DIR__ . '/scripts/stations-scripts.php';
?>
<!-- accounts -->
<?php require __DIR__ . '/scripts/account-scripts.php';
?>
<!-- account recharge -->
<?php require __DIR__ . '/scripts/recharge-scripts.php';
?>
<!-- investment approval -->
<?php require __DIR__ . '/scripts/investment-scripts.php';
?>
<!-- expense category -->
<?php require __DIR__ . '/scripts/expense-category-scripts.php';
?>
<!-- expense consume new -->
<?php require __DIR__ . '/scripts/expense-consumer-scripts-new.php';
?>
<!-- expense -->
<?php require __DIR__ . '/scripts/expense-scripts.php';
?>
<!-- expense consume  -->
<?php require __DIR__ . '/scripts/expense-consume-scripts.php';
?>
<!-- expense statement  -->
<?php require __DIR__ . '/scripts/expense-statement-scripts.php';
?>
<!-- Selize -->
<?php require __DIR__ . '/scripts/sellize-script.php';
?>
<!-- Category -->
<?php require __DIR__ . '/scripts/category-script.php';
?>
<!-- Category type-->
<?php require __DIR__ . '/scripts/category-type-script.php';
?>
<!-- Unity -->
<?php require __DIR__ . '/scripts/unity-script.php';
?>
<!-- Category type unit -->
<?php require __DIR__ . '/scripts/category-type-unity-script.php';
?>
<!-- clients scripts -->

<?php require __DIR__ . '/scripts/company-clients-scripts.php';
?>
<!-- accounts transfers -->
<?php require __DIR__ . '/FinancialManagement/accounts-transfer-scripts.php';
?>
<!-- advance -->
<?php require __DIR__ . '/AdvanceManagment/request-Advance-scripts.php';
?>
<!-- access -->
<?php require __DIR__ . '/ManageAccess/manage-access-scripts.php';
?>

<!-- Station to Warehouse Transfer -->
<?php require __DIR__ . '/scripts/station-to-warehouse-scripts.php';
?>
<!-- Station Stock Management -->
<?php require __DIR__ . '/scripts/station-stock-script.php';
?>
<!-- Transfer to Production -->
<?php require __DIR__ . '/scripts/transfer-to-production-script.php';

?>
<?php require __DIR__ . '/scripts/supplier-statement-scripts.php'; ?>
<!-- Finance Stock Approval -->
<?php require __DIR__ . '/scripts/finance-stock-approval-script.php';
?>
<!-- Product Mixing -->
<?php require __DIR__ . '/scripts/product-mixing-script.php';
?>
<!-- Selling Management -->
<?php require __DIR__ . '/scripts/selling-management-script.php';
?>
<!-- Sales Price Approval -->
<?php require __DIR__ . '/scripts/sales-price-approval-script.php';
?>
