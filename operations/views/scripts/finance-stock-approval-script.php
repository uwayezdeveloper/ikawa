<script>
$(document).ready(function () {

    let PendingTable;

    // Initialize DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#pending-stock-table')) {
            $('#pending-stock-table').DataTable().destroy();
        }
        PendingTable = $('#pending-stock-table').DataTable({ 
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [0, 7, 11] }
            ]
        });
    }

    initDataTable();

    // Calculate total when unit price changes
    $(document).on('input', '.unit-price-input', function() {
        const $row = $(this).closest('tr');
        const quantity = parseFloat($(this).data('quantity')) || 0;
        const unitPrice = parseFloat($(this).val()) || 0;
        const total = quantity * unitPrice;
        
        $row.find('.total-price-cell').text(total.toLocaleString() + ' RWF');
        
        // Enable/disable approve button based on price
        const $approveBtn = $row.find('.approve-single-btn');
        if (unitPrice > 0) {
            $approveBtn.prop('disabled', false);
        } else {
            $approveBtn.prop('disabled', true);
        }
        
        updateApproveSelectedBtn();
    });

    // Select all checkbox
    $(document).on('change', '#selectAllPending', function() {
        $('.select-stock').prop('checked', $(this).is(':checked'));
        updateApproveSelectedBtn();
    });

    // Individual checkbox change
    $(document).on('change', '.select-stock', function() {
        updateApproveSelectedBtn();
        
        // Update select all checkbox
        const totalCheckboxes = $('.select-stock').length;
        const checkedCheckboxes = $('.select-stock:checked').length;
        $('#selectAllPending').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Update approve selected button state
    function updateApproveSelectedBtn() {
        let canApprove = false;
        
        $('.select-stock:checked').each(function() {
            const $row = $(this).closest('tr');
            const unitPrice = parseFloat($row.find('.unit-price-input').val()) || 0;
            if (unitPrice > 0) {
                canApprove = true;
            }
        });
        
        $('#approveSelectedBtn').prop('disabled', !canApprove);
    }

    // Approve single stock entry
    $(document).on('click', '.approve-single-btn', function() {
        const btn = this;
        const stockDetailId = $(this).data('id');
        const $row = $(this).closest('tr');
        const unitPrice = parseFloat($row.find('.unit-price-input').val()) || 0;

        if (unitPrice <= 0) {
            showToast('Please enter a valid unit price', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/stock/approve-price',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                stock_detail_id: stockDetailId,
                unit_price: unitPrice
            }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#pendingStockData tr').length === 0) {
                            $('#pendingStockData').html('<tr><td colspan="12" class="text-center">No pending stock entries</td></tr>');
                        }
                    });
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Something went wrong';
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

    // Approve selected stock entries
    $(document).on('click', '#approveSelectedBtn', function() {
        const btn = this;
        const items = [];

        $('.select-stock:checked').each(function() {
            const $row = $(this).closest('tr');
            const stockDetailId = $(this).data('id');
            const unitPrice = parseFloat($row.find('.unit-price-input').val()) || 0;

            if (unitPrice > 0) {
                items.push({
                    stock_detail_id: stockDetailId,
                    unit_price: unitPrice
                });
            }
        });

        if (items.length === 0) {
            showToast('Please enter valid prices for selected items', 'error');
            return;
        }

        if (!confirm('Are you sure you want to approve ' + items.length + ' stock item(s)?')) {
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/stock/approve-multiple',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ items: items }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadPendingStock();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Something went wrong';
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

    // Load pending stock
    function loadPendingStock() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/stock/get-pending', function(res) {
            if ($.fn.DataTable.isDataTable('#pending-stock-table')) {
                $('#pending-stock-table').DataTable().destroy();
            }
            
            $('#pendingStockData').empty();

            if (res.success && res.data && res.data.length > 0) {
                $.each(res.data, function(index, record) {
                    const recordedBy = (record.first_name || '') + ' ' + (record.last_name || '');
                    const row = `
                        <tr data-id="${record.stock_detail_id}">
                            <td><input type="checkbox" class="select-stock" data-id="${record.stock_detail_id}"></td>
                            <td>${index + 1}</td>
                            <td>${record.station_name || 'N/A'}</td>
                            <td>${record.type_name || 'N/A'}</td>
                            <td>${record.unit_name || 'N/A'}</td>
                            <td>${record.supplier_name || 'N/A'}</td>
                            <td class="quantity-cell">${record.quantity}</td>
                            <td>
                                <input type="number" step="0.01" class="form-control unit-price-input" 
                                       data-id="${record.stock_detail_id}" 
                                       data-quantity="${record.quantity}"
                                       placeholder="Enter price" style="width: 120px;">
                            </td>
                            <td class="total-price-cell">0 RWF</td>
                            <td>${recordedBy.trim()}</td>
                            <td>${new Date(record.created_at).toLocaleDateString()}</td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm approve-single-btn" 
                                        data-id="${record.stock_detail_id}" disabled>
                                    <i class="fa fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#pendingStockData').append(row);
                });
            } else {
                $('#pendingStockData').html('<tr><td colspan="12" class="text-center">No pending stock entries</td></tr>');
            }

            initDataTable();
            $('#selectAllPending').prop('checked', false);
            $('#approveSelectedBtn').prop('disabled', true);
        });
    }
});
</script>
