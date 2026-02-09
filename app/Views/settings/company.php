<?php
$permissions = $user['permissions'] ?? [];
$canEdit = in_array('edit-company', $permissions);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Get company data with defaults
$fullName = $company['full_name'] ?? '';
$shortName = $company['short_name'] ?? '';
$email = $company['email'] ?? '';
$phone = $company['phone'] ?? '';
$address = $company['address'] ?? '';
$logo = $company['logo'] ?? '';
?>

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/settings">Settings</a></li>
                    <li class="breadcrumb-item active">Company</li>
                </ol>
            </div>
            <h4 class="page-title">Company Settings</h4>
        </div>
    </div>
</div>

<!-- Settings Content -->
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Current Company Info Table -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2"></i> Current Company Information
                </h5>
            </div>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2"></i> Current Company Information
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 180px;">Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Full Name</strong></td>
                                <td><?= htmlspecialchars($fullName) ?: '<span class="text-muted fst-italic">Not set</span>' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Short Name</strong></td>
                                <td><?= htmlspecialchars($shortName) ?: '<span class="text-muted fst-italic">Not set</span>' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>
                                    <?php if ($email): ?>
                                        <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Not set</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Phone</strong></td>
                                <td>
                                    <?php if ($phone): ?>
                                        <a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Not set</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Address</strong></td>
                                <td><?= nl2br(htmlspecialchars($address)) ?: '<span class="text-muted fst-italic">Not set</span>' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Logo</strong></td>
                                <td>
                                    <?php if ($logo && file_exists(BASE_PATH . '/assets/images/logos/' . $logo)): ?>
                                        <img src="<?= \App\Core\View::asset('images/logos/' . $logo) ?>" alt="Logo" style="max-height: 40px;" class="me-2">
                                        <span class="text-muted"><?= htmlspecialchars($logo) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">No logo uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-edit me-2"></i> Edit Company Information
                </h5>
                <?php if ($canEdit): ?>
                    <span class="badge bg-success-subtle text-success">
                        <i class="ti ti-check me-1"></i> You can edit
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger">
                        <i class="ti ti-lock me-1"></i> Read-only
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/settings/company" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                    value="<?= htmlspecialchars($fullName) ?>" 
                                    placeholder="Enter company full name" 
                                    required <?= !$canEdit ? 'disabled' : '' ?>>
                                <small class="text-muted">The full legal name of your company</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="short_name" class="form-label">Short Name</label>
                                <input type="text" class="form-control" id="short_name" name="short_name" 
                                    value="<?= htmlspecialchars($shortName) ?>" 
                                    placeholder="e.g. GC" <?= !$canEdit ? 'disabled' : '' ?>>
                                <small class="text-muted">Abbreviated name or acronym</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?= htmlspecialchars($email) ?>" 
                                    placeholder="info@company.com" 
                                    required <?= !$canEdit ? 'disabled' : '' ?>>
                                <small class="text-muted">Primary contact email</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    Phone <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                    value="<?= htmlspecialchars($phone) ?>" 
                                    placeholder="+250 788 000 000" 
                                    required <?= !$canEdit ? 'disabled' : '' ?>>
                                <small class="text-muted">Primary contact phone number</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" 
                                    placeholder="Enter full address" <?= !$canEdit ? 'disabled' : '' ?>><?= htmlspecialchars($address) ?></textarea>
                                <small class="text-muted">Physical location of your company</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="logo" class="form-label">Company Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" 
                                    accept="image/jpeg,image/png,image/gif,image/webp" <?= !$canEdit ? 'disabled' : '' ?>>
                                <small class="text-muted">Accepted: JPG, PNG, GIF, WEBP. Max 2MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo Preview</label>
                                <div id="logoPreview" class="border rounded p-3 text-center bg-light" style="min-height: 100px;">
                                    <span class="text-muted">Select an image to preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($canEdit): ?>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Save Changes
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="ti ti-lock me-2"></i>
                            You don't have permission to modify settings. Contact an administrator.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Sidebar - Logo Preview -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="card-title mb-3">Current Logo</h6>
                <?php if ($logo && file_exists(BASE_PATH . '/assets/images/logos/' . $logo)): ?>
                    <img src="<?= \App\Core\View::asset('images/logos/' . $logo) ?>" alt="Company Logo" class="img-fluid rounded" style="max-height: 150px;">
                    <p class="text-muted mt-2 mb-0 small"><?= htmlspecialchars($logo) ?></p>
                <?php else: ?>
                    <div class="avatar-xl rounded bg-primary-subtle mx-auto d-flex align-items-center justify-content-center">
                        <i class="ti ti-building fs-32 text-primary"></i>
                    </div>
                    <p class="text-muted mt-2 mb-0">No logo uploaded</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Info Card -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="ti ti-info-circle me-1"></i> Quick Info</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Company:</span>
                    <strong><?= htmlspecialchars($fullName) ?: '-' ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Short Name:</span>
                    <strong><?= htmlspecialchars($shortName) ?: '-' ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Email:</span>
                    <strong><?= htmlspecialchars($email) ?: '-' ?></strong>
                </div>
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

    // Logo preview
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logoPreview');

    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Maximum file size is 2MB',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    logoInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 80px;">';
                };
                reader.readAsDataURL(file);
            } else {
                logoPreview.innerHTML = '<span class="text-muted">Select an image to preview</span>';
            }
        });
    }
});
</script>
