<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-stock-transfers', $permissions);
$canApprove = in_array('approve-stock-transfers', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Warehouse Processing</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/stock/receives">Stock</a></li>
                <li class="breadcrumb-item active">Warehouse Processing</li>
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
        timer: 3000,
        timerProgressBar: true
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
        timer: 3000,
        timerProgressBar: true
    });
});
</script>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="row">
    <!-- Processing Form -->
    <?php if ($canCreate): ?>
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-settings me-2"></i>Record Processing Step</h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/warehouse/processing/action" method="POST" id="processingForm">
                    <input type="hidden" name="action" value="create">

                    <h6 class="text-muted mb-3"><i class="ti ti-building-warehouse me-1"></i>Warehouse & Product</h6>

                    <!-- Warehouse Location -->
                    <div class="mb-3">
                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select class="form-select" name="location_id" id="warehouseSelect" required>
                            <option value="">Select Warehouse</option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Product Type -->
                    <div class="mb-3">
                        <label class="form-label">Product Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_type_id" id="categoryTypeSelect" required disabled>
                            <option value="">Select Warehouse first</option>
                        </select>
                    </div>

                    <!-- Unit -->
                    <div class="mb-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" name="measurement_unit_id" id="unitSelect" required disabled>
                            <option value="">Select Product Type first</option>
                        </select>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-muted mb-3"><i class="ti ti-users me-1"></i>Select Suppliers & Quantities</h6>

                    <!-- Suppliers Table - dynamically populated -->
                    <div id="suppliersContainer">
                        <div class="alert alert-info small py-2">
                            <i class="ti ti-info-circle me-1"></i>Select warehouse, product type and unit to see
                            available suppliers
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-muted mb-3"><i class="ti ti-tool me-1"></i>Processing Details</h6>

                    <!-- Target Processing Step -->
                    <div class="mb-3">
                        <label class="form-label">Move to Processing Step <span class="text-danger">*</span></label>
                        <select class="form-select" name="processing_step_id" id="processingStepSelect" required
                            disabled>
                            <option value="">Select suppliers first</option>
                        </select>
                        <small class="text-muted" id="stepInfo"></small>
                    </div>

                    <!-- Processing Date -->
                    <div class="mb-3">
                        <label class="form-label">Processing Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="processing_date" id="processingDate"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="2"
                            placeholder="Optional notes about this processing step"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                        <i class="ti ti-plus me-2"></i>Record Processing
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Processing Records Table -->
    <div class="<?= $canCreate ? 'col-lg-7' : 'col-12' ?>">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-list me-2"></i>Processing Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="processingTable">
                        <thead>
                            <tr>
                                <th>Processing #</th>
                                <th>Date</th>
                                <th>Warehouse</th>
                                <th>Product</th>
                                <th>From Step</th>
                                <th>To Step</th>
                                <th>Total Qty</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($processingRecords)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="ti ti-inbox fs-1 d-block mb-2"></i>
                                    No processing records found
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($processingRecords as $record): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($record['processing_number']) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($record['processing_date'])) ?></td>
                                <td><?= htmlspecialchars($record['location_name'] ?? '-') ?></td>
                                <td>
                                    <span
                                        class="text-muted"><?= htmlspecialchars($record['category_name'] ?? '-') ?></span><br>
                                    <?= htmlspecialchars($record['category_type_name'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['from_step_name'])): ?>
                                    <span
                                        class="badge bg-secondary"><?= htmlspecialchars($record['from_step_name']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-info"><?= htmlspecialchars($record['to_step_name'] ?? $record['processing_step'] ?? '-') ?></span>
                                </td>
                                <td><?= number_format($record['input_quantity'], 2) ?> <?= $record['unit_symbol'] ?>
                                </td>
                                <td>
                                    <?php if ($record['status'] === 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($record['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Completed</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($record['status'] === 'pending' && $canApprove): ?>
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="completeProcessing(<?= $record['id'] ?>, '<?= $record['processing_number'] ?>')"
                                        title="Complete Processing">
                                        <i class="ti ti-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="cancelProcessing(<?= $record['id'] ?>, '<?= $record['processing_number'] ?>')"
                                        title="Cancel">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Processing Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-check me-2"></i>Complete Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/warehouse/processing/action" method="POST" id="completeForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="complete">
                    <input type="hidden" name="processing_id" id="completeProcessingId">

                    <!-- Processing Info Header -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong id="completeProcessingNumber"></strong>
                                <span class="ms-2" id="completeProcessingStep"></span>
                            </div>
                            <span id="completeProcessingDate"></span>
                        </div>
                    </div>

                    <!-- Items Taken Table with Output Quantities -->
                    <h6 class="text-muted mb-2"><i class="ti ti-package me-1"></i>Items & Output Quantities</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered mb-0" id="itemsTakenTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Supplier</th>
                                    <th>From Step</th>
                                    <th>Input Qty</th>
                                    <th>Output Qty <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTakenBody">
                                <!-- Dynamically populated -->
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="2" class="text-end">Total:</th>
                                    <th id="totalInputQty">0</th>
                                    <th id="totalOutputQty">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <hr>

                    <!-- Output Product Selection -->
                    <h6 class="text-muted mb-2"><i class="ti ti-arrow-right me-1"></i>Output Product</h6>
                    <p class="small text-muted">Select the product type for the output stock.</p>

                    <div class="row">
                        <!-- Output Category -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="output_category_id" id="outputCategorySelect" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>

                        <!-- Output Product Type -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Product Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="output_category_type_id" id="outputCategoryTypeSelect"
                                required disabled>
                                <option value="">Select Category first</option>
                            </select>
                        </div>

                        <!-- Output Unit -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" name="output_measurement_unit_id" id="outputUnitSelect" required
                                disabled>
                                <option value="">Select Product Type first</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="completeSubmitBtn">
                        <i class="ti ti-check me-1"></i>Complete Processing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Processing Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-x me-2"></i>Cancel Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/warehouse/processing/action" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="processing_id" id="cancelProcessingId">

                    <p>Are you sure you want to cancel processing <strong id="cancelProcessingNumber"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone. No stock will be affected.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Processing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Complete Processing - load details and show modal
function completeProcessing(id, number) {
    document.getElementById('completeProcessingId').value = id;
    document.getElementById('completeProcessingNumber').textContent = number;

    // Reset form
    document.getElementById('outputCategorySelect').innerHTML = '<option value="">Loading...</option>';
    document.getElementById('outputCategoryTypeSelect').innerHTML = '<option value="">Select Category first</option>';
    document.getElementById('outputCategoryTypeSelect').disabled = true;
    document.getElementById('outputUnitSelect').innerHTML = '<option value="">Select Product Type first</option>';
    document.getElementById('outputUnitSelect').disabled = true;
    document.getElementById('itemsTakenBody').innerHTML =
        '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

    // Load processing details
    fetch(`<?= APP_URL ?>/warehouse/processing/action`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=get_processing_details&processing_id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const processing = data.processing;
                const items = data.items;

                // Update header
                document.getElementById('completeProcessingStep').textContent =
                    `${processing.from_step_name || 'Initial'} → ${processing.to_step_name}`;
                document.getElementById('completeProcessingDate').textContent =
                    new Date(processing.processing_date).toLocaleDateString();

                // Populate items table with output quantity inputs
                let itemsHtml = '';
                let totalQty = 0;
                const unitSymbol = data.unit ? data.unit.symbol : '';
                items.forEach((item, index) => {
                    const qty = parseFloat(item.quantity);
                    totalQty += qty;
                    itemsHtml += `<tr>
                    <td>${item.supplier_name}
                        <input type="hidden" name="output_items[${index}][supplier_id]" value="${item.supplier_id}">
                    </td>
                    <td>${item.step_name || 'Initial'}</td>
                    <td>${qty.toFixed(2)} ${unitSymbol}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm output-qty-input" 
                            name="output_items[${index}][output_quantity]" 
                            step="0.01" min="0" value="${qty.toFixed(2)}" 
                            data-input="${qty}" required>
                    </td>
                </tr>`;
                });
                document.getElementById('itemsTakenBody').innerHTML = itemsHtml ||
                    '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>';
                document.getElementById('totalInputQty').textContent = totalQty.toFixed(2) + ' ' + unitSymbol;
                document.getElementById('totalOutputQty').textContent = totalQty.toFixed(2) + ' ' + unitSymbol;

                // Add event listeners to recalculate output total
                document.querySelectorAll('.output-qty-input').forEach(input => {
                    input.addEventListener('input', updateOutputTotal);
                });
            }
        });

    // Load categories for output selection
    fetch(`<?= APP_URL ?>/warehouse/processing/action`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=get_categories'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                let html = '<option value="">Select Category</option>';
                data.data.forEach(c => {
                    html += `<option value="${c.id}">${c.name}</option>`;
                });
                document.getElementById('outputCategorySelect').innerHTML = html;
            }
        });

    new bootstrap.Modal(document.getElementById('completeModal')).show();
}

