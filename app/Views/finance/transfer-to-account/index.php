<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                    <li class="breadcrumb-item active">Transfer to Another Account</li>
                </ol>
            </div>
            <h4 class="page-title">Transfer to Another Account</h4>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
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
    <!-- Transfer Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-arrow-right me-2"></i>Transfer to Another Account
                </h4>
                <p class="text-muted mb-0">Transfer money from your location accounts to any other system account</p>
            </div>
            <div class="card-body">
                <?php if (empty($sourceAccounts ?? [])): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>No source accounts available!</strong><br>
                        You don't have any accounts in your location to transfer from.
                    </div>
                <?php elseif (empty($destinationAccounts ?? [])): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>No destination accounts available!</strong><br>
                        There are no other accounts in the system to transfer to.
                    </div>
                <?php else: ?>
                    <form id="transferForm" action="<?= APP_URL ?>/finance/transfer-to-account" method="POST">
                        <div class="row">
                            <!-- From Account -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_account_id" class="form-label">
                                        From Account (Your Location) <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= isset($_SESSION['errors']['from_account_id']) ? 'is-invalid' : '' ?>" 
                                            id="from_account_id" name="from_account_id" required>
                                        <option value="">Select source account...</option>
                                        <?php foreach ($sourceAccounts as $account): ?>
                                            <?php
                                                $accountName = (string)($account['account_name'] ?? $account['name'] ?? 'Unnamed Account');
                                                $accountNumber = (string)($account['account_number'] ?? 'N/A');
                                                $bankName = (string)($account['bank_name'] ?? 'N/A');
                                                $currencyName = (string)($account['currency_name'] ?? ('Currency ID ' . ($account['currency_type'] ?? 'N/A')));
                                                $currencySign = (string)($account['currency_sign'] ?? '$');
                                                $balanceText = number_format((float)($account['balance'] ?? 0), 2);
                                            ?>
                                            <option value="<?= $account['id'] ?>" 
                                                    data-balance="<?= htmlspecialchars($account['balance'] ?? '0') ?>"
                                                    <?= (($_SESSION['old']['from_account_id'] ?? '') == $account['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($accountName) ?> | <?= htmlspecialchars($accountNumber) ?> | <?= htmlspecialchars($bankName) ?> | <?= htmlspecialchars($currencyName) ?> (<?= htmlspecialchars($currencySign) ?><?= $balanceText ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($_SESSION['errors']['from_account_id'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($_SESSION['errors']['from_account_id']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- To Account -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="to_account_id" class="form-label">
                                        To Account (Other Locations) <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= isset($_SESSION['errors']['to_account_id']) ? 'is-invalid' : '' ?>" 
                                            id="to_account_id" name="to_account_id" required>
                                        <option value="">Select destination account...</option>
                                        <?php foreach ($destinationAccounts as $account): ?>
                                            <?php
                                                $accountName = (string)($account['account_name'] ?? $account['name'] ?? 'Unnamed Account');
                                                $accountNumber = (string)($account['account_number'] ?? 'N/A');
                                                $bankName = (string)($account['bank_name'] ?? 'N/A');
                                                $currencyName = (string)($account['currency_name'] ?? ('Currency ID ' . ($account['currency_type'] ?? 'N/A')));
                                                $currencySign = (string)($account['currency_sign'] ?? '$');
                                                $balanceText = number_format((float)($account['balance'] ?? 0), 2);
                                            ?>
                                            <option value="<?= $account['id'] ?>" 
                                                    <?= (($_SESSION['old']['to_account_id'] ?? '') == $account['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($accountName) ?> | <?= htmlspecialchars($accountNumber) ?> | <?= htmlspecialchars($bankName) ?> | <?= htmlspecialchars($currencyName) ?> (<?= htmlspecialchars($currencySign) ?><?= $balanceText ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($_SESSION['errors']['to_account_id'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($_SESSION['errors']['to_account_id']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">
                                Transfer Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control <?= isset($_SESSION['errors']['amount']) ? 'is-invalid' : '' ?>" 
                                       id="amount" name="amount" 
                                       value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>" 
                                       placeholder="0.00" required>
                                <?php if (isset($_SESSION['errors']['amount'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($_SESSION['errors']['amount']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Available balance will be shown when you select an account</small>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Transfer Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?= isset($_SESSION['errors']['description']) ? 'is-invalid' : '' ?>" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the purpose of this transfer..." required><?= htmlspecialchars($_SESSION['old']['description'] ?? '') ?></textarea>
                            <?php if (isset($_SESSION['errors']['description'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($_SESSION['errors']['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="ti ti-refresh me-1"></i>Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="ti ti-arrow-right me-1"></i>Process Transfer
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transfer Summary -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2"></i>Transfer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">How it works:</h6>
                    <ul class="mb-0 small">
                        <li>Select an account from your location as the source</li>
                        <li>Choose any account from other locations as destination</li>
                        <li>Enter transfer amount and description</li>
                        <li>Money will be deducted from source and added to destination</li>
                        <li>The source account serves as the source of income</li>
                    </ul>
                </div>

                <div id="transferSummary" style="display: none;">
                    <h6>Transfer Summary</h6>
                    <div class="border rounded p-3 bg-light">
                        <div class="mb-2">
                            <strong>From:</strong> <span id="summaryFromAccount">-</span>
                        </div>
                        <div class="mb-2">
                            <strong>To:</strong> <span id="summaryToAccount">-</span>
                        </div>
                        <div class="mb-2">
                            <strong>Amount:</strong> $<span id="summaryAmount">0.00</span>
                        </div>
                        <div>
                            <strong>Available Balance:</strong> $<span id="summaryBalance">0.00</span>
                        </div>
                    </div>
                </div>

                <hr>
                <h6>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/finance/account-recharge/history" class="btn btn-outline-info btn-sm">
                        <i class="ti ti-history me-1"></i>View Transaction History
                    </a>
                    <a href="<?= APP_URL ?>/finance/accounts" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-wallet me-1"></i>Manage Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Clear old form data after displaying
unset($_SESSION['old'], $_SESSION['errors']); 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromAccountSelect = document.getElementById('from_account_id');
    const toAccountSelect = document.getElementById('to_account_id');
    const amountInput = document.getElementById('amount');
    const transferSummary = document.getElementById('transferSummary');
    const form = document.getElementById('transferForm');

    // Update summary when form changes
    function updateSummary() {
        const fromAccount = fromAccountSelect.options[fromAccountSelect.selectedIndex];
        const toAccount = toAccountSelect.options[toAccountSelect.selectedIndex];
        const amount = amountInput.value;

        if (fromAccount.value && toAccount.value && amount) {
            document.getElementById('summaryFromAccount').textContent = fromAccount.text;
            document.getElementById('summaryToAccount').textContent = toAccount.text;
            document.getElementById('summaryAmount').textContent = parseFloat(amount).toFixed(2);
            
            const balance = fromAccount.dataset.balance || '0';
            document.getElementById('summaryBalance').textContent = parseFloat(balance).toFixed(2);
            
            transferSummary.style.display = 'block';
        } else {
            transferSummary.style.display = 'none';
        }
    }

    // Validate balance on amount input
    amountInput.addEventListener('input', function() {
        const fromAccount = fromAccountSelect.options[fromAccountSelect.selectedIndex];
        const balance = parseFloat(fromAccount.dataset.balance || '0');
        const amount = parseFloat(this.value || '0');

        if (amount > balance && fromAccount.value) {
            this.classList.add('is-invalid');
            this.setCustomValidity('Amount exceeds available balance');
        } else {
            this.classList.remove('is-invalid');
            this.setCustomValidity('');
        }
        
        updateSummary();
    });

    fromAccountSelect.addEventListener('change', updateSummary);
    toAccountSelect.addEventListener('change', updateSummary);

    // Form submission with loading state
    form.addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Processing...';
    });
});

function resetForm() {
    document.getElementById('transferForm').reset();
    document.getElementById('transferSummary').style.display = 'none';
}
</script>