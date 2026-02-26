<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-stock-transfers', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Sales History</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/warehouse/sales">Sales</a></li>
                <li class="breadcrumb-item active">History</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= APP_URL ?>/warehouse/sales" class="btn btn-primary">
            <i class="ti ti-shopping-cart me-1"></i> New Sale
        </a>
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

<!-- Sales List -->
<div class="card" data-table data-table-rows-per-page="15">
    <div class="card-header border-light justify-content-between">
        <h5 class="card-title mb-0"><i class="ti ti-list me-2"></i>All Sales</h5>
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control">
                    <option value="All">All Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Confirmed">Confirmed</option>
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
                    <th data-table-sort>Sale #</th>
                    <th data-table-sort>Date</th>
                    <th data-table-sort>Client</th>
                    <th data-table-sort>Warehouse</th>
                    <th class="text-end" data-table-sort>Total</th>
                    <th data-table-sort data-column="status">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="ti ti-shopping-cart fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No sales found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($sales as $sale): ?>
                <tr>
                    <td>
                        <span class="fw-semibold text-primary"><?= htmlspecialchars($sale['sale_number']) ?></span>
                    </td>
                    <td><?= date('M d, Y', strtotime($sale['sale_date'])) ?></td>
                    <td><?= htmlspecialchars($sale['client_name']) ?></td>
                    <td><?= htmlspecialchars($sale['location_name']) ?></td>
                    <td class="text-end fw-semibold"><?= number_format($sale['total_amount'], 2) ?></td>
                    <td data-column="status">
                        <?php if ($sale['status'] === 'confirmed'): ?>
                        <span class="badge bg-success-subtle text-success">Confirmed</span>
                        <?php elseif ($sale['status'] === 'cancelled'): ?>
                        <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                        <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-view-sale"
                                data-id="<?= $sale['id'] ?>" title="View Details">
                                <i class="ti ti-eye fs-lg"></i>
                            </button>
                            <?php if ($sale['status'] === 'draft' && $canCreate): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-confirm-sale"
                                data-id="<?= $sale['id'] ?>" data-number="<?= $sale['sale_number'] ?>"
                                title="Confirm Sale">
                                <i class="ti ti-check fs-lg text-success"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-cancel-sale"
                                data-id="<?= $sale['id'] ?>" data-number="<?= $sale['sale_number'] ?>"
                                title="Cancel Sale">
                                <i class="ti ti-x fs-lg text-danger"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
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

<!-- View Sale Modal -->
<div class="modal fade" id="viewSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-file-invoice me-2"></i>Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="saleDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms -->
<form id="confirmForm" action="<?= APP_URL ?>/warehouse/sales/action" method="POST" style="display:none;">
    <input type="hidden" name="action" value="confirm">
    <input type="hidden" name="id" id="confirmSaleId">
    <input type="hidden" name="redirect" value="history">
</form>
<form id="cancelForm" action="<?= APP_URL ?>/warehouse/sales/action" method="POST" style="display:none;">
    <input type="hidden" name="action" value="cancel">
    <input type="hidden" name="id" id="cancelSaleId">
    <input type="hidden" name="redirect" value="history">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View sale details
    document.querySelectorAll('.btn-view-sale').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('viewSaleModal'));
            const content = document.getElementById('saleDetailsContent');

            content.innerHTML =
                '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            fetch('<?= APP_URL ?>/warehouse/sales/action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=get_sale&id=${saleId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const s = data.sale;
                        let itemsHtml = data.items.map((item, i) => {
                            // Use sell_quantity and sell_unit if available, otherwise fall back to stock unit
                            const displayQty = item.sell_quantity ? parseFloat(item
                                .sell_quantity) : parseFloat(item.quantity);
                            const displayUnit = item.sell_unit_symbol || item
                                .unit_symbol;
                            return `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${item.category_name} - ${item.type_name}${item.step_name ? ' ('+item.step_name+')' : ''}</td>
                                <td class="text-end">${displayQty.toFixed(2)} ${displayUnit}</td>
                                <td class="text-end">${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(item.total_price).toFixed(2)}</td>
                            </tr>
                        `;
                        }).join('');

                        content.innerHTML = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Sale Number:</strong> ${s.sale_number}<br>
                                <strong>Date:</strong> ${s.sale_date}<br>
                                <strong>Status:</strong> <span class="badge ${s.status === 'confirmed' ? 'bg-success' : s.status === 'cancelled' ? 'bg-danger' : 'bg-warning'}">${s.status}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Client:</strong> ${s.client_name}<br>
                                <strong>Warehouse:</strong> ${s.location_name}<br>
                                <strong>Created By:</strong> ${s.created_by_name}
                            </div>
                        </div>
                        <table class="table table-bordered table-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>${itemsHtml}</tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold">${parseFloat(s.total_amount).toFixed(2)}</td>
                                </tr>
                            </tfoot>
                        </table>
                        ${s.notes ? '<p><strong>Notes:</strong> ' + s.notes + '</p>' : ''}
                    `;
                    } else {
                        content.innerHTML =
                            '<div class="alert alert-danger">Failed to load sale details</div>';
                    }
                });
        });
    });

    // Confirm sale
    document.querySelectorAll('.btn-confirm-sale').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.id;
            const saleNumber = this.dataset.number;

            Swal.fire({
                title: 'Confirm Sale?',
                html: `Are you sure you want to confirm <strong>${saleNumber}</strong>?<br><small class="text-muted">This will deduct stock from the warehouse.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Confirm'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('confirmSaleId').value = saleId;
                    document.getElementById('confirmForm').submit();
                }
            });
        });
    });

    // Cancel sale
    document.querySelectorAll('.btn-cancel-sale').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.id;
            const saleNumber = this.dataset.number;

            Swal.fire({
                title: 'Cancel Sale?',
                html: `Are you sure you want to cancel <strong>${saleNumber}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('cancelSaleId').value = saleId;
                    document.getElementById('cancelForm').submit();
                }
            });
        });
    });
});
</script>