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
                        <img src="<?= \App\Core\View::asset('images/users/user-1.jpg') ?>" alt="user-image"
                            class="rounded-circle mb-2 avatar-md" />
                        <span class="sidenav-user-name fw-bold"><?= $user['full_name'] ?? 'User' ?></span>
                        <span class="fs-12 fw-semibold"><?= ucfirst($user['role'] ?? 'Guest') ?></span>
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
                // Get user permissions - only check actual permissions from database
                $permissions = $user['permissions'] ?? [];
                ?>

                <?php if (in_array('view-users', $permissions) || in_array('view-roles', $permissions) || in_array('view-permissions', $permissions)): ?>
                <li class="side-nav-title mt-2">Management</li>

                <!-- Users -->
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#usersMenu" aria-expanded="false" aria-controls="usersMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text">Users</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="usersMenu">
                        <ul class="sub-menu">
                            <?php if (in_array('view-users', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/users" class="side-nav-link">
                                    <span class="menu-text">All Users</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-roles', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/roles" class="side-nav-link">
                                    <span class="menu-text">Roles</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-permissions', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/permissions" class="side-nav-link">
                                    <span class="menu-text">Permissions</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('manage-permissions', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/permissions/roles" class="side-nav-link">
                                    <span class="menu-text">Role Permissions</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('create-users', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/users/create" class="side-nav-link">
                                    <span class="menu-text">Add User</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>

                <!-- Settings -->
                <?php if (in_array('view-company', $permissions) || in_array('view-location-types', $permissions) || in_array('view-locations', $permissions) || in_array('view-location-categories', $permissions)): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#settingsMenu" aria-expanded="false" aria-controls="settingsMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-settings"></i></span>
                        <span class="menu-text">Settings</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="settingsMenu">
                        <ul class="sub-menu">
                            <?php if (in_array('view-company', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/company" class="side-nav-link">
                                    <span class="menu-text">Company</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-location-types', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/location-types" class="side-nav-link">
                                    <span class="menu-text">Location Types</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-locations', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/locations" class="side-nav-link">
                                    <span class="menu-text">Locations</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-location-categories', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/settings/location-categories" class="side-nav-link">
                                    <span class="menu-text">Location Categories</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Products -->
                <?php if (in_array('view-product-categories', $permissions) || in_array('view-category-types', $permissions) || in_array('view-measurement-units', $permissions) || in_array('view-type-unit-assignments', $permissions)): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#productsMenu" aria-expanded="false" aria-controls="productsMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-package"></i></span>
                        <span class="menu-text">Products</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="productsMenu">
                        <ul class="sub-menu">
                            <?php if (in_array('view-product-categories', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/categories" class="side-nav-link">
                                    <span class="menu-text">Categories</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-category-types', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/category-types" class="side-nav-link">
                                    <span class="menu-text">Category Types</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-measurement-units', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/measurement-units" class="side-nav-link">
                                    <span class="menu-text">Measurement Units</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-type-unit-assignments', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/products/type-units" class="side-nav-link">
                                    <span class="menu-text">Type Unit Assignments</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Suppliers -->
                <?php if (in_array('view-supplier-types', $permissions) || in_array('view-suppliers', $permissions)): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#suppliersMenu" aria-expanded="false" aria-controls="suppliersMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-truck"></i></span>
                        <span class="menu-text">Suppliers</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="suppliersMenu">
                        <ul class="sub-menu">
                            <?php if (in_array('view-suppliers', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/suppliers" class="side-nav-link">
                                    <span class="menu-text">Suppliers List</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-supplier-types', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/suppliers/types" class="side-nav-link">
                                    <span class="menu-text">Supplier Types</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Finance -->
                <?php if (in_array('view-payment-modes', $permissions) || in_array('view-accounts', $permissions)): ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#financeMenu" aria-expanded="false" aria-controls="financeMenu"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-report-money"></i></span>
                        <span class="menu-text">Finance</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="financeMenu">
                        <ul class="sub-menu">
                            <?php if (in_array('view-payment-modes', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/payment-modes" class="side-nav-link">
                                    <span class="menu-text">Payment Modes</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (in_array('view-accounts', $permissions)): ?>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/accounts" class="side-nav-link">
                                    <span class="menu-text">Accounts</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-recharge" class="side-nav-link">
                                    <span class="menu-text">Account Recharge</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="<?= APP_URL ?>/finance/account-transfer" class="side-nav-link">
                                    <span class="menu-text">Account Transfer</span>
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
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Expense Management -->
                <?php if (in_array('view-accounts', $permissions) || in_array('view-payment-modes', $permissions)): ?>
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
                        </ul>
                    </div>
                </li>
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
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>