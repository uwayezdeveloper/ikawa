<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                    <li class="breadcrumb-item active">Account Recharge</li>
                </ol>
            </div>
            <h4 class="page-title">Account Recharge</h4>
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
    <!-- Account Recharge Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-credit-card me-2"></i>Recharge Account
                </h4>
                <p class="text-muted mb-0">Add money to an account</p>
            </div>
            <div class="card-body">
                <form id="rechargeForm" action="<?= APP_URL ?>/finance/account-recharge/process" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_id" class="form-label">Select Account <span class="text-danger">*</span></label>
                                <select class="form-select" id="account_id" name="account_id" required>
                                    <option value="">Choose account to recharge...</option>
                                    <?php foreach ($receivingAccounts ?? [] as $account): ?>
                                        <option value="<?= $account['id'] ?>" 
                                                data-balance="<?= $account['balance'] ?>"
                                                data-account-name="<?= htmlspecialchars($account['account_name']) ?>"
                                                data-account-number="<?= htmlspecialchars($account['account_number'] ?? 'N/A') ?>"
                                                data-location="<?= htmlspecialchars($account['location_name'] ?? 'N/A') ?>"
                                                data-payment-mode="<?= htmlspecialchars($account['payment_mode_name'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($account['account_name']) ?> 
                                            <?php if ($account['account_number']): ?>
                                                (<?= htmlspecialchars($account['account_number']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['errors']['account_id'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($_SESSION['errors']['account_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="recharge_amount" class="form-label">Recharge Amount ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="recharge_amount" name="amount" 
                                           step="0.01" min="0.01" placeholder="0.00" required
                                           value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>">
                                </div>
                                <?php if (isset($_SESSION['errors']['amount'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($_SESSION['errors']['amount']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="in_id" class="form-label">Source of Income</label>
                                <select class="form-select" id="in_id" name="in_id">
                                    <option value="">Select source of income (Optional)</option>
                                    <?php foreach ($sourcesOfIncome ?? [] as $source): ?>
                                        <option value="<?= $source['in_id'] ?>" 
                                                <?= (($_SESSION['old']['in_id'] ?? '') == $source['in_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($source['in_name']) ?>
                                            <?php if (!empty($source['in_descr'])): ?>
                                                - <?= htmlspecialchars($source['in_descr']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Select the source of this income (optional)</small>
                                <?php if (isset($_SESSION['errors']['in_id'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($_SESSION['errors']['in_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Account Details Display -->
                    <div id="accountDetails" class="mb-3" style="display: none;">
                        <div class="card border border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-2">Account Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Account Name</small>
                                        <span id="detailAccountName" class="fw-medium">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Account Number</small>
                                        <span id="detailAccountNumber" class="fw-medium">-</span>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">Location</small>
                                        <span id="detailLocation" class="fw-medium">-</span>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted d-block">Payment Mode</small>
                                        <span id="detailPaymentMode" class="fw-medium">-</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted d-block">Current Balance</small>
                                        <span id="detailBalance" class="fw-bold text-success fs-5">$0.00</span>
                                        <span class="text-muted ms-2" id="newBalance" style="display: none;">
                                            → New Balance: <span class="fw-bold text-primary"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="<?= APP_URL ?>/finance/account-recharge/history" class="btn btn-outline-secondary">
                                <i class="ti ti-history me-2"></i>Transaction History
                            </a>
                            <!-- <a href="<?= APP_URL ?>/finance/account-transfer" class="btn btn-outline-success ms-2">
                                <i class="ti ti-transfer me-2"></i>Account Transfer
                            </a> -->
                        </div>
                        <button type="submit" class="btn btn-primary" id="rechargeSubmitBtn">
                            <i class="ti ti-plus me-2"></i>Process Recharge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Statistics</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-3">
                        <span class="avatar-md bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-credit-card fs-20 text-success"></i>
                        </span>
                    </div>
                    <h4 class="mb-1"><?= count($receivingAccounts ?? []) ?></h4>
                    <p class="text-muted mb-3">Active Accounts</p>
                    
                    <?php 
                    $totalBalance = array_sum(array_column($receivingAccounts ?? [], 'balance'));
                    ?>
                    <h5 class="text-success mb-1">$<?= number_format($totalBalance, 2) ?></h5>
                    <p class="text-muted mb-0">Total Balance</p>
                </div>
            </div>
        </div>

        <!-- Recent Accounts -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Accounts</h5>
            </div>
            <div class="card-body">
                <?php foreach (array_slice($receivingAccounts ?? [], 0, 5) as $account): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <span class="avatar-sm bg-light rounded-circle d-inline-flex align-items-center justify-content-center">
                                <i class="ti ti-wallet text-muted"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0"><?= htmlspecialchars($account['account_name']) ?></h6>
                            <small class="text-muted">
                                Balance: <span class="text-success">$<?= number_format($account['balance'], 2) ?></span>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($receivingAccounts ?? [])): ?>
                    <p class="text-muted text-center mb-0">No accounts available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['old']); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rechargeForm = document.getElementById('rechargeForm');
    const accountSelect = document.getElementById('account_id');
    const rechargeAmountInput = document.getElementById('recharge_amount');
    const accountDetails = document.getElementById('accountDetails');
    const rechargeSubmitBtn = document.getElementById('rechargeSubmitBtn');

    // Account selection change
    accountSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            document.getElementById('detailAccountName').textContent = selectedOption.dataset.accountName;
            document.getElementById('detailAccountNumber').textContent = selectedOption.dataset.accountNumber;
            document.getElementById('detailLocation').textContent = selectedOption.dataset.location;
            document.getElementById('detailPaymentMode').textContent = selectedOption.dataset.paymentMode;
            document.getElementById('detailBalance').textContent = '$' + parseFloat(selectedOption.dataset.balance).toFixed(2);
            
            accountDetails.style.display = 'block';
            updateNewBalance();
        } else {
            accountDetails.style.display = 'none';
        }
    });

    // Amount input change
    rechargeAmountInput.addEventListener('input', updateNewBalance);

    function updateNewBalance() {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const newBalanceSpan = document.getElementById('newBalance');
        
        if (accountSelect.value && rechargeAmountInput.value && parseFloat(rechargeAmountInput.value) > 0) {
            const currentBalance = parseFloat(selectedOption.dataset.balance);
            const rechargeAmount = parseFloat(rechargeAmountInput.value);
            const newBalance = currentBalance + rechargeAmount;
            
            newBalanceSpan.querySelector('.fw-bold').textContent = '$' + newBalance.toFixed(2);
            newBalanceSpan.style.display = 'inline';
        } else {
            newBalanceSpan.style.display = 'none';
        }
    }

    // Form submission
    rechargeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!accountSelect.value || !rechargeAmountInput.value || parseFloat(rechargeAmountInput.value) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Input',
                text: 'Please select an account and enter a valid amount.'
            });
            return;
        }

        const rechargeAmount = parseFloat(rechargeAmountInput.value);
        const accountName = accountSelect.options[accountSelect.selectedIndex].dataset.accountName;

        Swal.fire({
            title: 'Confirm Recharge',
            html: `
                <div class="text-start">
                    <p>Are you sure you want to recharge:</p>
                    <p><strong>${accountName}</strong></p>
                    <p>with amount: <strong class="text-success">$${rechargeAmount.toFixed(2)}</strong>?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Process Recharge!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                rechargeSubmitBtn.disabled = true;
                rechargeSubmitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Processing...';
                rechargeForm.submit();
            }
        });
    });
});
</script>