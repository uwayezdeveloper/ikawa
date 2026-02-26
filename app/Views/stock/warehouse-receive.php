<?php

$isSuperAdmin = ((int)($user['role_id'] ?? 0) === 1);
$menuAccess = $user['menu_identifiers'] ?? [];
$canCreate = $isSuperAdmin || in_array('warehouse-receive', $menuAccess);
$canApprove = $isSuperAdmin || in_array('warehouse-receive', $menuAccess);
$canDelete = $isSuperAdmin || in_array('warehouse-receive', $menuAccess);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Warehouse Receive</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/warehouse/processing">Warehouse</a></li>
                <li class="breadcrumb-item active">Receive Stock</li>
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
    <!-- Receive Stock Form -->
    <?php if ($canCreate): ?>
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-package-import me-2"></i>Receive Stock at Warehouse</h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/warehouse/receive/action" method="POST" id="receiveForm">
                    <input type="hidden" name="action" value="receive_stock">

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

                    <!-- Product Category -->
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="product_category_id" id="categorySelect" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Product Type -->
                    <div class="mb-3">
                        <label class="form-label">Product Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_type_id" id="categoryTypeSelect" required disabled>
                            <option value="">Select Category first</option>
                        </select>
                    </div>

                    <!-- Unit -->
                    <div class="mb-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_type_unit_id" id="unitSelect" required disabled>
                            <option value="">Select Product Type first</option>
                        </select>
                    </div>

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select" name="supplier_id" id="supplierSelect" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="supplierAdvanceInfo" class="mt-1" style="display: none;"></div>
                    </div>

                    <!-- Payment Account -->
                    <div class="mb-3">
                        <label class="form-label">Payment Account</label>
                        <select class="form-select" name="account_id" id="accountSelect" disabled>
                            <option value="">Select Warehouse first</option>
                        </select>
                        <small class="text-muted">Used if supplier has no advance. Leave empty to create
                            payable.</small>
                    </div>

                    <!-- Quantity and Price -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" id="quantityInput" required
                                min="0.01" step="0.01" placeholder="Enter quantity">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price/kg <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="unit_price" id="unitPriceInput" required
                                min="0" step="0.01" placeholder="Price per kg">
                        </div>
                    </div>

                    <!-- Total Price Display -->
                    <div class="mb-3">
                        <label class="form-label">Total Price</label>
                        <input type="text" class="form-control bg-light" id="totalPriceDisplay" readonly
                            value="RWF 0.00">
                    </div>

                    <!-- Kg Conversion Preview -->
                    <div class="mb-3 p-2 bg-light rounded" id="kgConversionPreview" style="display: none;">
                        <small class="text-muted d-block mb-1">Base Unit Conversion:</small>
                        <div class="d-flex justify-content-between">
                            <span>Quantity in kg: <strong id="qtyInKgPreview">0</strong></span>
                            <span>Price/kg: <strong id="pricePerKgPreview">0</strong></span>
                        </div>
                    </div>

                    <!-- Processing Step -->
                    <div class="mb-3">
                        <label class="form-label">Processing Step</label>
                        <select class="form-select" name="processing_step_id" id="processingStepSelect">
                            <option value="">Initial (No Processing Step)</option>
                            <?php foreach ($processingSteps as $step): ?>
                            <option value="<?= $step['id'] ?>" <?= $step['step_order'] == 1 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($step['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Receive Date -->
                    <div class="mb-3">
                        <label class="form-label">Receive Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="receive_date" id="receiveDateInput"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"
                            placeholder="Optional notes about this receive"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-check me-1"></i>Submit Stock Receive
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stock Receives Table -->
    <div class="<?= $canCreate ? 'col-lg-8' : 'col-12' ?>">
        <div class="card" data-table data-table-rows-per-page="10">
            <div class="card-header border-light justify-content-between">
                <h5 class="card-title mb-0"><i class="ti ti-list me-2"></i>Warehouse Stock Receives</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="app-search">
                        <input data-table-search type="search" class="form-control" placeholder="Search..." />
                        <i class="ti ti-search app-search-icon text-muted"></i>
                    </div>
                    <div class="app-search">
                        <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                            <option value="All">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <i class="ti ti-filter app-search-icon text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-centered table-hover w-100 mb-0">
                    <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                        <tr class="text-uppercase fs-xxs">
                            <th class="ps-3">#</th>
                            <th data-table-sort>Date</th>
                            <th data-table-sort>Warehouse</th>
                            <th data-table-sort>Product</th>
                            <th data-table-sort>Supplier</th>
                            <th data-table-sort>Qty</th>
                            <th data-table-sort>Price/kg</th>
                            <th data-table-sort>Total</th>
                            <th data-table-sort>Payment</th>
                            <th data-table-sort data-column="status">Status</th>
                            <?php if ($canApprove || $canDelete): ?>
                            <th class="text-center">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($receives)): ?>
                        <tr>
                            <td colspan="<?= ($canApprove || $canDelete) ? 11 : 10 ?>" class="text-center py-4">
                                <i class="ti ti-package fs-1 text-muted"></i>
                                <p class="text-muted mb-0">No warehouse receives found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($receives as $index => $receive): ?>
                        <tr>
                            <td class="ps-3"><?= $index + 1 ?></td>
                            <td><?= date('M d, Y', strtotime($receive['receive_date'])) ?></td>
                            <td>
                                <span class="fw-medium"><?= htmlspecialchars($receive['location_name']) ?></span>
                            </td>
                            <td>
                                <span class="fw-medium"><?= htmlspecialchars($receive['type_name']) ?></span>
                                <br><small class="text-muted"><?= htmlspecialchars($receive['category_name']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($receive['supplier_name']) ?></td>
                            <td>
                                <span class="fw-semibold"><?= number_format($receive['quantity'], 2) ?></span>
                                <small class="text-muted"><?= htmlspecialchars($receive['unit_symbol']) ?></small>
                            </td>
                            <td>
                                <span
                                    class="fw-semibold"><?= number_format($receive['price_per_kg'] ?? $receive['unit_price'], 0) ?></span>
                            </td>
                            <td>
                                <span class="fw-semibold">RWF <?= number_format($receive['total_price'], 0) ?></span>
                            </td>
                            <td>
                                <?php if ($receive['status'] === 'approved'): ?>
                                <?php 
                                    $advanceAmt = floatval($receive['advance_amount'] ?? 0);
                                    $accountAmt = floatval($receive['account_amount'] ?? 0);
                                    $payableAmt = floatval($receive['payable_amount'] ?? 0);
                                    ?>
                                <?php if ($advanceAmt > 0): ?>
                                <div><span class="badge bg-warning-subtle text-warning">Adv:
                                        <?= number_format($advanceAmt, 0) ?></span></div>
                                <?php endif; ?>
                                <?php if ($accountAmt > 0): ?>
                                <div><span class="badge bg-primary-subtle text-primary">Acc:
                                        <?= number_format($accountAmt, 0) ?></span></div>
                                <?php endif; ?>
                                <?php if ($payableAmt > 0): ?>
                                <div><span class="badge bg-danger-subtle text-danger">Due:
                                        <?= number_format($payableAmt, 0) ?></span></div>
                                <?php endif; ?>
                                <?php if ($advanceAmt == 0 && $accountAmt == 0 && $payableAmt == 0): ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td data-column="status">
                                <?php if ($receive['status'] === 'approved'): ?>
                                <span class="badge bg-success-subtle text-success">Approved</span>
                                <?php elseif ($receive['status'] === 'pending'): ?>
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($canApprove || $canDelete): ?>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if ($receive['status'] === 'pending'): ?>
                                    <?php if ($canApprove): ?>
                                    <form action="<?= APP_URL ?>/warehouse/receive/action" method="POST"
                                        class="d-inline approve-form">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="id" value="<?= $receive['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-icon btn-sm" title="Approve">
                                            <i class="ti ti-check"></i>
                                        </button>
                                    </form>
                                    <form action="<?= APP_URL ?>/warehouse/receive/action" method="POST"
                                        class="d-inline cancel-form">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id" value="<?= $receive['id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-icon btn-sm" title="Cancel">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                    <form action="<?= APP_URL ?>/warehouse/receive/action" method="POST"
                                        class="d-inline delete-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $receive['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-icon btn-sm" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
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
            <div class="card-footer py-0">
                <nav aria-label="Page navigation">
                    <ul data-table-paginate class="pagination justify-content-end mb-0"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('warehouseSelect');
    const categorySelect = document.getElementById('categorySelect');
    const categoryTypeSelect = document.getElementById('categoryTypeSelect');
    const unitSelect = document.getElementById('unitSelect');
    const supplierSelect = document.getElementById('supplierSelect');
    const accountSelect = document.getElementById('accountSelect');
    const quantityInput = document.getElementById('quantityInput');
    const unitPriceInput = document.getElementById('unitPriceInput');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const supplierAdvanceInfo = document.getElementById('supplierAdvanceInfo');
    const kgConversionPreview = document.getElementById('kgConversionPreview');
    const qtyInKgPreview = document.getElementById('qtyInKgPreview');
    const pricePerKgPreview = document.getElementById('pricePerKgPreview');

    let unitConversionFactor = 1000; // default kg = 1000 grams

    // Warehouse change - load accounts
    warehouseSelect.addEventListener('change', function() {
        const locationId = this.value;

        accountSelect.innerHTML = '<option value="">Loading...</option>';
        accountSelect.disabled = true;

        if (!locationId) {
            accountSelect.innerHTML = '<option value="">Select Warehouse first</option>';
            return;
        }

        fetch(`<?= APP_URL ?>/warehouse/receive/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=get_accounts&location_id=${locationId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    let html = '<option value="">No Account (Create Payable)</option>';
                    data.data.forEach(a => {
                        html +=
                            `<option value="${a.id}">${a.account_name} (Bal: ${Number(a.balance).toLocaleString()})</option>`;
                    });
                    accountSelect.innerHTML = html;
                    accountSelect.disabled = false;
                }
            });
    });

    // Category change - load product types
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;

        categoryTypeSelect.innerHTML = '<option value="">Loading...</option>';
        categoryTypeSelect.disabled = true;
        unitSelect.innerHTML = '<option value="">Select Product Type first</option>';
        unitSelect.disabled = true;

        if (!categoryId) {
            categoryTypeSelect.innerHTML = '<option value="">Select Category first</option>';
            return;
        }

        fetch(`<?= APP_URL ?>/warehouse/receive/action`, {
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
                    categoryTypeSelect.innerHTML = html;
                    categoryTypeSelect.disabled = false;
                }
            });
    });

    // Product Type change - load units
    categoryTypeSelect.addEventListener('change', function() {
        const typeId = this.value;

        unitSelect.innerHTML = '<option value="">Loading...</option>';
        unitSelect.disabled = true;

        if (!typeId) {
            unitSelect.innerHTML = '<option value="">Select Product Type first</option>';
            return;
        }

        fetch(`<?= APP_URL ?>/warehouse/receive/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=get_type_units&category_type_id=${typeId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    let html = '<option value="">Select Unit</option>';
                    data.data.forEach(u => {
                        html +=
                            `<option value="${u.id}" data-factor="${u.conversion_factor || 1000}">${u.name} (${u.symbol})</option>`;
                    });
                    unitSelect.innerHTML = html;
                    unitSelect.disabled = false;
                }
            });
    });

    // Unit change - update conversion factor
    unitSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        unitConversionFactor = parseFloat(selected.dataset.factor) || 1000;
        updateTotalPrice();
    });

    // Supplier change - check advance
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        supplierAdvanceInfo.style.display = 'none';

        if (!supplierId) return;

        fetch(`<?= APP_URL ?>/warehouse/receive/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=check_supplier_advance&supplier_id=${supplierId}&amount=0`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    if (data.data.has_advance) {
                        supplierAdvanceInfo.innerHTML = `<span class="badge bg-success-subtle text-success">
                        <i class="ti ti-cash me-1"></i>Advance Available: RWF ${Number(data.data.total_advance).toLocaleString()}
                    </span>`;
                        supplierAdvanceInfo.style.display = 'block';
                    } else {
                        supplierAdvanceInfo.innerHTML = `<span class="badge bg-warning-subtle text-warning">
                        <i class="ti ti-alert-circle me-1"></i>No Advance Available
                    </span>`;
                        supplierAdvanceInfo.style.display = 'block';
                    }
                }
            });
    });

    // Calculate total price
    function updateTotalPrice() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;

        // Convert quantity to kg: quantity * (factor / 1000)
        const qtyInKg = quantity * (unitConversionFactor / 1000);
        const total = unitPrice * qtyInKg;

        totalPriceDisplay.value =
            `RWF ${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

        // Show conversion preview if not already kg
        if (unitConversionFactor !== 1000) {
            kgConversionPreview.style.display = 'block';
            qtyInKgPreview.textContent = qtyInKg.toFixed(4);
            pricePerKgPreview.textContent = unitPrice.toLocaleString();
        } else {
            kgConversionPreview.style.display = 'none';
        }
    }

    quantityInput.addEventListener('input', updateTotalPrice);
    unitPriceInput.addEventListener('input', updateTotalPrice);

    // Confirm forms
    document.querySelectorAll('.approve-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Approve Stock Receive?',
                text: 'This will process payment and add stock to warehouse.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Approve'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    document.querySelectorAll('.cancel-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Cancel Stock Receive?',
                text: 'This receive will be marked as cancelled.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Yes, Cancel'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete Stock Receive?',
                text: 'This action cannot be undone.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Delete'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>