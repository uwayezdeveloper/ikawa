<script>
$(document).ready(function () {

    let TransferTable;
    let allStockData = [];
    let receiveRowCounter = 0;
    let categoriesData = [];
    let maxReceiveQuantity = 0;
    
    function initTransferTable() {
        if ($.fn.DataTable.isDataTable('#transfer-history-table')) {
            $('#transfer-history-table').DataTable().destroy();
        }
        TransferTable = $('#transfer-history-table').DataTable({
            pageLength: 10
        });
    }

    initTransferTable();

    function loadTransferHistory() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/transfers/get-all', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#transfer-history-table')) {
                $('#transfer-history-table').DataTable().destroy();
            }
            
            $('#transferHistoryData').empty();

            if (res.data && res.data.length > 0) {
                $.each(res.data, function (index, record) {
                    // Use detail_status for individual item status, fallback to tracking_status
                    var itemStatus = record.detail_status || record.tracking_status || 'pending';
                    
                    var statusClass = itemStatus === 'completed' ? 'label-success' : 
                                    (itemStatus === 'returned' ? 'label-danger' :
                                    (itemStatus === 'in_transit' ? 'label-info' :
                                    (itemStatus === 'pending' ? 'label-warning' : 'label-default')));
                    
                    // Only show approve button if status is pending
                    var approveBtn = itemStatus === 'pending' ? 
                        '<button class="btn btn-sm btn-success approve-transfer-btn" ' +
                            'data-id="' + record.tracking_id + '" ' +
                            'data-detail-id="' + record.transfer_detail_id + '" ' +
                            'data-ref="' + record.reference_no + '" ' +
                            'title="Approve This Item">' +
                            '<i class="fa fa-check"></i>' +
                        '</button> ' : '';
                    
                    // Only show receive button if status is in_transit (NOT completed)
                    var receiveBtn = (itemStatus === 'in_transit') ? 
                        '<button class="btn btn-sm btn-primary receive-production-btn" ' +
                            'data-id="' + record.tracking_id + '" ' +
                            'data-detail-id="' + record.transfer_detail_id + '" ' +
                            'data-ref="' + record.reference_no + '" ' +
                            'title="Receive from Production">' +
                            '<i class="fa fa-download"></i>' +
                        '</button> ' : '';
                    
                    // Only show return button if in_transit (not pending, returned, or completed)
                    var returnBtn = (itemStatus === 'in_transit') ? 
                        '<button class="btn btn-sm btn-warning return-transfer-btn" ' +
                            'data-id="' + record.tracking_id + '" ' +
                            'data-detail-id="' + record.transfer_detail_id + '" ' +
                            'data-ref="' + record.reference_no + '" ' +
                            'title="Return Transfer">' +
                            '<i class="fa fa-undo"></i>' +
                        '</button> ' : '';
                    
                    var statusLabel = itemStatus.charAt(0).toUpperCase() + itemStatus.slice(1).replace('_', ' ');
                    
                    var row = '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td><strong>' + record.reference_no + '</strong></td>' +
                        '<td>' + (record.type_name || 'N/A') + '</td>' +
                        '<td>' + (record.unit_name || 'N/A') + '</td>' +
                        '<td>' + (record.quantity || 0) + '</td>' +
                        '<td>' + new Date(record.transfer_date).toLocaleDateString() + '</td>' +
                        '<td><span class="label ' + statusClass + '">' + statusLabel + '</span></td>' +
                        '<td>' +
                            approveBtn +
                            receiveBtn +
                            returnBtn +
                            '<button class="btn btn-sm btn-info view-details-btn" data-id="' + record.tracking_id + '" data-ref="' + record.reference_no + '">' +
                                '<i class="fa fa-eye"></i>' +
                            '</button>' +
                        '</td>' +
                    '</tr>';
                    
                    $('#transferHistoryData').append(row);
                });
            } else {
                $('#transferHistoryData').html('<tr><td colspan="8" class="text-center">No transfers found</td></tr>');
            }

            TransferTable = $('#transfer-history-table').DataTable({
                pageLength: 10
            });
        });
    }

    // Load available stock (no supplier filter needed)
    function loadAvailableStock() {
        var tbody = document.getElementById('availableStockBody');
        
        if (!tbody) {
            console.log('availableStockBody not found in DOM');
            return;
        }
        
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Loading...</td></tr>';
        
        var apiUrl = '<?= App::baseUrl() ?>/_ikawa/transfers/get-available-stock';
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(res) {
                var tbody = document.getElementById('availableStockBody');
                if (!tbody) return;
                
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var item = res.data[i];
                        var stockJson = encodeURIComponent(JSON.stringify(item));
                        var displayName = (item.type_name || 'N/A') + ' / ' + (item.unit_name || 'N/A');
                        
                        html += '<tr data-stock-encoded="' + stockJson + '">' +
                            '<td><input type="checkbox" class="stock-checkbox"></td>' +
                            '<td>' + displayName + '</td>' +
                            '<td>' + item.total_quantity + '</td>' +
                            '<td><input type="number" step="0.01" class="form-control transfer-qty" max="' + item.total_quantity + '" placeholder="0"></td>' +
                        '</tr>';
                    }
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No stock available</td></tr>';
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', status, error);
                var tbody = document.getElementById('availableStockBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error: ' + error + '</td></tr>';
                }
            }
        });
    }

    // Load categories for receive modal
    function loadCategories() {
        return $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/categories/get-active-categories',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    categoriesData = res.data;
                }
            }
        });
    }

    // Generate category options HTML for receive modal
    function getCategoryOptionsForReceive() {
        let options = '<option value="">Select Category</option>';
        $.each(categoriesData, function(index, cat) {
            options += '<option value="' + cat.category_id + '">' + cat.category_name + '</option>';
        });
        return options;
    }

    // Add row to receive items table
    function addReceiveRow() {
        receiveRowCounter++;
        const rowHtml = `
            <tr id="receiveRow_${receiveRowCounter}" data-row="${receiveRowCounter}">
                <td>
                    <select class="form-control receive-category-select" data-row="${receiveRowCounter}" name="receive_category_${receiveRowCounter}">
                        ${getCategoryOptionsForReceive()}
                    </select>
                </td>
                <td>
                    <select class="form-control receive-type-unit-select" data-row="${receiveRowCounter}" name="receive_type_unit_${receiveRowCounter}" disabled>
                        <option value="">Select Type / Unity</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control receive-quantity" data-row="${receiveRowCounter}" name="receive_qty_${receiveRowCounter}" placeholder="0">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-receive-row-btn" data-row="${receiveRowCounter}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#receiveItemsBody').append(rowHtml);
        updateReceiveTotalDisplay();
    }

    // Calculate and display total receive quantity
    function calculateReceiveTotal() {
        let total = 0;
        $('#receiveItemsBody tr').each(function() {
            var rowId = $(this).data('row');
            var qty = parseFloat($('input[name="receive_qty_' + rowId + '"]').val()) || 0;
            total += qty;
        });
        return total;
    }

    // Update receive total display
    function updateReceiveTotalDisplay() {
        var total = calculateReceiveTotal();
        var remaining = maxReceiveQuantity - total;
        
        $('#receive_total_entering').text(total.toFixed(2));
        $('#receive_remaining').text(remaining.toFixed(2));
        
        if (total > maxReceiveQuantity) {
            $('#receive_total_entering').addClass('text-danger').removeClass('text-success');
            $('#receive_remaining').addClass('text-danger').removeClass('text-success');
        } else {
            $('#receive_total_entering').removeClass('text-danger').addClass('text-success');
            $('#receive_remaining').removeClass('text-danger').addClass('text-success');
        }
    }

    $(document).on('input change', '.receive-quantity', function() {
        updateReceiveTotalDisplay();
    });

    $(document).on('change', '.receive-category-select', function() {
        const rowId = $(this).data('row');
        const categoryId = $(this).val();
        const typeUnitSelect = $('select[name="receive_type_unit_' + rowId + '"]');

        typeUnitSelect.html('<option value="">Select Type / Unity</option>').prop('disabled', true);

        if (!categoryId) return;

        typeUnitSelect.html('<option value="">Loading...</option>');

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-type-units/get-type-unity-by-category/' + categoryId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                let options = '<option value="">Select Type / Unity</option>';
                if (res.success && res.data && res.data.length > 0) {
                    $.each(res.data, function(index, item) {
                        const value = JSON.stringify({type_id: item.type_id, unit_id: item.unit_id});
                        options += '<option value=\'' + value + '\'>' + item.type_unit_name + '</option>';
                    });
                    typeUnitSelect.html(options).prop('disabled', false);
                } else {
                    typeUnitSelect.html('<option value="">No types available</option>').prop('disabled', true);
                }
            },
            error: function() {
                typeUnitSelect.html('<option value="">Error loading</option>').prop('disabled', true);
            }
        });
    });

    $(document).on('click', '.remove-receive-row-btn', function() {
        const rowId = $(this).data('row');
        $('#receiveRow_' + rowId).remove();
        
        if ($('#receiveItemsBody tr').length === 0) {
            addReceiveRow();
        }
        updateReceiveTotalDisplay();
    });

    $(document).on('click', '#addReceiveRowBtn', function() {
        addReceiveRow();
    });

    // Load stock when transfer modal opens (no supplier selection needed)
    $(document).on('show.bs.modal', '#transferModal', function() {
        console.log('Transfer modal opening...');
        $('#transfer_date').val('<?= date('Y-m-d') ?>');
        $('#transfer_notes').val('');
        loadAvailableStock();
    });

    $(document).on('change', '#selectAllStock', function() {
        $('.stock-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Save transfer (no supplier needed)
    $(document).on('click', '#saveTransferBtn', function() {
        var btn = this;
        var transfer_date = $('#transfer_date').val();
        var notes = $('#transfer_notes').val();

        if (!transfer_date) {
            showToast('Please select transfer date!', 'error');
            return;
        }

        var items = [];
        var hasError = false;
        
        $('#availableStockBody tr').each(function() {
            var checkbox = $(this).find('.stock-checkbox');
            var qtyInput = $(this).find('.transfer-qty');
            
            if (checkbox.is(':checked')) {
                var qty = parseFloat(qtyInput.val()) || 0;
                var maxQty = parseFloat(qtyInput.attr('max')) || 0;
                
                if (qty > 0 && qty <= maxQty) {
                    var encodedData = $(this).attr('data-stock-encoded');
                    if (encodedData) {
                        try {
                            var stockData = JSON.parse(decodeURIComponent(encodedData));
                            items.push({
                                assignment_id: stockData.assignment_id,
                                quantity: qty
                            });
                        } catch(e) {
                            console.log('Parse error:', e);
                        }
                    }
                } else if (qty > maxQty) {
                    showToast('Transfer quantity cannot exceed available quantity!', 'error');
                    hasError = true;
                    return false;
                }
            }
        });

        if (hasError) return;

        if (items.length === 0) {
            showToast('Please select items and enter valid quantities to transfer!', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/create-multiple',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                transfer_date: transfer_date,
                notes: notes,
                items: items
            }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message + ' Ref: ' + response.data.reference_no, 'success');
                    $('#transferModal').modal('hide');
                    loadTransferHistory();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Error creating transfer';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'error');
            },
            complete: function() {
                setButtonLoading(btn, false);
            }
        });
    });

    // View transfer details (updated without supplier)
    $(document).on('click', '.view-details-btn', function() {
        var tracking_id = $(this).data('id');
        var ref_no = $(this).data('ref');
        
        $('#detailRefNo').text(ref_no);
        $('#transferDetailsBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#viewDetailsModal').modal('show');

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/get-details/' + tracking_id,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                var tbody = document.getElementById('transferDetailsBody');
                if (!tbody) return;
                
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var item = res.data[i];
                        html += '<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + (item.type_name || 'N/A') + '</td>' +
                            '<td>' + (item.unit_name || 'N/A') + '</td>' +
                            '<td>' + item.quantity + '</td>' +
                        '</tr>';
                    }
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No details found</td></tr>';
                }
            },
            error: function() {
                var tbody = document.getElementById('transferDetailsBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading details</td></tr>';
                }
            }
        });
    });

    // Approve individual transfer item by transfer_detail_id
    $(document).on('click', '.approve-transfer-btn', function() {
        var btn = this;
        var tracking_id = $(this).data('id');
        var detail_id = $(this).data('detail-id');
        var ref_no = $(this).data('ref');
        
        swal({   
            title: "Approve This Item?",   
            text: "Are you sure you want to approve this item from transfer " + ref_no + "?",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonText: "Yes, approve it!",
            cancelButtonText: "No, cancel!"
        }).then(function(isConfirm){
            if (isConfirm) {
                setButtonLoading(btn, true);

                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/transfers/approve',
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({ transfer_detail_id: detail_id }),
                    success: function(response) {
                        if (response.success) {
                            swal("Approved!", response.message, "success");
                            loadTransferHistory();
                        } else {
                            swal("Error!", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Error approving transfer';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) msg = response.message;
                        } catch(e) {}
                        swal("Error!", msg, "error");
                    },
                    complete: function() {
                        setButtonLoading(btn, false);
                    }
                });
            }
        });
    });

    // Return transfer (updated without supplier)
    $(document).on('click', '.return-transfer-btn', function() {
        var tracking_id = $(this).data('id');
        var ref_no = $(this).data('ref');
        
        $('#returnRefNo').text(ref_no);
        $('#returnItemsBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#return_date').val('<?= date('Y-m-d') ?>');
        $('#return_reason').val('');
        $('#returnTransferModal').modal('show');

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/get-details/' + tracking_id,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                var tbody = document.getElementById('returnItemsBody');
                if (!tbody) return;
                
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var item = res.data[i];
                        var displayName = (item.type_name || 'N/A') + ' / ' + (item.unit_name || 'N/A');
                        
                        html += '<tr data-detail-id="' + item.transfer_detail_id + '">' +
                            '<td><input type="checkbox" class="return-checkbox"></td>' +
                            '<td>' + displayName + '</td>' +
                            '<td>' + item.quantity + '</td>' +
                            '<td><input type="number" step="0.01" class="form-control return-qty" max="' + item.quantity + '" placeholder="0" style="width:100px;"></td>' +
                        '</tr>';
                    }
                    tbody.innerHTML = html;
                    $('#returnTransferModal').data('tracking-id', tracking_id);
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No details found</td></tr>';
                }
            }
        });
    });

    $(document).on('change', '#selectAllReturn', function() {
        $('.return-checkbox').prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '#saveReturnBtn', function() {
        var btn = this;
        var tracking_id = $('#returnTransferModal').data('tracking-id');
        var return_date = $('#return_date').val();
        var return_reason = $('#return_reason').val().trim();

        if (!tracking_id) {
            showToast('Invalid tracking ID!', 'error');
            return;
        }

        if (!return_date) {
            showToast('Please select return date!', 'error');
            return;
        }

        if (!return_reason) {
            showToast('Please enter return reason!', 'error');
            return;
        }

        var items = [];
        var hasError = false;
        
        $('#returnItemsBody tr').each(function() {
            var checkbox = $(this).find('.return-checkbox');
            var qtyInput = $(this).find('.return-qty');
            
            if (checkbox.is(':checked')) {
                var qty = parseFloat(qtyInput.val()) || 0;
                var maxQty = parseFloat(qtyInput.attr('max')) || 0;
                var detail_id = $(this).data('detail-id');
                
                if (qty > 0 && qty <= maxQty) {
                    items.push({
                        detail_id: detail_id,
                        return_quantity: qty
                    });
                } else if (qty > maxQty) {
                    showToast('Return quantity cannot exceed transferred quantity!', 'error');
                    hasError = true;
                    return false;
                }
            }
        });

        if (hasError) return;

        if (items.length === 0) {
            showToast('Please select items and enter valid quantities to return!', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/return',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                tracking_id: tracking_id,
                return_date: return_date,
                return_reason: return_reason,
                items: items
            }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#returnTransferModal').modal('hide');
                    loadTransferHistory();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Error returning transfer';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) msg = response.message;
                } catch(e) {}
                showToast(msg, 'error');
            },
            complete: function() {
                setButtonLoading(btn, false);
            }
        });
    });

    // Receive from Production - use specific detail item
    $(document).on('click', '.receive-production-btn', function() {
        var tracking_id = $(this).data('id');
        var detail_id = $(this).data('detail-id');
        var ref_no = $(this).data('ref');
        
        $('#receiveRefNo').text(ref_no);
        $('#receive_date').val('<?= date('Y-m-d') ?>');
        $('#receive_notes').val('');
        $('#receiveItemsBody').empty();
        receiveRowCounter = 0;
        maxReceiveQuantity = 0;
        
        $('#receiveProductionModal').data('tracking-id', tracking_id);
        $('#receiveProductionModal').data('detail-id', detail_id);
        
        // Get specific detail info
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/get-detail-info/' + detail_id,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    var item = res.data;
                    var typeUnitDisplay = (item.type_name || 'N/A') + ' / ' + (item.unit_name || 'N/A');
                    
                    $('#receive_original_type').val(typeUnitDisplay);
                    $('#receive_total_qty').val(item.quantity || 0);
                    
                    // Calculate available to receive for this specific item
                    var sentQty = parseFloat(item.quantity) || 0;
                    var receivedQty = parseFloat(item.received_quantity) || 0;
                    maxReceiveQuantity = sentQty - receivedQty;
                    
                    $('#receive_max_qty').text(maxReceiveQuantity.toFixed(2));
                    updateReceiveTotalDisplay();
                }
            },
            error: function() {
                // Fallback to old behavior if endpoint doesn't exist
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/transfers/get-details/' + tracking_id,
                    method: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success && res.data && res.data.length > 0) {
                            // Find the specific detail
                            var item = res.data.find(function(d) { return d.transfer_detail_id == detail_id; });
                            if (!item) item = res.data[0];
                            
                            var typeUnitDisplay = (item.type_name || 'N/A') + ' / ' + (item.unit_name || 'N/A');
                            
                            $('#receive_original_type').val(typeUnitDisplay);
                            $('#receive_total_qty').val(item.quantity || 0);
                            
                            maxReceiveQuantity = parseFloat(item.quantity) || 0;
                            $('#receive_max_qty').text(maxReceiveQuantity.toFixed(2));
                            updateReceiveTotalDisplay();
                        }
                    }
                });
            }
        });
        
        loadCategories().done(function() {
            addReceiveRow();
        });
        
        $('#receiveProductionModal').modal('show');
    });

    // Save received items - use specific detail
    $(document).on('click', '#saveReceiveBtn', function() {
        var btn = this;
        var tracking_id = $('#receiveProductionModal').data('tracking-id');
        var detail_id = $('#receiveProductionModal').data('detail-id');
        var receive_date = $('#receive_date').val();
        var notes = $('#receive_notes').val();

        if (!tracking_id) {
            showToast('Invalid tracking ID!', 'error');
            return;
        }

        if (!receive_date) {
            showToast('Please select receive date!', 'error');
            return;
        }

        var items = [];
        var hasError = false;
        var totalReceiving = 0;

        $('#receiveItemsBody tr').each(function() {
            var rowId = $(this).data('row');
            var typeUnitValue = $('select[name="receive_type_unit_' + rowId + '"]').val();
            var quantity = parseFloat($('input[name="receive_qty_' + rowId + '"]').val()) || 0;

            if (!typeUnitValue || quantity <= 0) {
                hasError = true;
                $(this).addClass('danger');
                return;
            } else {
                $(this).removeClass('danger');
            }

            totalReceiving += quantity;

            try {
                var parsed = JSON.parse(typeUnitValue);
                items.push({
                    type_id: parsed.type_id,
                    unit_id: parsed.unit_id,
                    quantity: quantity
                });
            } catch(e) {
                hasError = true;
            }
        });

        if (hasError || items.length === 0) {
            showToast('Please fill all required fields in each row!', 'error');
            return;
        }

        if (totalReceiving > maxReceiveQuantity) {
            showToast('Total receive quantity (' + totalReceiving.toFixed(2) + ') exceeds available quantity (' + maxReceiveQuantity.toFixed(2) + ')!', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/receive-production',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                tracking_id: tracking_id,
                transfer_detail_id: detail_id,
                receive_date: receive_date,
                notes: notes,
                items: items
            }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#receiveProductionModal').modal('hide');
                    loadTransferHistory();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Error receiving production';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) msg = response.message;
                } catch(e) {}
                showToast(msg, 'error');
            },
            complete: function() {
                setButtonLoading(btn, false);
            }
        });
    });

    // Load transfer history on page load
    loadTransferHistory();
});
</script>
