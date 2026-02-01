<?php
use Config\MenuHelper;
$permittedMenus = MenuHelper::getMenus($_SESSION['role_id'] ?? 0);
?>
<style>
.access-alert {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    margin: 20px 0;
}
.access-alert .alert-icon {
    font-size: 32px;
    margin-right: 15px;
}
.access-alert .alert-content h5 {
    color: #856404;
    margin-bottom: 5px;
    font-weight: 600;
}
.access-alert .alert-content p {
    color: #856404;
    opacity: 0.9;
    font-size: 14px;
}
</style>
<!-- Main Menu area start -->
<div class="main-menu-area mg-tb-40">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php
                if($_SESSION['role_id']==1){
                  ?> 
                <ul class="nav nav-tabs notika-menu-wrap menu-it-icon-pro">
                    <li class="active">
                        <a data-toggle="tab" href="javascript:void(0)" onclick="location.reload()">
                            <i class="notika-icon notika-house"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#UsersRoles">
                            <i class="notika-icon notika-support"></i> Users & Roles
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#Settings">
                            <i class="notika-icon notika-settings"></i> Settings
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#ProductSetting">
                            <i class="notika-icon notika-house"></i>Products Management
                        </a>
                    </li>
                     <li>
                        <a data-toggle="tab" href="#Finance">
                            <i class="notika-icon notika-dollar"></i> Financial Management
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#Expenses">
                            <i class="notika-icon notika-edit"></i>Expense Management
                        </a>
                    </li>
                   
                   <li class="menu-item">
                    <a data-toggle="tab" href="#Advances">
                        <i class="notika-icon notika-form"></i> Advances Management
                    </a>
                </li>

                <li class="menu-item">
                    <a data-toggle="tab" href="#Permissions">
                        <i class="notika-icon notika-next"></i> Manage Access
                    </a>
                </li>
                <li>
                        <a data-toggle="tab" href="#StockManagement">
                            <i class="notika-icon notika-edit"></i>Stock Management
                        </a>
                    </li>
                <li>
                        <a data-toggle="tab" href="#SellingManagement">
                            <i class="notika-icon notika-edit"></i>Selling Management
                        </a>
                    </li>    
                <li>
                    <a data-toggle="tab" href="#ProductTransfer">
                        <i class="notika-icon notika-edit"></i>Product transfer
                    </a>
                </li>
                         <li>
                        <a data-toggle="tab" href="#Investment">
                            <i class="notika-icon notika-dollar"></i> Investment
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#Reports">
                            <i class="notika-icon notika-bar-chart"></i> Reports
                        </a>
                    </li>
                </ul>
                <?php }
                else if (!empty($permittedMenus)){
                ?>    
                <ul class="nav nav-tabs notika-menu-wrap menu-it-icon-pro">
                    <?php $first = true; foreach ($permittedMenus as $menu): 
                        if ($menu['menu_name'] == 'Manage Access' && $roleId != 1) continue; ?>
                    <li class="<?= $first ? 'active' : '' ?>">
                        <a data-toggle="tab" href="<?= $menu['url'] ?>">
                            <i class="notika-icon <?= $menu['icon'] ?>"></i> 
                            <?= $menu['menu_name'] ?>
                        </a>
                    </li>
                    <?php $first = false; endforeach; ?>
                </ul>   
                <?php }
                else{
                    ?>
                 <div class="access-alert">
                    <div class="alert-icon">
                        <i class="notika-icon notika-alert text-warning"></i>
                    </div>
                    <div class="alert-content">
                        <h5>Access Required</h5>
                        <p class="mb-0">You need menu permissions to use the system. Please contact the administrator.</p>
                    </div>
                </div>  
                <?php } ?>
                

                <div class="tab-content custom-menu-content">
                    <!-- Users & Roles Content -->
                    <div id="UsersRoles" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                         <li><a href="javascript:void(0)" onclick="loadContent('manage-users')">Manage Users</a></li>
                         <li><a href="javascript:void(0)" onclick="loadContent('manage-roles')">Manage Roles</a></li>
                        </ul>
                    </div>
               <!-- Settings -->
                    <div id="Settings" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('profile')">Company Profile</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('stations')">Stations</a></li>
                             <li><a href="javascript:void(0)" onclick="loadContent('suppliers')">Suppliers</a></li>
                             <li><a href="javascript:void(0)" onclick="loadContent('company-clients')">Clients</a></li>                      
                        </ul>
                    </div>
                    <!-- Expenses -->
                    <div id="Expenses" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expense-categories')">Expense Categories</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expenses')">Expense Type</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expense-consumers')">Expense Consumers</a></li>
             
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expense-consume')">Expense Consumption</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('expense-consumed-statement')">Expense Statement</a></li>
                     
                        </ul>
                    </div>
                                       <!-- Investment -->
                    <div id="Investment" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('account-recharge')">Account Recharge</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('approve-investments')">Approve Investments</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('my-rejected-investments')">My Rejected Investments</a></li>
                        </ul>
                    </div>
                    <!-- Reports -->
                    <div id="Reports" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('general-items-sales-report')">General Items Sales Report</a></li>
                        </ul>
                    </div>
                    <!-- Products -->
                    <div id="ProductSetting" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                          <li><a href="javascript:void(0)" onclick="loadContent('unity')">Unity</a></li>
                          <li><a href="javascript:void(0)" onclick="loadContent('coffee-categories')">Product Categories</a></li>
                          <li><a href="javascript:void(0)" onclick="loadContent('coffee-types')">Product Types</a></li>
                          <li><a href="javascript:void(0)" onclick="loadContent('coffee-types-assign-unity')">Assign Product</a></li>
                        </ul>
                    </div>
                     <!-- Financial Management -->
                    <div id="Finance" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                        <li><a href="javascript:void(0)" onclick="loadContent('manage-accounts')">Accounts</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('accounts-transfer')">Accounts Transfer</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('account-recharge')">Account Recharge</a></li>
                        </ul>
                    </div>

                    <div id="Advances" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                        <li><a href="javascript:void(0)" onclick="loadContent('request-Advance')">Request Advance</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('pending-request-Advance')">Pending Requests Advance</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('disburse-approved-Advance')">Advance Disbursement</a></li>
                         <!--<li><a href="javascript:void(0)" onclick="loadContent('disburse-Advance-report')">Disbursement Report</a></li>-->
                        </ul>
                    </div>
                    <div id="Permissions" class="tab-pane in notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-access')">Manage permissions</a></li>
                        </ul>
                    </div>
                    
                    <!-- Product Transfer -->
                    <div id="ProductTransfer" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                          <li><a href="javascript:void(0)" onclick="loadContent('Station-to-warehouse')">Transfer product</a></li>
                        </ul>
                    </div>
                    
                    <!-- Stock Management -->
                    <div id="StockManagement" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('station-manage-stock')">Manage Stock</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('finance-stock-approval')">Finance Stock Approval</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('transfer-to-production')">Transifer To Production</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('product-mixing')">Product Mixing</a></li>
                             <li><a href="javascript:void(0)" onclick="loadContent('supplier-statement')">Supplier Statement</a></li>
                        </ul>
                    </div>
                    <!-- Selling Management -->
                    <div id="SellingManagement" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-selling')">Manage Selling</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('sales-price-approval')">Sales Price Approval</a></li>
                        </ul>
                    </div>
                    
                    <!-- Reports -->
                    <div id="Reports" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('general-items-sales-report')">General Items Sales Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


