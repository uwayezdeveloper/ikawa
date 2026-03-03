<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                    <li class="breadcrumb-item active">Global Transfer</li>
                </ol>
            </div>
            <h4 class="page-title">Global Transfer</h4>
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
    <?php unset($_SESSION['errors']['general']); ?>
<?php endif; ?>

<!-- Exchange Rate Request -->
<?php if (isset($_SESSION['exchange_rate_request'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="ti ti-currency-dollar me-2"></i>
        <strong>Exchange Rate Required:</strong> 
        Please enter the exchange rate from 
        <?= htmlspecialchars($_SESSION['exchange_rate_request']['from_currency']['name']) ?> 
        to 
        <?= htmlspecialchars($_SESSION['exchange_rate_request']['to_currency']['name']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
        // Auto-populate form when returning from exchange rate request
        document.addEventListener('DOMContentLoaded', function() {
            const requestData = <?= json_encode($_SESSION['exchange_rate_request']) ?>;
            if (requestData && requestData.transfer_data) {
                document.getElementById('from_account_id').value = requestData.transfer_data.from_account_id || '';
                document.getElementById('to_account_id').value = requestData.transfer_data.to_account_id || '';
                document.getElementById('amount').value = requestData.transfer_data.amount || '';
                document.getElementById('amount_single').value = requestData.transfer_data.amount || '';
                document.getElementById('description').value = requestData.transfer_data.description || '';
                
                // Trigger change event to update the UI
                document.getElementById('from_account_id').dispatchEvent(new Event('change'));
                document.getElementById('to_account_id').dispatchEvent(new Event('change'));
            }
        });
    </script>
    <?php unset($_SESSION['exchange_rate_request']); ?>
<?php endif; ?>

<!-- Main Content -->
<div class="row">
    <!-- Transfer Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-arrows-transfer-up me-2"></i>Global Account Transfer
                </h5>
                <p class="text-muted mb-0">Transfer money between any accounts in the system</p>
            </div>
            <div class="card-body">
                <?php if (empty($accounts ?? [])): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>No accounts available!</strong><br>
                        There are no active accounts in the system to transfer between.
                    </div>
                <?php else: ?>
                    <form id="globalTransferForm" action="<?= APP_URL ?>/finance/global-transfer" method="POST">
                        <div class="row">
                            <!-- From Account -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_account_id" class="form-label">
                                        From Account <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= isset($_SESSION['errors']['from_account_id']) ? 'is-invalid' : '' ?>" 
                                            id="from_account_id" name="from_account_id" required>
                                        <option value="">Select source account...</option>
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="<?= $account['id'] ?>" 
                                                    data-balance="<?= htmlspecialchars($account['balance'] ?? '0') ?>"
                                                    data-currency-id="<?= htmlspecialchars($account['currency_type_id'] ?? '') ?>"
                                                    data-currency-sign="<?= htmlspecialchars($account['currency_sign'] ?? '') ?>"
                                                    data-currency-name="<?= htmlspecialchars($account['currency_name'] ?? '') ?>"
                                                    <?= (($_SESSION['old']['from_account_id'] ?? '') == $account['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($account['account_name'] ?? 'Unnamed Account') ?> 
                                                (Balance: <?= number_format(floatval($account['balance'] ?? 0), 2) ?> <?= htmlspecialchars($account['currency_sign'] ?? $account['currency_name'] ?? 'N/A') ?>)
                                                <?php if (!empty($account['location_name'])): ?>
                                                    - <?= htmlspecialchars($account['location_name']) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($_SESSION['errors']['from_account_id'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($_SESSION['errors']['from_account_id']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="text-muted" id="fromAccountBalance" style="display: none;"></small>
                                </div>
                            </div>

                            <!-- To Account -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="to_account_id" class="form-label">
                                        To Account <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= isset($_SESSION['errors']['to_account_id']) ? 'is-invalid' : '' ?>" 
                                            id="to_account_id" name="to_account_id" required>
                                        <option value="">Select destination account...</option>
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="<?= $account['id'] ?>" 
                                                    data-currency-id="<?= htmlspecialchars($account['currency_type_id'] ?? '') ?>"
                                                    data-currency-sign="<?= htmlspecialchars($account['currency_sign'] ?? '') ?>"
                                                    data-currency-name="<?= htmlspecialchars($account['currency_name'] ?? '') ?>"
                                                    <?= (($_SESSION['old']['to_account_id'] ?? '') == $account['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($account['account_name'] ?? 'Unnamed Account') ?>
                                                (<?= htmlspecialchars($account['currency_name'] ?? 'N/A') ?>)
                                                <?php if (!empty($account['location_name'])): ?>
                                                    - <?= htmlspecialchars($account['location_name']) ?>
                                                <?php endif; ?>
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

                        <!-- Currency Conversion Alert -->
                        <div id="currencyConversionAlert" class="alert alert-warning" style="display: none;">
                            <div class="d-flex align-items-start">
                                <i class="ti ti-currency-dollar me-2 mt-1"></i>
                                <div>
                                    <strong>Currency Conversion Required</strong>
                                    <p id="conversionDetails" class="mb-2">Different currencies detected between source and destination accounts.</p>
                                    <div id="exchangeRateDisplay" style="display: none;">
                                        <strong>Exchange Rate:</strong> 1 <span id="fromCurrency">-</span> = <span id="exchangeRate">-</span> <span id="toCurrency">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Rate Error Alert -->
                        <div id="exchangeRateError" class="alert alert-danger" style="display: none;">
                            <div class="d-flex align-items-start">
                                <i class="ti ti-alert-triangle me-2 mt-1"></i>
                                <div>
                                    <strong>Exchange Rate Not Available</strong>
                                    <p id="exchangeRateErrorMessage" class="mb-0">Exchange rate setup required for this currency pair.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Rate Input Field -->
                        <div id="exchangeRateInputSection" class="mb-3" style="display: none;">
                            <div class="row">
                                <!-- Transfer Amount -->
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">
                                        Transfer Amount <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0.01" 
                                               class="form-control <?= isset($_SESSION['errors']['amount']) ? 'is-invalid' : '' ?>" 
                                               id="amount" name="amount" 
                                               value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>" 
                                               placeholder="0.00" required>
                                        <span class="input-group-text" id="amountCurrency">Currency</span>
                                        <?php if (isset($_SESSION['errors']['amount'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($_SESSION['errors']['amount']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Amount to be deducted from source account</small>
                                </div>
                                
                                <!-- Exchange Rate -->
                                <div class="col-md-6">
                                    <label for="exchange_rate" class="form-label">
                                        Exchange Rate <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">1 <span id="fromCurrencyInput">-</span> =</span>
                                        <input type="number" step="0.000001" min="0.000001" 
                                               class="form-control <?= isset($_SESSION['errors']['exchange_rate']) ? 'is-invalid' : '' ?>" 
                                               id="exchange_rate" name="exchange_rate" 
                                               value="<?= htmlspecialchars($_SESSION['old']['exchange_rate'] ?? '') ?>" 
                                               placeholder="0.000000">
                                        <span class="input-group-text"><span id="toCurrencyInput">-</span></span>
                                        <?php if (isset($_SESSION['errors']['exchange_rate'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($_SESSION['errors']['exchange_rate']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Current exchange rate between currencies</small>
                                </div>
                            </div>
                            
                            <!-- Conversion Result Display -->
                            <div id="conversionResultSection" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-calculator me-2"></i>
                                        <div>
                                            <strong>Conversion Result:</strong><br>
                                            <span id="deductionAmount">0.00</span> <span id="deductionCurrency">-</span> will be deducted from source account<br>
                                            <span id="receivedAmount">0.00</span> <span id="receivedCurrency">-</span> will be added to destination account
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Field for Same Currency -->
                        <div id="singleAmountSection" class="mb-3">
                            <label for="amount_single" class="form-label">
                                Transfer Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control <?= isset($_SESSION['errors']['amount']) ? 'is-invalid' : '' ?>" 
                                       id="amount_single" name="amount_single" 
                                       value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>" 
                                       placeholder="0.00" required>
                                <span class="input-group-text" id="amountCurrencySingle">Currency</span>
                                <?php if (isset($_SESSION['errors']['amount'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($_SESSION['errors']['amount']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Available balance will be shown when you select a source account</small>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Transfer Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?= isset($_SESSION['errors']['description']) ? 'is-invalid' : '' ?>" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the purpose of this global transfer..." required><?= htmlspecialchars($_SESSION['old']['description'] ?? '') ?></textarea>
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
                                <i class="ti ti-arrows-transfer-up me-1"></i>Process Global Transfer
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transfer Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2"></i>Transfer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Global Transfer Features:</h6>
                    <ul class="mb-0 small">
                        <li>Transfer between ANY accounts in the system</li>
                        <li>No location restrictions</li>
                        <li>Multi-currency support</li>
                        <li>Real-time balance validation</li>
                        <li>Automatic transaction logging</li>
                        <li>Full admin control</li>
                    </ul>
                </div>

                <div id="globalTransferSummary" style="display: none;">
                    <h6>Transfer Summary</h6>
                    <div class="border rounded p-3 bg-light">
                        <div class="mb-2">
                            <strong>From:</strong> <span id="summaryFromAccount">-</span>
                        </div>
                        <div class="mb-2">
                            <strong>To:</strong> <span id="summaryToAccount">-</span>
                        </div>
                        <div class="mb-2">
                            <strong>Amount:</strong> <span id="summaryAmount">0.00</span> <span id="summaryCurrency">N/A</span>
                        </div>
                        <div class="mb-2">
                            <strong>Available Balance:</strong> <span id="summaryBalance">0.00</span> <span id="summaryBalanceCurrency">N/A</span>
                        </div>
                        <div id="currencyWarning" class="alert alert-warning mt-2" style="display: none;">
                            <small><i class="ti ti-alert-triangle me-1"></i><strong>Note:</strong> Different currencies detected. Please verify exchange rates if applicable.</small>
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
                    <a href="<?= APP_URL ?>/finance/transfer-to-account" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-arrow-right me-1"></i>Location-Based Transfer
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
    const amountSingleInput = document.getElementById('amount_single');
    const amountCurrency = document.getElementById('amountCurrency');
    const amountCurrencySingle = document.getElementById('amountCurrencySingle');
    const transferSummary = document.getElementById('globalTransferSummary');
    const currencyWarning = document.getElementById('currencyWarning');
    const exchangeRateSection = document.getElementById('exchangeRateInputSection');
    const singleAmountSection = document.getElementById('singleAmountSection');
    const exchangeRateInput = document.getElementById('exchange_rate');
    const fromCurrencyInput = document.getElementById('fromCurrencyInput');
    const toCurrencyInput = document.getElementById('toCurrencyInput');
    const conversionResultSection = document.getElementById('conversionResultSection');
    const form = document.getElementById('globalTransferForm');

    let isDifferentCurrency = false;

    // Calculate and display conversion results
    function updateConversionResult() {
        const amount = parseFloat(amountInput.value || '0');
        const rate = parseFloat(exchangeRateInput.value || '0');
        
        if (amount > 0 && rate > 0 && isDifferentCurrency) {
            const convertedAmount = amount * rate;
            const fromAccount = fromAccountSelect.options[fromAccountSelect.selectedIndex];
            const toAccount = toAccountSelect.options[toAccountSelect.selectedIndex];
            
            if (fromAccount.value && toAccount.value) {
                const fromCurrency = fromAccount.dataset.currencySign || '-';
                const toCurrency = toAccount.dataset.currencySign || '-';
                
                document.getElementById('deductionAmount').textContent = amount.toFixed(2);
                document.getElementById('deductionCurrency').textContent = fromCurrency;
                document.getElementById('receivedAmount').textContent = convertedAmount.toFixed(2);
                document.getElementById('receivedCurrency').textContent = toCurrency;
                
                conversionResultSection.style.display = 'block';
            }
        } else {
            conversionResultSection.style.display = 'none';
        }
    }

    // Update currency display and summary
    function updateSummary() {
        const fromAccount = fromAccountSelect.options[fromAccountSelect.selectedIndex];
        const toAccount = toAccountSelect.options[toAccountSelect.selectedIndex];
        const currentAmount = isDifferentCurrency ? amountInput.value : amountSingleInput.value;

        // Update currency display for amount inputs
        if (fromAccount.value && fromAccount.dataset.currencySign) {
            amountCurrency.textContent = fromAccount.dataset.currencySign;
            amountCurrencySingle.textContent = fromAccount.dataset.currencySign;
        } else {
            amountCurrency.textContent = 'Currency';
            amountCurrencySingle.textContent = 'Currency';
        }

        // Check for currency mismatch immediately when accounts are selected
        if (fromAccount.value && toAccount.value) {
            const fromCurrency = fromAccount.dataset.currencySign || 'N/A';
            const toCurrency = toAccount.dataset.currencySign || 'N/A';
            
            // Handle currency conversion detection
            if (fromCurrency !== 'N/A' && toCurrency !== 'N/A' && fromCurrency !== toCurrency) {
                isDifferentCurrency = true;
                currencyWarning.style.display = 'block';
                exchangeRateSection.style.display = 'block';
                singleAmountSection.style.display = 'none';
                exchangeRateInput.required = true;
                amountInput.required = true;
                amountSingleInput.required = false;
                
                // Update exchange rate field labels
                fromCurrencyInput.textContent = fromCurrency;
                toCurrencyInput.textContent = toCurrency;
                
                // Try to get existing exchange rate via AJAX
                fetchExchangeRate(fromAccount.dataset.currencyId, toAccount.dataset.currencyId);
                
                // Update conversion result if both amount and rate are available
                updateConversionResult();
            } else {
                isDifferentCurrency = false;
                currencyWarning.style.display = 'none';
                exchangeRateSection.style.display = 'none';
                singleAmountSection.style.display = 'block';
                conversionResultSection.style.display = 'none';
                exchangeRateInput.required = false;
                exchangeRateInput.value = '';
                amountInput.required = false;
                amountSingleInput.required = true;
            }
        } else {
            // Hide currency conversion when accounts not selected
            currencyWarning.style.display = 'none';
            exchangeRateSection.style.display = 'none';
            singleAmountSection.style.display = 'block';
            conversionResultSection.style.display = 'none';
            exchangeRateInput.required = false;
            exchangeRateInput.value = '';
            amountInput.required = false;
            amountSingleInput.required = true;
            isDifferentCurrency = false;
        }

        // Update summary only when amount is provided
        if (fromAccount.value && toAccount.value && currentAmount) {
            // Update summary
            document.getElementById('summaryFromAccount').textContent = fromAccount.text.split(' (Balance:')[0];
            document.getElementById('summaryToAccount').textContent = toAccount.text.split(' (')[0];
            document.getElementById('summaryAmount').textContent = parseFloat(currentAmount).toFixed(2);
            
            const fromCurrency = fromAccount.dataset.currencySign || 'N/A';
            const toCurrency = toAccount.dataset.currencySign || 'N/A';
            const balance = fromAccount.dataset.balance || '0';
            
            document.getElementById('summaryCurrency').textContent = fromCurrency;
            document.getElementById('summaryBalance').textContent = parseFloat(balance).toFixed(2);
            document.getElementById('summaryBalanceCurrency').textContent = fromCurrency;
            
            transferSummary.style.display = 'block';
        } else {
            transferSummary.style.display = 'none';
        }
    }

    // Validate balance and prevent same account selection
    function validateForm() {
        const fromAccount = fromAccountSelect.options[fromAccountSelect.selectedIndex];
        const toAccount = toAccountSelect.options[toAccountSelect.selectedIndex];
        const balance = parseFloat(fromAccount.dataset.balance || '0');
        const currentAmountInput = isDifferentCurrency ? amountInput : amountSingleInput;
        const amount = parseFloat(currentAmountInput.value || '0');

        // Check same account
        if (fromAccount.value && toAccount.value && fromAccount.value === toAccount.value) {
            toAccountSelect.classList.add('is-invalid');
            toAccountSelect.setCustomValidity('Source and destination accounts cannot be the same');
            return false;
        } else {
            toAccountSelect.classList.remove('is-invalid');
            toAccountSelect.setCustomValidity('');
        }

        // Check balance
        if (amount > balance && fromAccount.value) {
            currentAmountInput.classList.add('is-invalid');
            currentAmountInput.setCustomValidity('Amount exceeds available balance');
            return false;
        } else {
            currentAmountInput.classList.remove('is-invalid');
            currentAmountInput.setCustomValidity('');
        }

        return true;
    }

    // Event listeners
    amountInput.addEventListener('input', function() {
        validateForm();
        updateSummary();
        updateConversionResult();
    });

    amountSingleInput.addEventListener('input', function() {
        validateForm();
        updateSummary();
    });

    exchangeRateInput.addEventListener('input', function() {
        updateConversionResult();
    });

    fromAccountSelect.addEventListener('change', function() {
        validateForm();
        updateSummary();
        
        // Filter out selected account from destination dropdown
        const selectedFromId = this.value;
        Array.from(toAccountSelect.options).forEach(option => {
            if (option.value === selectedFromId) {
                option.style.display = 'none';
                if (option.selected) {
                    toAccountSelect.selectedIndex = 0;
                }
            } else {
                option.style.display = 'block';
            }
        });
    });

    toAccountSelect.addEventListener('change', function() {
        validateForm();
        updateSummary();
    });

    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Ensure the correct amount field is used for submission
        if (isDifferentCurrency) {
            // Copy amount from currency conversion field
            amountSingleInput.value = amountInput.value;
            amountSingleInput.name = 'amount';
            amountInput.name = '';
        } else {
            // Use single amount field
            amountInput.name = '';
            amountSingleInput.name = 'amount';
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Processing...';
    });

    // Initialize
    updateSummary();

    // Function to fetch exchange rate via AJAX
    function fetchExchangeRate(fromCurrencyId, toCurrencyId) {
        if (!fromCurrencyId || !toCurrencyId || fromCurrencyId === toCurrencyId) {
            return;
        }

        fetch(`<?= APP_URL ?>/api/currency/exchange-rate?from_currency_id=${fromCurrencyId}&to_currency_id=${toCurrencyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.exchange_rate && data.data.exchange_rate !== 1.0) {
                    exchangeRateInput.value = data.data.exchange_rate;
                    exchangeRateInput.placeholder = `Current rate: ${data.data.exchange_rate}`;
                    // Update conversion result when rate is loaded
                    updateConversionResult();
                } else {
                    exchangeRateInput.value = '';
                    exchangeRateInput.placeholder = 'Enter exchange rate';
                }
            })
            .catch(error => {
                console.log('Exchange rate not available:', error);
                exchangeRateInput.value = '';
                exchangeRateInput.placeholder = 'Enter exchange rate';
            });
    }
});

function resetForm() {
    document.getElementById('globalTransferForm').reset();
    document.getElementById('globalTransferSummary').style.display = 'none';
    document.getElementById('currencyWarning').style.display = 'none';
    document.getElementById('exchangeRateInputSection').style.display = 'none';
    document.getElementById('singleAmountSection').style.display = 'block';
    document.getElementById('conversionResultSection').style.display = 'none';
    document.getElementById('amountCurrency').textContent = 'Currency';
    document.getElementById('amountCurrencySingle').textContent = 'Currency';
    
    // Reset exchange rate field
    const exchangeRateInput = document.getElementById('exchange_rate');
    const amountInput = document.getElementById('amount');
    const amountSingleInput = document.getElementById('amount_single');
    
    exchangeRateInput.value = '';
    exchangeRateInput.required = false;
    exchangeRateInput.placeholder = '0.000000';
    
    amountInput.value = '';
    amountInput.required = false;
    amountInput.name = '';
    
    amountSingleInput.value = '';
    amountSingleInput.required = true;
    amountSingleInput.name = 'amount';
    
    // Reset validation states
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.setCustomValidity('');
    });
    
    // Show all destination options again
    Array.from(document.getElementById('to_account_id').options).forEach(option => {
        option.style.display = 'block';
    });
}
</script>