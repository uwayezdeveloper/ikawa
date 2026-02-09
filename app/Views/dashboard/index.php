<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
            <h4 class="page-title">Dashboard</h4>
        </div>
    </div>
</div>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img src="<?= \App\Core\View::asset('images/users/user-1.jpg') ?>" class="avatar-lg rounded-circle" alt="user" />
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">Welcome back, <?= $user['first_name'] ?? 'User' ?>! 👋</h4>
                        <p class="text-muted mb-0">
                            Here's what's happening with your account today.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="badge bg-success-subtle text-success fs-12">
                            <i class="ti ti-circle-filled fs-6 me-1"></i> Online
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-primary-subtle rounded">
                            <i class="ti ti-users fs-28 text-primary"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Total Users</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-success-subtle text-success me-1">
                                <i class="ti ti-trending-up"></i> 8.5%
                            </span>
                            <span>from last month</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-success-subtle rounded">
                            <i class="ti ti-shopping-cart fs-28 text-success"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Total Orders</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-success-subtle text-success me-1">
                                <i class="ti ti-trending-up"></i> 12.3%
                            </span>
                            <span>from last month</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-warning-subtle rounded">
                            <i class="ti ti-currency-dollar fs-28 text-warning"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Revenue</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-danger-subtle text-danger me-1">
                                <i class="ti ti-trending-down"></i> 2.1%
                            </span>
                            <span>from last month</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-info-subtle rounded">
                            <i class="ti ti-coffee fs-28 text-info"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Products</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-success-subtle text-success me-1">
                                <i class="ti ti-trending-up"></i> 5.2%
                            </span>
                            <span>from last month</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/profile" class="btn btn-outline-primary w-100 py-3">
                            <i class="ti ti-user fs-24 d-block mb-1"></i>
                            My Profile
                        </a>
                    </div>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/users" class="btn btn-outline-success w-100 py-3">
                            <i class="ti ti-users fs-24 d-block mb-1"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/settings" class="btn btn-outline-warning w-100 py-3">
                            <i class="ti ti-settings fs-24 d-block mb-1"></i>
                            Settings
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/logout" class="btn btn-outline-danger w-100 py-3">
                            <i class="ti ti-logout fs-24 d-block mb-1"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Info -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Full Name:</td>
                            <td class="fw-medium"><?= $user['full_name'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td class="fw-medium"><?= $user['email'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Role:</td>
                            <td>
                                <span class="badge bg-primary"><?= ucfirst($user['role'] ?? 'N/A') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge bg-success"><?= ucfirst($user['status'] ?? 'N/A') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since:</td>
                            <td class="fw-medium">
                                <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Session Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Last Login:</td>
                            <td class="fw-medium">
                                <?= isset($user['last_login_at']) ? date('M d, Y H:i', strtotime($user['last_login_at'])) : 'N/A' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">IP Address:</td>
                            <td class="fw-medium"><?= $_SERVER['REMOTE_ADDR'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Browser:</td>
                            <td class="fw-medium text-truncate" style="max-width: 200px;">
                                <?= $_SERVER['HTTP_USER_AGENT'] ?? 'N/A' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Session Started:</td>
                            <td class="fw-medium"><?= date('M d, Y H:i') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
