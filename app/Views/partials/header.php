<header class="app-topbar">
    <div class="container-fluid topbar-menu">
        <div class="d-flex align-items-center gap-2">
            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="<?= APP_URL ?>/dashboard" class="logo-light">
                    <span class="logo-lg">
                        <img src="<?= \App\Core\View::asset('images/logo.png') ?>" alt="logo" />
                    </span>
                    <span class="logo-sm">
                        <img src="<?= \App\Core\View::asset('images/logo-sm.png') ?>" alt="small logo" />
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="<?= APP_URL ?>/dashboard" class="logo-dark">
                    <span class="logo-lg">
                        <img src="<?= \App\Core\View::asset('images/logo-black.png') ?>" alt="dark logo" />
                    </span>
                    <span class="logo-sm">
                        <img src="<?= \App\Core\View::asset('images/logo-sm.png') ?>" alt="small logo" />
                    </span>
                </a>
            </div>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-primary btn-icon">
                <i class="ti ti-menu-4"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu">
                <i class="ti ti-menu-4"></i>
            </button>

            <div id="search-box-rounded" class="app-search d-none d-xl-flex">
                <input type="search" class="form-control rounded-pill topbar-search" name="search" placeholder="Quick Search..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Theme Toggle -->
            <div id="theme-dropdown" class="topbar-item d-none d-sm-flex">
                <div class="dropdown">
                    <button class="topbar-link" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-sun topbar-link-icon d-none" id="theme-icon-light"></i>
                        <i class="ti ti-moon topbar-link-icon d-none" id="theme-icon-dark"></i>
                        <i class="ti ti-sun-moon topbar-link-icon d-none" id="theme-icon-auto"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <button type="button" class="dropdown-item" data-bs-theme-value="light">
                            <i class="ti ti-sun fs-lg align-middle me-1"></i>
                            <span class="align-middle">Light</span>
                        </button>
                        <button type="button" class="dropdown-item" data-bs-theme-value="dark">
                            <i class="ti ti-moon fs-lg align-middle me-1"></i>
                            <span class="align-middle">Dark</span>
                        </button>
                        <button type="button" class="dropdown-item" data-bs-theme-value="auto">
                            <i class="ti ti-sun-moon fs-lg align-middle me-1"></i>
                            <span class="align-middle">Auto</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div id="notification-dropdown" class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link position-relative" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-bell topbar-link-icon"></i>
                        <span class="notify-signal"></span>
                    </button>
                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                        <div class="p-3 border-bottom border-dashed">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-body m-0 fw-semibold">Notifications</h6>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 text-center text-muted">
                            <p>No new notifications</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div id="user-dropdown" class="topbar-item nav-user">
                <div class="dropdown">
                    <button class="topbar-link btn dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                        <?php
                        $avatarUrl = !empty($user['avatar']) ? APP_URL . '/' . $user['avatar'] : \App\Core\View::asset('images/users/user-1.jpg');
                        ?>
                        <img src="<?= $avatarUrl ?>" alt="user-image" class="avatar-sm rounded-circle me-2" style="object-fit: cover; width: 38px; height: 38px;" />
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            <span class="fw-semibold"><?= ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? 'User') ?></span>
                            <span class="text-muted fs-10"><?= $user['role_name'] ?? 'Guest' ?></span>
                        </span>
                        <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-1"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome back!</h6>
                        </div>
                        <a href="<?= APP_URL ?>/profile" class="dropdown-item">
                            <i class="ti ti-user-circle me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Profile</span>
                        </a>
                        <a href="<?= APP_URL ?>/settings" class="dropdown-item">
                            <i class="ti ti-settings-2 me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= APP_URL ?>/logout" class="dropdown-item text-danger fw-semibold">
                            <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
