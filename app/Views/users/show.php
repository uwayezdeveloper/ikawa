<!-- Page Title -->
<?php
// Get user permissions
$permissions = $user['permissions'] ?? [];
$canEdit = in_array('edit-users', $permissions);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/users">Users</a></li>
                    <li class="breadcrumb-item active">User Details</li>
                </ol>
            </div>
            <h4 class="page-title">User Details</h4>
        </div>
    </div>
</div>

<!-- User Details -->
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="<?= \App\Core\View::asset('images/users/user-1.jpg') ?>" alt="user"
                    class="avatar-xxl rounded-circle mb-3" />
                <h4 class="mb-1"><?= htmlspecialchars($viewUser['full_name']) ?></h4>
                <p class="text-muted mb-3"><?= htmlspecialchars($viewUser['email']) ?></p>

                <span class="badge bg-primary mb-3"><?= htmlspecialchars($viewUser['role_name'] ?? 'No Role') ?></span>

                <div class="d-flex justify-content-center gap-2">
                    <?php if ($canEdit): ?>
                    <a href="<?= APP_URL ?>/users/<?= $viewUser['id'] ?>/edit" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">First Name</label>
                            <p class="fw-medium"><?= htmlspecialchars($viewUser['first_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Last Name</label>
                            <p class="fw-medium"><?= htmlspecialchars($viewUser['last_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="fw-medium"><?= htmlspecialchars($viewUser['email']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone</label>
                            <p class="fw-medium"><?= htmlspecialchars($viewUser['phone'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Role</label>
                            <p>
                                <span class="badge bg-primary"><?= htmlspecialchars($viewUser['role_name'] ?? 'No Role') ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p>
                                <?php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'pending' => 'warning',
                                    'suspended' => 'danger'
                                ];
                                $statusColor = $statusColors[$viewUser['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($viewUser['status']) ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">UUID</label>
                            <p class="fw-medium text-muted"><?= htmlspecialchars($viewUser['uuid']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Email Verified</label>
                            <p>
                                <?php if ($viewUser['email_verified_at']): ?>
                                <span class="badge bg-success">Verified</span>
                                <small
                                    class="text-muted">(<?= date('M d, Y', strtotime($viewUser['email_verified_at'])) ?>)</small>
                                <?php else: ?>
                                <span class="badge bg-warning">Not Verified</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Created At</label>
                            <p class="fw-medium"><?= date('M d, Y H:i', strtotime($viewUser['created_at'])) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Last Login</label>
                            <p class="fw-medium">
                                <?= $viewUser['last_login_at'] ? date('M d, Y H:i', strtotime($viewUser['last_login_at'])) : 'Never' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>