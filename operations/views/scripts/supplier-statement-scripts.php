
<script>
// Supplier Statement initialization function
let supplierStatementRetryCount = 0;
const SUPPLIER_STATEMENT_MAX_RETRIES = 20; // Max 1 second of retries

function initSupplierStatement() {
    console.log('initSupplierStatement called');
    const BASE_URL = '<?= App::baseUrl() ?>';
    const LOC_ID = <?= (int)($_SESSION['loc_id'] ?? 0) ?>;

    const supplierSelect = $('#supplierSelect');
    const startDate = $('#startDate');
    const endDate = $('#endDate');
    const btnFetch = $('#btnFetch');
    const btnRefresh = $('#btnRefresh');
    const statementBody = $('#statementBody');
    const payableEl = $('#payable');
    const paidEl = $('#paid');
    const remainingEl = $('#remaining');

    console.log('Elements found:', {
        supplierSelect: supplierSelect.length,
        btnFetch: btnFetch.length,
        statementBody: statementBody.length
    });

    if (!supplierSelect.length || !btnFetch.length) {
        supplierStatementRetryCount++;
        if (supplierStatementRetryCount < SUPPLIER_STATEMENT_MAX_RETRIES) {
            console.log('Elements not ready, retrying... (' + supplierStatementRetryCount + '/' + SUPPLIER_STATEMENT_MAX_RETRIES + ')');
            setTimeout(initSupplierStatement, 50);
            return;
        } else {
            console.log('Max retries reached for supplier statement. Elements not found on this page.');
            return;
        }
    }

    // Reset retry count on successful initialization
    supplierStatementRetryCount = 0;
    console.log('Initializing supplier statement...');

    // Remove any existing event handlers to prevent duplicates
    btnFetch.off('click');
    btnRefresh.off('click');

    // Initialize chosen plugin
    if ($.fn.chosen) {
        if (supplierSelect.data('chosen')) {
            supplierSelect.chosen('destroy');
        }
        supplierSelect.chosen({ 
            width: '100%', 
            placeholder_text_single: '-- Select Supplier --' 
        });
    }

    function fetchStatement() {
        console.log('fetchStatement called');
        const supId = supplierSelect.val();
        console.log('Selected supplier ID:', supId);
        
        if (!supId) { 
            if (typeof showToast === 'function') {
                showToast('Please select a supplier first', 'error');
            } else {
                alert('Please select a supplier first');
            }
            return; 
        }

        const s = startDate.val() ? '&start=' + encodeURIComponent(startDate.val()) : '';
        const e = endDate.val() ? '&end=' + encodeURIComponent(endDate.val()) : '';
        const loc = LOC_ID ? '&loc_id=' + LOC_ID : '';
        
        const ajaxUrl = BASE_URL + '/_ikawa/stock/supplier-statement/' + supId + '?_=' + Date.now() + loc + s + e;
        console.log('AJAX URL:', ajaxUrl);
        console.log('Making request to backend...');

        // Show loading state
        if (typeof setButtonLoading === 'function') {
            setButtonLoading(btnFetch[0], true);
        } else {
            btnFetch.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        }
        statementBody.html('<tr><td colspan="10" class="text-center">Loading statement...</td></tr>');

        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            dataType: 'json',
            success: function(j) {
                console.log('AJAX Success - Full response:', j);
                if (!j.success) {
                    if (typeof showToast === 'function') {
                        showToast(j.message || 'Failed to load statement', 'error');
                    } else {
                        alert(j.message || 'Failed to load statement');
                    }
                    statementBody.html('<tr><td colspan="10" class="text-center text-danger">Failed to load data</td></tr>');
                    return; 
                }
                
                const res = j.data;
                payableEl.text(Number(res.payable || 0).toLocaleString() + ' RWF');
                paidEl.text(Number(res.paid || 0).toLocaleString() + ' RWF');
                remainingEl.text(Number(res.remaining || 0).toLocaleString() + ' RWF');

                // Show/hide payment button based on remaining amount
                updatePaymentButton(Number(res.remaining || 0));

                // Store unpaid items for payment processing
                unpaidItems = (res.rows || []).filter(row => {
                    const remaining = Number(row.total_price || 0) - Number(row.paid_amount || 0);
                    return remaining > 0;
                }).map(row => ({
                    stock_detail_id: row.stock_detail_id,
                    total_price: Number(row.total_price || 0),
                    paid_amount: Number(row.paid_amount || 0),
                    remaining: Number(row.total_price || 0) - Number(row.paid_amount || 0)
                }));

                statementBody.empty();
                
                if (!res.rows || res.rows.length === 0) {
                    statementBody.html('<tr><td colspan="10" class="text-center">No records found for this supplier</td></tr>');
                    if (typeof showToast === 'function') {
                        showToast('No records found', 'error');
                    }
                    return;
                }

                console.log('Adding rows to table:', res.rows.length);

                $.each(res.rows, function(idx, row) {
                    const dateStr = row.created_at || '';
                    const remaining = (Number(row.total_price || 0) - Number(row.paid_amount || 0));
                    
                    // Determine status badge based on remaining amount
                    let statusBadge;
                    if (remaining <= 0) {
                        statusBadge = '<span class="badge" style="background:#28a745;color:#fff;padding:4px 8px;">Cleared</span>';
                    } else if (Number(row.paid_amount || 0) > 0) {
                        statusBadge = '<span class="badge" style="background:#ffc107;color:#000;padding:4px 8px;">Partial</span>';
                    } else {
                        statusBadge = '<span class="badge" style="background:#dc3545;color:#fff;padding:4px 8px;">Unpaid</span>';
                    }
                    
                    const tr = $('<tr>').html(`
                        <td>${idx+1}</td>
                        <td>${dateStr}</td>
                        <td>${row.type_name || ''}</td>
                        <td>${row.unit_name || ''}</td>
                        <td>${row.quantity || ''}</td>
                        <td>${Number(row.unit_price || 0).toLocaleString()}</td>
                        <td>${Number(row.total_price || 0).toLocaleString()}</td>
                        <td>${Number(row.paid_amount || 0).toLocaleString()}</td>
                        <td>${remaining.toLocaleString()}</td>
                        <td>${statusBadge}</td>
                    `);
                    statementBody.append(tr);
                });

                console.log('Rows added to tbody. Total rows:', statementBody.find('tr').length);

                // Destroy DataTable if it exists
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#data-table-basic')) {
                    console.log('Destroying existing DataTable');
                    $('#data-table-basic').DataTable().destroy();
                }
                
                // Reinitialize DataTable
                console.log('Initializing DataTable');
                $('#data-table-basic').DataTable({ 
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });

                if (typeof showToast === 'function') {
                    showToast('Statement loaded successfully (' + res.rows.length + ' records)', 'success');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error occurred!');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response Status:', xhr.status);
                console.log('Response Text:', xhr.responseText);
                console.log('Full XHR object:', xhr);
                
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    msg = 'Server error: ' + xhr.responseText.substring(0, 100);
                }
                statementBody.html('<tr><td colspan="10" class="text-center text-danger">Error: ' + msg + '</td></tr>');
                if (typeof showToast === 'function') {
                    showToast('Failed to load statement: ' + msg, 'error');
                } else {
                    alert('Failed to load statement: ' + msg);
                }
            },
            complete: function() {
                if (typeof setButtonLoading === 'function') {
                    setButtonLoading(btnFetch[0], false);
                } else {
                    btnFetch.prop('disabled', false).html('<i class="fa fa-search"></i> Search Statement');
                }
            }
        });
    }

    btnFetch.on('click', function(e) {
        e.preventDefault();
        console.log('Search Statement button clicked');
        fetchStatement();
    });
    
    btnRefresh.on('click', function() {
        console.log('Refresh button clicked');
        location.reload(); 
    });

    console.log('Event handlers attached successfully');

    // Payment functionality
    let currentSupplierData = null;
    let availableAccounts = [];
    let unpaidItems = [];

    // Load accounts by location
    function loadAccounts() {
        $.ajax({
            url: BASE_URL + '/_ikawa/accounts/get-allbylocation?st_id=' + LOC_ID,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    availableAccounts = response.data;
                }
            },
            error: function() {
                console.warn('Failed to load accounts');
            }
        });
    }

    // Show payment button only if there's remaining amount
    function updatePaymentButton(remaining) {
        const btnPaySupplier = $('#btnPaySupplier');
        if (remaining > 0) {
            btnPaySupplier.show();
        } else {
            btnPaySupplier.hide();
        }
    }

    // Open payment modal
    $('#btnPaySupplier').on('click', function() {
        const supplierName = supplierSelect.find('option:selected').text();
        const remaining = parseFloat($('#remaining').text().replace(/[^0-9.-]+/g, ''));
        
        $('#paymentSupplierName').text(supplierName);
        $('#paymentTotalAmount').text(remaining.toLocaleString() + ' RWF');
        $('#paymentSupplierId').val(supplierSelect.val());
        
        $('#paymentUnpaidItems').val(JSON.stringify(unpaidItems));
        
        // Reset form
        $('#paymentAccountsContainer').empty();
        $('#paymentWarning').hide();
        addPaymentAccountRow();
        
        $('#paymentModal').modal('show');
    });

    // Add payment account row
    let accountRowCounter = 0;
    function addPaymentAccountRow() {
        accountRowCounter++;
        const rowHtml = `
            <div class="payment-account-row" data-row="${accountRowCounter}" style="margin-bottom:10px; padding:10px; border:1px solid #ddd; border-radius:4px;">
                <div class="row">
                    <div class="col-lg-6">
                        <label>Select Account</label>
                        <select class="form-control account-select" data-row="${accountRowCounter}" required>
                            <option value="">-- Select Account --</option>
                            ${availableAccounts.map(acc => `
                                <option value="${acc.acc_id}" data-balance="${acc.balance}">
                                    ${acc.acc_name} (Balance: ${Number(acc.balance).toLocaleString()} RWF)
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="col-lg-5">
                        <label>Amount from this Account</label>
                        <input type="number" class="form-control account-amount" data-row="${accountRowCounter}" 
                               placeholder="Amount" min="0" step="0.01" required>
                        <small class="text-muted">Available: <span class="available-balance-${accountRowCounter}">0</span> RWF</small>
                    </div>
                    <div class="col-lg-1">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-account" data-row="${accountRowCounter}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#paymentAccountsContainer').append(rowHtml);
    }

    // Account selection change
    $(document).on('change', '.account-select', function() {
        const row = $(this).data('row');
        const selectedOption = $(this).find('option:selected');
        const balance = selectedOption.data('balance') || 0;
        $(`.available-balance-${row}`).text(Number(balance).toLocaleString());
        
        // Set max for amount input
        $(`.account-amount[data-row="${row}"]`).attr('max', balance);
    });

    // Validate payment amounts
    $(document).on('input', '.account-amount', function() {
        validatePaymentAmounts();
    });

    function validatePaymentAmounts() {
        let totalFromAccounts = 0;
        let isValid = true;
        $('#paymentWarning').hide();

        $('.account-amount').each(function() {
            const amount = parseFloat($(this).val()) || 0;
            const row = $(this).data('row');
            const selectedAccount = $(`.account-select[data-row="${row}"]`);
            const balance = parseFloat(selectedAccount.find('option:selected').data('balance')) || 0;

            if (amount > balance) {
                isValid = false;
                $('#paymentWarning').html(`<i class="fa fa-exclamation-triangle"></i> Amount in one of the accounts exceeds available balance!`).show();
                return false;
            }

            totalFromAccounts += amount;
        });

        if (totalFromAccounts <= 0) {
            $('#paymentWarning').html(`<i class="fa fa-info-circle"></i> Please enter payment amounts`).show();
            isValid = false;
        }

        return isValid;
    }

    // Add another account button - remove existing handler to prevent duplicates
    $('#btnAddPaymentAccount').off('click').on('click', function() {
        addPaymentAccountRow();
    });

    // Remove account row
    $(document).on('click', '.btn-remove-account', function() {
        const row = $(this).data('row');
        $(`.payment-account-row[data-row="${row}"]`).remove();
        validatePaymentAmounts();
    });

    // Submit payment
    $('#btnSubmitPayment').off('click').on('click', function() {
        // Collect payment modes
        const paymentModes = [];
        let totalAmount = 0;
        
        $('.payment-account-row').each(function() {
            const row = $(this).data('row');
            const accId = $(`.account-select[data-row="${row}"]`).val();
            const amount = parseFloat($(`.account-amount[data-row="${row}"]`).val()) || 0;
            
            if (accId && amount > 0) {
                paymentModes.push({
                    acc_id: accId,
                    amount: amount
                });
                totalAmount += amount;
            }
        });

        if (paymentModes.length === 0) {
            showToast('Please select at least one payment account', 'error');
            return;
        }
        
        if (totalAmount <= 0) {
            showToast('Please enter payment amounts', 'error');
            return;
        }

        if (!validatePaymentAmounts()) {
            showToast('Please correct the errors in payment form', 'error');
            return;
        }

        const payload = {
            supplier_id: $('#paymentSupplierId').val(),
            amount: totalAmount,
            payment_modes: paymentModes,
            unpaid_items: JSON.parse($('#paymentUnpaidItems').val())
        };

        setButtonLoading($('#btnSubmitPayment')[0], true);

        $.ajax({
            url: BASE_URL + '/_ikawa/stock/process-supplier-payment',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function(response) {
                if (response.success) {
                    showToast(response.message || 'Payment processed successfully', 'success');
                    $('#paymentModal').modal('hide');
                    // Reload statement
                    setTimeout(function() {
                        $('#btnFetch').click();
                    }, 500);
                } else {
                    showToast(response.message || 'Payment failed', 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Failed to process payment';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'error');
            },
            complete: function() {
                setButtonLoading($('#btnSubmitPayment')[0], false);
            }
        });
    });

    // Load accounts on init
    loadAccounts();

    console.log('Supplier Statement initialized successfully');
}

// Execute immediately when script loads
if (typeof jQuery !== 'undefined') {
    if (document.readyState === 'loading') {
        $(document).ready(initSupplierStatement);
    } else {
        initSupplierStatement();
    }
}

// Expose globally for AJAX page loads
window.initSupplierStatement = initSupplierStatement;
</script>
