<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-category-types', $permissions);
$canEdit = in_array('edit-category-types', $permissions);
$canDelete = in_array('delete-category-types', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Category Types</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/products/categories">Product Categories</a></li>
                <li class="breadcrumb-item active">Category Types</li>
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

<!-- Category Types Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search category types..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Category Filter -->
            <div class="app-search">
                <select data-table-filter="category" class="form-select form-control my-1 my-md-0">
                    <option value="All">Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                <i class="ti ti-plus me-1"></i> Add Type
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
                    <th data-table-sort data-column="category">Category</th>
                    <th data-table-sort>Description</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categoryTypes)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 6 : 5 ?>" class="text-center py-4">
                        <i class="ti ti-folder-open fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No category types found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($categoryTypes as $index => $type): ?>
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
                        <span class="badge bg-info-subtle text-info"><?= htmlspecialchars($type['category_name'] ?? 'N/A') ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($type['description'] ?? '-') ?></span>
                    </td>
                    <td><?= $type['status'] === 'active' ? 'Active' : 'Inactive' ?></td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-type"
                                data-id="<?= $type['id'] ?>" 
                                data-name="<?= htmlspecialchars($type['name']) ?>"
                                data-category-id="<?= $type['category_id'] ?>"
                                data-description="<?= htmlspecialchars($type['description'] ?? '') ?>"
                                data-status="<?= $type['status'] ?>" 
                                data-bs-toggle="modal"
                                data-bs-target="#editTypeModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-type"
                                data-id="<?= $type['id'] ?>" 
                                data-name="<?= htmlspecialchars($type['name']) ?>">
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

<?php if ($canCreate): ?>
<!-- Add Type Modal -->
<div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/products/category-types" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="addCategory" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addName" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addName" name="name" required
                            placeholder="Enter type name">
                    </div>
                    <div class="mb-3">
                        <label for="addDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="addDescription" name="description" rows="3"
                            placeholder="Enter description (optional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="addStatus" class="form-label">Status</label>
                        <select class="form-select" id="addStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Type Modal -->
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/products/category-types" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="editCategory" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editName" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editName" name="name" required
                            placeholder="Enter type name">
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"
                            placeholder="Enter description (optional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Form -->
<form id="deleteTypeForm" action="<?= APP_URL ?>/products/category-types" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit button click handler
    document.querySelectorAll('.btn-edit-type').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editCategory').value = this.dataset.categoryId;
            document.getElementById('editDescription').value = this.dataset.description;
            document.getElementById('editStatus').value = this.dataset.status;
        });
    });

    // Delete button click handler
    document.querySelectorAll('.btn-delete-type').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Category Type?',
                text: 'Are you sure you want to delete "' + name + '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteTypeForm').submit();
                }
            });
        });
    });
});
</script>
