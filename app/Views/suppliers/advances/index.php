<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-supplier-advances', $permissions);
$canEdit = in_array('edit-supplier-advances', $permissions);
$canDelete = in_array('delete-supplier-advances', $permissions);
$canApprove = in_array('approve-supplier-advances', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Supplier Advances</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/suppliers">Suppliers</a></li>
                <li class="breadcrumb-item active">Advances</li>
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

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-receipt fs-2 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0"><?= count($advances) ?></h5>
                        <p class="text-muted mb-0">Total Advances</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-clock fs-2 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <?php $pendingAmount = array_sum(array_map(fn($a) => $a['status'] === 'pending' ? $a['amount'] : 0, $advances)); ?>
                        <h5 class="mb-0"><?= number_format($pendingAmount, 0) ?> RWF</h5>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-check fs-2 text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <?php $approvedAmount = array_sum(array_map(fn($a) => $a['status'] === 'approved' ? $a['amount'] : 0, $advances)); ?>
                        <h5 class="mb-0"><?= number_format($approvedAmount, 0) ?> RWF</h5>
                        <p class="text-muted mb-0">Approved</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-circle-check fs-2 text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <?php $settledAmount = array_sum(array_map(fn($a) => $a['status'] === 'settled' ? $a['amount'] : 0, $advances)); ?>
                        <h5 class="mb-0"><?= number_format($settledAmount, 0) ?> RWF</h5>
                        <p class="text-muted mb-0">Settled</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advances Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search advances..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Status Filter -->
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                    <option value="All">Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Settled">Settled</option>
                    <option value="Cancelled">Cancelled</option>
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdvanceModal">
                <i class="ti ti-plus me-1"></i> New Advance
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Advance #</th>
                    <th data-table-sort>Supplier</th>
                    <th data-table-sort>Location</th>
                    <th data-table-sort>Account</th>
                    <th data-table-sort>Amount</th>
                    <th data-table-sort>Date</th>
                    <th data-table-sort data-column="status">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($advances)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="ti ti-receipt-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No advances found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($advances as $index => $advance): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <span
                            class="badge bg-dark-subtle text-dark"><?= htmlspecialchars($advance['advance_number']) ?></span>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($advance['supplier_name']) ?></h5>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($advance['location_name'] ?? '-') ?></span>
                    </td>
                    <td>
                        <span
                            class="badge bg-info-subtle text-info"><?= htmlspecialchars($advance['account_name'] ?? '-') ?></span>
                        <?php if ($advance['payment_mode_name']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($advance['payment_mode_name']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-semibold text-primary"><?= number_format($advance['amount'], 2) ?> RWF</span>
                    </td>
                    <td>
                        <span class="text-muted"><?= date('d M Y', strtotime($advance['advance_date'])) ?></span>
                    </td>
                    <td data-column="status">
                        <?php 
                        $statusColors = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'settled' => 'info',
                            'cancelled' => 'danger'
                        ];
                        $statusColor = $statusColors[$advance['status']] ?? 'secondary';
                        ?>
                        <span
                            class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?>"><?= ucfirst($advance['status']) ?></span>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <!-- View Details -->
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-view-advance"
                                data-id="<?= $advance['id'] ?>"
                                data-advance-number="<?= htmlspecialchars($advance['advance_number']) ?>"
                                data-supplier-name="<?= htmlspecialchars($advance['supplier_name']) ?>"
                                data-location-name="<?= htmlspecialchars($advance['location_name'] ?? '-') ?>"
                                data-account-name="<?= htmlspecialchars($advance['account_name'] ?? '-') ?>"
                                data-payment-mode="<?= htmlspecialchars($advance['payment_mode_name'] ?? '-') ?>"
                                data-amount="<?= number_format($advance['amount'], 2) ?>"
                                data-date="<?= date('d M Y', strtotime($advance['advance_date'])) ?>"
                                data-description="<?= htmlspecialchars($advance['description'] ?? '-') ?>"
                                data-status="<?= ucfirst($advance['status']) ?>"
                                data-created-by="<?= htmlspecialchars($advance['created_by_name'] ?? '-') ?>"
                                data-bs-toggle="modal" data-bs-target="#viewAdvanceModal">
                                <i class="ti ti-eye fs-lg"></i>
                            </button>

                            <?php if ($advance['status'] === 'pending'): ?>
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-advance"
                                data-id="<?= $advance['id'] ?>"
                                data-supplier-type-id="<?= $advance['supplier_type_id'] ?? '' ?>"
                                data-supplier-id="<?= $advance['supplier_id'] ?>"
                                data-location-id="<?= $advance['location_id'] ?>"
                                data-account-id="<?= $advance['account_id'] ?>" data-amount="<?= $advance['amount'] ?>"
                                data-advance-date="<?= $advance['advance_date'] ?>"
                                data-description="<?= htmlspecialchars($advance['description'] ?? '') ?>"
                                data-bs-toggle="modal" data-bs-target="#editAdvanceModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>

                            <?php if ($canApprove): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-approve-advance"
                                data-id="<?= $advance['id'] ?>"
                                data-number="<?= htmlspecialchars($advance['advance_number']) ?>"
                                data-amount="<?= number_format($advance['amount'], 2) ?>">
                                <i class="ti ti-check fs-lg text-success"></i>
                            </button>
                            <?php endif; ?>

                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-advance"
                                data-id="<?= $advance['id'] ?>"
                                data-number="<?= htmlspecialchars($advance['advance_number']) ?>">
                                <i class="ti ti-trash fs-lg text-danger"></i>
                            </button>
                            <?php endif; ?>
                            <?php elseif ($advance['status'] === 'approved'): ?>
                            <?php if ($canApprove): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-settle-advance"
                                data-id="<?= $advance['id'] ?>"
                                data-number="<?= htmlspecialchars($advance['advance_number']) ?>">
                                <i class="ti ti-circle-check fs-lg text-info"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-cancel-advance"
                                data-id="<?= $advance['id'] ?>"
                                data-number="<?= htmlspecialchars($advance['advance_number']) ?>">
                                <i class="ti ti-x fs-lg text-danger"></i>
                            </button>
                            <?php endif; ?>
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

<?php if ($canCreate): ?>
<!-- Add Advance Modal -->
<div class="modal fade" id="addAdvanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Supplier Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/suppliers/advances" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplierTypeId" class="form-label">Supplier Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="supplierTypeId" name="supplier_type_id" required>
                                <option value="">Select Supplier Type</option>
                                <?php foreach ($supplierTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supplierId" class="form-label">Supplier <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="supplierId" name="supplier_id" required disabled>
                                <option value="">Select Supplier Type First</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="locationId" class="form-label">Location <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="locationId" name="location_id" required>
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?>
                                    (<?= htmlspecialchars($location['location_type_name'] ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accountId" class="form-label">Account <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountId" name="account_id" required disabled>
                                <option value="">Select Location First</option>
                            </select>
                            <small class="text-muted" id="accountBalance"></small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount"
                                    name="amount" required placeholder="0.00">
                                <span class="input-group-text">RWF</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="advanceDate" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="advanceDate" name="advance_date"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                            placeholder="Optional notes about this advance..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Advance</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Advance Modal -->
<div class="modal fade" id="editAdvanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/suppliers/advances" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editSupplierTypeId" class="form-label">Supplier Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editSupplierTypeId" name="supplier_type_id" required>
                                <option value="">Select Supplier Type</option>
                                <?php foreach ($supplierTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editSupplierId" class="form-label">Supplier <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editSupplierId" name="supplier_id" required>
                                <option value="">Select Supplier Type First</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editLocationId" class="form-label">Location <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editLocationId" name="location_id" required>
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?>
                                    (<?= htmlspecialchars($location['location_type_name'] ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAccountId" class="form-label">Account <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editAccountId" name="account_id" required>
                                <option value="">Select Location First</option>
                            </select>
                            <small class="text-muted" id="editAccountBalance"></small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editAmount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" class="form-control" id="editAmount"
                                    name="amount" required>
                                <span class="input-group-text">RWF</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAdvanceDate" class="form-label">Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editAdvanceDate" name="advance_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Advance</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Advance Modal -->
<div class="modal fade" id="viewAdvanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Advance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Advance Number:</th>
                        <td id="viewAdvanceNumber"></td>
                    </tr>
                    <tr>
                        <th>Supplier:</th>
                        <td id="viewSupplierName"></td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td id="viewLocationName"></td>
                    </tr>
                    <tr>
                        <th>Account:</th>
                        <td id="viewAccountName"></td>
                    </tr>
                    <tr>
                        <th>Payment Mode:</th>
                        <td id="viewPaymentMode"></td>
                    </tr>
                    <tr>
                        <th>Amount:</th>
                        <td id="viewAmount" class="fw-bold text-primary"></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td id="viewDate"></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td id="viewStatus"></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td id="viewDescription"></td>
                    </tr>
                    <tr>
                        <th>Created By:</th>
                        <td id="viewCreatedBy"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form id="approveForm" action="<?= APP_URL ?>/suppliers/advances" method="POST" style="display: none;">
    <input type="hidden" name="action" value="approve">
    <input type="hidden" name="id" id="approveId">
</form>

<form id="cancelForm" action="<?= APP_URL ?>/suppliers/advances" method="POST" style="display: none;">
    <input type="hidden" name="action" value="cancel">
    <input type="hidden" name="id" id="cancelId">
</form>

<form id="settleForm" action="<?= APP_URL ?>/suppliers/advances" method="POST" style="display: none;">
    <input type="hidden" name="action" value="settle">
    <input type="hidden" name="id" id="settleId">
</form>

<form id="deleteForm" action="<?= APP_URL ?>/suppliers/advances" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const APP_URL = '<?= APP_URL ?>';
    let accountsCache = {};
    let suppliersCache = {};

    // Function to load suppliers by type
    function loadSuppliers(typeId, selectElement, selectedSupplierId = null) {
        if (!typeId) {
            selectElement.innerHTML = '<option value="">Select Supplier Type First</option>';
            selectElement.disabled = true;
            return;
        }

        // Check cache first
        if (suppliersCache[typeId]) {
            populateSuppliers(suppliersCache[typeId], selectElement, selectedSupplierId);
            return;
        }

        selectElement.innerHTML = '<option value="">Loading...</option>';
        selectElement.disabled = true;

        fetch(APP_URL + '/suppliers/advances', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_suppliers&supplier_type_id=' + typeId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    suppliersCache[typeId] = data.suppliers;
                    populateSuppliers(data.suppliers, selectElement, selectedSupplierId);
                } else {
                    selectElement.innerHTML = '<option value="">No suppliers found</option>';
                    selectElement.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                selectElement.innerHTML = '<option value="">Error loading suppliers</option>';
                selectElement.disabled = true;
            });
    }

    function populateSuppliers(suppliers, selectElement, selectedSupplierId) {
        if (suppliers.length === 0) {
            selectElement.innerHTML = '<option value="">No suppliers available</option>';
            selectElement.disabled = true;
            return;
        }

        let options = '<option value="">Select Supplier</option>';
        suppliers.forEach(function(supplier) {
            const selected = selectedSupplierId && supplier.id == selectedSupplierId ? 'selected' : '';
            options += `<option value="${supplier.id}" ${selected}>${supplier.name}</option>`;
        });
        selectElement.innerHTML = options;
        selectElement.disabled = false;
    }

    // Function to load accounts by location
    function loadAccounts(locationId, selectElement, balanceElement, selectedAccountId = null) {
        if (!locationId) {
            selectElement.innerHTML = '<option value="">Select Location First</option>';
            selectElement.disabled = true;
            if (balanceElement) balanceElement.textContent = '';
            return;
        }

        // Check cache first
        if (accountsCache[locationId]) {
            populateAccounts(accountsCache[locationId], selectElement, balanceElement, selectedAccountId);
            return;
        }

        selectElement.innerHTML = '<option value="">Loading...</option>';
        selectElement.disabled = true;

        fetch(APP_URL + '/suppliers/advances', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_accounts&location_id=' + locationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    accountsCache[locationId] = data.accounts;
                    populateAccounts(data.accounts, selectElement, balanceElement, selectedAccountId);
                } else {
                    selectElement.innerHTML = '<option value="">No accounts found</option>';
                    selectElement.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                selectElement.innerHTML = '<option value="">Error loading accounts</option>';
                selectElement.disabled = true;
            });
    }

    function populateAccounts(accounts, selectElement, balanceElement, selectedAccountId) {
        if (accounts.length === 0) {
            selectElement.innerHTML = '<option value="">No accounts available</option>';
            selectElement.disabled = true;
            return;
        }

        let options = '<option value="">Select Account</option>';
        accounts.forEach(function(account) {
            const selected = selectedAccountId && account.id == selectedAccountId ? 'selected' : '';
            const balanceText = parseFloat(account.balance).toLocaleString('en-US', {
                minimumFractionDigits: 2
            });
            options +=
                `<option value="${account.id}" data-balance="${account.balance}" ${selected}>${account.account_name} - ${account.payment_mode_name} (${balanceText} RWF)</option>`;
        });
        selectElement.innerHTML = options;
        selectElement.disabled = false;

        // Update balance display
        if (selectedAccountId && balanceElement) {
            const selectedAccount = accounts.find(a => a.id == selectedAccountId);
            if (selectedAccount) {
                balanceElement.textContent = 'Available: ' + parseFloat(selectedAccount.balance).toLocaleString(
                    'en-US', {
                        minimumFractionDigits: 2
                    }) + ' RWF';
            }
        }
    }

    // Add Modal: Supplier Type change
    document.getElementById('supplierTypeId').addEventListener('change', function() {
        loadSuppliers(this.value, document.getElementById('supplierId'));
    });

    // Add Modal: Location change
    document.getElementById('locationId').addEventListener('change', function() {
        loadAccounts(this.value, document.getElementById('accountId'), document.getElementById(
            'accountBalance'));
    });

    // Add Modal: Account change - show balance
    document.getElementById('accountId').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const balance = selectedOption.dataset.balance;
        if (balance) {
            document.getElementById('accountBalance').textContent = 'Available: ' + parseFloat(balance)
                .toLocaleString('en-US', {
                    minimumFractionDigits: 2
                }) + ' RWF';
        } else {
            document.getElementById('accountBalance').textContent = '';
        }
    });

    // Edit Modal: Supplier Type change
    document.getElementById('editSupplierTypeId').addEventListener('change', function() {
        loadSuppliers(this.value, document.getElementById('editSupplierId'));
    });

    // Edit Modal: Location change
    document.getElementById('editLocationId').addEventListener('change', function() {
        loadAccounts(this.value, document.getElementById('editAccountId'), document.getElementById(
            'editAccountBalance'));
    });

    // Edit Modal: Account change - show balance
    document.getElementById('editAccountId').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const balance = selectedOption.dataset.balance;
        if (balance) {
            document.getElementById('editAccountBalance').textContent = 'Available: ' + parseFloat(
                balance).toLocaleString('en-US', {
                minimumFractionDigits: 2
            }) + ' RWF';
        } else {
            document.getElementById('editAccountBalance').textContent = '';
        }
    });

    // Edit button handler
    document.querySelectorAll('.btn-edit-advance').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editSupplierTypeId').value = this.dataset.supplierTypeId;
            document.getElementById('editLocationId').value = this.dataset.locationId;
            document.getElementById('editAmount').value = this.dataset.amount;
            document.getElementById('editAdvanceDate').value = this.dataset.advanceDate;
            document.getElementById('editDescription').value = this.dataset.description;

            // Load suppliers for the type and pre-select
            loadSuppliers(this.dataset.supplierTypeId, document.getElementById(
                'editSupplierId'), this.dataset.supplierId);

            // Load accounts for the location and pre-select
            loadAccounts(this.dataset.locationId, document.getElementById('editAccountId'),
                document.getElementById('editAccountBalance'), this.dataset.accountId);
        });
    });

    // View button handler
    document.querySelectorAll('.btn-view-advance').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('viewAdvanceNumber').textContent = this.dataset
                .advanceNumber;
            document.getElementById('viewSupplierName').textContent = this.dataset.supplierName;
            document.getElementById('viewLocationName').textContent = this.dataset.locationName;
            document.getElementById('viewAccountName').textContent = this.dataset.accountName;
            document.getElementById('viewPaymentMode').textContent = this.dataset.paymentMode;
            document.getElementById('viewAmount').textContent = this.dataset.amount + ' RWF';
            document.getElementById('viewDate').textContent = this.dataset.date;
            document.getElementById('viewStatus').innerHTML =
                '<span class="badge bg-secondary">' + this.dataset.status + '</span>';
            document.getElementById('viewDescription').textContent = this.dataset.description;
            document.getElementById('viewCreatedBy').textContent = this.dataset.createdBy;
        });
    });

    // Approve button handler
    document.querySelectorAll('.btn-approve-advance').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const number = this.dataset.number;
            const amount = this.dataset.amount;

            Swal.fire({
                title: 'Approve Advance?',
                html: `Are you sure you want to approve advance <strong>${number}</strong>?<br><br>Amount: <strong>${amount} RWF</strong> will be deducted from the account.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approveId').value = id;
                    document.getElementById('approveForm').submit();
                }
            });
        });
    });

    // Cancel button handler
    document.querySelectorAll('.btn-cancel-advance').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const number = this.dataset.number;

            Swal.fire({
                title: 'Cancel Advance?',
                html: `Are you sure you want to cancel advance <strong>${number}</strong>?<br><br>The amount will be refunded to the account.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('cancelId').value = id;
                    document.getElementById('cancelForm').submit();
                }
            });
        });
    });

    // Settle button handler
    document.querySelectorAll('.btn-settle-advance').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const number = this.dataset.number;

            Swal.fire({
                title: 'Mark as Settled?',
                html: `Mark advance <strong>${number}</strong> as settled?<br><br>This indicates the advance has been used/deducted from supplier payment.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark settled!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('settleId').value = id;
                    document.getElementById('settleForm').submit();
                }
            });
        });
    });

    // Delete button handler
    document.querySelectorAll('.btn-delete-advance').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const number = this.dataset.number;

            Swal.fire({
                title: 'Delete Advance?',
                text: `Are you sure you want to delete advance "${number}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        });
    });
});
</script>