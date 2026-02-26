<!-- Page Title -->
<?php
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/users">Users</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </div>
            <h4 class="page-title">Edit User</h4>
        </div>
    </div>
</div>

<!-- Edit User Form -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/users/<?= $viewUser['id'] ?>" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?= htmlspecialchars($viewUser['first_name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    value="<?= htmlspecialchars($viewUser['last_name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($viewUser['email']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="<?= htmlspecialchars($viewUser['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="6">
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirm"
                                    name="password_confirm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"
                                        <?= $viewUser['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location_id" class="form-label">Location <?= !empty($locations) ? '<span class="text-danger">*</span>' : '<span class="text-muted">(Optional - no locations available)</span>' ?></label>
                                <select class="form-select" id="location_id" name="location_id" <?= !empty($locations) ? 'required' : '' ?>>
                                    <option value="">Select Location</option>
                                    <?php if (!empty($locations)): ?>
                                        <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location['id'] ?>" <?= ($viewUser['location_id'] ?? '') == $location['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($location['name']) ?> (<?= htmlspecialchars($location['type_name'] ?? '') ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No locations available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= $viewUser['status'] == 'active' ? 'selected' : '' ?>>
                                        Active</option>
                                    <option value="inactive" <?= $viewUser['status'] == 'inactive' ? 'selected' : '' ?>>
                                        Inactive</option>
                                    <option value="pending" <?= $viewUser['status'] == 'pending' ? 'selected' : '' ?>>
                                        Pending</option>
                                    <option value="suspended"
                                        <?= $viewUser['status'] == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i> Update User
                        </button>
                        <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="<?= \App\Core\View::asset('images/users/user-1.jpg') ?>" alt="user"
                    class="avatar-xxl rounded-circle mb-3" />
                <h5 class="mb-1"><?= htmlspecialchars($viewUser['full_name']) ?></h5>
                <p class="text-muted mb-2"><?= htmlspecialchars($viewUser['email']) ?></p>
                <span class="badge bg-primary"><?= htmlspecialchars($viewUser['role_name'] ?? 'No Role') ?></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">User Details</h5>
                <div class="mb-2">
                    <small class="text-muted">UUID:</small>
                    <p class="mb-0 text-truncate"><?= htmlspecialchars($viewUser['uuid']) ?></p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Created:</small>
                    <p class="mb-0"><?= date('M d, Y H:i', strtotime($viewUser['created_at'])) ?></p>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Last Login:</small>
                    <p class="mb-0">
                        <?= $viewUser['last_login_at'] ? date('M d, Y H:i', strtotime($viewUser['last_login_at'])) : 'Never' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($flashError): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= addslashes($flashError) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
    <?php endif; ?>

    // Password confirmation validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;

        if (password && password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Passwords do not match',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    });
});
</script>