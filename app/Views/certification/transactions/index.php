<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-accounts', $permissions);
$canEdit = in_array('edit-accounts', $permissions);
$canDelete = in_array('delete-accounts', $permissions);
$accounts = $accounts ?? [];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Certification</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Record Certification Transactions</li>
            </ol>
        </nav>
    </div>
</div>

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

<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search certification transactions..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                    <option value="All">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
            <div>
                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
            <?php if ($canCreate): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                <i class="ti ti-plus me-1"></i> Add Transaction
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Category</th>
                    <th data-table-sort>Done By</th>
                    <th data-table-sort>Date Done</th>
                    <th data-table-sort>Due Date</th>
                    <th data-table-sort>Payment Accounts</th>
                    <th data-table-sort>Comments</th>
                    <th data-table-sort data-column="status">Status</th>
                    <th data-table-sort>Amount</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 10 : 9 ?>" class="text-center py-4">
                        <i class="ti ti-file-invoice fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No certification transactions found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($transactions as $index => $transaction): ?>
                <tr>
                    <td class="ps-3"><h5 class="m-0"><?= $index + 1 ?></h5></td>
                    <td><?= htmlspecialchars($transaction['cert_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(trim($transaction['done_by_name'] ?? '') ?: ('User #' . (int)$transaction['done_by'])) ?></td>
                    <td><?= htmlspecialchars($transaction['date_done']) ?></td>
                    <td><?= htmlspecialchars($transaction['due_date']) ?></td>
                    <td><?= htmlspecialchars($transaction['payment_accounts_display'] ?? $transaction['payment_accounts']) ?></td>
                    <td><?= htmlspecialchars($transaction['coments'] ?? '-') ?></td>
                    <td><?= (int)$transaction['status'] === 1 ? 'Active' : 'Inactive' ?></td>
                    <td>RWF <?= number_format((int)$transaction['amount']) ?></td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-transaction"
                                data-id="<?= (int)$transaction['trans_id'] ?>"
                                data-cert-id="<?= (int)$transaction['cert_id'] ?>"
                                data-date-done="<?= htmlspecialchars($transaction['date_done']) ?>"
                                data-payment-accounts="<?= htmlspecialchars($transaction['payment_accounts']) ?>"
                                data-coments="<?= htmlspecialchars($transaction['coments'] ?? '') ?>"
                                data-amount="<?= (int)$transaction['amount'] ?>"
                                data-bs-toggle="modal" data-bs-target="#editTransactionModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-transaction"
                                data-id="<?= (int)$transaction['trans_id'] ?>"
                                data-name="<?= htmlspecialchars($transaction['cert_name'] ?? 'Transaction') ?>">
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
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Certification Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/certification/transactions" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Certification Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="cert_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['cert_id'] ?>"><?= htmlspecialchars($category['cert_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Done <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date_done" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Payment Accounts & Amounts <span class="text-danger">*</span></label>
                        <div class="border p-3 rounded" id="addAccountsContainer">
                            <?php if (!empty($accounts)): ?>
                                <div class="row">
                                    <?php foreach ($accounts as $account): ?>
                                    <div class="col-md-12 mb-3 account-item">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input add-account-checkbox" type="checkbox"
                                                                   name="payment_accounts[]" value="<?= (int)$account['id'] ?>"
                                                                   id="add_account_<?= (int)$account['id'] ?>">
                                                            <label class="form-check-label" for="add_account_<?= (int)$account['id'] ?>">
                                                                <strong><?= htmlspecialchars($account['account_name']) ?></strong><br>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars($account['payment_mode_name'] ?? 'N/A') ?>
                                                                    <?php if (!empty($account['account_number'])): ?>
                                                                        - <?= htmlspecialchars($account['account_number']) ?>
                                                                    <?php endif; ?>
                                                                    <br>Balance: RWF <?= number_format((float)($account['balance'] ?? 0)) ?>
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <span class="input-group-text">RWF</span>
                                                            <input type="number" class="form-control add-account-amount"
                                                                   name="account_amounts[<?= (int)$account['id'] ?>]"
                                                                   id="add_amount_<?= (int)$account['id'] ?>"
                                                                   placeholder="Amount" min="0" step="1"
                                                                   max="<?= (float)($account['balance'] ?? 0) ?>"
                                                                   disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-info">
                                        <i class="ti ti-info-circle"></i>
                                        Select accounts and enter amounts. Total must equal transaction amount below.
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <strong>Selected Total: RWF <span id="addSelectedTotal">0</span></strong>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="ti ti-alert-triangle"></i>
                                    No accounts available for your location. Please contact administrator.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Comments</label>
                        <input type="text" class="form-control" name="coments" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="addTotalAmount" name="amount" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" <?= empty($accounts) ? 'disabled' : '' ?>>Create Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Certification Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/certification/transactions" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="trans_id" id="editTransId">
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Certification Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="cert_id" id="editCertId" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['cert_id'] ?>"><?= htmlspecialchars($category['cert_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Done <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="editDateDone" name="date_done" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Payment Accounts & Amounts <span class="text-danger">*</span></label>
                        <div class="border p-3 rounded" id="editAccountsContainer">
                            <?php if (!empty($accounts)): ?>
                                <div class="row">
                                    <?php foreach ($accounts as $account): ?>
                                    <div class="col-md-12 mb-3 account-item">
                                        <div class="card">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input edit-account-checkbox" type="checkbox"
                                                                   name="payment_accounts[]" value="<?= (int)$account['id'] ?>"
                                                                   id="edit_account_<?= (int)$account['id'] ?>">
                                                            <label class="form-check-label" for="edit_account_<?= (int)$account['id'] ?>">
                                                                <strong><?= htmlspecialchars($account['account_name']) ?></strong><br>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars($account['payment_mode_name'] ?? 'N/A') ?>
                                                                    <?php if (!empty($account['account_number'])): ?>
                                                                        - <?= htmlspecialchars($account['account_number']) ?>
                                                                    <?php endif; ?>
                                                                    <br>Balance: RWF <?= number_format((float)($account['balance'] ?? 0)) ?>
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <span class="input-group-text">RWF</span>
                                                            <input type="number" class="form-control edit-account-amount"
                                                                   name="account_amounts[<?= (int)$account['id'] ?>]"
                                                                   id="edit_amount_<?= (int)$account['id'] ?>"
                                                                   placeholder="Amount" min="0" step="1"
                                                                   max="<?= (float)($account['balance'] ?? 0) ?>"
                                                                   disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-2">
                                    <small class="text-info">
                                        <i class="ti ti-info-circle"></i>
                                        Select accounts and enter amounts. Total must equal transaction amount below.
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <strong>Selected Total: RWF <span id="editSelectedTotal">0</span></strong>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="ti ti-alert-triangle"></i>
                                    No accounts available for your location. Please contact administrator.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Comments</label>
                        <input type="text" class="form-control" id="editComents" name="coments" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="editAmount" name="amount" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" <?= empty($accounts) ? 'disabled' : '' ?>>Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDelete): ?>
<form id="deleteTransactionForm" action="<?= APP_URL ?>/certification/transactions" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="trans_id" id="deleteTransId">
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function initAccountSection(prefix, totalElementId, amountInputId) {
        const checkboxes = document.querySelectorAll('.' + prefix + '-account-checkbox');
        const amountInputs = document.querySelectorAll('.' + prefix + '-account-amount');
        const totalElement = document.getElementById(totalElementId);
        const totalAmountInput = document.getElementById(amountInputId);

        function updateSelectedTotal() {
            let total = 0;

            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    const input = document.getElementById(prefix + '_amount_' + checkbox.value);
                    total += parseFloat(input.value || 0);
                }
            });

            if (totalElement) {
                totalElement.textContent = new Intl.NumberFormat().format(total);
            }

            if (totalAmountInput && total > 0) {
                totalAmountInput.value = total;
            }
        }

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const input = document.getElementById(prefix + '_amount_' + this.value);
                if (this.checked) {
                    input.disabled = false;
                    input.focus();
                } else {
                    input.disabled = true;
                    input.value = '';
                }
                updateSelectedTotal();
            });
        });

        amountInputs.forEach(function(input) {
            input.addEventListener('input', updateSelectedTotal);
        });

        updateSelectedTotal();
        return updateSelectedTotal;
    }

    const updateAddSelectedTotal = initAccountSection('add', 'addSelectedTotal', 'addTotalAmount');
    const updateEditSelectedTotal = initAccountSection('edit', 'editSelectedTotal', 'editAmount');

    document.querySelectorAll('.btn-edit-transaction').forEach(function(button) {
        button.addEventListener('click', function() {
            document.getElementById('editTransId').value = this.dataset.id;
            document.getElementById('editCertId').value = this.dataset.certId;
            document.getElementById('editDateDone').value = this.dataset.dateDone;
            document.getElementById('editComents').value = this.dataset.coments;
            document.getElementById('editAmount').value = this.dataset.amount;

            document.querySelectorAll('.edit-account-checkbox').forEach(function(checkbox) {
                checkbox.checked = false;
                const input = document.getElementById('edit_amount_' + checkbox.value);
                input.value = '';
                input.disabled = true;
            });

            let parsed = [];
            try {
                parsed = JSON.parse(this.dataset.paymentAccounts || '[]');
            } catch (e) {
                parsed = [];
            }

            if (Array.isArray(parsed)) {
                parsed.forEach(function(item) {
                    if (!item || typeof item !== 'object' || !item.account_id) {
                        return;
                    }
                    const checkbox = document.getElementById('edit_account_' + item.account_id);
                    const amountInput = document.getElementById('edit_amount_' + item.account_id);
                    if (checkbox && amountInput) {
                        checkbox.checked = true;
                        amountInput.disabled = false;
                        amountInput.value = item.amount || '';
                    }
                });
            }

            updateEditSelectedTotal();
        });
    });

    document.querySelectorAll('.btn-delete-transaction').forEach(function(button) {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Transaction?',
                text: 'Are you sure you want to delete transaction for "' + name + '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('deleteTransId').value = id;
                    document.getElementById('deleteTransactionForm').submit();
                }
            });
        });
    });
});
</script>
