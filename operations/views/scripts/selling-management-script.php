<script>
$(function () {
    // Variables scoped to this module
    var cart = [];
    var availableStock = [];
    var paymentModes = [];
    var accounts = [];
    var clients = [];

    // Initialize - exposed globally for AJAX page loading
    window.initSellingPage = function() {
        console.log('initSellingPage called');
        if ($('#availableStockData').length > 0) {
            console.log('Found availableStockData, loading stock...');
            loadAvailableStock();
            loadPaymentModes();
            loadClients();
        } else {
            console.log('availableStockData element not found');
        }
    };

    // Load available stock for selling
    function loadAvailableStock() {
        console.log('Loading available stock...');
        console.log('Session user_id:', '<?= $_SESSION["user_id"] ?? "NOT_SET" ?>');
        console.log('Session loc_id:', '<?= $_SESSION["loc_id"] ?? "NOT_SET" ?>');
        $('#availableStockData').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/selling/get-available-stock',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                console.log('Stock response:', res);
                if (res.success && res.data && res.data.length > 0) {
                    availableStock = res.data;
                    renderAvailableStock();
                } else {
                    availableStock = [];
                    $('#availableStockData').html('<tr><td colspan="5" class="text-center">' + (res.message || 'No stock available') + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                var errorMsg = 'Error loading stock: ';
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg += (response.message || error);
                } catch(e) {
                    // If response is not JSON, show first part of HTML response
                    var htmlSnippet = xhr.responseText.substring(0, 200).replace(/<[^>]*>/g, ' ');
                    errorMsg += 'Server returned HTML instead of JSON. Check authentication.';
                    console.error('HTML Response:', htmlSnippet);
                }
                $('#availableStockData').html('<tr><td colspan="5" class="text-center text-danger">' + errorMsg + '</td></tr>');
            }
        });
    }

    // Render available stock table (without unit price column)
    function renderAvailableStock() {
        var tbody = $('#availableStockData');
        tbody.empty();

        if (!availableStock || availableStock.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center">No stock available</td></tr>');
            return;
        }

        var html = '';
        
        for (var i = 0; i < availableStock.length; i++) {
            var item = availableStock[i];
            var displayName = (item.type_name || 'N/A') + ' / ' + (item.unit_name || 'N/A');
            var availableQty = parseFloat(item.total_quantity) || 0;
            
            // Check if item is already in cart
            var inCartQty = 0;
            for (var j = 0; j < cart.length; j++) {
                if (cart[j].assignment_id == item.assignment_id) {
                    inCartQty = cart[j].quantity;
                    break;
                }
            }
            var remainingQty = availableQty - inCartQty;
            
            var stockJson = encodeURIComponent(JSON.stringify(item));
            var isDisabled = remainingQty <= 0 ? 'disabled' : '';

            html += '<tr data-assignment-id="' + item.assignment_id + '" data-stock-encoded="' + stockJson + '">' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + displayName + '</td>' +
                '<td><span class="available-qty">' + remainingQty.toFixed(2) + '</span></td>' +
                '<td><input type="number" step="0.01" min="0.01" max="' + remainingQty + '" class="form-control sell-qty-input" style="width:80px;" placeholder="0" ' + isDisabled + '></td>' +
                '<td><button class="btn btn-sm btn-primary add-to-cart-btn" ' + isDisabled + '><i class="fa fa-cart-plus"></i></button></td>' +
            '</tr>';
        }
        
        tbody.html(html);
    }

    // Add item to cart
    $(document).on('click', '.add-to-cart-btn', function() {
        var row = $(this).closest('tr');
        var encodedData = row.attr('data-stock-encoded');
        var qtyInput = row.find('.sell-qty-input');
        var qty = parseFloat(qtyInput.val()) || 0;

        if (!encodedData || qty <= 0) {
            showToast('Please enter a valid quantity!', 'error');
            return;
        }

        try {
            var stockData = JSON.parse(decodeURIComponent(encodedData));
            var availableQty = parseFloat(stockData.total_quantity) || 0;
            
            // Check if already in cart
            var existingIndex = -1;
            for (var i = 0; i < cart.length; i++) {
                if (cart[i].assignment_id == stockData.assignment_id) {
                    existingIndex = i;
                    break;
                }
            }
            var currentCartQty = existingIndex >= 0 ? cart[existingIndex].quantity : 0;
            
            if (qty + currentCartQty > availableQty) {
                showToast('Quantity exceeds available stock!', 'error');
                return;
            }

            if (existingIndex >= 0) {
                cart[existingIndex].quantity += qty;
            } else {
                cart.push({
                    assignment_id: stockData.assignment_id,
                    type_name: stockData.type_name,
                    unit_name: stockData.unit_name,
                    quantity: qty,
                    max_quantity: availableQty
                });
            }

            qtyInput.val('');
            renderCart();
            renderAvailableStock();
            showToast('Item added to cart!', 'success');
        } catch(e) {
            console.log('Parse error:', e);
            showToast('Error adding item to cart!', 'error');
        }
    });

    // Render cart (without price columns)
    function renderCart() {
        var tbody = $('#cartBody');
        tbody.empty();

        if (cart.length === 0) {
            tbody.html('<tr id="emptyCartRow"><td colspan="3" class="text-center text-muted">Cart is empty</td></tr>');
            $('#cartBadge').text('0');
            $('#clearCartBtn, #checkoutBtn').prop('disabled', true);
            return;
        }

        var html = '';
        var totalItems = 0;

        for (var i = 0; i < cart.length; i++) {
            var item = cart[i];
            var displayName = item.type_name + ' / ' + item.unit_name;
            totalItems += item.quantity;
            
            html += '<tr>' +
                '<td>' + displayName + '</td>' +
                '<td>' + item.quantity.toFixed(2) + '</td>' +
                '<td><button class="btn btn-xs btn-danger remove-cart-item" data-index="' + i + '"><i class="fa fa-times"></i></button></td>' +
            '</tr>';
        }

        tbody.html(html);
        $('#cartBadge').text(cart.length);
        $('#clearCartBtn, #checkoutBtn').prop('disabled', false);
    }

    // Remove item from cart
    $(document).on('click', '.remove-cart-item', function() {
        var index = $(this).data('index');
        cart.splice(index, 1);
        renderCart();
        renderAvailableStock();
    });

    // Clear cart
    $(document).on('click', '#clearCartBtn', function() {
        Swal.fire({
            title: 'Clear Cart?',
            text: 'Are you sure you want to clear all items from the cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
                renderAvailableStock();
                Swal.fire('Cleared!', 'Cart has been cleared.', 'success');
            }
        });
    });

    // Checkout - open payment modal
    $(document).on('click', '#checkoutBtn', function() {
        if (cart.length === 0) {
            showToast('Cart is empty!', 'error');
            return;
        }
        
        // Reset modal state
        $('input[name="has_price"][value="yes"]').prop('checked', true);
        $('#priceEntrySection').show();
        $('#pendingPriceSection').hide();
        $('#paymentDetailsSection').show();
        
        // Populate price entry table
        renderPriceEntryTable();
        
        // Populate pending items summary
        renderPendingItemsSummary();
        
        // Reset payment fields
        $('#payment_mode').val('');
        $('#payment_account').val('').prop('disabled', true);
        $('#payment_amount_received').val('');
        $('#payment_document').val('');
        $('#payment_client').val('').trigger('chosen:updated');
        $('#payment_notes').val('');
        
        $('#paymentModal').modal('show');
    });

    // Render price entry table in modal
    function renderPriceEntryTable() {
        var tbody = $('#priceEntryBody');
        var html = '';
        
        for (var i = 0; i < cart.length; i++) {
            var item = cart[i];
            var displayName = item.type_name + ' / ' + item.unit_name;
            
            html += '<tr data-index="' + i + '">' +
                '<td>' + displayName + '</td>' +
                '<td>' + item.quantity.toFixed(2) + '</td>' +
                '<td><input type="number" step="0.01" min="0" class="form-control price-input" data-index="' + i + '" placeholder="0"></td>' +
                '<td class="item-total">0 RWF</td>' +
            '</tr>';
        }
        
        tbody.html(html);
        calculateTotals();
    }

    // Render pending items summary
    function renderPendingItemsSummary() {
        var html = '<ul class="list-group">';
        
        for (var i = 0; i < cart.length; i++) {
            var item = cart[i];
            var displayName = item.type_name + ' / ' + item.unit_name;
            html += '<li class="list-group-item">' + displayName + ' - <strong>' + item.quantity.toFixed(2) + '</strong></li>';
        }
        
        html += '</ul>';
        $('#pendingItemsSummary').html(html);
    }

    // Calculate totals when price changes
    $(document).on('input', '.price-input', function() {
        calculateTotals();
    });

    function calculateTotals() {
        var grandTotal = 0;
        
        $('.price-input').each(function() {
            var index = $(this).data('index');
            var price = parseFloat($(this).val()) || 0;
            var quantity = cart[index].quantity;
            var total = price * quantity;
            
            $(this).closest('tr').find('.item-total').text(total.toLocaleString() + ' RWF');
            grandTotal += total;
        });
        
        $('#calculatedTotal').text(grandTotal.toLocaleString() + ' RWF');
        $('#payment_amount_received').val(grandTotal);
    }

    // Toggle price sections
    $(document).on('change', 'input[name="has_price"]', function() {
        var hasPrice = $(this).val() === 'yes';
        
        if (hasPrice) {
            $('#priceEntrySection').show();
            $('#pendingPriceSection').hide();
            $('#paymentDetailsSection').show();
        } else {
            $('#priceEntrySection').hide();
            $('#pendingPriceSection').show();
            $('#paymentDetailsSection').hide();
        }
    });

    // Load payment modes
    function loadPaymentModes() {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/paymentmodes',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    paymentModes = res.data;
                    var options = '<option value="">Select Payment Mode</option>';
                    for (var i = 0; i < paymentModes.length; i++) {
                        options += '<option value="' + paymentModes[i].Mode_id + '">' + paymentModes[i].Mode_names + '</option>';
                    }
                    $('#payment_mode').html(options);
                }
            }
        });
    }

    // Load clients
    function loadClients() {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/selling/get-clients',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    clients = res.data;
                    var options = '<option value="">-- Select Client (Optional) --</option>';
                    for (var i = 0; i < clients.length; i++) {
                        var clientInfo = clients[i].client_name;
                        if (clients[i].phone) clientInfo += ' (' + clients[i].phone + ')';
                        options += '<option value="' + clients[i].client_id + '">' + clientInfo + '</option>';
                    }
                    $('#payment_client').html(options);
                    
                    // Re-initialize chosen if exists
                    if ($('#payment_client').hasClass('chosen') && $.fn.chosen) {
                        $('#payment_client').trigger('chosen:updated');
                    }
                }
            }
        });
    }

    // Load accounts by payment mode
    $(document).on('change', '#payment_mode', function() {
        var modeId = $(this).val();
        var accountSelect = $('#payment_account');
        
        accountSelect.html('<option value="">Select Account</option>').prop('disabled', true);
        
        if (!modeId) return;

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/accounts/by-mode/' + modeId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data && res.data.length > 0) {
                    var options = '<option value="">Select Account</option>';
                    for (var i = 0; i < res.data.length; i++) {
                        options += '<option value="' + res.data[i].acc_id + '">' + res.data[i].acc_name + ' (' + res.data[i].acc_reference_num + ')</option>';
                    }
                    accountSelect.html(options).prop('disabled', false);
                } else {
                    accountSelect.html('<option value="">No accounts available</option>');
                }
            },
            error: function() {
                accountSelect.html('<option value="">Error loading accounts</option>');
            }
        });
    });

    // Save new client
    $(document).on('click', '#saveClientBtn', function() {
        var clientName = $('#new_client_name').val().trim();
        var phone = $('#new_client_phone').val().trim();
        var email = $('#new_client_email').val().trim();
        var address = $('#new_client_address').val().trim();
        
        if (!clientName) {
            showToast('Client name is required!', 'error');
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/selling/create-client',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                client_name: clientName,
                phone: phone || null,
                email: email || null,
                address: address || null
            }),
            success: function(res) {
                if (res.success) {
                    showToast('Client created successfully!', 'success');
                    $('#addClientModal').modal('hide');
                    
                    // Clear form
                    $('#new_client_name, #new_client_phone, #new_client_email, #new_client_address').val('');
                    
                    // Reload clients and select new one
                    loadClients();
                    setTimeout(function() {
                        $('#payment_client').val(res.data.client_id);
                        if ($('#payment_client').hasClass('chosen') && $.fn.chosen) {
                            $('#payment_client').trigger('chosen:updated');
                        }
                    }, 500);
                } else {
                    showToast(res.message || 'Failed to create client', 'error');
                }
            },
            error: function() {
                showToast('Error creating client!', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Client');
            }
        });
    });

    // Confirm payment/sale
    $(document).on('click', '#confirmPaymentBtn', function() {
        var hasPrice = $('input[name="has_price"]:checked').val() === 'yes';
        var clientId = $('#payment_client').val() || null;
        var notes = $('#payment_notes').val().trim();
        
        // Prepare items with prices if has_price
        var items = [];
        for (var i = 0; i < cart.length; i++) {
            var itemData = {
                assignment_id: cart[i].assignment_id,
                quantity: cart[i].quantity
            };
            
            if (hasPrice) {
                var priceInput = $('.price-input[data-index="' + i + '"]');
                var unitPrice = parseFloat(priceInput.val()) || 0;
                
                if (unitPrice <= 0) {
                    showToast('Please enter unit price for all items!', 'error');
                    return;
                }
                
                itemData.unit_price = unitPrice;
                itemData.total = unitPrice * cart[i].quantity;
            }
            
            items.push(itemData);
        }
        
        var saleData = {
            items: items,
            has_price: hasPrice,
            client_id: clientId,
            notes: notes
        };
        
        // Validate payment if has_price
        if (hasPrice) {
            var modeId = $('#payment_mode').val();
            var accId = $('#payment_account').val();
            var amount = parseFloat($('#payment_amount_received').val()) || 0;
            
            if (!modeId || !accId) {
                showToast('Please select payment mode and account!', 'error');
                return;
            }
            
            if (amount <= 0) {
                showToast('Please enter amount received!', 'error');
                return;
            }
            
            saleData.payment = {
                mode_id: modeId,
                acc_id: accId,
                amount: amount,
                document_ref: null,
                document_path: null
            };
            
            // Handle file upload if present
            var fileInput = $('#payment_document')[0];
            if (fileInput.files && fileInput.files[0]) {
                // For now, just note that file upload would need separate handling
                // In production, you'd upload file first then include path
                saleData.payment.document_ref = fileInput.files[0].name;
            }
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/selling/create-sale',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(saleData),
            success: function(res) {
                if (res.success) {
                    var message = hasPrice ? 
                        'Sale completed! Invoice: ' + res.data.invoice_no : 
                        'Sale recorded as pending price. Invoice: ' + res.data.invoice_no;
                    showToast(message, 'success');
                    
                    $('#paymentModal').modal('hide');
                    cart = [];
                    renderCart();
                    loadAvailableStock();
                } else {
                    showToast(res.message || 'Failed to record sale', 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Error recording sale!';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    msg = resp.message || msg;
                } catch(e) {}
                showToast(msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm Sale');
            }
        });
    });

    // Load sales history
    function loadSalesHistory() {
        console.log('Loading sales history...');
        $('#salesHistoryData').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/selling/get-sales-history',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                console.log('Sales history response:', res);
                var tbody = $('#salesHistoryData');
                
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var sale = res.data[i];
                        var clientInfo = sale.client_name || 'Walk-in';
                        if (sale.client_phone) clientInfo += '<br><small>' + sale.client_phone + '</small>';
                        
                        var amount = sale.price_status === 'pending' ? 
                            '<span class="text-warning">Pending</span>' : 
                            parseFloat(sale.total_amount).toLocaleString() + ' RWF';
                        
                        var statusClass = sale.price_status === 'pending' ? 'label-warning' : 'label-success';
                        var statusText = sale.price_status === 'pending' ? 'Pending Price' : 'Completed';
                        
                        html += '<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td><strong>' + sale.invoice_no + '</strong></td>' +
                            '<td>' + clientInfo + '</td>' +
                            '<td>' + sale.total_items + '</td>' +
                            '<td>' + amount + '</td>' +
                            '<td>' + (sale.mode_name || '-') + '</td>' +
                            '<td>' + new Date(sale.created_at).toLocaleDateString() + '</td>' +
                            '<td><span class="label ' + statusClass + '">' + statusText + '</span></td>' +
                            '<td>' +
                                '<button class="btn btn-xs btn-info view-sale-btn" data-id="' + sale.sale_id + '"><i class="fa fa-eye"></i></button>' +
                            '</td>' +
                        '</tr>';
                    }
                    tbody.html(html);
                } else {
                    tbody.html('<tr><td colspan="9" class="text-center">No sales found</td></tr>');
                }

                if ($.fn.DataTable.isDataTable('#sales-history-table')) {
                    $('#sales-history-table').DataTable().destroy();
                }
                $('#sales-history-table').DataTable({ pageLength: 10 });
            },
            error: function(xhr, status, error) {
                console.log('Sales history error:', status, error, xhr.responseText);
                $('#salesHistoryData').html('<tr><td colspan="9" class="text-center text-danger">Error loading sales: ' + error + '</td></tr>');
            }
        });
    }

    // Load sales history when modal opens - use document delegation for AJAX loaded content
    $(document).on('show.bs.modal', '#salesHistoryModal', function() {
        console.log('Sales History modal opening...');
        loadSalesHistory();
    });

    // Show notification using SweetAlert
    function showToast(message, type) {
        type = type || 'info';
        
        // Map type to SweetAlert icon
        var iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        var icon = iconMap[type] || 'info';
        
        // Check if Swal is available (SweetAlert2)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else if (typeof swal !== 'undefined') {
            // Try lowercase swal (older version)
            swal({
                title: message,
                icon: icon,
                timer: 3000,
                buttons: false
            });
        } else {
            // Fallback - use custom notification
            var bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';
            var toast = $('<div class="custom-toast" style="position:fixed;top:20px;right:20px;background:' + bgColor + ';color:#fff;padding:15px 25px;border-radius:5px;z-index:9999;box-shadow:0 4px 6px rgba(0,0,0,0.3);"><i class="fa fa-' + (type === 'success' ? 'check' : type === 'error' ? 'times' : 'info') + '"></i> ' + message + '</div>');
            $('body').append(toast);
            setTimeout(function() {
                toast.fadeOut(300, function() { $(this).remove(); });
            }, 3000);
        }
    }

    // Initialize if already on selling page (direct access)
    if ($('#availableStockData').length > 0) {
        window.initSellingPage();
    }
});
</script>