// Update output total when quantities change
function updateOutputTotal() {
    let total = 0;
    document.querySelectorAll('.output-qty-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    const unitSelect = document.getElementById('outputUnitSelect');
    const unitText = unitSelect.options[unitSelect.selectedIndex]?.text || '';
    document.getElementById('totalOutputQty').textContent = total.toFixed(2) + ' ' + unitText.match(/\(([^)]+)\)/)?. [
        1
    ] || '';
}

// Output Category change - load product types
document.getElementById('outputCategorySelect').addEventListener('change', function() {
    const categoryId = this.value;
    const typeSelect = document.getElementById('outputCategoryTypeSelect');
    const unitSelect = document.getElementById('outputUnitSelect');

    typeSelect.innerHTML = '<option value="">Loading...</option>';
    typeSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">Select Product Type first</option>';
    unitSelect.disabled = true;

    if (!categoryId) {
        typeSelect.innerHTML = '<option value="">Select Category first</option>';
        return;
    }

    fetch(`<?= APP_URL ?>/warehouse/processing/action`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=get_category_types&category_id=${categoryId}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                let html = '<option value="">Select Product Type</option>';
                data.data.forEach(t => {
                    html += `<option value="${t.id}">${t.name}</option>`;
                });
                typeSelect.innerHTML = html;
                typeSelect.disabled = false;
            }
        });
});

// Output Product Type change - load units
document.getElementById('outputCategoryTypeSelect').addEventListener('change', function() {
    const typeId = this.value;
    const unitSelect = document.getElementById('outputUnitSelect');

    unitSelect.innerHTML = '<option value="">Loading...</option>';
    unitSelect.disabled = true;

    if (!typeId) {
        unitSelect.innerHTML = '<option value="">Select Product Type first</option>';
        return;
    }

    fetch(`<?= APP_URL ?>/warehouse/processing/action`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=get_output_units&category_type_id=${typeId}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                let html = '<option value="">Select Unit</option>';
                data.data.forEach(u => {
                    html +=
                        `<option value="${u.measurement_unit_id}">${u.name} (${u.symbol})</option>`;
                });
                unitSelect.innerHTML = html;
                unitSelect.disabled = false;
            }
        });
});

function cancelProcessing(id, number) {
    document.getElementById('cancelProcessingId').value = id;
    document.getElementById('cancelProcessingNumber').textContent = number;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>