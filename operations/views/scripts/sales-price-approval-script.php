<script>
// Expose init function globally BEFORE anything else
window.initPriceApprovalPage = function() {
    console.log('=== initPriceApprovalPage called ===');
    
    var pendingSalesData = document.getElementById('pendingSalesData');
    if (!pendingSalesData) {
        console.log('pendingSalesData element NOT found');
        return;
    }
    
    console.log('pendingSalesData element found, loading data...');
    loadPendingSales();
    loadPaymentModes();
};

function loadPendingSales() {
    console.log('Loading pending sales...');
    $('#pendingSalesData').html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    
    $.ajax({
        url: '/www.ikawa.rw/_ikawa/selling/get-pending-sales',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Pending sales response:', response);
            if (response.success && response.data && response.data.length > 0) {
                renderPendingSales(response.data);
            } else if (response.data && response.data.length === 0) {
                $('#pendingSalesData').html('<tr><td colspan="7" class="text-center"><i class="fa fa-check-circle text-success"></i> No pending sales found</td></tr>');
                $('#pendingCount').text('0');
            } else {
                $('#pendingSalesData').html('<tr><td colspan="7" class="text-center text-warning">' + (response.message || 'No data') + '</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading pending sales:', error);
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseText);
            $('#pendingSalesData').html('<tr><td colspan="7" class="text-center text-danger">Error loading: ' + error + '</td></tr>');
        }
    });
}

function renderPendingSales(sales) {
    console.log('Rendering', sales.length, 'pending sales');
    $('#pendingCount').text(sales.length);
    
    // Destroy existing DataTable if exists
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#pending-sales-table')) {
        $('#pending-sales-table').DataTable().destroy();
    }
    
    var tbody = $('#pendingSalesData');
    tbody.empty();
    
    for (var i = 0; i < sales.length; i++) {
        var sale = sales[i];
        var row = '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td><strong>' + (sale.invoice_no || 'N/A') + '</strong></td>' +
            '<td>' + (sale.client_name || sale.customer_name || 'Walk-in') + '</td>' +
            '<td><span class="badge badge-info">' + (sale.item_count || 0) + ' item(s)</span></td>' +
            '<td>' + formatDate(sale.created_at) + '</td>' +
            '<td>' + (sale.created_by_name || '-') + '</td>' +
            '<td><button class="btn btn-success btn-sm enter-prices-btn" data-sale-id="' + sale.sale_id + '"><i class="fa fa-edit"></i> Enter Prices</button></td>' +
            '</tr>';
        tbody.append(row);
    }
    
    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#pending-sales-table').DataTable({
            pageLength: 10,
            order: [[4, 'desc']]
        });
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        var date = new Date(dateStr);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    } catch(e) {
        return dateStr;
    }
}

function loadPaymentModes() {
    $.ajax({
        url: '/www.ikawa.rw/_ikawa/settings/paymentmodes',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Payment modes:', response);
            if (response.success && response.data) {
                var select = $('#price_payment_mode');
                select.html('<option value="">Select Payment Mode</option>');
                for (var i = 0; i < response.data.length; i++) {
                    var mode = response.data[i];
                    select.append('<option value="' + (mode.Mode_id || mode.mode_id) + '">' + (mode.Mode_names || mode.mode_name) + '</option>');
                }
            }
        }
    });
}

// Handle payment mode change
$(document).on('change', '#price_payment_mode', function() {
    var modeId = $(this).val();
    var accountSelect = $('#price_payment_account');
    
    accountSelect.html('<option value="">Select Account</option>').prop('disabled', true);
    
    if (!modeId) return;

    $.ajax({
        url: '/www.ikawa.rw/_ikawa/accounts/by-mode/' + modeId,
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            console.log('Accounts:', res);
            if (res.success && res.data && res.data.length > 0) {
                var options = '<option value="">Select Account</option>';
                for (var i = 0; i < res.data.length; i++) {
                    options += '<option value="' + res.data[i].acc_id + '">' + res.data[i].acc_name + '</option>';
                }
                accountSelect.html(options).prop('disabled', false);
            }
        }
    });
});

