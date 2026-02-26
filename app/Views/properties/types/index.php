<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-accounts', $permissions);
$canEdit = in_array('edit-accounts', $permissions);
$canDelete = in_array('delete-accounts', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Property Types</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/properties">Property Management</a></li>
                <li class="breadcrumb-item active">Property Types</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?= addslashes($_SESSION['flash_success']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
});
</script>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?= addslashes($_SESSION['flash_error']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
});
</script>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- Property Types Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search property types..." />
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyTypeModal">
                <i class="ti ti-plus me-1"></i> Add Property Type
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Type Name</th>
                    <th data-table-sort>Description</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($propertyTypes)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 5 : 4 ?>" class="text-center py-4">
                        <i class="ti ti-category fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No property types found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($propertyTypes as $index => $propertyType): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0">
                            <a href="#" class="link-reset"><?= htmlspecialchars($propertyType['type_name']) ?></a>
                        </h5>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($propertyType['description'] ?: '-') ?></span>
                    </td>
                    <td>
                        <?php if ($propertyType['status'] == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-property-type"
                                data-id="<?= $propertyType['type_id'] ?>"
                                data-name="<?= htmlspecialchars($propertyType['type_name']) ?>"
                                data-description="<?= htmlspecialchars($propertyType['description'] ?? '') ?>"
                                data-status="<?= $propertyType['status'] ?>"
                                data-bs-toggle="modal" data-bs-target="#editPropertyTypeModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-danger btn-icon btn-sm btn-delete-property-type"
                                data-id="<?= $propertyType['type_id'] ?>"
                                data-name="<?= htmlspecialchars($propertyType['type_name']) ?>">
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
</div>

<!-- Add Property Type Modal -->
<?php if ($canCreate): ?>
<div class="modal fade" id="addPropertyTypeModal" tabindex="-1" aria-labelledby="addPropertyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPropertyTypeModalLabel">Add Property Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/properties/types">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="type_name" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="type_name" name="type_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Property Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Property Type Modal -->
<?php if ($canEdit): ?>
<div class="modal fade" id="editPropertyTypeModal" tabindex="-1" aria-labelledby="editPropertyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPropertyTypeModalLabel">Edit Property Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/properties/types">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="type_id" id="edit_type_id">
                    
                    <div class="mb-3">
                        <label for="edit_type_name" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_type_name" name="type_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Property Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<?php if ($canDelete): ?>
<div class="modal fade" id="deletePropertyTypeModal" tabindex="-1" aria-labelledby="deletePropertyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePropertyTypeModalLabel">Delete Property Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/properties/types">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="type_id" id="delete_type_id">
                    <p>Are you sure you want to delete the property type "<strong id="delete_type_name"></strong>"?</p>
                    <p class="text-muted small">This action will deactivate the property type but won't permanently remove it from the database.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Property Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Property Type Modal
    const editButtons = document.querySelectorAll('.btn-edit-property-type');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const status = this.getAttribute('data-status');
            
            document.getElementById('edit_type_id').value = id;
            document.getElementById('edit_type_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_status').value = status;
        });
    });
    
    // Delete Property Type Modal
    const deleteButtons = document.querySelectorAll('.btn-delete-property-type');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('delete_type_id').value = id;
            document.getElementById('delete_type_name').textContent = name;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deletePropertyTypeModal'));
            deleteModal.show();
        });
    });
});
</script>