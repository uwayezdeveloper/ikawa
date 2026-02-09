<?php
/**
 * Permissions Management View
 */

$permissions = $permissions ?? ['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 1];
$stats = $stats ?? ['active' => 0, 'inactive' => 0, 'total' => 0];
$modules = $modules ?? [];

// Flash messages for SweetAlert
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Page Header -->
<div class="d-flex align-items-sm-center flex-sm-row flex-column my-3">
    <div class="flex-grow-1">
        <h4 class="fs-xl mb-1">Manage Permissions</h4>
        <p class="text-muted mb-0">
            Define and manage system permissions for access control.
        </p>
    </div>
    <div class="text-end d-flex gap-2">
        <a href="<?= APP_URL ?>/permissions/roles" class="btn btn-info">
            <i class="ti ti-shield-lock me-1"></i> Role Permissions
        </a>
        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addPermissionModal" class="btn btn-success">
            <i class="ti ti-plus me-1"></i> Add Permission
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-md rounded bg-primary-subtle">
                        <i class="ti ti-key fs-24 text-primary d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="mb-0"><?= $stats['total'] ?></h4>
                        <p class="text-muted mb-0">Total Permissions</p>
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
                        <i class="ti ti-check fs-24 text-success d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="mb-0"><?= $stats['active'] ?></h4>
                        <p class="text-muted mb-0">Active Permissions</p>
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
                        <p class="text-muted mb-0">Inactive Permissions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">All Permissions</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="filterModule" style="width: 150px;">
                <option value="">All Modules</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= htmlspecialchars($module) ?>"><?= ucfirst(htmlspecialchars($module)) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" class="form-control form-control-sm" id="searchPermission" placeholder="Search..." style="width: 180px;">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0" id="permissionsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Permission Name</th>
                        <th>Slug</th>
                        <th>Module</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($permissions['data'])): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ti ti-mood-empty fs-32 d-block mb-2"></i>
                                    No permissions found
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($permissions['data'] as $index => $permission): ?>
                            <tr data-module="<?= htmlspecialchars($permission['module']) ?>">
                                <td><?= (($permissions['page'] - 1) * 15) + $index + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded bg-info-subtle me-2">
                                            <i class="ti ti-key fs-18 text-info d-flex align-items-center justify-content-center h-100"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold"><?= htmlspecialchars($permission['name']) ?></span>
                                            <?php if ($permission['description']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-primary"><?= htmlspecialchars($permission['slug']) ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= ucfirst(htmlspecialchars($permission['module'])) ?></span>
                                </td>
                                <td>
                                    <?php if ($permission['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success px-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger px-2">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-soft-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPermissionModal"
                                                data-id="<?= $permission['id'] ?>"
                                                data-name="<?= htmlspecialchars($permission['name']) ?>"
                                                data-slug="<?= htmlspecialchars($permission['slug']) ?>"
                                                data-description="<?= htmlspecialchars($permission['description'] ?? '') ?>"
                                                data-module="<?= htmlspecialchars($permission['module']) ?>"
                                                data-status="<?= $permission['status'] ?>"
                                                title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-danger btn-sm btn-delete" 
                                                data-id="<?= $permission['id'] ?>"
                                                data-name="<?= htmlspecialchars($permission['name']) ?>"
                                                title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($permissions['totalPages'] > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing page <?= $permissions['page'] ?> of <?= $permissions['totalPages'] ?> 
                    (<?= $permissions['total'] ?> total permissions)
                </div>
                <nav aria-label="Permission pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($permissions['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= APP_URL ?>/permissions?page=<?= $permissions['page'] - 1 ?>">
                                    <i class="ti ti-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $permissions['totalPages']; $i++): ?>
                            <li class="page-item <?= $i === $permissions['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= APP_URL ?>/permissions?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($permissions['page'] < $permissions['totalPages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= APP_URL ?>/permissions?page=<?= $permissions['page'] + 1 ?>">
                                    <i class="ti ti-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Permission Modal -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPermissionModalLabel">Add New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/permissions" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="permissionName" class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="permissionName" name="name" 
                               placeholder="e.g. Create Users, View Reports" required>
                    </div>
                    <div class="mb-3">
                        <label for="permissionSlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="permissionSlug" name="slug" 
                               placeholder="Auto-generated if empty">
                        <small class="text-muted">Leave empty to auto-generate from name</small>
                    </div>
                    <div class="mb-3">
                        <label for="permissionModule" class="form-label">Module</label>
                        <input type="text" class="form-control" id="permissionModule" name="module" 
                               placeholder="e.g. users, roles, settings" list="moduleList">
                        <datalist id="moduleList">
                            <?php foreach ($modules as $module): ?>
                                <option value="<?= htmlspecialchars($module) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label for="permissionDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="permissionDescription" name="description" rows="2" 
                                  placeholder="Brief description of the permission"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="permissionStatus" class="form-label">Status</label>
                        <select class="form-select" id="permissionStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPermissionModalLabel">Edit Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPermissionForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editPermissionName" class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPermissionName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPermissionSlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="editPermissionSlug" name="slug">
                    </div>
                    <div class="mb-3">
                        <label for="editPermissionModule" class="form-label">Module</label>
                        <input type="text" class="form-control" id="editPermissionModule" name="module" list="editModuleList">
                        <datalist id="editModuleList">
                            <?php foreach ($modules as $module): ?>
                                <option value="<?= htmlspecialchars($module) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label for="editPermissionDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editPermissionDescription" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editPermissionStatus" class="form-label">Status</label>
                        <select class="form-select" id="editPermissionStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Permission</button>
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

    // Edit Permission Modal - populate form
    const editModal = document.getElementById('editPermissionModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const slug = button.getAttribute('data-slug');
            const description = button.getAttribute('data-description');
            const module = button.getAttribute('data-module');
            const status = button.getAttribute('data-status');
            
            document.getElementById('editPermissionName').value = name;
            document.getElementById('editPermissionSlug').value = slug;
            document.getElementById('editPermissionDescription').value = description;
            document.getElementById('editPermissionModule').value = module;
            document.getElementById('editPermissionStatus').value = status;
            document.getElementById('editPermissionForm').action = '<?= APP_URL ?>/permissions/' + id;
        });
    }
    
    // Delete Permission - SweetAlert confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Delete Permission?',
                html: `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-muted">This will also remove it from all roles.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= APP_URL ?>/permissions/' + id + '/delete';
                }
            });
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchPermission');
    const filterModule = document.getElementById('filterModule');
    
    function filterTable() {
        const searchFilter = searchInput.value.toLowerCase();
        const moduleFilter = filterModule.value.toLowerCase();
        const rows = document.querySelectorAll('#permissionsTable tbody tr');
        
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            const rowModule = (row.getAttribute('data-module') || '').toLowerCase();
            
            const matchesSearch = text.includes(searchFilter);
            const matchesModule = !moduleFilter || rowModule === moduleFilter;
            
            row.style.display = (matchesSearch && matchesModule) ? '' : 'none';
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('keyup', filterTable);
    }
    
    if (filterModule) {
        filterModule.addEventListener('change', filterTable);
    }
    
    // Auto-generate slug from name
    const nameInput = document.getElementById('permissionName');
    const slugInput = document.getElementById('permissionSlug');
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                slugInput.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9-]/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    }
});
</script>
