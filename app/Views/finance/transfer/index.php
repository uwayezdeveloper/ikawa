<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                    <li class="breadcrumb-item active">Account Transfer</li>
                </ol>
            </div>
            <h4 class="page-title">Account Transfer</h4>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['errors']['general'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['errors']['general']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<div class="row">
    <!-- Account Transfer Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-transfer me-2"></i>Transfer Between Accounts
                </h4>
                <p class="text-muted mb-0">Transfer money from location type 3 accounts to any other account</p>
            </div>
            <div class="card-body">
                <?php if (empty($transferAccounts ?? [])): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>No accounts available for transfer!</strong><br>
                        Accounts need to have a location type assigned and positive balance to transfer money.
                    </div>
                <?php else: ?>
                <form id="transferForm" action="<?= APP_URL ?>/finance/account-transfer" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="from_account_id" class="form-label">
                                    From Account <span class="text-danger">*</span>
                                    <small class="text-muted d-block">Source account (can transfer money)</small>
                                </label>
                                <select class="form-select" id="from_account_id" name="from_account_id" required>
                                    <option value="">Choose source account...</option>
                                    <?php foreach ($transferAccounts ?? [] as $account): ?>
                                        <option value="<?= $account['id'] ?>" 
                                                data-balance="<?= $account['balance'] ?>"
                                                data-account-name="<?= htmlspecialchars($account['account_name']) ?>"
                                                data-account-number="<?= htmlspecialchars($account['account_number'] ?? 'N/A') ?>"
                                                data-location="<?= htmlspecialchars($account['location_name'] ?? 'N/A') ?>"
                                                data-location-type="<?= htmlspecialchars($account['location_type_name'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($account['account_name']) ?> 
                                            <span class="text-success">(Balance: $<?= number_format($account['balance'], 2) ?>)</span>
                                            <?php if ($account['account_number']): ?>
                                                - <?= htmlspecialchars($account['account_number']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['errors']['from_account_id'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($_SESSION['errors']['from_account_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="to_account_id" class="form-label">
                                    To Account <span class="text-danger">*</span>
                                    <small class="text-muted d-block">Destination account</small>
                                </label>
                                <select class="form-select" id="to_account_id" name="to_account_id" required>
                                    <option value="">Choose destination account...</option>
                                    <?php foreach ($receivingAccounts ?? [] as $account): ?>
                                        <option value="<?= $account['id'] ?>" 
                                                data-balance="<?= $account['balance'] ?>"
                                                data-account-name="<?= htmlspecialchars($account['account_name']) ?>"
                                                data-account-number="<?= htmlspecialchars($account['account_number'] ?? 'N/A') ?>"
                                                data-location="<?= htmlspecialchars($account['location_name'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($account['account_name']) ?> 
                                            <span class="text-info">(Balance: $<?= number_format($account['balance'], 2) ?>)</span>
                                            <?php if ($account['account_number']): ?>
                                                - <?= htmlspecialchars($account['account_number']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['errors']['to_account_id'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($_SESSION['errors']['to_account_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="transfer_amount" class="form-label">Transfer Amount ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="transfer_amount" name="amount" 
                                           step="0.01" min="0.01" placeholder="0.00" required
                                           value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>">
                                </div>
                                <small class="text-muted">Available balance will be shown after selecting source account</small>
                                <?php if (isset($_SESSION['errors']['amount'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($_SESSION['errors']['amount']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Preview -->
                    <div id="transferPreview" class="mb-3" style="display: none;">
                        <div class="card border border-info">
                            <div class="card-body">
                                <h6 class="card-title text-info mb-3"><i class="ti ti-eye me-2"></i>Transfer Preview</h6>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="text-center">
                                            <h6 class="mb-1">From Account</h6>
                                            <div id="fromAccountPreview" class="border rounded p-3 bg-light">
                                                <div class="fw-bold" id="fromAccountName">-</div>
                                                <small class="text-muted" id="fromAccountNumber">-</small><br>
                                                <small class="text-muted" id="fromLocationInfo">-</small><br>
                                                <div class="mt-2">
                                                    <small class="text-muted">Current: </small><span id="fromCurrentBalance" class="text-success fw-bold">$0.00</span><br>
                                                    <small class="text-muted">After: </small><span id="fromNewBalance" class="fw-bold">$0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            <i class="ti ti-arrow-right fs-2 text-primary"></i>
                                            <div class="mt-2">
                                                <span class="badge bg-primary" id="transferAmountDisplay">$0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="text-center">
                                            <h6 class="mb-1">To Account</h6>
                                            <div id="toAccountPreview" class="border rounded p-3 bg-light">
                                                <div class="fw-bold" id="toAccountName">-</div>
                                                <small class="text-muted" id="toAccountNumber">-</small><br>
                                                <small class="text-muted" id="toLocationInfo">-</small><br>
                                                <div class="mt-2">
                                                    <small class="text-muted">Current: </small><span id="toCurrentBalance" class="text-success fw-bold">$0.00</span><br>
                                                    <small class="text-muted">After: </small><span id="toNewBalance" class="text-primary fw-bold">$0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="insufficientFundsWarning" class="alert alert-danger mt-3" style="display: none;">
                                    <i class="ti ti-alert-circle me-2"></i>
                                    <strong>Insufficient Balance!</strong> The source account doesn't have enough funds for this transfer.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="<?= APP_URL ?>/finance/account-recharge/history" class="btn btn-outline-secondary">
                                <i class="ti ti-history me-2"></i>Transaction History
                            </a>
                            <!-- <a href="<?= APP_URL ?>/finance/account-recharge" class="btn btn-outline-primary ms-2">
                                <i class="ti ti-plus me-2"></i>Account Recharge
                            </a> -->
                        </div>
                        <button type="submit" class="btn btn-success" id="transferSubmitBtn">
                            <i class="ti ti-transfer me-2"></i>Process Transfer
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transfer Stats & Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transfer Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <span class="avatar-md bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-transfer fs-20 text-primary"></i>
                        </span>
                        <h4 class="mt-2 mb-1"><?= count($transferAccounts ?? []) ?></h4>
                        <p class="text-muted mb-0 small">Location Type 3</p>
                    </div>
                    <div class="col-6">
                        <span class="avatar-md bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-wallet fs-20 text-success"></i>
                        </span>
                        <h4 class="mt-2 mb-1"><?= count($receivingAccounts ?? []) ?></h4>
                        <p class="text-muted mb-0 small">All Accounts</p>
                    </div>
                </div>
                
                <?php 
                $totalTransferBalance = array_sum(array_column($transferAccounts ?? [], 'balance'));
                $totalBalance = array_sum(array_column($receivingAccounts ?? [], 'balance'));
                ?>
                
                <hr>
                <div class="text-center">
                    <h5 class="text-primary mb-1">$<?= number_format($totalTransferBalance, 2) ?></h5>
                    <p class="text-muted mb-2 small">From Location Type 3 Accounts</p>
                    <h6 class="text-success mb-1">$<?= number_format($totalBalance, 2) ?></h6>
                    <p class="text-muted mb-0 small">Total System Balance</p>
                </div>
            </div>
        </div>

        <!-- Transfer Enabled Accounts -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transfer Enabled Accounts</h5>
                <small class="text-muted">Accounts that can send money</small>
            </div>
            <div class="card-body">
                <?php if (!empty($transferAccounts ?? [])): ?>
                    <?php foreach (array_slice($transferAccounts, 0, 5) as $account): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-sm bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <i class="ti ti-building-bank text-primary"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><?= htmlspecialchars($account['account_name']) ?></h6>
                                <small class="text-muted">
                                    <?= htmlspecialchars($account['location_type_name'] ?? 'No Type') ?> | 
                                    Balance: <span class="text-success">$<?= number_format($account['balance'], 2) ?></span>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($transferAccounts) > 5): ?>
                        <small class="text-muted">And <?= count($transferAccounts) - 5 ?> more accounts...</small>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center">
                        <i class="ti ti-info-circle fs-2 text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-1">No transfer-enabled accounts</p>
                        <small class="text-muted">Accounts with location type 3 and positive balance</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['old']); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const transferForm = document.getElementById('transferForm');
    const fromAccountSelect = document.getElementById('from_account_id');
    const toAccountSelect = document.getElementById('to_account_id');
    const transferAmountInput = document.getElementById('transfer_amount');
    const transferPreview = document.getElementById('transferPreview');
    const transferSubmitBtn = document.getElementById('transferSubmitBtn');
    const insufficientFundsWarning = document.getElementById('insufficientFundsWarning');

    if (!transferForm) return; // Exit if no form (no transfer accounts)

    function updateTransferPreview() {
        const fromOption = fromAccountSelect.options[fromAccountSelect.selectedIndex];
        const toOption = toAccountSelect.options[toAccountSelect.selectedIndex];
        const amount = parseFloat(transferAmountInput.value) || 0;

        if (fromAccountSelect.value && toAccountSelect.value && amount > 0) {
            const fromBalance = parseFloat(fromOption.dataset.balance);
            const toBalance = parseFloat(toOption.dataset.balance);
            const newFromBalance = fromBalance - amount;
            const newToBalance = toBalance + amount;

            // Update preview information
            document.getElementById('fromAccountName').textContent = fromOption.dataset.accountName;
            document.getElementById('fromAccountNumber').textContent = 'Account: ' + fromOption.dataset.accountNumber;
            document.getElementById('fromLocationInfo').textContent = 'Location: ' + fromOption.dataset.location;
            document.getElementById('fromCurrentBalance').textContent = '$' + fromBalance.toFixed(2);
            document.getElementById('fromNewBalance').textContent = '$' + newFromBalance.toFixed(2);

            document.getElementById('toAccountName').textContent = toOption.dataset.accountName;
            document.getElementById('toAccountNumber').textContent = 'Account: ' + toOption.dataset.accountNumber;
            document.getElementById('toLocationInfo').textContent = 'Location: ' + toOption.dataset.location;
            document.getElementById('toCurrentBalance').textContent = '$' + toBalance.toFixed(2);
            document.getElementById('toNewBalance').textContent = '$' + newToBalance.toFixed(2);

            document.getElementById('transferAmountDisplay').textContent = '$' + amount.toFixed(2);

            transferPreview.style.display = 'block';

            // Check for insufficient funds
            if (fromBalance < amount) {
                document.getElementById('fromNewBalance').className = 'text-danger fw-bold';
                insufficientFundsWarning.style.display = 'block';
                transferAmountInput.setCustomValidity('Insufficient balance');
                transferSubmitBtn.disabled = true;
            } else {
                document.getElementById('fromNewBalance').className = 'text-warning fw-bold';
                insufficientFundsWarning.style.display = 'none';
                transferAmountInput.setCustomValidity('');
                transferSubmitBtn.disabled = false;
            }
        } else {
            transferPreview.style.display = 'none';
            insufficientFundsWarning.style.display = 'none';
            transferAmountInput.setCustomValidity('');
            transferSubmitBtn.disabled = false;
        }
    }

    // Filter destination accounts to exclude selected source
    function updateDestinationOptions() {
        const selectedFromId = fromAccountSelect.value;
        const toOptions = toAccountSelect.querySelectorAll('option');
        
        toOptions.forEach(option => {
            if (option.value === selectedFromId && option.value !== '') {
                option.disabled = true;
                option.style.display = 'none';
            } else {
                option.disabled = false;
                option.style.display = 'block';
            }
        });

        // Clear destination if it's same as source
        if (toAccountSelect.value === selectedFromId) {
            toAccountSelect.value = '';
        }
        
        updateTransferPreview();
    }

    fromAccountSelect.addEventListener('change', updateDestinationOptions);
    toAccountSelect.addEventListener('change', updateTransferPreview);
    transferAmountInput.addEventListener('input', updateTransferPreview);

    // Form submission
    transferForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!fromAccountSelect.value || !toAccountSelect.value || !transferAmountInput.value || parseFloat(transferAmountInput.value) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Input',
                text: 'Please select both accounts and enter a valid amount.'
            });
            return;
        }

        const fromOption = fromAccountSelect.options[fromAccountSelect.selectedIndex];
        const toOption = toAccountSelect.options[toAccountSelect.selectedIndex];
        const amount = parseFloat(transferAmountInput.value);

        if (parseFloat(fromOption.dataset.balance) < amount) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Balance',
                text: 'Source account does not have enough balance for this transfer.'
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Transfer',
            html: `
                <div class="text-start">
                    <p><strong>Transfer Details:</strong></p>
                    <p>From: <strong>${fromOption.dataset.accountName}</strong></p>
                    <p>To: <strong>${toOption.dataset.accountName}</strong></p>
                    <p>Amount: <strong class="text-success">$${amount.toFixed(2)}</strong></p>
                    <hr>
                    <p class="small text-muted">This action cannot be undone.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Transfer Money!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                transferSubmitBtn.disabled = true;
                transferSubmitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Processing Transfer...';
                transferForm.submit();
            }
        });
    });
});
</script>