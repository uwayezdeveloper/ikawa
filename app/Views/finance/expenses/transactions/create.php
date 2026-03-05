<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'New Expense Transaction') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/expense-transactions">Expense Transactions</a></li>
                    <li class="breadcrumb-item active">New Transaction</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-plus me-2"></i>New Expense Transaction
                </h5>
            </div>
            
            <div class="card-body">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <!-- Location Info -->
                <div class="alert alert-info">
                    <i class="ti ti-map-pin me-2"></i>
                    <strong>Transaction Location:</strong> Station ID <?= (int)($userLocationId ?? ($_SESSION['user']['location_id'] ?? 0)) ?> (User Location)
                </div>

                <!-- Form -->
                <form method="POST" action="<?= APP_URL ?>/finance/expense-transactions/store" id="transactionForm">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_id" class="form-label">Expense Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="expense_id" name="expense_id" required>
                                    <option value="">Select Expense Type</option>
                                    <?php foreach ($expenseTypes as $type): ?>
                                        <option value="<?= $type['expense_id'] ?>" 
                                                <?= ($_SESSION['old_input']['expense_id'] ?? '') == $type['expense_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['expense_name']) ?>
                                            <?php if ($type['categ_name']): ?>
                                                (<?= htmlspecialchars($type['categ_name']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="payer_name" class="form-label">Consumer</label>
                                <select class="form-control" id="payer_name" name="payer_name">
                                    <option value="">Select Consumer (Optional)</option>
                                    <?php foreach ($consumers as $consumer): ?>
                                        <option value="<?= $consumer['cons_id'] ?>" 
                                                data-phone="<?= htmlspecialchars($consumer['phone']) ?>"
                                                <?= ($_SESSION['old_input']['payer_name'] ?? '') == $consumer['cons_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($consumer['cons_name']) ?> - <?= htmlspecialchars($consumer['phone']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select the person/entity this expense is for</div>
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           placeholder="0.00" min="0" step="0.01" required
                                           value="<?= htmlspecialchars($_SESSION['old_input']['amount'] ?? '') ?>">
                                </div>
                                <div class="form-text">The main expense amount</div>
                            </div>

                            <div class="mb-3">
                                <label for="charges" class="form-label">Additional Charges</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="charges" name="charges" 
                                           placeholder="0.00" min="0" step="0.01"
                                           value="<?= htmlspecialchars($_SESSION['old_input']['charges'] ?? '') ?>">
                                </div>
                                <div class="form-text">Any additional charges (optional)</div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pay_mode" class="form-label">Payment Account <span class="text-danger">*</span></label>
                                <select class="form-control" id="pay_mode" name="pay_mode" required>
                                    <option value="">Select Payment Account</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>" 
                                                data-balance="<?= $account['balance'] ?? 0 ?>"
                                                <?= ($_SESSION['old_input']['pay_mode'] ?? '') == $account['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($account['account_name']) ?> 
                                            (Balance: $<?= number_format($account['balance'] ?? 0, 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select account to pay from</div>
                            </div>

                            <!-- Multi-Payment Section -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Multiple Payments</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="useMultiPayment" name="use_multi_payment" value="1">
                                        <label class="form-check-label" for="useMultiPayment">Split payment</label>
                                    </div>
                                </div>
                                <div class="form-text">Use multiple accounts if one doesn't have enough balance</div>
                                
                                <div id="multiPaymentSection" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                                    <h6 class="mb-3">Payment Distribution</h6>
                                    
                                    <div id="paymentRows">
                                        <!-- Payment rows will be added here dynamically -->
                                    </div>
                                    
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="addPaymentRow">
                                            <i class="ti ti-plus me-1"></i>Add Payment
                                        </button>
                                        <div>
                                            <strong>Total: <span id="totalPayments">$0.00</span></strong>
                                            <span id="paymentBalance" class="ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="receipt_type" class="form-label">Receipt Type</label>
                                <select class="form-control" id="receipt_type" name="receipt_type">
                                    <option value="">Select Receipt Type (Optional)</option>
                                    <?php foreach ($receiptTypes as $type): ?>
                                        <option value="<?= $type['rec_id'] ?>" 
                                                <?= ($_SESSION['old_input']['receipt_type'] ?? '') == $type['rec_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['rec_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="recorded_date" class="form-label">Transaction Date</label>
                                <input type="date" class="form-control" id="recorded_date" name="recorded_date" 
                                       value="<?= $_SESSION['old_input']['recorded_date'] ?? date('Y-m-d') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" placeholder="Enter transaction description (optional)" 
                                          maxlength="100"><?= htmlspecialchars($_SESSION['old_input']['description'] ?? '') ?></textarea>
                                <div class="form-text">Maximum 100 characters</div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="col-12">
                            <div class="card bg-light border-0 mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">Transaction Summary</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between">
                                                <span>Amount:</span>
                                                <strong id="summary-amount">$0.00</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between">
                                                <span>Charges:</span>
                                                <strong id="summary-charges">$0.00</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between border-top pt-2">
                                                <span><strong>Total:</strong></span>
                                                <strong class="text-primary" id="summary-total">$0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= APP_URL ?>/finance/expense-transactions" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Create Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Clear old input
unset($_SESSION['old_input']);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const chargesInput = document.getElementById('charges');
    const summaryAmount = document.getElementById('summary-amount');
    const summaryCharges = document.getElementById('summary-charges');
    const summaryTotal = document.getElementById('summary-total');
    const useMultiPaymentCheck = document.getElementById('useMultiPayment');
    const multiPaymentSection = document.getElementById('multiPaymentSection');
    const paymentRows = document.getElementById('paymentRows');
    const payModeSelect = document.getElementById('pay_mode');
    
    // Available accounts data
    const accounts = <?= json_encode($accounts) ?>;
    let paymentRowCounter = 0;
    let isMultiPaymentMode = false;
    
    // Update summary
    function updateSummary() {
        const amount = parseFloat(amountInput.value) || 0;
        const charges = parseFloat(chargesInput.value) || 0;
        const total = amount + charges;
        
        summaryAmount.textContent = '$' + amount.toFixed(2);
        summaryCharges.textContent = '$' + charges.toFixed(2);
        summaryTotal.textContent = '$' + total.toFixed(2);
        
        if (isMultiPaymentMode) {
            updatePaymentBalance();
        }
    }
    
    // Toggle multi-payment mode
    function toggleMultiPayment() {
        isMultiPaymentMode = useMultiPaymentCheck.checked;
        
        if (isMultiPaymentMode) {
            multiPaymentSection.style.display = 'block';
            payModeSelect.required = false;
            payModeSelect.disabled = true;
            addPaymentRow();
            addPaymentRow(); // Start with 2 rows
        } else {
            multiPaymentSection.style.display = 'none';
            payModeSelect.required = true;
            payModeSelect.disabled = false;
            paymentRows.innerHTML = '';
            paymentRowCounter = 0;
        }
    }
    
    // Add payment row
    function addPaymentRow() {
        paymentRowCounter++;
        const row = document.createElement('div');
        row.className = 'row payment-row mb-2';
        row.setAttribute('data-row', paymentRowCounter);

        row.innerHTML = `
            <div class="col-md-5">
                <select class="form-control payment-account" name="payment_accounts[]" required>
                    <option value="">Select Account</option>
                    ${accounts.map(account => 
                        `<option value="${account.id}" data-balance="${account.balance || 0}">
                            ${account.account_name} (Bal: $${Number(account.balance || 0).toFixed(2)})
                        </option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control payment-amount" 
                           name="payment_amounts[]" placeholder="0.00" 
                           min="0" step="0.01" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control payment-charges" 
                           name="payment_charges[]" placeholder="Charges" 
                           min="0" step="0.01">
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-payment-row">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        `;

        paymentRows.appendChild(row);

        // Add event listeners
        const accountSelect = row.querySelector('.payment-account');
        const amountInput = row.querySelector('.payment-amount');
        const chargesInput = row.querySelector('.payment-charges');
        const removeBtn = row.querySelector('.remove-payment-row');

        accountSelect.addEventListener('change', function() {
            updatePaymentBalance();
            validateAccountBalance(this, amountInput);
        });

        amountInput.addEventListener('input', function() {
            updatePaymentBalance();
            validateAccountBalance(accountSelect, this);
        });

        chargesInput.addEventListener('input', function() {
            updatePaymentBalance();
        });

        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.payment-row').length > 1) {
                row.remove();
                updatePaymentBalance();
            }
        });
    }
    
    // Validate account balance
    function validateAccountBalance(accountSelect, amountInput) {
        const selectedOption = accountSelect.options[accountSelect.selectedIndex];
        const accountBalance = parseFloat(selectedOption.getAttribute('data-balance') || 0);
        const paymentAmount = parseFloat(amountInput.value || 0);
        
        if (accountSelect.value && paymentAmount > accountBalance) {
            amountInput.classList.add('is-invalid');
            amountInput.title = `Insufficient balance. Available: $${accountBalance.toFixed(2)}`;
        } else {
            amountInput.classList.remove('is-invalid');
            amountInput.title = '';
        }
    }
    
    // Update payment balance
    function updatePaymentBalance() {
        if (!isMultiPaymentMode) return;
        
        let totalPayments = 0;
        let totalCharges = 0;
        const paymentAmounts = document.querySelectorAll('.payment-amount');
        const paymentCharges = document.querySelectorAll('.payment-charges');

        paymentAmounts.forEach(input => {
            totalPayments += parseFloat(input.value) || 0;
        });
        paymentCharges.forEach(input => {
            totalCharges += parseFloat(input.value) || 0;
        });

        const transactionAmount = parseFloat(amountInput.value) || 0;
        const charges = parseFloat(chargesInput.value) || 0;
        const totalRequired = transactionAmount + charges;
        const difference = totalRequired - totalPayments;

        document.getElementById('totalPayments').textContent = '$' + totalPayments.toFixed(2);

        // Optionally, you can show total charges for multi-payment mode
        // document.getElementById('totalCharges').textContent = '$' + totalCharges.toFixed(2);

        const balanceElement = document.getElementById('paymentBalance');
        if (Math.abs(difference) < 0.01) {
            balanceElement.innerHTML = '<span class="text-success"><i class="ti ti-check"></i> Balanced</span>';
        } else if (difference > 0) {
            balanceElement.innerHTML = '<span class="text-warning"><i class="ti ti-alert-triangle"></i> Short $' + difference.toFixed(2) + '</span>';
        } else {
            balanceElement.innerHTML = '<span class="text-danger"><i class="ti ti-alert-circle"></i> Over $' + Math.abs(difference).toFixed(2) + '</span>';
        }
    }
    
    // Event listeners
    amountInput.addEventListener('input', updateSummary);
    chargesInput.addEventListener('input', updateSummary);
    useMultiPaymentCheck.addEventListener('change', toggleMultiPayment);
    document.getElementById('addPaymentRow').addEventListener('click', addPaymentRow);
    
    // Initial update
    updateSummary();
    
    // Character counter for description
    const descriptionInput = document.getElementById('description');
    const descriptionCounter = document.createElement('small');
    descriptionCounter.className = 'form-text text-muted';
    descriptionInput.parentNode.appendChild(descriptionCounter);
    
    function updateDescriptionCounter() {
        const length = descriptionInput.value.length;
        descriptionCounter.textContent = `${length}/100 characters`;
        if (length > 90) {
            descriptionCounter.className = 'form-text text-warning';
        } else if (length === 100) {
            descriptionCounter.className = 'form-text text-danger';
        } else {
            descriptionCounter.className = 'form-text text-muted';
        }
    }
    
    descriptionInput.addEventListener('input', updateDescriptionCounter);
    updateDescriptionCounter();
    
    // Form validation
    document.getElementById('transactionForm').addEventListener('submit', function(e) {
        const expenseType = document.getElementById('expense_id').value;
        const amount = parseFloat(amountInput.value) || 0;
        const charges = parseFloat(chargesInput.value) || 0;
        const totalRequired = amount + charges;
        
        let hasError = false;
        
        if (!expenseType) {
            e.preventDefault();
            document.getElementById('expense_id').classList.add('is-invalid');
            if (!hasError) document.getElementById('expense_id').focus();
            hasError = true;
        }
        
        if (amount <= 0) {
            e.preventDefault();
            amountInput.classList.add('is-invalid');
            if (!hasError) amountInput.focus();
            hasError = true;
        }
        
        // Validate payment
        if (isMultiPaymentMode) {
            const paymentRows = document.querySelectorAll('.payment-row');
            let totalPayments = 0;
            let validPayments = 0;
            let hasInsufficientBalance = false;
            
            paymentRows.forEach(row => {
                const accountSelect = row.querySelector('.payment-account');
                const amountInput = row.querySelector('.payment-amount');
                const accountId = accountSelect.value;
                const paymentAmount = parseFloat(amountInput.value) || 0;
                
                if (accountId && paymentAmount > 0) {
                    validPayments++;
                    totalPayments += paymentAmount;
                    
                    // Check balance
                    const selectedOption = accountSelect.options[accountSelect.selectedIndex];
                    const accountBalance = parseFloat(selectedOption.getAttribute('data-balance') || 0);
                    if (paymentAmount > accountBalance) {
                        hasInsufficientBalance = true;
                        amountInput.classList.add('is-invalid');
                    }
                }
            });
            
            if (validPayments === 0) {
                e.preventDefault();
                alert('Please add at least one valid payment');
                hasError = true;
            } else if (hasInsufficientBalance) {
                e.preventDefault();
                alert('One or more accounts have insufficient balance');
                hasError = true;
            } else if (Math.abs(totalPayments - totalRequired) > 0.01) {
                e.preventDefault();
                alert(`Total payment amount ($${totalPayments.toFixed(2)}) must equal transaction amount ($${totalRequired.toFixed(2)})`);
                hasError = true;
            }
        } else {
            const payMode = payModeSelect.value;
            if (!payMode) {
                e.preventDefault();
                payModeSelect.classList.add('is-invalid');
                if (!hasError) payModeSelect.focus();
                hasError = true;
            } else {
                // Check single account balance
                const selectedOption = payModeSelect.options[payModeSelect.selectedIndex];
                const accountBalance = parseFloat(selectedOption.getAttribute('data-balance') || 0);
                if (totalRequired > accountBalance) {
                    e.preventDefault();
                    alert(`Insufficient balance in selected account. Available: $${accountBalance.toFixed(2)}, Required: $${totalRequired.toFixed(2)}`);
                    hasError = true;
                }
            }
        }
        
        if (hasError) {
            return false;
        }
        
        // Remove invalid classes
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        return true;
    });
    
    // Remove invalid class on change
    ['expense_id'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    payModeSelect.addEventListener('change', function() {
        this.classList.remove('is-invalid');
    });
    
    amountInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>