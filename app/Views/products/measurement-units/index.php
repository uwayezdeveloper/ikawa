<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-measurement-units', $permissions);
$canEdit = in_array('edit-measurement-units', $permissions);
$canDelete = in_array('delete-measurement-units', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Measurement Units</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/products/categories">Products</a></li>
                <li class="breadcrumb-item active">Measurement Units</li>
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

<!-- Conversion Calculator Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-calculator me-2"></i>Unit Converter</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="convertValue" class="form-label">Value</label>
                <input type="number" class="form-control" id="convertValue" placeholder="Enter value" step="any" min="0">
            </div>
            <div class="col-md-3">
                <label for="convertFrom" class="form-label">From Unit</label>
                <select class="form-select" id="convertFrom">
                    <option value="">Select unit</option>
                    <?php foreach ($units as $unit): ?>
                    <?php if ($unit['status'] === 'active'): ?>
                    <option value="<?= $unit['id'] ?>" data-symbol="<?= htmlspecialchars($unit['symbol']) ?>">
                        <?= htmlspecialchars($unit['name']) ?> (<?= htmlspecialchars($unit['symbol']) ?>)
                    </option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="convertTo" class="form-label">To Unit</label>
                <select class="form-select" id="convertTo">
                    <option value="">Select unit</option>
                    <?php foreach ($units as $unit): ?>
                    <?php if ($unit['status'] === 'active'): ?>
                    <option value="<?= $unit['id'] ?>" data-symbol="<?= htmlspecialchars($unit['symbol']) ?>">
                        <?= htmlspecialchars($unit['name']) ?> (<?= htmlspecialchars($unit['symbol']) ?>)
                    </option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100" id="btnConvert">
                    <i class="ti ti-arrows-exchange me-1"></i> Convert
                </button>
            </div>
        </div>
        <div class="row mt-3" id="conversionResult" style="display: none;">
            <div class="col-12">
                <div class="alert alert-success mb-0">
                    <strong>Result:</strong> <span id="resultText"></span>
                </div>
            </div>
        </div>
        <div class="row mt-3" id="conversionTable" style="display: none;">
            <div class="col-12">
                <h6 class="mb-2">All Conversions:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Unit</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody id="conversionTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Measurement Units Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search units..." />
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                <i class="ti ti-plus me-1"></i> Add Unit
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Unit Name</th>
                    <th data-table-sort>Symbol</th>
                    <th data-table-sort>Conversion Factor</th>
                    <th>Description</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($units)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 7 : 6 ?>" class="text-center py-4">
                        <i class="ti ti-ruler fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No measurement units found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($units as $index => $unit): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0">
                            <a href="#" class="link-reset"><?= htmlspecialchars($unit['name']) ?></a>
                        </h5>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary fs-sm"><?= htmlspecialchars($unit['symbol']) ?></span>
                    </td>
                    <td>
                        <code><?= number_format($unit['conversion_factor'], 10) ?></code>
                        <?php if ($unit['base_unit_name']): ?>
                        <small class="text-muted d-block">to <?= htmlspecialchars($unit['base_unit_symbol']) ?></small>
                        <?php else: ?>
                        <small class="text-success d-block">(Base Unit)</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($unit['description'] ?? '-') ?></span>
                    </td>
                    <td><?= $unit['status'] === 'active' ? 'Active' : 'Inactive' ?></td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-unit"
                                data-id="<?= $unit['id'] ?>" 
                                data-name="<?= htmlspecialchars($unit['name']) ?>"
                                data-symbol="<?= htmlspecialchars($unit['symbol']) ?>"
                                data-base-unit-id="<?= $unit['base_unit_id'] ?? '' ?>"
                                data-conversion-factor="<?= $unit['conversion_factor'] ?>"
                                data-description="<?= htmlspecialchars($unit['description'] ?? '') ?>"
                                data-status="<?= $unit['status'] ?>" 
                                data-bs-toggle="modal"
                                data-bs-target="#editUnitModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-unit"
                                data-id="<?= $unit['id'] ?>" 
                                data-name="<?= htmlspecialchars($unit['name']) ?>">
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
<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Measurement Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/products/measurement-units" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addName" class="form-label">Unit Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addName" name="name" required
                                placeholder="e.g., Kilogram">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addSymbol" class="form-label">Symbol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addSymbol" name="symbol" required
                                placeholder="e.g., kg">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addBaseUnit" class="form-label">Base Unit</label>
                            <select class="form-select" id="addBaseUnit" name="base_unit_id">
                                <option value="">None (This is a base unit)</option>
                                <?php foreach ($baseUnits as $baseUnit): ?>
                                <option value="<?= $baseUnit['id'] ?>"><?= htmlspecialchars($baseUnit['name']) ?> (<?= htmlspecialchars($baseUnit['symbol']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Leave empty if this is a base unit</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addConversionFactor" class="form-label">Conversion Factor</label>
                            <input type="number" class="form-control" id="addConversionFactor" name="conversion_factor" 
                                step="any" min="0" value="1" placeholder="e.g., 1000">
                            <small class="text-muted">How many base units equal 1 of this unit</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="addDescription" name="description" rows="2"
                            placeholder="e.g., 1 kg = 1000 g"></textarea>
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
                    <button type="submit" class="btn btn-primary">Create Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Measurement Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/products/measurement-units" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editName" class="form-label">Unit Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editSymbol" class="form-label">Symbol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSymbol" name="symbol" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editBaseUnit" class="form-label">Base Unit</label>
                            <select class="form-select" id="editBaseUnit" name="base_unit_id">
                                <option value="">None (This is a base unit)</option>
                                <?php foreach ($baseUnits as $baseUnit): ?>
                                <option value="<?= $baseUnit['id'] ?>"><?= htmlspecialchars($baseUnit['name']) ?> (<?= htmlspecialchars($baseUnit['symbol']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editConversionFactor" class="form-label">Conversion Factor</label>
                            <input type="number" class="form-control" id="editConversionFactor" name="conversion_factor" 
                                step="any" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
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
                    <button type="submit" class="btn btn-primary">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Form -->
<form id="deleteUnitForm" action="<?= APP_URL ?>/products/measurement-units" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit button click handler
    document.querySelectorAll('.btn-edit-unit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editSymbol').value = this.dataset.symbol;
            document.getElementById('editBaseUnit').value = this.dataset.baseUnitId || '';
            document.getElementById('editConversionFactor').value = this.dataset.conversionFactor;
            document.getElementById('editDescription').value = this.dataset.description;
            document.getElementById('editStatus').value = this.dataset.status;
        });
    });

    // Delete button click handler
    document.querySelectorAll('.btn-delete-unit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Measurement Unit?',
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
                    document.getElementById('deleteUnitForm').submit();
                }
            });
        });
    });

    // Unit Converter
    document.getElementById('btnConvert').addEventListener('click', function() {
        const value = parseFloat(document.getElementById('convertValue').value);
        const fromSelect = document.getElementById('convertFrom');
        const toSelect = document.getElementById('convertTo');
        const fromUnitId = fromSelect.value;
        const toUnitId = toSelect.value;

        if (!value || value <= 0) {
            Swal.fire({icon: 'warning', title: 'Invalid Value', text: 'Please enter a valid value'});
            return;
        }

        if (!fromUnitId || !toUnitId) {
            Swal.fire({icon: 'warning', title: 'Select Units', text: 'Please select both units'});
            return;
        }

        // Perform conversion via AJAX
        fetch('<?= APP_URL ?>/products/measurement-units', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=convert&value=' + value + '&from_unit_id=' + fromUnitId + '&to_unit_id=' + toUnitId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fromSymbol = fromSelect.options[fromSelect.selectedIndex].dataset.symbol;
                const toSymbol = toSelect.options[toSelect.selectedIndex].dataset.symbol;
                document.getElementById('resultText').textContent = 
                    value + ' ' + fromSymbol + ' = ' + data.formatted;
                document.getElementById('conversionResult').style.display = 'block';
                
                // Also get full conversion table
                fetch('<?= APP_URL ?>/products/measurement-units/conversion-table', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'value=' + value + '&from_unit_id=' + fromUnitId
                })
                .then(response => response.json())
                .then(tableData => {
                    if (tableData.success) {
                        let tableHtml = '';
                        tableData.conversions.forEach(function(conv) {
                            tableHtml += '<tr><td>' + conv.unit_name + ' (' + conv.symbol + ')</td>';
                            tableHtml += '<td><strong>' + conv.formatted + '</strong></td></tr>';
                        });
                        document.getElementById('conversionTableBody').innerHTML = tableHtml;
                        document.getElementById('conversionTable').style.display = 'block';
                    }
                });
            } else {
                Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Conversion failed'});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred'});
        });
    });
});
</script>
