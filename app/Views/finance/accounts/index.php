<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-accounts', $permissions);
$canEdit = in_array('edit-accounts', $permissions);
$canDelete = in_array('delete-accounts', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Accounts</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                <li class="breadcrumb-item active">Accounts</li>
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

<!-- Accounts Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search accounts..." />
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                <i class="ti ti-plus me-1"></i> Add Account
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Account Name</th>
                    <th data-table-sort>Location</th>
                    <th data-table-sort>Payment Mode</th>
                    <th data-table-sort>Account Number</th>
                    <th data-table-sort>Balance</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 8 : 7 ?>" class="text-center py-4">
                        <i class="ti ti-wallet fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No accounts found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($accounts as $index => $account): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($account['account_name']) ?></h5>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info"><?= htmlspecialchars($account['location_name'] ?? '-') ?></span>
                        <br><small class="text-muted"><?= htmlspecialchars($account['location_type_name'] ?? '') ?></small>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($account['payment_mode_name'] ?? '-') ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($account['account_number'] ?? '-') ?></span>
                    </td>
                    <td>
                        <span class="fw-semibold <?= $account['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($account['balance'], 2) ?> RWF
                        </span>
                    </td>
                    <td data-column="status">
                        <?php if ($account['status'] === 'active'): ?>
                        <span class="badge bg-success-subtle text-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-account"
                                data-id="<?= $account['id'] ?>"
                                data-location-type-id="<?= $account['location_type_id'] ?>"
                                data-location-id="<?= $account['location_id'] ?>"
                                data-payment-mode-id="<?= $account['payment_mode_id'] ?>"
                                data-account-name="<?= htmlspecialchars($account['account_name']) ?>"
                                data-account-number="<?= htmlspecialchars($account['account_number'] ?? '') ?>"
                                data-balance="<?= $account['balance'] ?>"
                                data-status="<?= $account['status'] ?>"
                                data-bs-toggle="modal" data-bs-target="#editAccountModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-toggle-status"
                                data-id="<?= $account['id'] ?>"
                                data-name="<?= htmlspecialchars($account['account_name']) ?>"
                                data-status="<?= $account['status'] ?>">
                                <?php if ($account['status'] === 'active'): ?>
                                <i class="ti ti-ban fs-lg text-warning"></i>
                                <?php else: ?>
                                <i class="ti ti-check fs-lg text-success"></i>
                                <?php endif; ?>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-account"
                                data-id="<?= $account['id'] ?>"
                                data-name="<?= htmlspecialchars($account['account_name']) ?>">
                                <i class="ti ti-trash fs-lg text-danger"></i>
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
<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/finance/accounts" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="locationTypeId" class="form-label">Location Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="locationTypeId" name="location_type_id" required>
                                <option value="">Select Location Type</option>
                                <?php foreach ($locationTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="locationId" class="form-label">Location <span class="text-danger">*</span></label>
                            <select class="form-select" id="locationId" name="location_id" required disabled>
                                <option value="">Select Location Type First</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="paymentModeId" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentModeId" name="payment_mode_id" required>
                                <option value="">Select Payment Mode</option>
                                <?php foreach ($paymentModes as $mode): ?>
                                <option value="<?= $mode['id'] ?>"><?= htmlspecialchars($mode['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accountNumber" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="accountNumber" name="account_number" placeholder="e.g., 1234567890">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="accountName" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="accountName" name="account_name" required placeholder="e.g., Main Cash Account">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="balance" class="form-label">Opening Balance</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="0.00">
                                <span class="input-group-text">RWF</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/finance/accounts" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editLocationTypeId" class="form-label">Location Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="editLocationTypeId" name="location_type_id" required>
                                <option value="">Select Location Type</option>
                                <?php foreach ($locationTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLocationId" class="form-label">Location <span class="text-danger">*</span></label>
                            <select class="form-select" id="editLocationId" name="location_id" required>
                                <option value="">Select Location Type First</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPaymentModeId" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select" id="editPaymentModeId" name="payment_mode_id" required>
                                <option value="">Select Payment Mode</option>
                                <?php foreach ($paymentModes as $mode): ?>
                                <option value="<?= $mode['id'] ?>"><?= htmlspecialchars($mode['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAccountNumber" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="editAccountNumber" name="account_number">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editAccountName" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editAccountName" name="account_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editBalance" class="form-label">Balance</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="editBalance" name="balance">
                                <span class="input-group-text">RWF</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Form -->
<form id="deleteForm" action="<?= APP_URL ?>/finance/accounts" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<!-- Toggle Status Form -->
<form id="toggleStatusForm" action="<?= APP_URL ?>/finance/accounts" method="POST" style="display: none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id" id="toggleStatusId">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const APP_URL = '<?= APP_URL ?>';

    // Function to load locations by type
    function loadLocations(typeId, selectElement, selectedLocationId = null) {
        if (!typeId) {
            selectElement.innerHTML = '<option value="">Select Location Type First</option>';
            selectElement.disabled = true;
            return;
        }

        selectElement.innerHTML = '<option value="">Loading...</option>';
        selectElement.disabled = true;

        fetch(APP_URL + '/finance/accounts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_locations&location_type_id=' + typeId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let options = '<option value="">Select Location</option>';
                data.locations.forEach(function(location) {
                    const selected = selectedLocationId && location.id == selectedLocationId ? 'selected' : '';
                    options += `<option value="${location.id}" ${selected}>${location.name}</option>`;
                });
                selectElement.innerHTML = options;
                selectElement.disabled = false;
            } else {
                selectElement.innerHTML = '<option value="">No locations found</option>';
                selectElement.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            selectElement.innerHTML = '<option value="">Error loading locations</option>';
            selectElement.disabled = true;
        });
    }

    // Add Modal: Location Type change
    document.getElementById('locationTypeId').addEventListener('change', function() {
        loadLocations(this.value, document.getElementById('locationId'));
    });

    // Edit Modal: Location Type change
    document.getElementById('editLocationTypeId').addEventListener('change', function() {
        loadLocations(this.value, document.getElementById('editLocationId'));
    });

    // Edit button handler
    document.querySelectorAll('.btn-edit-account').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editLocationTypeId').value = this.dataset.locationTypeId;
            document.getElementById('editPaymentModeId').value = this.dataset.paymentModeId;
            document.getElementById('editAccountName').value = this.dataset.accountName;
            document.getElementById('editAccountNumber').value = this.dataset.accountNumber;
            document.getElementById('editBalance').value = this.dataset.balance;
            document.getElementById('editStatus').value = this.dataset.status;

            // Load locations for the selected type and pre-select the location
            loadLocations(this.dataset.locationTypeId, document.getElementById('editLocationId'), this.dataset.locationId);
        });
    });

    // Delete button handler
    document.querySelectorAll('.btn-delete-account').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Account?',
                text: `Are you sure you want to delete "${name}"?`,
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

    // Toggle status button handler
    document.querySelectorAll('.btn-toggle-status').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const currentStatus = this.dataset.status;
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            Swal.fire({
                title: 'Change Status?',
                text: `Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} "${name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: newStatus === 'active' ? '#28a745' : '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${newStatus === 'active' ? 'activate' : 'deactivate'} it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('toggleStatusId').value = id;
                    document.getElementById('toggleStatusForm').submit();
                }
            });
        });
    });
});
</script>
