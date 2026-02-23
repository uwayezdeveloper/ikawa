<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-accounts', $permissions);
$canEdit = in_array('edit-accounts', $permissions);
$canDelete = in_array('delete-accounts', $permissions);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Certification</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Record Certification Categories</li>
            </ol>
        </nav>
    </div>
</div>

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

<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search certification categories..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                    <option value="All">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
            <div>
                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
            <?php if ($canCreate): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertificationModal">
                <i class="ti ti-plus me-1"></i> Add Certification Category
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Certification Name</th>
                    <th data-table-sort>Description</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 5 : 4 ?>" class="text-center py-4">
                        <i class="ti ti-certificate fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No certification categories found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $index => $category): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($category['cert_name']) ?></h5>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($category['cert_desc'] ?? '-') ?></span>
                    </td>
                    <td><?= (int)$category['status'] === 1 ? 'Active' : 'Inactive' ?></td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-certification"
                                data-id="<?= $category['cert_id'] ?>"
                                data-name="<?= htmlspecialchars($category['cert_name']) ?>"
                                data-description="<?= htmlspecialchars($category['cert_desc'] ?? '') ?>"
                                data-status="<?= (int)$category['status'] ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#editCertificationModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-certification"
                                data-id="<?= $category['cert_id'] ?>"
                                data-name="<?= htmlspecialchars($category['cert_name']) ?>">
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
<div class="modal fade" id="addCertificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Certification Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/certification/categories" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addCertName" class="form-label">Certification Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="addCertName" name="cert_name" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="addCertDesc" class="form-label">Description</label>
                        <input type="text" class="form-control" id="addCertDesc" name="cert_desc" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="addCertStatus" class="form-label">Status</label>
                        <select class="form-select" id="addCertStatus" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<div class="modal fade" id="editCertificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Certification Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/certification/categories" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="cert_id" id="editCertId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCertName" class="form-label">Certification Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editCertName" name="cert_name" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCertDesc" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editCertDesc" name="cert_desc" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="editCertStatus" class="form-label">Status</label>
                        <select class="form-select" id="editCertStatus" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDelete): ?>
<form id="deleteCertificationForm" action="<?= APP_URL ?>/certification/categories" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="cert_id" id="deleteCertId">
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-certification').forEach(function(button) {
        button.addEventListener('click', function() {
            document.getElementById('editCertId').value = this.dataset.id;
            document.getElementById('editCertName').value = this.dataset.name;
            document.getElementById('editCertDesc').value = this.dataset.description;
            document.getElementById('editCertStatus').value = this.dataset.status;
        });
    });

    document.querySelectorAll('.btn-delete-certification').forEach(function(button) {
        button.addEventListener('click', function() {
            const certId = this.dataset.id;
            const certName = this.dataset.name;

            Swal.fire({
                title: 'Delete Certification Category?',
                text: 'Are you sure you want to delete "' + certName + '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('deleteCertId').value = certId;
                    document.getElementById('deleteCertificationForm').submit();
                }
            });
        });
    });
});
</script>
