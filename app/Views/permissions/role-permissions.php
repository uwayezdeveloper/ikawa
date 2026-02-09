<?php
/**
 * Role Permissions Management View
 */

$roles = $roles ?? [];
$selectedRole = $selectedRole ?? null;
$selectedRoleId = $selectedRoleId ?? 0;
$permissionsGrouped = $permissionsGrouped ?? [];
$rolePermissionIds = $rolePermissionIds ?? [];

// Flash messages for SweetAlert
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Page Header -->
<div class="d-flex align-items-sm-center flex-sm-row flex-column my-3">
    <div class="flex-grow-1">
        <h4 class="fs-xl mb-1">Role Permissions</h4>
        <p class="text-muted mb-0">
            Assign permissions to roles for fine-grained access control.
        </p>
    </div>
    <div class="text-end">
        <a href="<?= APP_URL ?>/permissions" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Permissions
        </a>
    </div>
</div>

<div class="row">
    <!-- Role Selection Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Select Role</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($roles as $role): ?>
                    <a href="<?= APP_URL ?>/permissions/roles?role_id=<?= $role['id'] ?>"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $role['id'] == $selectedRoleId ? 'active' : '' ?>">
                        <div>
                            <i class="ti ti-shield me-2"></i>
                            <?= htmlspecialchars($role['name']) ?>
                        </div>
                        <?php if ($role['status'] === 'active'): ?>
                        <span class="badge bg-success rounded-pill">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary rounded-pill">Inactive</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($roles)): ?>
                <div class="text-center text-muted py-4">
                    <i class="ti ti-mood-empty fs-32 d-block mb-2"></i>
                    No roles found. <a href="<?= APP_URL ?>/roles">Create a role first</a>.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Permissions Assignment Card -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <?php if ($selectedRole): ?>
                    Permissions for <span class="text-primary"><?= htmlspecialchars($selectedRole['name']) ?></span>
                    <?php else: ?>
                    Select a Role
                    <?php endif; ?>
                </h5>
                <?php if ($selectedRole): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect
                        All</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($selectedRole && !empty($permissionsGrouped)): ?>
                <form action="<?= APP_URL ?>/permissions/roles" method="POST" id="permissionsForm">
                    <input type="hidden" name="role_id" value="<?= $selectedRoleId ?>">

                    <div class="accordion" id="permissionsAccordion">
                        <?php $moduleIndex = 0; ?>
                        <?php foreach ($permissionsGrouped as $module => $modulePermissions): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $moduleIndex > 0 ? 'collapsed' : '' ?>"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#module-<?= $moduleIndex ?>"
                                    aria-expanded="<?= $moduleIndex === 0 ? 'true' : 'false' ?>">
                                    <i class="ti ti-folder me-2"></i>
                                    <strong><?= ucfirst(htmlspecialchars($module)) ?></strong>
                                    <span class="badge bg-primary ms-2"><?= count($modulePermissions) ?></span>
                                </button>
                            </h2>
                            <div id="module-<?= $moduleIndex ?>"
                                class="accordion-collapse collapse <?= $moduleIndex === 0 ? 'show' : '' ?>"
                                data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <?php foreach ($modulePermissions as $permission): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox"
                                                    name="permissions[]" value="<?= $permission['id'] ?>"
                                                    id="perm-<?= $permission['id'] ?>"
                                                    <?= in_array((int)$permission['id'], $rolePermissionIds, true) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="perm-<?= $permission['id'] ?>">
                                                    <span
                                                        class="fw-medium"><?= htmlspecialchars($permission['name']) ?></span>
                                                    <?php if ($permission['description']): ?>
                                                    <br><small
                                                        class="text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $moduleIndex++; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <span id="selectedCount"><?= count($rolePermissionIds) ?></span> permissions selected
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Permissions
                        </button>
                    </div>
                </form>
                <?php elseif ($selectedRole && empty($permissionsGrouped)): ?>
                <div class="text-center text-muted py-5">
                    <i class="ti ti-key-off fs-48 d-block mb-3"></i>
                    <p class="mb-3">No permissions available.</p>
                    <a href="<?= APP_URL ?>/permissions" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Create Permissions
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="ti ti-click fs-48 d-block mb-3"></i>
                    <p>Please select a role from the left panel to manage its permissions.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show SweetAlert for flash messages
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

    // Select/Deselect all
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const selectedCount = document.getElementById('selectedCount');

    function updateCount() {
        const checked = document.querySelectorAll('.permission-checkbox:checked').length;
        if (selectedCount) {
            selectedCount.textContent = checked;
        }
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateCount();
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateCount();
        });
    }

    // Update count on checkbox change
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });

    // Confirm before saving
    const form = document.getElementById('permissionsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const checked = document.querySelectorAll('.permission-checkbox:checked').length;

            Swal.fire({
                title: 'Save Permissions?',
                html: `You are about to assign <strong>${checked}</strong> permission(s) to this role.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, save!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>