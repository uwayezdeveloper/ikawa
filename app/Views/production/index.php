<?php
$permissions = $user['permissions'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Production</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Production</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <select id="stationSelect" class="form-select" style="min-width: 250px;">
            <option value="">Select Station</option>
            <?php foreach ($stations as $st): ?>
            <option value="<?= $st['id'] ?>" <?= $st['id'] == $selectedStationId ? 'selected' : '' ?>>
                <?= htmlspecialchars($st['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
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

<?php if ($selectedStationId): ?>

<div class="row">
    <!-- Send to Production Form -->
    <?php if ($canCreate && !empty($stationStock)): ?>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="ti ti-package-export me-2"></i>Send to Production
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/production/action" method="POST" id="sendToProductionForm">
                    <input type="hidden" name="action" value="send_to_production">
                    <input type="hidden" name="station_id" value="<?= $selectedStationId ?>">

                    <div class="mb-3">
                        <label class="form-label">Select Supplier <span class="text-danger">*</span></label>
                        <select class="form-select" name="supplier_id" id="supplierSelect" required>
                            <option value="">Choose supplier</option>
                            <?php 
                            // Get unique suppliers with stock at this location
                            $suppliersWithStock = [];
                            foreach ($stationStock as $stock) {
                                if ($stock['total_quantity'] > 0 && !empty($stock['supplier_id'])) {
                                    $suppliersWithStock[$stock['supplier_id']] = $stock['supplier_name'] ?? 'Unknown Supplier';
                                }
                            }
                            ?>
                            <?php foreach ($suppliersWithStock as $supplierId => $supplierName): ?>
                            <option value="<?= $supplierId ?>"><?= htmlspecialchars($supplierName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Product Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_type_id" id="productTypeSelect" required disabled>
                            <option value="">Select supplier first</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" name="measurement_unit_id" id="measurementUnitSelect" required
                            disabled>
                            <option value="">Select product type first</option>
                        </select>
                        <small class="text-muted" id="totalStockInfo"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="quantity" id="productionQuantity"
                                step="0.01" min="0.01" required>
                            <span class="input-group-text" id="unitLabel">units</span>
                        </div>
                        <small class="text-muted" id="availableInfo">You can specify quantity in any unit</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-arrow-right me-1"></i>Send to Production
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pending Production -->
    <div class="col-lg-<?= ($canCreate && !empty($stationStock)) ? '8' : '12' ?>">
        <?php if (!empty($pendingProduction)): ?>
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning-subtle">
                <h5 class="card-title mb-0 text-warning">
                    <i class="ti ti-loader me-2"></i>In Production
                    <span class="badge bg-warning ms-2"><?= count($pendingProduction) ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Production #</th>
                                <th>Date</th>
                                <th>Input Material</th>
                                <th class="text-end">Input Qty</th>
                                <th class="text-end">Value</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingProduction as $p): ?>
                            <tr class="table-warning-subtle">
                                <td><code class="text-dark"><?= htmlspecialchars($p['production_number']) ?></code></td>
                                <td><?= date('M d, Y', strtotime($p['production_date'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['input_category_name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['input_type_name']) ?></small>
                                </td>
                                <td class="text-end">
                                    <strong><?= number_format($p['input_quantity'], 2) ?></strong>
                                    <small class="text-muted"><?= $p['input_unit_symbol'] ?></small>
                                </td>
                                <td class="text-end">
                                    <?= number_format($p['input_quantity'] * $p['input_unit_price'], 2) ?></td>
                                <td><?= htmlspecialchars($p['created_by_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($canComplete): ?>
                                    <button type="button" class="btn btn-sm btn-success" title="Complete Production"
                                        onclick="openCompleteModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                                        <i class="ti ti-check"></i> Complete
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($canCreate): ?>
                                    <form action="<?= APP_URL ?>/production/action" method="POST"
                                        class="d-inline confirm-form" data-title="Cancel Production"
                                        data-text="Cancel this production? Stock will be returned to station."
                                        data-confirm-text="Yes, Cancel" data-icon="warning">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="station_id" value="<?= $selectedStationId ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info mb-4">
            <i class="ti ti-info-circle me-2"></i>
            No items currently in production.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- All Production History -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-history me-2"></i>Production History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="productionTable">
                <thead class="bg-light">
                    <tr>
                        <th>Production #</th>
                        <th>Date</th>
                        <th>Input</th>
                        <th class="text-end">Input Qty</th>
                        <th>Output</th>
                        <th class="text-end">Output Qty</th>
                        <th>Status</th>
                        <th>Completed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allProduction)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No production records found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($allProduction as $p): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($p['production_number']) ?></code></td>
                        <td><?= date('M d, Y', strtotime($p['production_date'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($p['input_category_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($p['input_type_name']) ?></small>
                        </td>
                        <td class="text-end">
                            <?= number_format($p['input_quantity'], 2) ?>
                            <small class="text-muted"><?= $p['input_unit_symbol'] ?></small>
                        </td>
                        <td>
                            <?php if ($p['output_category_name']): ?>
                            <strong><?= htmlspecialchars($p['output_category_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($p['output_type_name']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($p['output_quantity']): ?>
                            <?= number_format($p['output_quantity'], 2) ?>
                            <small class="text-muted"><?= $p['output_unit_symbol'] ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['status'] === 'pending'): ?>
                            <span class="badge bg-warning-subtle text-warning">In Production</span>
                            <?php elseif ($p['status'] === 'completed'): ?>
                            <span class="badge bg-success-subtle text-success">Completed</span>
                            <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['completed_by_name']): ?>
                            <?= htmlspecialchars($p['completed_by_name']) ?>
                            <br><small
                                class="text-muted"><?= $p['completed_at'] ? date('M d, H:i', strtotime($p['completed_at'])) : '' ?></small>
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

<?php else: ?>
<div class="alert alert-info">
    <i class="ti ti-info-circle me-2"></i>
    Please select a station to manage production.
</div>
<?php endif; ?>

<!-- Complete Production Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= APP_URL ?>/production/action" method="POST" id="completeForm">
                <input type="hidden" name="action" value="complete">
                <input type="hidden" name="id" id="complete_production_id">
                <input type="hidden" name="station_id" value="<?= $selectedStationId ?>">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="ti ti-check me-2"></i>Complete Production
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Production Info -->
                    <div class="alert alert-info mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Production #:</strong> <span id="modal_production_number"></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Date:</strong> <span id="modal_production_date"></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Input Value:</strong> <span id="modal_input_value"></span> RWF
                            </div>
                        </div>
                    </div>

                    <!-- Input Material Info -->
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">Input Material:</small><br>
                                    <strong id="modal_input_material"></strong>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">Quantity:</small><br>
                                    <strong id="modal_input_quantity"></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-3">
                        <i class="ti ti-info-circle me-2"></i>
                        Select the finished product category and type, then enter the output quantity.
                    </div>

                    <div class="row g-3">
                        <!-- Product Category -->
                        <div class="col-md-4">
                            <label class="form-label">Product Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="output_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Category Type -->
                        <div class="col-md-4">
                            <label class="form-label">Category Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="output_type_id" name="type_id" required disabled>
                                <option value="">Select Type</option>
                            </select>
                        </div>

                        <!-- Unit -->
                        <div class="col-md-4">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="output_category_type_unit_id"
                                name="output_category_type_unit_id" required disabled>
                                <option value="">Select Unit</option>
                            </select>
                        </div>

                        <!-- Output Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">Output Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="output_quantity" name="output_quantity"
                                step="0.01" min="0.01" required>
                        </div>

                        <!-- Output Unit Price -->
                        <div class="col-md-6">
                            <label class="form-label">Unit Price (RWF) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="output_unit_price" name="output_unit_price"
                                step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>Complete Production
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.APP_URL = '<?= APP_URL ?>';
let currentProduction = null;

document.getElementById('stationSelect')?.addEventListener('change', function() {
    if (this.value) {
        window.location.href = window.APP_URL + '/production?station_id=' + this.value;
    }
});

// Load product types when supplier is selected
const supplierSelect = document.getElementById('supplierSelect');
if (supplierSelect) {
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        const productTypeSelect = document.getElementById('productTypeSelect');
        const unitSelect = document.getElementById('measurementUnitSelect');
        const stationId = document.querySelector('input[name="station_id"]').value;

        // Reset dependent fields
        unitSelect.innerHTML = '<option value="">Select product type first</option>';
        unitSelect.disabled = true;
        document.getElementById('unitLabel').textContent = 'units';
        document.getElementById('totalStockInfo').textContent = '';

        if (!supplierId) {
            productTypeSelect.innerHTML = '<option value="">Select supplier first</option>';
            productTypeSelect.disabled = true;
            return;
        }

        // Show loading state
        productTypeSelect.innerHTML = '<option value="">Loading...</option>';
        productTypeSelect.disabled = true;

        // Fetch product types for this supplier at this location
        const formData = new FormData();
        formData.append('action', 'get_supplier_product_types');
        formData.append('location_id', stationId);
        formData.append('supplier_id', supplierId);

        fetch(window.APP_URL + '/production/action', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    productTypeSelect.innerHTML = '<option value="">Choose product type</option>';
                    data.data.forEach(function(item) {
                        const option = document.createElement('option');
                        option.value = item.category_type_id;
                        option.textContent = item.category_name + ' - ' + item.type_name + ' (' +
                            item.stock_display + ')';
                        productTypeSelect.appendChild(option);
                    });
                    productTypeSelect.disabled = false;
                } else {
                    productTypeSelect.innerHTML = '<option value="">No stock available</option>';
                    productTypeSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productTypeSelect.innerHTML = '<option value="">Error loading</option>';
                productTypeSelect.disabled = true;
            });
    });
}

// Load available units when product type is selected
const productTypeSelect = document.getElementById('productTypeSelect');

if (productTypeSelect) {
    productTypeSelect.addEventListener('change', function() {
        const categoryTypeId = this.value;
        const unitSelect = document.getElementById('measurementUnitSelect');
        const unitLabel = document.getElementById('unitLabel');
        const totalStockInfo = document.getElementById('totalStockInfo');
        const stationId = document.querySelector('input[name="station_id"]').value;
        const supplierId = document.getElementById('supplierSelect')?.value;

        if (!categoryTypeId) {
            unitSelect.innerHTML = '<option value="">Select product type first</option>';
            unitSelect.disabled = true;
            unitLabel.textContent = 'units';
            totalStockInfo.textContent = '';
            return;
        }

        // Show loading state
        unitSelect.innerHTML = '<option value="">Loading...</option>';
        unitSelect.disabled = true;

        // Fetch available units for this category type and supplier
        const formData = new FormData();
        formData.append('action', 'get_available_units');
        formData.append('location_id', stationId);
        formData.append('category_type_id', categoryTypeId);
        if (supplierId) {
            formData.append('supplier_id', supplierId);
        }

        fetch(window.APP_URL + '/production/action', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    unitSelect.innerHTML = '<option value="">Select unit</option>';
                    data.data.units.forEach(function(unit) {
                        const option = document.createElement('option');
                        option.value = unit.measurement_unit_id;
                        option.textContent = unit.name + ' (' + unit.symbol + ')';
                        option.dataset.symbol = unit.symbol;
                        // Pre-select if has stock
                        if (unit.has_stock) {
                            option.textContent += ' - Has stock';
                        }
                        unitSelect.appendChild(option);
                    });
                    unitSelect.disabled = false;

                    // Show total stock info
                    totalStockInfo.textContent = data.data.total_stock ? 'Available: ' + data.data
                        .total_stock :
                        '';
                } else {
                    unitSelect.innerHTML = '<option value="">No units available</option>';
                    unitSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching units:', error);
                unitSelect.innerHTML = '<option value="">Error loading units</option>';
                unitSelect.disabled = true;
            });
    });
}

// Update unit label when measurement unit is selected
document.getElementById('measurementUnitSelect')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const symbol = selected.dataset.symbol || 'units';
    document.getElementById('unitLabel').textContent = symbol;
});

// Open complete modal with production data
function openCompleteModal(production) {
    currentProduction = production;

    // Set hidden fields and display info
    document.getElementById('complete_production_id').value = production.id;
    document.getElementById('modal_production_number').textContent = production.production_number;
    document.getElementById('modal_production_date').textContent = production.production_date;
    document.getElementById('modal_input_value').textContent = (production.input_quantity * production.input_unit_price)
        .toLocaleString();
    document.getElementById('modal_input_material').textContent =
        (production.input_category_name || '') + ' - ' + (production.input_type_name || '');
    document.getElementById('modal_input_quantity').textContent =
        parseFloat(production.input_quantity).toFixed(2) + ' ' + (production.input_unit_symbol || '');

    // Pre-fill quantity with input quantity
    document.getElementById('output_quantity').value = production.input_quantity;
    document.getElementById('output_unit_price').value = production.input_unit_price || '';

    // Reset dropdowns
    document.getElementById('output_category_id').value = '';
    document.getElementById('output_type_id').innerHTML = '<option value="">Select Type</option>';
    document.getElementById('output_type_id').disabled = true;
    document.getElementById('output_category_type_unit_id').innerHTML = '<option value="">Select Unit</option>';
    document.getElementById('output_category_type_unit_id').disabled = true;

    // Show modal
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}

// Load category types when category changes
document.getElementById('output_category_id')?.addEventListener('change', function() {
    loadCategoryTypes(this.value);
});

// Load types for a category
function loadCategoryTypes(categoryId) {
    const typeSelect = document.getElementById('output_type_id');
    const unitSelect = document.getElementById('output_category_type_unit_id');

    if (!categoryId) {
        typeSelect.innerHTML = '<option value="">Select Type</option>';
        typeSelect.disabled = true;
        unitSelect.innerHTML = '<option value="">Select Unit</option>';
        unitSelect.disabled = true;
        return;
    }

    const formData = new FormData();
    formData.append('action', 'get_types');
    formData.append('category_id', categoryId);

    fetch(window.APP_URL + '/production/action', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                typeSelect.innerHTML = '<option value="">Select Type</option>';
                data.data.forEach(type => {
                    typeSelect.innerHTML += `<option value="${type.id}">${type.name}</option>`;
                });
                typeSelect.disabled = false;
            }
        });
}

// Load units when type changes
document.getElementById('output_type_id')?.addEventListener('change', function() {
    loadTypeUnits(this.value);
});

// Load units for a type
function loadTypeUnits(typeId) {
    const unitSelect = document.getElementById('output_category_type_unit_id');

    if (!typeId) {
        unitSelect.innerHTML = '<option value="">Select Unit</option>';
        unitSelect.disabled = true;
        return;
    }

    const formData = new FormData();
    formData.append('action', 'get_units');
    formData.append('type_id', typeId);

    fetch(window.APP_URL + '/production/action', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                unitSelect.innerHTML = '<option value="">Select Unit</option>';
                data.data.forEach(unit => {
                    unitSelect.innerHTML += `<option value="${unit.id}">${unit.name}</option>`;
                });
                unitSelect.disabled = false;
            }
        });
}

// Handle confirmation forms with SweetAlert
document.querySelectorAll('.confirm-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formElement = this;

        Swal.fire({
            title: formElement.dataset.title || 'Confirm',
            text: formElement.dataset.text || 'Are you sure?',
            icon: formElement.dataset.icon || 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: formElement.dataset.confirmText || 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                formElement.submit();
            }
        });
    });
});
</script>