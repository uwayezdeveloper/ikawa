<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Pay Worker Loan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loan-payments">Loan Payments</a></li>
                    <li class="breadcrumb-item active">Pay Loan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Pay Worker Loan</h4>
                    <a href="<?= APP_URL ?>/finance/loan-payments" class="btn btn-secondary btn-sm">
                        <i class="ri-arrow-left-line"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($loans)): ?>
                        <div class="alert alert-warning text-center">
                            <i class="ri-alert-line"></i>
                            <h5 class="mt-3">No Loans Available for Payment</h5>
                            <p class="mb-3">There are currently no disbursed loans with remaining balance available for payment.</p>
                            <p class="text-muted"><strong>Note:</strong> Only loans with status "disbursed" and remaining balance can be paid.</p>
                            <a href="<?= APP_URL ?>/finance/loans" class="btn btn-primary">
                                <i class="ri-eye-line"></i> View All Loans
                            </a>
                        </div>
                    <?php else: ?>
                    <form id="payment-form" method="POST" action="<?= APP_URL ?>/finance/loan-payments/store">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="loan_id" class="form-label">Select Loan <span class="text-danger">*</span></label>
                                    <select class="form-select" id="loan_id" name="loan_id" required>
                                        <option value="">Select a loan...</option>
                                        <?php foreach ($loans as $loan): ?>
                                        <option value="<?= $loan['l_id'] ?>" 
                                                data-amount="<?= $loan['request_amount'] ?>"
                                                data-paid="<?= $loan['payed_amount'] ?>"
                                                data-remaining="<?= $loan['remaining_balance'] ?>"
                                                data-worker="<?= htmlspecialchars(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? '')) ?>">
                                            <?= htmlspecialchars(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? '')) ?> - 
                                            RWF <?= number_format($loan['request_amount']) ?> 
                                            (Remaining: RWF <?= number_format($loan['remaining_balance']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="payed_amount" class="form-label">Payment Amount (RWF) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="payed_amount" name="payed_amount" 
                                           min="1" step="0.01" required>
                                    <div class="form-text">
                                        <span id="remaining-balance" class="text-muted"></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="pay_account" class="form-label">Payment Account (Worker's Account) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pay_account" name="pay_account" 
                                           placeholder="Enter account number or ID used by worker" required>
                                    <div class="form-text">Account number/ID that the worker used to pay</div>
                                </div>

                                <div class="mb-3">
                                    <label for="debited_account" class="form-label">Receiving Account <span class="text-danger">*</span></label>
                                    <select class="form-select" id="debited_account" name="debited_account" required>
                                        <option value="">Select receiving account...</option>
                                        <?php foreach ($accounts as $account): ?>
                                        <option value="<?= $account['id'] ?>">
                                            <?= htmlspecialchars($account['account_name']) ?> 
                                            (<?= htmlspecialchars($account['account_number'] ?? '') ?>) - 
                                            Balance: RWF <?= number_format($account['balance'] ?? 0) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Account that will receive the payment</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div id="loan-details" class="mb-3" style="display: none;">
                                    <h6>Loan Details</h6>
                                    <div class="card border">
                                        <div class="card-body">
                                            <p><strong>Worker:</strong> <span id="worker-name"></span></p>
                                            <p><strong>Total Loan:</strong> RWF <span id="total-amount"></span></p>
                                            <p><strong>Already Paid:</strong> RWF <span id="paid-amount"></span></p>
                                            <p><strong>Remaining Balance:</strong> RWF <span id="remaining-amount" class="text-danger"></span></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Payment Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                              placeholder="Optional description for this payment"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= APP_URL ?>/finance/loan-payments" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary" id="submit-btn">
                                        <i class="ri-check-line"></i> Record Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle loan selection
    $('#loan_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const totalAmount = selectedOption.data('amount');
            const paidAmount = selectedOption.data('paid');
            const remainingAmount = selectedOption.data('remaining');
            const workerName = selectedOption.data('worker');
            
            $('#worker-name').text(workerName);
            $('#total-amount').text(new Intl.NumberFormat().format(totalAmount));
            $('#paid-amount').text(new Intl.NumberFormat().format(paidAmount));
            $('#remaining-amount').text(new Intl.NumberFormat().format(remainingAmount));
            $('#remaining-balance').text(`Maximum payment: RWF ${new Intl.NumberFormat().format(remainingAmount)}`);
            
            $('#payed_amount').attr('max', remainingAmount);
            $('#loan-details').show();
        } else {
            $('#loan-details').hide();
            $('#remaining-balance').text('');
            $('#payed_amount').removeAttr('max');
        }
    });

    // Handle form submission
    $('#payment-form').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $('#submit-btn');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="ri-loader-2-line spinning"></i> Processing...');
        
        $.ajax({
            url: '<?= APP_URL ?>/finance/loan-payments/store',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    
                    // Redirect after short delay
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        // Fallback redirect
                        setTimeout(() => {
                            window.location.href = '<?= APP_URL ?>/finance/loan-payments';
                        }, 1000);
                    }
                } else {
                    showAlert('error', response.message || 'An error occurred');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                showAlert('error', response.message || 'An error occurred while processing the payment');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.card-body').prepend(alertHtml);
    }
});
</script>

<style>
.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>