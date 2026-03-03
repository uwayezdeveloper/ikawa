<?php
$permissions = $user['permissions'] ?? [];
if (is_string($permissions)) {
    $decodedPermissions = json_decode($permissions, true);
    if (is_array($decodedPermissions)) {
        $permissions = $decodedPermissions;
    } else {
        $permissions = array_filter(array_map('trim', explode(',', $permissions)));
    }
}
if (!is_array($permissions)) {
    $permissions = [];
}
$canManage = in_array('manage-type-unit-assignments', $permissions, true);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Type Unit Assignments</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/products/categories">Products</a></li>
                <li class="breadcrumb-item active">Type Unit Assignments</li>
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
        <h5 class="card-title mb-0"><i class="ti ti-link me-2"></i>Assign Units to Category Type</h5>
    </div>
    <div class="card-body">
        <form action="<?= APP_URL ?>/products/type-units" method="POST" id="assignForm">
            <input type="hidden" name="action" value="assign">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="categoryType" class="form-label">Category Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="categoryType" name="category_type_id" required>
                        <option value="">Select Category Type</option>
                        <?php foreach ($categoryTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['category_name']) ?> → <?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="measurementUnits" class="form-label">Measurement Units <span class="text-danger">*</span></label>
                    <select class="form-select" id="measurementUnits" name="unit_ids[]" multiple required style="height: 100px;">
                        <?php foreach ($measurementUnits as $unit): ?>
                        <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?> (<?= htmlspecialchars($unit['symbol']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                </div>
                <div class="col-md-3">
                    <label for="defaultUnit" class="form-label">Default Unit</label>
                    <select class="form-select" id="defaultUnit" name="default_unit_id">
                        <option value="">Select default...</option>
                        <?php foreach ($measurementUnits as $unit): ?>
                        <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?> (<?= htmlspecialchars($unit['symbol']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
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

<?php if (!empty($typesWithoutUnits)): ?>
<!-- Types Without Units Alert -->
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <strong><i class="ti ti-alert-triangle me-2"></i>Types Without Units:</strong>
    The following category types have no measurement units assigned:
    <ul class="mb-0 mt-2">
        <?php foreach ($typesWithoutUnits as $type): ?>
        <li><?= htmlspecialchars($type['category_name']) ?> → <strong><?= htmlspecialchars($type['name']) ?></strong></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                    <th data-table-sort>Category</th>
                    <th data-table-sort>Type</th>
                    <th>Assigned Units</th>
                    <th>Default Unit</th>
                    <?php if ($canManage): ?>
                    <th class="text-center" style="width: 100px">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                <tr>
                    <td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center py-4">
                        <i class="ti ti-link-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No unit assignments found</p>
                        <p class="text-muted small">Use the form above to assign measurement units to category types</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($assignments as $index => $assignment): ?>
                <tr data-type-id="<?= $assignment['type_id'] ?>">
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info"><?= htmlspecialchars($assignment['category_name']) ?></span>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($assignment['type_name']) ?></h5>
                    </td>
                    <td>
                        <?php foreach ($assignment['units'] as $unit): ?>
                        <span class="badge bg-<?= $unit['is_default'] ? 'primary' : 'secondary' ?>-subtle text-<?= $unit['is_default'] ? 'primary' : 'secondary' ?> me-1 mb-1">
                            <?= htmlspecialchars($unit['unit_name']) ?> (<?= htmlspecialchars($unit['unit_symbol']) ?>)
                            <?php if ($unit['is_default']): ?>
                            <i class="ti ti-star-filled ms-1"></i>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php 
                        $defaultUnit = array_filter($assignment['units'], function ($u) {
                            return !empty($u['is_default']);
                        });
                        $defaultUnit = reset($defaultUnit);
                        ?>
                        <?php if ($defaultUnit): ?>
                        <span class="fw-medium text-primary">
                            <?= htmlspecialchars($defaultUnit['unit_name']) ?> (<?= htmlspecialchars($defaultUnit['unit_symbol']) ?>)
                        </span>
                        <?php else: ?>
                        <span class="text-muted">Not set</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canManage): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-assignment"
                                data-type-id="<?= $assignment['type_id'] ?>"
                                data-type-name="<?= htmlspecialchars($assignment['type_name']) ?>"
                                data-category-name="<?= htmlspecialchars($assignment['category_name']) ?>"
                                data-units='<?= json_encode($assignment['units']) ?>'
                                data-bs-toggle="modal"
                                data-bs-target="#editAssignmentModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-clear-assignment"
                                data-type-id="<?= $assignment['type_id'] ?>"
                                data-type-name="<?= htmlspecialchars($assignment['type_name']) ?>">
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
                <h5 class="modal-title">Edit Unit Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/products/type-units" method="POST" id="editAssignmentForm">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="category_type_id" id="editTypeId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Type</label>
                        <input type="text" class="form-control" id="editTypeName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editUnits" class="form-label">Measurement Units <span class="text-danger">*</span></label>
                        <select class="form-select" id="editUnits" name="unit_ids[]" multiple required style="height: 120px;">
                            <?php foreach ($measurementUnits as $unit): ?>
                            <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?> (<?= htmlspecialchars($unit['symbol']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>
                    <div class="mb-3">
                        <label for="editDefaultUnit" class="form-label">Default Unit</label>
                        <select class="form-select" id="editDefaultUnit" name="default_unit_id">
                            <option value="">Select default...</option>
                            <?php foreach ($measurementUnits as $unit): ?>
                            <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?> (<?= htmlspecialchars($unit['symbol']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
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
<form id="clearAssignmentForm" action="<?= APP_URL ?>/products/type-units" method="POST" style="display: none;">
    <input type="hidden" name="action" value="assign">
    <input type="hidden" name="category_type_id" id="clearTypeId">
    <!-- Empty unit_ids array will clear all assignments -->
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update default unit dropdown based on selected units
    const measurementUnits = document.getElementById('measurementUnits');
    const defaultUnit = document.getElementById('defaultUnit');
    
    if (measurementUnits && defaultUnit) {
        measurementUnits.addEventListener('change', function() {
            const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
            
            // Update default dropdown
            Array.from(defaultUnit.options).forEach(opt => {
                if (opt.value === '') return; // Skip placeholder
                opt.disabled = !selectedValues.includes(opt.value);
                if (opt.disabled && opt.selected) {
                    opt.selected = false;
                }
            });
        });
    }

    // Edit button click handler
    document.querySelectorAll('.btn-edit-assignment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const typeId = this.dataset.typeId;
            const typeName = this.dataset.typeName;
            const categoryName = this.dataset.categoryName;
            const units = JSON.parse(this.dataset.units);
            
            document.getElementById('editTypeId').value = typeId;
            document.getElementById('editTypeName').value = categoryName + ' → ' + typeName;
            
            // Select assigned units
            const editUnits = document.getElementById('editUnits');
            Array.from(editUnits.options).forEach(opt => {
                opt.selected = units.some(u => u.unit_id == opt.value);
            });
            
            // Set default unit
            const defaultUnit = units.find(u => u.is_default);
            document.getElementById('editDefaultUnit').value = defaultUnit ? defaultUnit.unit_id : '';
        });
    });

    // Clear assignment button
    document.querySelectorAll('.btn-clear-assignment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const typeId = this.dataset.typeId;
            const typeName = this.dataset.typeName;

            Swal.fire({
                title: 'Clear All Assignments?',
                text: 'Are you sure you want to remove all unit assignments from "' + typeName + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, clear all!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Submit form with empty units to clear all
                    const form = document.getElementById('clearAssignmentForm');
                    document.getElementById('clearTypeId').value = typeId;
                    
                    // Add a hidden input for empty unit_ids
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'unit_ids[]';
                    input.value = '';
                    form.appendChild(input);
                    
                    form.submit();
                }
            });
        });
    });

    // Update edit modal default dropdown based on selected units
    const editUnits = document.getElementById('editUnits');
    const editDefaultUnit = document.getElementById('editDefaultUnit');
    
    if (editUnits && editDefaultUnit) {
        editUnits.addEventListener('change', function() {
            const selectedValues = Array.from(this.selectedOptions).map(opt => opt.value);
            
            Array.from(editDefaultUnit.options).forEach(opt => {
                if (opt.value === '') return;
                opt.disabled = !selectedValues.includes(opt.value);
                if (opt.disabled && opt.selected) {
                    opt.selected = false;
                }
            });
        });
    }
});
</script>
