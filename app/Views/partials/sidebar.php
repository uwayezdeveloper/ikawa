<div class="sidenav-menu">
    <!-- Brand Logo -->
    <a href="<?= APP_URL ?>/dashboard" class="logo">
        <span class="logo logo-light">
            <span class="logo-lg"><img src="<?= \App\Core\View::asset('images/logo.png') ?>" alt="logo" /></span>
            <span class="logo-sm"><img src="<?= \App\Core\View::asset('images/logo-sm.png') ?>"
                    alt="small logo" /></span>
        </span>
        <span class="logo logo-dark">
            <span class="logo-lg"><img src="<?= \App\Core\View::asset('images/logo-black.png') ?>"
                    alt="dark logo" /></span>
            <span class="logo-sm"><img src="<?= \App\Core\View::asset('images/logo-sm.png') ?>"
                    alt="small logo" /></span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-on-hover">
        <i class="ti ti-circle align-middle"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-offcanvas">
        <i class="ti ti-menu-4 align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar="">
        <!-- User Profile Section -->
        <div id="user-profile-settings" class="sidenav-user"
            style="background: url(<?= \App\Core\View::asset('images/user-bg-pattern.svg') ?>)">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= APP_URL ?>/profile" class="link-reset">
                        <?php
                        $avatarUrl = !empty($user['avatar']) ? APP_URL . '/' . $user['avatar'] : \App\Core\View::asset('images/users/user-1.jpg');
                        ?>
                        <img src="<?= $avatarUrl ?>" alt="user-image"
                            class="rounded-circle mb-2 avatar-md" style="object-fit: cover; width: 48px; height: 48px;" />
                        <span class="sidenav-user-name fw-bold"><?= ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? 'User') ?></span>
                        <span class="fs-12 fw-semibold"><?= $user['role_name'] ?? 'Guest' ?></span>
                    </a>
                </div>
                <div>
                    <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon"
                        data-bs-toggle="dropdown" data-bs-offset="0,12" href="#" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome back!</h6>
                        </div>
                        <a href="<?= APP_URL ?>/profile" class="dropdown-item">
                            <i class="ti ti-user-circle me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Profile</span>
                        </a>
                        <a href="<?= APP_URL ?>/settings" class="dropdown-item">
                            <i class="ti ti-settings-2 me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Account Settings</span>
                        </a>
                        <a href="<?= APP_URL ?>/logout" class="dropdown-item text-danger fw-semibold">
                            <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidenav Menu -->
        <div id="sidenav-menu">
            <ul class="side-nav">
                <li class="side-nav-title mt-2">Main</li>

                <!-- Dashboard -->
                <li class="side-nav-item">
                    <a href="<?= APP_URL ?>/dashboard" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                <?php
                $menuIdentifiers = $user['menu_identifiers'] ?? [];
                $isSuperAdmin = ((int) ($user['role_id'] ?? 0) === 1);
                $hasMenu = function (string $identifier) use ($menuIdentifiers): bool {
                    return in_array($identifier, $menuIdentifiers, true);
                };

                if ($isSuperAdmin) {
                    $hasMenu = function (string $identifier): bool {
                        return true;
                    };
                }

                $hasAnyManagementMenu =
                    $hasMenu('users') ||
                    $hasMenu('settings') ||
                    $hasMenu('products') ||
                    $hasMenu('suppliers') ||
                    $hasMenu('clients') ||
                    $hasMenu('finance') ||
                    $hasMenu('stock') ||
                    $hasMenu('station-finance') ||
                    $hasMenu('warehouse') ||
                    $hasMenu('production') ||
                    $hasMenu('expenses') ||
                    $hasMenu('non-exploitable') ||
                    $hasMenu('certification') ||
                    $hasMenu('properties') ||
                    $hasMenu('account-reconciliation');
                ?>

                <?php if ($hasAnyManagementMenu): ?>
                <li class="side-nav-title mt-2">Management</li>

                <!-- Users -->
                <?php if ($hasMenu('users')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#usersMenu" aria-expanded="false" aria-controls="usersMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text">Users</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="usersMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/users" class="side-nav-link">
                                    <span class="menu-text">All Users</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/roles" class="side-nav-link">
                                    <span class="menu-text">Roles</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/permissions" class="side-nav-link">
                                    <span class="menu-text">Menus</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/permissions/roles" class="side-nav-link">
                                    <span class="menu-text">Role Menus</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/users/create" class="side-nav-link">
                                    <span class="menu-text">Add User</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Settings -->
                <?php if ($hasMenu('settings')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#settingsMenu" aria-expanded="false" aria-controls="settingsMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-settings"></i></span>
                        <span class="menu-text">Settings</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="settingsMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/company" class="side-nav-link">
                                    <span class="menu-text">Company</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/location-types" class="side-nav-link">
                                    <span class="menu-text">Location Types</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/locations" class="side-nav-link">
                                    <span class="menu-text">Locations</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/location-categories" class="side-nav-link">
                                    <span class="menu-text">Location Categories</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/processing-steps" class="side-nav-link">
                                    <span class="menu-text">Processing Steps</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Products -->
                <?php if ($hasMenu('products')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#productsMenu" aria-expanded="false" aria-controls="productsMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-package"></i></span>
                        <span class="menu-text">Products</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="productsMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/categories" class="side-nav-link">
                                    <span class="menu-text">Categories</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/category-types" class="side-nav-link">
                                    <span class="menu-text">Category Types</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/inner-category-types" class="side-nav-link">
                                    <span class="menu-text">Inner Category Types</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/measurement-units" class="side-nav-link">
                                    <span class="menu-text">Measurement Units</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/type-units" class="side-nav-link">
                                    <span class="menu-text">Type Unit Assignments</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Suppliers -->
                <?php if ($hasMenu('suppliers')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#suppliersMenu" aria-expanded="false" aria-controls="suppliersMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-truck"></i></span>
                        <span class="menu-text">Suppliers</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="suppliersMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/suppliers" class="side-nav-link">
                                    <span class="menu-text">Suppliers List</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/suppliers/types" class="side-nav-link">
                                    <span class="menu-text">Supplier Types</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/suppliers/advances" class="side-nav-link">
                                    <span class="menu-text">Advances</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/suppliers/records" class="side-nav-link">
                                    <span class="menu-text">Supplier Records</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                
                <!-- Clients -->
                <?php if ($hasMenu('clients')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#clientsMenu" aria-expanded="false" aria-controls="clientsMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-users-group"></i></span>
                        <span class="menu-text">Clients</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="clientsMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/clients" class="side-nav-link">
                                    <span class="menu-text">All Clients</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/client-types" class="side-nav-link">
                                    <span class="menu-text">Client Types</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Finance -->
                <?php if ($hasMenu('finance')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#financeMenu" aria-expanded="false" aria-controls="financeMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-report-money"></i></span>
                        <span class="menu-text">Finance</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <div class="collapse" id="financeMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/payment-modes" class="side-nav-link">
                                    <span class="menu-text">Payment Modes</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/transactions" class="side-nav-link">
                                    <span class="menu-text">Transactions</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances" class="side-nav-link">
                                    <span class="menu-text">Station Finances</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/withdraw" class="side-nav-link">
                                    <span class="menu-text">Withdraw</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/journal" class="side-nav-link">
                                    <span class="menu-text">Journal</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/final-report" class="side-nav-link">
                                    <span class="menu-text">Final Report</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/detailed-expense-report" class="side-nav-link">
                                    <span class="menu-text">Detailed Expense Report</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/accounts" class="side-nav-link">
                                    <span class="menu-text">Accounts</span>
                                </a>
                            </li>
                              <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-recharge" class="side-nav-link">
                                    <span class="menu-text">Cash Replenishment</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-transfer" class="side-nav-link">
                                    <span class="menu-text">Account Transfer</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/transfer-to-account" class="side-nav-link">
                                    <span class="menu-text">Transfer to Another Account</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/global-transfer" class="side-nav-link">
                                    <span class="menu-text">Global Transfer</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-recharge/history" class="side-nav-link">
                                    <span class="menu-text">Transaction History</span>
                                </a>
                            </li>
                             <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans/create" class="side-nav-link">
                                    <span class="menu-text">Request Loan</span>
                                </a>
                            </li>
                              <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans" class="side-nav-link">
                                    <span class="menu-text">Manage Loans</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans/disbursement" class="side-nav-link">
                                    <span class="menu-text">Loan Disbursement</span>
                                </a>
                            </li>
                                    
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loan-payments/create" class="side-nav-link">
                                    <span class="menu-text">Pay Worker Loan</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loan-payments" class="side-nav-link">
                                    <span class="menu-text">Loan Payment History</span>
                                </a>
                            </li>
                             <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans/statement" class="side-nav-link">
                                    <span class="menu-text">Loan Statements</span>
                                </a>
                            </li>
                                                        <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/proforma-invoice" class="side-nav-link">
                                    <span class="menu-text">Proforma Invoice</span>
                                </a>
                            </li>
                              <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/source-of-income" class="side-nav-link">
                                    <span class="menu-text">Register Source of Income</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/autre-credit" class="side-nav-link">
                                    <span class="menu-text">Autre Credit</span>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Account Reconciliation -->
                <?php if ($hasMenu('account-reconciliation')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#reconciliationMenu" aria-expanded="false" aria-controls="reconciliationMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-file-check"></i></span>
                        <span class="menu-text">Account Reconciliation</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <div class="collapse" id="reconciliationMenu">
                        <ul class="side-nav-second-level">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/reconciliation" class="side-nav-link">
                                    <span class="menu-text">Dashboard</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/reconciliation/view-all-accounts" class="side-nav-link">
                                    <span class="menu-text">View All Accounts</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                
                <!-- Stock Management -->
                <?php if ($hasMenu('stock')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#stockMenu" aria-expanded="false" aria-controls="stockMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-packages"></i></span>
                        <span class="menu-text">Stock</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="stockMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/stock/receives" class="side-nav-link">
                                    <span class="menu-text">Receive Stock</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/stock/transfers" class="side-nav-link">
                                    <span class="menu-text">Transfer to Warehouse</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/stock/summary" class="side-nav-link">
                                    <span class="menu-text">Stock Summary</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>


                <!-- Station Finance -->
                <?php if ($hasMenu('station-finance')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#stationFinanceMenu" aria-expanded="false" aria-controls="stationFinanceMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-building-bank"></i></span>
                        <span class="menu-text">Station Finance</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="stationFinanceMenu">
                        <ul class="sub-menu">
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/payment-modes" class="side-nav-link">
                                    <span class="menu-text">Payment Modes</span>
                                </a>
                            </li> -->
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/transactions" class="side-nav-link">
                                    <span class="menu-text">Transactions</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances" class="side-nav-link">
                                    <span class="menu-text">Station Finances</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/withdraw" class="side-nav-link">
                                    <span class="menu-text">Withdraw</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/journal" class="side-nav-link">
                                    <span class="menu-text">Journal</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/station-finances/detailed-expense-report" class="side-nav-link">
                                    <span class="menu-text">Detailed Expense Report</span>
                                </a>
                            </li>
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/accounts" class="side-nav-link">
                                    <span class="menu-text">Accounts</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-recharge" class="side-nav-link">
                                    <span class="menu-text">Account Recharge</span>
                                </a>
                            </li> -->
                            <li class="side-nav-item">
                                <!-- <a href="<?= APP_URL ?>/finance/account-transfer" class="side-nav-link">
                                    <span class="menu-text">Account Transfer</span>
                                </a>
                            </li> -->
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/transfer-to-account" class="side-nav-link">
                                    <span class="menu-text">Transfer to Another Account</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/global-transfer" class="side-nav-link">
                                    <span class="menu-text">Global Transfer</span>
                                </a>
                            </li>
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-recharge/history" class="side-nav-link">
                                    <span class="menu-text">Transaction History</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans/create" class="side-nav-link">
                                    <span class="menu-text">Request Loan</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans" class="side-nav-link">
                                    <span class="menu-text">Manage Loans</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans/disbursement" class="side-nav-link">
                                    <span class="menu-text">Loan Disbursement</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loan-payments/create" class="side-nav-link">
                                    <span class="menu-text">Pay Worker Loan</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loan-payments" class="side-nav-link">
                                    <span class="menu-text">Loan Payment History</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/loans/statement" class="side-nav-link">
                                    <span class="menu-text">Loan Statements</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/proforma-invoice" class="side-nav-link">
                                    <span class="menu-text">Proforma Invoice</span>
                                </a>
                            </li> -->
                            <!-- <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/source-of-income" class="side-nav-link">
                                    <span class="menu-text">Register Source of Income</span>
                                </a>
                            </li> -->
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Warehouse -->
                <?php if ($hasMenu('warehouse')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#warehouseMenu" aria-expanded="false"
                        aria-controls="warehouseMenu" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-building-warehouse"></i></span>
                        <span class="menu-text">Warehouse</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="warehouseMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/warehouse/stock" class="side-nav-link">
                                    <span class="menu-text">Warehouse Stock</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/warehouse/incoming" class="side-nav-link">
                                    <span class="menu-text">Incoming Transfers</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/warehouse/processing" class="side-nav-link">
                                    <span class="menu-text">Processing Steps</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/warehouse/receive" class="side-nav-link">
                                    <span class="menu-text">Receive Stock</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/warehouse/sales" class="side-nav-link">
                                    <span class="menu-text">Sales</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/warehouse/sales/history" class="side-nav-link">
                                    <span class="menu-text">Sales History</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                
                <!-- Production -->
                <?php if ($hasMenu('production')): ?>
                <li class="side-nav-item">
                    <a href="<?= APP_URL ?>/production" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-tools"></i></span>
                        <span class="menu-text">Production</span>
                    </a>
                </li>
                <?php endif; ?>
                <!-- Expense Management -->
                <?php if ($hasMenu('expenses')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#expenseMenu" aria-expanded="false" aria-controls="expenseMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-receipt"></i></span>
                        <span class="menu-text">Expense Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="expenseMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/expense-categories" class="side-nav-link">
                                    <span class="menu-text">Register Expense Categories</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/expense-types" class="side-nav-link">
                                    <span class="menu-text">Expense Types</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/expense-consumers" class="side-nav-link">
                                    <span class="menu-text">Expense Consumers</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/expense-transactions" class="side-nav-link">
                                    <span class="menu-text">Expense Transactions</span>
                                </a>
                            </li>
                              <li class="side-nav-item">
                                    <a href="<?= APP_URL ?>/finance/expense-transactions?statement=1" class="side-nav-link">
                                        <span class="menu-text">Expense Statement</span>
                                    </a>
                                </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                
                <!-- Non-Exploitable Management -->
                <?php if ($hasMenu('non-exploitable')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#nonExploitableMenu" aria-expanded="false" aria-controls="nonExploitableMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-box-off"></i></span>
                        <span class="menu-text">Non-Exploitable Mgmt</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="nonExploitableMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/non-exploitable/categories" class="side-nav-link">
                                    <span class="menu-text">Non-Exploitable Categories</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/non-exploitable/transactions" class="side-nav-link">
                                    <span class="menu-text">Non-Exploitable Transactions</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Certification Management -->
                <?php if ($hasMenu('certification')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#certificationMenu" aria-expanded="false" aria-controls="certificationMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-certificate"></i></span>
                        <span class="menu-text">Certification</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="certificationMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/certification/categories" class="side-nav-link">
                                    <span class="menu-text">Record Certification Categories</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/certification/transactions" class="side-nav-link">
                                    <span class="menu-text">Record Certification Transactions</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Property Management -->
                <?php if ($hasMenu('properties')): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#propertyMenu" aria-expanded="false" aria-controls="propertyMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-home"></i></span>
                        <span class="menu-text">Property Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="propertyMenu">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/properties/types" class="side-nav-link">
                                    <span class="menu-text">Property Types</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/properties" class="side-nav-link">
                                    <span class="menu-text">Record Properties</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php endif; ?>

                <li class="side-nav-title mt-2">Account</li>

                <!-- Profile -->
                <li class="side-nav-item">
                    <a href="<?= APP_URL ?>/profile" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-user"></i></span>
                        <span class="menu-text">My Profile</span>
                    </a>
                </li>

                <!-- Logout -->
                <li class="side-nav-item">
                    <a href="<?= APP_URL ?>/logout" class="side-nav-link text-danger">
                        <span class="menu-icon"><i class="ti ti-logout"></i></span>
                        <span class="menu-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>