// Handle enter prices button
$(document).on('click', '.enter-prices-btn', function() {
    var saleId = $(this).data('sale-id');
    console.log('Opening price entry for sale:', saleId);
    
    $.ajax({
        url: '/www.ikawa.rw/_ikawa/selling/get-sale-details/' + saleId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Sale details:', response);
            if (response.success && response.data) {
                populatePriceModal(response.data);
                $('#priceEntryModal').modal('show');
            } else {
                alert('Failed to load sale details');
            }
        },
        error: function() {
            alert('Error loading sale details');
        }
    });
});

function populatePriceModal(sale) {
    $('#current_sale_id').val(sale.sale_id);
    $('#modal_invoice_no').text(sale.invoice_no || 'N/A');
    $('#modal_client_name').text(sale.client_name || sale.customer_name || 'Walk-in');
    $('#modal_sale_date').text(formatDate(sale.created_at));
    $('#modal_total_items').text((sale.items ? sale.items.length : 0) + ' item(s)');
    
    var tbody = $('#priceEntryItems');
    tbody.empty();
    
    if (!sale.items || sale.items.length === 0) {
        tbody.html('<tr><td colspan="5">No items</td></tr>');
        return;
    }
    
    for (var i = 0; i < sale.items.length; i++) {
        var item = sale.items[i];
        var row = '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + (item.stock_name || item.type_name || 'Item') + '</td>' +
            '<td>' + parseFloat(item.quantity).toFixed(2) + ' ' + (item.unity_name || item.unit_name || '') + '</td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control item-price-input" data-item-id="' + item.item_id + '" data-quantity="' + item.quantity + '" placeholder="Price"></td>' +
            '<td class="item-total-cell">0 RWF</td>' +
            '</tr>';
        tbody.append(row);
    }
    
    $('#price_payment_mode').val('');
    $('#price_payment_account').val('').prop('disabled', true);
    $('#price_amount_received').val('');
    $('#price_notes').val('');
    updateGrandTotal();
}

$(document).on('input', '.item-price-input', function() {
    var price = parseFloat($(this).val()) || 0;
    var qty = parseFloat($(this).data('quantity')) || 0;
    $(this).closest('tr').find('.item-total-cell').text((price * qty).toLocaleString() + ' RWF');
    updateGrandTotal();
});

function updateGrandTotal() {
    var total = 0;
    $('.item-price-input').each(function() {
        var price = parseFloat($(this).val()) || 0;
        var qty = parseFloat($(this).data('quantity')) || 0;
        total += price * qty;
    });
    $('#modal_grand_total').text(total.toLocaleString() + ' RWF');
    $('#price_amount_received').val(total);
}

$(document).on('click', '#savePricesBtn', function() {
    var saleId = $('#current_sale_id').val();
    var paymentMode = $('#price_payment_mode').val();
    var account = $('#price_payment_account').val();
    var amount = parseFloat($('#price_amount_received').val()) || 0;
    var notes = $('#price_notes').val();
    
    var items = [];
    var valid = true;
    var grandTotal = 0;
    
    $('.item-price-input').each(function() {
        var price = parseFloat($(this).val()) || 0;
        var qty = parseFloat($(this).data('quantity')) || 0;
        if (price <= 0) valid = false;
        items.push({
            item_id: $(this).data('item-id'),
            unit_price: price,
            total: price * qty
        });
        grandTotal += price * qty;
    });
    
    if (!valid) { alert('Enter all prices'); return; }
    if (!paymentMode) { alert('Select payment mode'); return; }
    if (!account) { alert('Select account'); return; }
    if (amount <= 0) { alert('Enter amount'); return; }
    
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: '/www.ikawa.rw/_ikawa/selling/update-sale-prices',
        method: 'POST',
        data: {
            sale_id: saleId,
            items: JSON.stringify(items),
            grand_total: grandTotal,
            payment_mode: paymentMode,
            account_id: account,
            amount_received: amount,
            notes: notes
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Saved!');
                $('#priceEntryModal').modal('hide');
                loadPendingSales();
            } else {
                alert(response.message || 'Failed');
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
            alert('Error saving');
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Prices & Complete Sale');
        }
    });
});

console.log('Sales Price Approval script loaded - initPriceApprovalPage is ready');
</script>
