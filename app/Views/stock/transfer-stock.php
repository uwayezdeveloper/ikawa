<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-stock-transfers', $permissions);
$canApprove = in_array('approve-stock-transfers', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Transfer to Warehouse</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/stock/receives">Stock</a></li>
                <li class="breadcrumb-item active">Transfer to Warehouse</li>
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
    <!-- Transfer Form -->
    <?php if ($canCreate): ?>
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-transfer me-2"></i>Create Transfer</h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/stock/transfers/action" method="POST" id="transferStockForm">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="product_category_id" id="productCategoryId">

                    <h6 class="text-muted mb-3"><i class="ti ti-arrow-up me-1"></i>Source Location</h6>

                    <!-- From Location Type -->
                    <div class="mb-3">
                        <label class="form-label">Location Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="from_location_type_id" id="fromLocationTypeSelect" required>
                            <option value="">Select Location Type</option>
                            <?php foreach ($locationTypes as $lt): ?>
                            <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- From Location -->
                    <div class="mb-3">
                        <label class="form-label">From Location <span class="text-danger">*</span></label>
                        <select class="form-select" name="from_location_id" id="fromLocationSelect" required disabled>
                            <option value="">Select Location Type first</option>
                        </select>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-muted mb-3"><i class="ti ti-arrow-down me-1"></i>Destination Warehouse</h6>

                    <!-- To Warehouse -->
                    <div class="mb-3">
                        <label class="form-label">To Warehouse <span class="text-danger">*</span></label>
                        <select class="form-select" name="to_location_id" id="toLocationSelect" required>
                            <option value="">Select Warehouse</option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-muted mb-3"><i class="ti ti-package me-1"></i>Product Details</h6>

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select" name="supplier_id" id="supplierSelect" required disabled>
                            <option value="">Select Location first</option>
                        </select>
                    </div>

                    <!-- Category Type (Product Type) -->
                    <div class="mb-3">
                        <label class="form-label">Product Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_type_id" id="categoryTypeSelect" required disabled>
                            <option value="">Select Supplier first</option>
                        </select>
                    </div>

                    <!-- Type Unit -->
                    <div class="mb-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_type_unit_id" id="typeUnitSelect" required disabled>
                            <option value="">Select Product Type first</option>
                        </select>
                        <input type="hidden" name="measurement_unit_id" id="measurementUnitId">
                        <small class="text-muted" id="totalStockInfo"></small>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="quantity" id="quantityInput" required
                                min="0.01" step="0.01">
                            <span class="input-group-text" id="unitLabel">units</span>
                        </div>
                        <small class="text-muted" id="availableInfo">Select product and unit to see available
                            stock</small>
                    </div>

                    <!-- Unit Price -->
                    <div class="mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" class="form-control" name="unit_price" id="unitPriceInput" min="0"
                            step="0.01" value="0">
                    </div>

                    <!-- Total -->
                    <div class="mb-3">
                        <label class="form-label">Total Value</label>
                        <input type="text" class="form-control bg-light" id="totalValue" readonly value="0.00">
                    </div>

                    <!-- Transfer Date -->
                    <div class="mb-3">
                        <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="transfer_date" id="transferDate"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-transfer me-1"></i> Create Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Transfers List -->
    <div class="<?= $canCreate ? 'col-lg-8' : 'col-12' ?>">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-list me-2"></i>Transfer History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="transfersTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Transfer #</th>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transfers)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="ti ti-transfer-off fs-2 d-block mb-2"></i>
                                    No transfers found
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($transfers as $t): ?>
                            <tr>
                                <td>
                                    <code class="text-dark"><?= htmlspecialchars($t['transfer_number']) ?></code>
                                </td>
                                <td><?= date('M d, Y', strtotime($t['transfer_date'])) ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        <?= htmlspecialchars($t['from_location_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <?= htmlspecialchars($t['to_location_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($t['category_name']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($t['type_name']) ?></small>
                                </td>
                                <td class="text-end">
                                    <?= number_format($t['quantity'], 2) ?>
                                    <small class="text-muted"><?= $t['unit_symbol'] ?></small>
                                </td>
                                <td class="text-end"><?= number_format($t['total_price'], 2) ?></td>
                                <td>
                                    <?php if ($t['status'] === 'pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                    <?php elseif ($t['status'] === 'in_transit'): ?>
                                    <span class="badge bg-info-subtle text-info">In Transit</span>
                                    <?php elseif ($t['status'] === 'received'): ?>
                                    <span class="badge bg-success-subtle text-success">Received</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['status'] === 'pending' && $canApprove): ?>
                                    <form action="<?= APP_URL ?>/stock/transfers/action" method="POST"
                                        class="d-inline confirm-form" data-title="Approve Transfer"
                                        data-text="Approve this transfer and send to warehouse?"
                                        data-confirm-text="Yes, Approve" data-icon="question">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve & Send">
                                            <i class="ti ti-send"></i>
                                        </button>
                                    </form>
                                    <form action="<?= APP_URL ?>/stock/transfers/action" method="POST"
                                        class="d-inline confirm-form" data-title="Cancel Transfer"
                                        data-text="Cancel this transfer?" data-confirm-text="Yes, Cancel"
                                        data-icon="warning">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </form>
                                    <?php elseif ($t['status'] === 'in_transit' && $canApprove): ?>
                                    <form action="<?= APP_URL ?>/stock/transfers/action" method="POST"
                                        class="d-inline confirm-form" data-title="Cancel Transfer"
                                        data-text="Cancel this transfer? Stock will be returned to source."
                                        data-confirm-text="Yes, Cancel" data-icon="warning">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </form>
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

<script>
window.APP_URL = '<?= APP_URL ?>';

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