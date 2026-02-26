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
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/profile">Profile</a></li>
                    <li class="breadcrumb-item active">Change Password</li>
                </ol>
            </div>
            <h4 class="page-title">Change Password</h4>
        </div>
    </div>
</div>

<?php if ($flashError): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Change Password Form -->
<div class="row">
    <div class="col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Update Password</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Password Requirements:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Minimum 6 characters long</li>
                        <li>Include uppercase and lowercase letters (recommended)</li>
                        <li>Include at least one number (recommended)</li>
                    </ul>
                </div>

                <form action="<?= APP_URL ?>/profile/update-password" method="POST" id="changePasswordForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="ti ti-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="ti ti-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                <i class="ti ti-eye" id="confirm_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <div id="passwordMatchError" class="alert alert-danger" style="display: none;">
                        Passwords do not match!
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= APP_URL ?>/profile" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ti ti-lock me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        field.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}

// Validate password match
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const errorDiv = document.getElementById('passwordMatchError');

    function checkPasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                errorDiv.style.display = 'block';
                submitBtn.disabled = true;
                return false;
            } else {
                errorDiv.style.display = 'none';
                submitBtn.disabled = false;
                return true;
            }
        }
        return true;
    }

    newPassword.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);

    form.addEventListener('submit', function(e) {
        if (!checkPasswordMatch()) {
            e.preventDefault();
        }
    });
});
</script>
