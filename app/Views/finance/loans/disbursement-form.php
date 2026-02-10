<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Disburse Loan #<?= htmlspecialchars($loan['l_id']) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loans/disbursement">Loan Disbursement</a></li>
                    <li class="breadcrumb-item active">Disburse Loan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Loan Details Card -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Loan Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Loan ID</label>
                        <div class="h5">#<?= htmlspecialchars($loan['l_id']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Employee</label>
                        <div><?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <div><?= htmlspecialchars($loan['email']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Requested Amount</label>
                        <div class="h4 text-primary">RWF <?= number_format($loan['request_amount']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <div><?= htmlspecialchars($loan['description']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="badge bg-warning">
                                <?= ucfirst(htmlspecialchars($loan['status'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disbursement Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Loan Disbursement Form</h5>
                </div>
                <div class="card-body">
                    <form action="<?= APP_URL ?>/finance/loans/disbursement/process" method="POST" id="disbursementForm">
                        <input type="hidden" name="loan_id" value="<?= htmlspecialchars($loan['l_id']) ?>">
                        
                        <!-- Worker Account Section -->
                        <div class="mb-4">
                            <label class="form-label">Worker Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="worker_account" 
                                   placeholder="Enter worker's account number where money will be sent" required>
                            <small class="text-muted">This is where the disbursed loan amount will be transferred</small>
                        </div>
                        
                        <!-- Payment Accounts Section -->
                        <div class="mb-4">
                            <h6 class="mb-3">Payment Accounts Distribution</h6>
                            <div id="payment-accounts">
                                <div class="row payment-account-row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Account</label>
                                        <select class="form-select account-select" name="account_ids[]" required>
                                            <option value="">Choose account...</option>
                                            <?php foreach ($accounts as $account): ?>
                                                <option value="<?= $account['id'] ?>" 
                                                        data-balance="<?= $account['balance'] ?>">
                                                    <?= htmlspecialchars($account['account_name']) ?> 
                                                    (Balance: RWF <?= number_format($account['balance']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Amount</label>
                                        <input type="number" class="form-control amount-input" 
                                               name="amounts[]" min="1" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger remove-account" disabled>
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary" id="add-account">
                                <i class="bx bx-plus"></i> Add Another Account
                            </button>
                        </div>

                        <!-- Summary Section -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Disbursement Summary</h6>
                                        <div class="d-flex justify-content-between">
                                            <span>Total Amount:</span>
                                            <span id="total-amount">RWF 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Required Amount:</span>
                                            <span class="text-primary">RWF <?= number_format($loan['request_amount']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Remaining:</span>
                                            <span id="remaining-amount" class="text-warning">RWF <?= number_format($loan['request_amount']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Processing Charges (Optional)</label>
                                <input type="number" class="form-control" name="charges" 
                                       min="0" value="0" id="charges-input">
                                <small class="text-muted">Any additional charges for processing the loan</small>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                <i class="bx bx-money-withdraw me-2"></i>Disburse Loan
                            </button>
                            <a href="<?= APP_URL ?>/finance/loans/disbursement" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requiredAmount = <?= $loan['request_amount'] ?>;
    let accountCounter = 1;

    function updateSummary() {
        let totalAmount = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            if (input.value) {
                totalAmount += parseFloat(input.value) || 0;
            }
        });

        document.getElementById('total-amount').textContent = 'RWF ' + totalAmount.toLocaleString();
        
        const remaining = requiredAmount - totalAmount;
        const remainingElement = document.getElementById('remaining-amount');
        remainingElement.textContent = 'RWF ' + remaining.toLocaleString();
        
        if (remaining === 0) {
            remainingElement.className = 'text-success';
            document.getElementById('submit-btn').disabled = false;
        } else if (remaining > 0) {
            remainingElement.className = 'text-warning';
            document.getElementById('submit-btn').disabled = true;
        } else {
            remainingElement.className = 'text-danger';
            document.getElementById('submit-btn').disabled = true;
        }
    }

    function addPaymentAccountRow() {
        const container = document.getElementById('payment-accounts');
        const newRow = document.querySelector('.payment-account-row').cloneNode(true);
        
        // Clear values
        newRow.querySelector('.account-select').selectedIndex = 0;
        newRow.querySelector('.amount-input').value = '';
        
        // Enable remove button
        const removeBtn = newRow.querySelector('.remove-account');
        removeBtn.disabled = false;
        
        container.appendChild(newRow);
        
        // Add event listeners to new row
        addEventListeners(newRow);
        updateRemoveButtons();
    }

    function addEventListeners(row) {
        const accountSelect = row.querySelector('.account-select');
        const amountInput = row.querySelector('.amount-input');
        const removeBtn = row.querySelector('.remove-account');

        accountSelect.addEventListener('change', function() {
            const balance = this.options[this.selectedIndex].dataset.balance || 0;
            amountInput.max = balance;
            updateSummary();
        });

        amountInput.addEventListener('input', updateSummary);

        removeBtn.addEventListener('click', function() {
            row.remove();
            updateRemoveButtons();
            updateSummary();
        });
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.payment-account-row');
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-account');
            removeBtn.disabled = rows.length <= 1;
        });
    }

    // Initialize event listeners
    document.querySelectorAll('.payment-account-row').forEach(addEventListeners);

    // Add account button
    document.getElementById('add-account').addEventListener('click', addPaymentAccountRow);

    // Form validation
    document.getElementById('disbursementForm').addEventListener('submit', function(e) {
        let totalAmount = 0;
        document.querySelectorAll('.amount-input').forEach(input => {
            totalAmount += parseFloat(input.value) || 0;
        });

        const workerAccount = document.querySelector('input[name="worker_account"]').value.trim();
        
        if (!workerAccount) {
            e.preventDefault();
            alert('Please enter the worker account number.');
            return false;
        }

        if (totalAmount !== requiredAmount) {
            e.preventDefault();
            alert('Total disbursement amount must equal the requested loan amount of RWF ' + requiredAmount.toLocaleString());
            return false;
        }
    });

    // Initialize summary
    updateSummary();
});
</script>