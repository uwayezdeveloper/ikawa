<!-- Page Title -->
<?php
$permissions = $user['permissions'] ?? [];
$canManage = in_array('manage-settings', $permissions);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
            <h4 class="page-title">Settings</h4>
        </div>
    </div>
</div>

<!-- Settings Navigation -->
<div class="row">
    <div class="col-xl-3 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Settings Menu</h5>
                <div class="list-group list-group-flush">
                          <a href="<?= APP_URL ?>/profile" class="list-group-item list-group-item-action">
                        <i class="ti ti-user me-2"></i> My Profile
                    </a>
                    <a href="<?= APP_URL ?>/settings/company" class="list-group-item list-group-item-action">
                        <i class="ti ti-building me-2"></i> Company
                    </a>
                    <a href="#" class="list-group-item list-group-item-action disabled">
                        <i class="ti ti-mail me-2"></i> Email (Coming Soon)
                    </a>
                    <a href="#" class="list-group-item list-group-item-action disabled">
                        <i class="ti ti-credit-card me-2"></i> Payment (Coming Soon)
                    </a>
                    <a href="#" class="list-group-item list-group-item-action disabled">
                        <i class="ti ti-bell me-2"></i> Notifications (Coming Soon)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-9 col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-settings fs-48 text-muted mb-3 d-block"></i>
                <h4>Welcome to Settings</h4>
                <p class="text-muted mb-4">Select a category from the menu to manage your application settings.</p>
                <a href="<?= APP_URL ?>/settings/company" class="btn btn-primary">
                    <i class="ti ti-building me-1"></i> Go to Company Settings
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($flashSuccess): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= addslashes($flashSuccess) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php endif; ?>

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
});
</script>