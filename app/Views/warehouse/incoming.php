<?php
$permissions = $user['permissions'] ?? [];
$canReceive = in_array('receive-warehouse-transfers', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Warehouse - Incoming Transfers</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/warehouse/stock">Warehouse</a></li>
                <li class="breadcrumb-item active">Incoming Transfers</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <select id="warehouseSelect" class="form-select" style="min-width: 250px;">
            <option value="">Select Warehouse</option>
            <?php foreach ($warehouses as $wh): ?>
            <option value="<?= $wh['id'] ?>" <?= $wh['id'] == $selectedWarehouseId ? 'selected' : '' ?>>
                <?= htmlspecialchars($wh['name']) ?>
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

<?php if ($selectedWarehouseId): ?>

<!-- Pending Incoming Transfers -->
<?php if (!empty($pendingTransfers)): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning-subtle">
        <h5 class="card-title mb-0 text-warning">
            <i class="ti ti-truck-delivery me-2"></i>Pending Incoming Transfers
            <span class="badge bg-warning ms-2"><?= count($pendingTransfers) ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Transfer #</th>
                        <th>Date</th>
                        <th>From Location</th>
                        <th>Product</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Value</th>
                        <th>Status</th>
                        <th>Sent By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingTransfers as $t): ?>
                    <tr class="table-warning-subtle">
                        <td>
                            <code class="text-dark"><?= htmlspecialchars($t['transfer_number']) ?></code>
                        </td>
                        <td><?= date('M d, Y', strtotime($t['transfer_date'])) ?></td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary">
                                <i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($t['from_location_name']) ?>
                            </span>
                            <br><small class="text-muted"><?= htmlspecialchars($t['from_location_type_name']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($t['category_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($t['type_name']) ?></small>
                        </td>
                        <td class="text-end">
                            <strong><?= number_format($t['quantity'], 2) ?></strong>
                            <small class="text-muted"><?= $t['unit_symbol'] ?></small>
                        </td>
                        <td class="text-end"><?= number_format($t['total_price'], 2) ?></td>
                        <td>
                            <?php if ($t['status'] === 'pending'): ?>
                            <span class="badge bg-warning-subtle text-warning">Pending Approval</span>
                            <?php else: ?>
                            <span class="badge bg-info-subtle text-info">In Transit</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($t['created_by_name'] ?? '-') ?></td>
                        <td>
                            <?php if ($t['status'] === 'in_transit' && $canReceive): ?>
                            <form action="<?= APP_URL ?>/warehouse/action" method="POST" class="d-inline confirm-form"
                                data-title="Receive Transfer"
                                data-text="Receive this transfer and add to warehouse stock?"
                                data-confirm-text="Yes, Receive" data-icon="question">
                                <input type="hidden" name="action" value="receive">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="warehouse_id" value="<?= $selectedWarehouseId ?>">
                                <button type="submit" class="btn btn-sm btn-success" title="Receive">
                                    <i class="ti ti-check"></i> Receive
                                </button>
                            </form>
                            <form action="<?= APP_URL ?>/warehouse/action" method="POST" class="d-inline confirm-form"
                                data-title="Reject Transfer"
                                data-text="Reject this transfer? Stock will be returned to source."
                                data-confirm-text="Yes, Reject" data-icon="warning">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="warehouse_id" value="<?= $selectedWarehouseId ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                    <i class="ti ti-x"></i>
                                </button>
                            </form>
                            <?php elseif ($t['status'] === 'pending'): ?>
                            <span class="text-muted small">Awaiting sender approval</span>
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
    No pending incoming transfers for this warehouse.
</div>
<?php endif; ?>

<!-- All Incoming Transfers History -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-history me-2"></i>All Incoming Transfers</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="transfersTable">
                <thead class="bg-light">
                    <tr>
                        <th>Transfer #</th>
                        <th>Date</th>
                        <th>From Location</th>
                        <th>Product</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Value</th>
                        <th>Status</th>
                        <th>Received At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allTransfers)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="ti ti-package-off fs-2 d-block mb-2"></i>
                            No transfer history found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($allTransfers as $t): ?>
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
                            <?php if ($t['received_at']): ?>
                            <?= date('M d, Y H:i', strtotime($t['received_at'])) ?>
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
    Please select a warehouse to view incoming transfers.
</div>
<?php endif; ?>

<script>
window.APP_URL = '<?= APP_URL ?>';
document.getElementById('warehouseSelect')?.addEventListener('change', function() {
    if (this.value) {
        window.location.href = window.APP_URL + '/warehouse/incoming?warehouse_id=' + this.value;
    }
});

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