<?php
/**
 * Roles Management View
 * Based on apps-users-roles.html template
 */

$roles = $roles ?? ['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 1];
$stats = $stats ?? ['active' => 0, 'inactive' => 0, 'total' => 0];

// Get user permissions - only check actual permissions, not role name
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-roles', $permissions);
$canEdit = in_array('edit-roles', $permissions);
$canDelete = in_array('delete-roles', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-sm-center flex-sm-row flex-column my-3">
    <div class="flex-grow-1">
        <h4 class="fs-xl mb-1">Manage Roles</h4>
        <p class="text-muted mb-0">
            Manage roles for smoother operations and secure access.
        </p>
    </div>
</div>

<!-- Flash Messages with SweetAlert -->
<?php 
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-md rounded bg-primary-subtle">
                        <i
                            class="ti ti-shield fs-24 text-primary d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="mb-0"><?= $stats['total'] ?></h4>
                        <p class="text-muted mb-0">Total Roles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-md rounded bg-success-subtle">
                        <i
                            class="ti ti-check fs-24 text-success d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="mb-0"><?= $stats['active'] ?></h4>
                        <p class="text-muted mb-0">Active Roles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-md rounded bg-danger-subtle">
                        <i class="ti ti-x fs-24 text-danger d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="mb-0"><?= $stats['inactive'] ?></h4>
                        <p class="text-muted mb-0">Inactive Roles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search roles..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Status Filter -->
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                    <option value="All">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
            <!-- Records Per Page -->
            <div>
                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
            <?php if ($canCreate): ?>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addRoleModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add New Role
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0" id="rolesTable">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Role Name</th>
                    <th data-table-sort>Description</th>
                    <th data-table-sort data-column="status">Status</th>
                    <th data-table-sort>Created At</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles['data'])): ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="ti ti-mood-empty fs-32 d-block mb-2"></i>
                            No roles found
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($roles['data'] as $index => $role): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded bg-primary-subtle me-2">
                                <i class="ti ti-shield fs-18 text-primary d-flex align-items-center justify-content-center h-100"></i>
                            </div>
                            <h5 class="fs-base mb-0">
                                <a href="#" class="link-reset"><?= htmlspecialchars($role['name']) ?></a>
                            </h5>
                        </div>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($role['description'] ?? '-') ?></span>
                    </td>
                    <td><?= $role['status'] === 'active' ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <span class="text-muted"><?= date('M d, Y', strtotime($role['created_at'])) ?></span>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editRoleModal" data-id="<?= $role['id'] ?>"
                                data-name="<?= htmlspecialchars($role['name']) ?>"
                                data-description="<?= htmlspecialchars($role['description'] ?? '') ?>"
                                data-status="<?= $role['status'] ?>" title="Edit">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-toggle-status"
                                data-id="<?= $role['id'] ?>" data-name="<?= htmlspecialchars($role['name']) ?>"
                                data-status="<?= $role['status'] ?>" title="Toggle Status">
                                <i class="ti ti-toggle-<?= $role['status'] === 'active' ? 'right' : 'left' ?> fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete"
                                data-id="<?= $role['id'] ?>" data-name="<?= htmlspecialchars($role['name']) ?>"
                                title="Delete">
                                <i class="ti ti-trash fs-lg"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer border-light d-flex justify-content-between align-items-center">
        <div data-table-pagination-info></div>
        <div data-table-pagination></div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/roles" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name"
                            placeholder="e.g. Manager, Staff, Customer" required>
                    </div>
                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3"
                            placeholder="Brief description of the role responsibilities"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="roleStatus" class="form-label">Status</label>
                        <select class="form-select" id="roleStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRoleForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editRoleName" class="form-label">Role Name <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRoleName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRoleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editRoleDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editRoleStatus" class="form-label">Status</label>
                        <select class="form-select" id="editRoleStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
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

    // Edit Role Modal - populate form
    const editModal = document.getElementById('editRoleModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const status = button.getAttribute('data-status');

            document.getElementById('editRoleName').value = name;
            document.getElementById('editRoleDescription').value = description;
            document.getElementById('editRoleStatus').value = status;
            document.getElementById('editRoleForm').action = '<?= APP_URL ?>/roles/' + id;
        });
    }

    // Delete Role - SweetAlert confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            Swal.fire({
                title: 'Delete Role?',
                html: `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= APP_URL ?>/roles/' + id + '/delete';
                }
            });
        });
    });

    // Toggle Status - SweetAlert confirmation
    const toggleButtons = document.querySelectorAll('.btn-toggle-status');
    toggleButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const currentStatus = this.getAttribute('data-status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            Swal.fire({
                title: 'Change Status?',
                html: `Do you want to change <strong>${name}</strong> status to <strong>${newStatus}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= APP_URL ?>/roles/' + id +
                        '/toggle-status';
                }
            });
        });
    });
});
</script>