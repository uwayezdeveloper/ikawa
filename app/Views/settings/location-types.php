<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-location-types', $permissions);
$canEdit = in_array('edit-location-types', $permissions);
$canDelete = in_array('delete-location-types', $permissions);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/settings">Settings</a></li>
                    <li class="breadcrumb-item active">Location Types</li>
                </ol>
            </div>
            <h4 class="page-title">Location Types</h4>
        </div>
    </div>
</div>

<!-- Content -->
<div class="row">
    <!-- Main Content -->
    <div class="col-12">
        <div class="card" data-table data-table-rows-per-page="10">
            <div class="card-header border-light justify-content-between">
                <div class="d-flex gap-2">
                    <div class="app-search">
                        <input data-table-search type="search" class="form-control" placeholder="Search location types..." />
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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationTypeModal">
                        <i class="ti ti-plus me-1"></i> Add New
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-centered table-hover w-100 mb-0">
                    <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                        <tr class="text-uppercase fs-xxs">
                            <th class="ps-3" style="width: 5%">#</th>
                            <th data-table-sort>Name</th>
                            <th data-table-sort>Description</th>
                            <th data-table-sort data-column="status">Status</th>
                            <?php if ($canEdit || $canDelete): ?>
                            <th class="text-center">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($locationTypes)): ?>
                        <tr>
                            <td colspan="<?= ($canEdit || $canDelete) ? 5 : 4 ?>" class="text-center text-muted py-4">
                                <i class="ti ti-map-pin-off fs-32 d-block mb-2"></i>
                                No location types found
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($locationTypes as $index => $type): ?>
                        <tr>
                            <td class="ps-3">
                                <h5 class="m-0"><?= $index + 1 ?></h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0">
                                    <a href="#" class="link-reset"><?= htmlspecialchars($type['name']) ?></a>
                                </h5>
                            </td>
                            <td>
                                <span class="text-muted"><?= htmlspecialchars($type['description'] ?? '') ?: '-' ?></span>
                            </td>
                            <td><?= $type['status'] === 'active' ? 'Active' : 'Inactive' ?></td>
                            <?php if ($canEdit || $canDelete): ?>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if ($canEdit): ?>
                                    <button type="button" class="btn btn-default btn-icon btn-sm" 
                                        onclick="editLocationType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($type['description'] ?? '', ENT_QUOTES) ?>', '<?= $type['status'] ?>')">
                                        <i class="ti ti-edit fs-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                    <button type="button" class="btn btn-default btn-icon btn-sm" 
                                        onclick="deleteLocationType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['name'], ENT_QUOTES) ?>')">
                                        <i class="ti ti-trash fs-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
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
    </div>
</div>

<?php if ($canCreate): ?>
<!-- Add Location Type Modal -->
<div class="modal fade" id="addLocationTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= APP_URL ?>/settings/location-types" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-plus me-2"></i>Add Location Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_name" name="name" required placeholder="e.g. Warehouse, Store, Office">
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3" placeholder="Brief description of this location type"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status</label>
                        <select class="form-select" id="add_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Location Type Modal -->
<div class="modal fade" id="editLocationTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= APP_URL ?>/settings/location-types" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Edit Location Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Form (Hidden) -->
<form id="deleteForm" action="<?= APP_URL ?>/settings/location-types" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>
<?php endif; ?>

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

function editLocationType(id, name, description, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_status').value = status;
    
    new bootstrap.Modal(document.getElementById('editLocationTypeModal')).show();
}

function deleteLocationType(id, name) {
    Swal.fire({
        title: 'Delete Location Type?',
        html: `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>
