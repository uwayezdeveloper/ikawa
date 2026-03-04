<?php
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Withdraw</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/station-finances">Station Finances</a></li>
                <li class="breadcrumb-item active">Withdraw</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($errors['general']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transfer Between Accounts (Your Location)</h5>
                <small class="text-muted">
                    Location: <?= htmlspecialchars($location['name'] ?? 'N/A') ?>
                </small>
            </div>
            <div class="card-body">
                <?php if (empty($accounts)): ?>
                <div class="alert alert-warning mb-0">No active accounts found for your location.</div>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/finance/station-finances/withdraw">
                    <input type="hidden" name="withdraw_token" value="<?= htmlspecialchars((string)($withdrawToken ?? '')) ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="from_account_id" class="form-label">From Account <span class="text-danger">*</span></label>
                            <select name="from_account_id" id="from_account_id" class="form-select <?= isset($errors['from_account_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">Select source account...</option>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int)$account['id'] ?>" data-balance="<?= htmlspecialchars((string)($account['balance'] ?? 0)) ?>" <?= ((string)($old['from_account_id'] ?? '') === (string)$account['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account['account_name']) ?> (<?= number_format((float)($account['balance'] ?? 0), 2) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['from_account_id'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['from_account_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="to_account_id" class="form-label">To Account <span class="text-danger">*</span></label>
                            <select name="to_account_id" id="to_account_id" class="form-select <?= isset($errors['to_account_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">Select destination account...</option>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int)$account['id'] ?>" <?= ((string)($old['to_account_id'] ?? '') === (string)$account['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account['account_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['to_account_id'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['to_account_id']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars((string)($old['amount'] ?? '')) ?>" required>
                        <?php if (isset($errors['amount'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['amount']) ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Only available balance from selected source account can be transferred.</small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="3" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" required><?= htmlspecialchars((string)($old['description'] ?? '')) ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" id="processWithdrawBtn">Process Withdraw</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
unset($_SESSION['errors'], $_SESSION['old']);
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action$="/finance/station-finances/withdraw"]');
    if (!form) return;

    var fromSelect = document.getElementById('from_account_id');
    var amountInput = document.getElementById('amount');
    var submitBtn = document.getElementById('processWithdrawBtn');

    form.addEventListener('submit', function (e) {
        if (!fromSelect || !amountInput) return;

        var selected = fromSelect.options[fromSelect.selectedIndex];
        var balance = parseFloat((selected && selected.getAttribute('data-balance')) || '0') || 0;
        var amount = parseFloat(amountInput.value || '0') || 0;

        if (amount > balance) {
            e.preventDefault();
            alert('Amount exceeds source account balance.');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
        }
    });
});
</script>
