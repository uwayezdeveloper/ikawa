<?php
$permissions = $user['permissions'] ?? [];
$canManage = in_array('manage-location-categories', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Location Type Categories</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/settings">Settings</a></li>
                <li class="breadcrumb-item active">Location Type Categories</li>
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

<?php if ($canManage): ?>
<!-- Assignment Form Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-link me-2"></i>Assign Product Categories to Location Type</h5>
    </div>
    <div class="card-body">
        <form action="<?= APP_URL ?>/settings/location-categories" method="POST" id="assignForm">
            <input type="hidden" name="action" value="assign">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="locationType" class="form-label">Location Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="locationType" name="location_type_id" required>
                        <option value="">Select Location Type</option>
                        <?php foreach ($locationTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="productCategories" class="form-label">Product Categories <span class="text-danger">*</span></label>
                    <select class="form-select" id="productCategories" name="category_ids[]" multiple required style="height: 100px;">
                        <?php foreach ($productCategories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple categories</small>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($locationTypesWithout) || !empty($categoriesWithout)): ?>
<!-- Alerts for unassigned items -->
<div class="row mb-4">
    <?php if (!empty($locationTypesWithout)): ?>
    <div class="col-md-6">
        <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
            <strong><i class="ti ti-map-pin-off me-2"></i>Location Types Without Categories:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($locationTypesWithout as $type): ?>
                <li><strong><?= htmlspecialchars($type['name']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($categoriesWithout)): ?>
    <div class="col-md-6">
        <div class="alert alert-info alert-dismissible fade show mb-0" role="alert">
            <strong><i class="ti ti-category-off me-2"></i>Categories Not Assigned:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($categoriesWithout as $category): ?>
                <li><strong><?= htmlspecialchars($category['name']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Assignments Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search assignments..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Records Per Page -->
            <div>
                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Location Type</th>
                    <th>Assigned Product Categories</th>
                    <th class="text-center" style="width: 80px">Count</th>
                    <?php if ($canManage): ?>
                    <th class="text-center" style="width: 100px">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                <tr>
                    <td colspan="<?= $canManage ? 5 : 4 ?>" class="text-center py-4">
                        <i class="ti ti-link-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No category assignments found</p>
                        <p class="text-muted small">Use the form above to assign product categories to location types</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($assignments as $index => $assignment): ?>
                <tr data-location-type-id="<?= $assignment['location_type_id'] ?>">
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($assignment['location_type_name']) ?></h5>
                        <?php if (!empty($assignment['location_type_description'])): ?>
                        <small class="text-muted"><?= htmlspecialchars($assignment['location_type_description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php foreach ($assignment['categories'] as $category): ?>
                        <span class="badge bg-primary-subtle text-primary me-1 mb-1">
                            <?= htmlspecialchars($category['category_name']) ?>
                        </span>
                        <?php endforeach; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary"><?= count($assignment['categories']) ?></span>
                    </td>
                    <?php if ($canManage): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-assignment"
                                data-location-type-id="<?= $assignment['location_type_id'] ?>"
                                data-location-type-name="<?= htmlspecialchars($assignment['location_type_name']) ?>"
                                data-categories='<?= json_encode($assignment['categories']) ?>'
                                data-bs-toggle="modal"
                                data-bs-target="#editAssignmentModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-clear-assignment"
                                data-location-type-id="<?= $assignment['location_type_id'] ?>"
                                data-location-type-name="<?= htmlspecialchars($assignment['location_type_name']) ?>">
                                <i class="ti ti-trash fs-lg text-danger"></i>
                            </button>
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

<?php if ($canManage): ?>
<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/settings/location-categories" method="POST" id="editAssignmentForm">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="location_type_id" id="editLocationTypeId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Type</label>
                        <input type="text" class="form-control" id="editLocationTypeName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editCategories" class="form-label">Product Categories <span class="text-danger">*</span></label>
                        <select class="form-select" id="editCategories" name="category_ids[]" multiple required style="height: 150px;">
                            <?php foreach ($productCategories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple categories</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clear Assignment Form -->
<form id="clearAssignmentForm" action="<?= APP_URL ?>/settings/location-categories" method="POST" style="display: none;">
    <input type="hidden" name="action" value="assign">
    <input type="hidden" name="location_type_id" id="clearLocationTypeId">
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit button click handler
    document.querySelectorAll('.btn-edit-assignment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const locationTypeId = this.dataset.locationTypeId;
            const locationTypeName = this.dataset.locationTypeName;
            const categories = JSON.parse(this.dataset.categories);
            
            document.getElementById('editLocationTypeId').value = locationTypeId;
            document.getElementById('editLocationTypeName').value = locationTypeName;
            
            // Select assigned categories
            const editCategories = document.getElementById('editCategories');
            Array.from(editCategories.options).forEach(opt => {
                opt.selected = categories.some(c => c.category_id == opt.value);
            });
        });
    });

    // Clear assignment button
    document.querySelectorAll('.btn-clear-assignment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const locationTypeId = this.dataset.locationTypeId;
            const locationTypeName = this.dataset.locationTypeName;

            Swal.fire({
                title: 'Clear All Assignments?',
                text: 'Are you sure you want to remove all category assignments from "' + locationTypeName + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, clear all!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    const form = document.getElementById('clearAssignmentForm');
                    document.getElementById('clearLocationTypeId').value = locationTypeId;
                    form.submit();
                }
            });
        });
    });
});
</script>
