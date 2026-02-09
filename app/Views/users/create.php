<!-- Page Title -->
<?php
$flashError = $_SESSION['flash_error'] ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['old']);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/users">Users</a></li>
                    <li class="breadcrumb-item active">Add User</li>
                </ol>
            </div>
            <h4 class="page-title">Add New User</h4>
        </div>
    </div>
</div>

<!-- Create User Form -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/users" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                    value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                    value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= ($old['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($old['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($old['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="pending" <?= ($old['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="suspended" <?= ($old['status'] ?? '') == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i> Create User
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
            <div class="card-body">
                <h5 class="card-title mb-3">Tips</h5>
                <ul class="text-muted ps-3">
                    <li class="mb-2">All fields marked with <span class="text-danger">*</span> are required.</li>
                    <li class="mb-2">Password must be at least 6 characters long.</li>
                    <li class="mb-2">Email must be unique in the system.</li>
                    <li class="mb-2">Role determines user permissions in the system.</li>
                </ul>
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
        
        if (password !== confirm) {
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
