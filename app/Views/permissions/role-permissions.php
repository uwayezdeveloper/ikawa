<?php
$roles = $roles ?? [];
$menus = $menus ?? [];
$selectedRole = $selectedRole ?? null;
$selectedRoleId = $selectedRoleId ?? 0;
$roleMenuIds = $roleMenuIds ?? [];

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="d-flex align-items-sm-center flex-sm-row flex-column my-3">
    <div class="flex-grow-1">
        <h4 class="fs-xl mb-1">Role Menus</h4>
        <p class="text-muted mb-0">Assign global menu access by role.</p>
    </div>
    <div class="text-end">
        <a href="<?= APP_URL ?>/permissions" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Menus
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Select Role</h5></div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($roles as $role): ?>
                        <a href="<?= APP_URL ?>/permissions/roles?role_id=<?= $role['id'] ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $role['id'] == $selectedRoleId ? 'active' : '' ?>">
                            <span><i class="ti ti-shield me-2"></i><?= htmlspecialchars($role['name']) ?></span>
                            <?php if ($role['status'] === 'active'): ?>
                                <span class="badge bg-success rounded-pill">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill">Inactive</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <?php if ($selectedRole): ?>
                        Menus for <span class="text-primary"><?= htmlspecialchars($selectedRole['name']) ?></span>
                    <?php else: ?>
                        Select a role
                    <?php endif; ?>
                </h5>
                <?php if ($selectedRole): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect All</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($selectedRole): ?>
                    <form action="<?= APP_URL ?>/permissions/roles" method="POST" id="menusForm">
                        <input type="hidden" name="role_id" value="<?= $selectedRoleId ?>">

                        <div class="row">
                            <?php foreach ($menus as $menu): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check border rounded p-3">
                                        <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" value="<?= $menu['menu_id'] ?>" id="menu-<?= $menu['menu_id'] ?>" <?= in_array((int) $menu['menu_id'], $roleMenuIds, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="menu-<?= $menu['menu_id'] ?>">
                                            <span class="fw-semibold d-block"><?= htmlspecialchars($menu['menu_name']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($menu['menu_identifier']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted"><span id="selectedCount"><?= count($roleMenuIds) ?></span> menus selected</span>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Save Role Menus</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($flashSuccess): ?>
    Swal.fire({ icon: 'success', title: 'Success', text: '<?= addslashes($flashSuccess) ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    <?php endif; ?>

    <?php if ($flashError): ?>
    Swal.fire({ icon: 'error', title: 'Error', text: '<?= addslashes($flashError) ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000 });
    <?php endif; ?>

    const checkboxes = document.querySelectorAll('.menu-checkbox');
    const selectedCount = document.getElementById('selectedCount');

    function updateCount() {
        if (selectedCount) {
            selectedCount.textContent = document.querySelectorAll('.menu-checkbox:checked').length;
        }
    }

    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateCount();
        });
    }

    const deselectAll = document.getElementById('deselectAll');
    if (deselectAll) {
        deselectAll.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateCount();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    const form = document.getElementById('menusForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const checked = document.querySelectorAll('.menu-checkbox:checked').length;
            Swal.fire({
                title: 'Save Role Menus?',
                html: `Assign <strong>${checked}</strong> menu(s) to this role?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>
