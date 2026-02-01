<script>
$(document).ready(function () {

    let rowCounter = 0;
    let categoryTypes = [];
    let stockQuantities = {}; // Track original max quantity per assignment_id

    // Load category types from stock at user's station
    function loadCategoryTypes(categoryId, callback) {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/stock-types/' + categoryId,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    categoryTypes = res.data || [];
                    updateAllCategoryTypeSelects();
                    if (callback) callback(categoryTypes);
                    
                    if (categoryTypes.length === 0) {
                        showToast('No stock available for this category at your station', 'warning');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading category types from stock:', error);
                showToast('Failed to load available products', 'error');
            }
        });
    }

    // Update all category type select dropdowns
    function updateAllCategoryTypeSelects() {
        let options = '<option value="">Select Type...</option>';
        $.each(categoryTypes, function(i, type) {
            options += `<option value="${type.type_id}">${type.type_name}</option>`;
        });
        $('.category-type-select').html(options);
    }

    // Load units from stock by type at user's station
    function loadUnitsByType(typeId, selectElement, row) {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/stock-units/' + typeId,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    let units = res.data || [];
                    
                    let options = '<option value="">Select Unit...</option>';
                    $.each(units, function(i, unit) {
                        options += `<option value="${unit.unit_id}" data-assignment-id="${unit.assignment_id}" data-quantity="${unit.total_quantity}">${unit.unit_name} ( ${unit.total_quantity})</option>`;
                    });
                    selectElement.html(options);
                    
                    if (units.length === 0) {
                        showToast('No stock available for this type at your station', 'warning');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading units from stock:', error);
            }
        });
    }

    // Load available quantity and set max on amount input
    function loadAvailableQuantity(assignmentId, row) {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/available-quantity/' + assignmentId,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const availableQty = res.data.available_quantity || 0;
                    // Store the original max quantity for this assignment_id
                    stockQuantities[assignmentId] = availableQty;
                    
                    // Calculate remaining quantity (subtract amounts from other rows with same assignment_id)
                    const remainingQty = calculateRemainingQuantity(assignmentId, row);
                    
                    const amountInput = $(`.amount-input[data-row="${row}"]`);
                    amountInput.attr('max', remainingQty);
                    amountInput.attr('placeholder', ` ${remainingQty}`);
                    amountInput.data('max-quantity', remainingQty);
                    amountInput.data('original-max', availableQty);
                    
                    // Disable input if max is 0
                    if (remainingQty <= 0) {
                        amountInput.prop('disabled', true).val('');
                        showToast('No remaining quantity available for this product', 'warning');
                    } else {
                        amountInput.prop('disabled', false);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading available quantity:', error);
            }
        });
    }

    // Calculate remaining quantity for a specific assignment_id (excluding a specific row)
    function calculateRemainingQuantity(assignmentId, excludeRow) {
        const originalMax = stockQuantities[assignmentId] || 0;
        let usedAmount = 0;
        
        // Sum amounts from all rows with the same assignment_id (except excludeRow)
        $('.item-row').each(function() {
            const rowNum = $(this).data('row');
            if (rowNum != excludeRow) {
                const unitSelect = $(`.unit-select[data-row="${rowNum}"]`);
                const rowAssignmentId = unitSelect.find(':selected').data('assignment-id');
                
                if (rowAssignmentId == assignmentId) {
                    const amount = parseFloat($(`.amount-input[data-row="${rowNum}"]`).val()) || 0;
                    usedAmount += amount;
                }
            }
        });
        
        return Math.max(0, originalMax - usedAmount);
    }

    // Update max quantities for all rows with a specific assignment_id
    function updateMaxQuantitiesForAssignment(assignmentId) {
        const originalMax = stockQuantities[assignmentId] || 0;
        
        // Find all rows with this assignment_id
        $('.item-row').each(function() {
            const rowNum = $(this).data('row');
            const unitSelect = $(`.unit-select[data-row="${rowNum}"]`);
            const rowAssignmentId = unitSelect.find(':selected').data('assignment-id');
            
            if (rowAssignmentId == assignmentId) {
                const remainingQty = calculateRemainingQuantity(assignmentId, rowNum);
                const currentAmount = parseFloat($(`.amount-input[data-row="${rowNum}"]`).val()) || 0;
                const newMax = remainingQty + currentAmount; // Add back current row's amount
                
                const amountInput = $(`.amount-input[data-row="${rowNum}"]`);
                amountInput.attr('max', newMax);
                amountInput.attr('placeholder', ` ${newMax}`);
                amountInput.data('max-quantity', newMax);
                
                // Disable input if max is 0 (and no current value)
                if (newMax <= 0 && currentAmount <= 0) {
                    amountInput.prop('disabled', true).val('');
                } else {
                    amountInput.prop('disabled', false);
                }
            }
        });
    }

    // On modal show, reset items
    $('#createTransferModal').on('show.bs.modal', function () {
        resetItemsTable();
        // Reset selected values
        $('#warehouse_id').val('').trigger('chosen:updated');
        $('#categories_id').val('').trigger('chosen:updated');
        $('#driver_id').val('').trigger('chosen:updated');
        $('#supporting_documents').val('');
        $('#additional_info').val('');
    });

    // On modal shown, initialize chosen
    $('#createTransferModal').on('shown.bs.modal', function () {
        // Initialize chosen after modal is fully visible
        if ($('#warehouse_id').length && !$('#warehouse_id').hasClass('chosen-done')) {
            $('#warehouse_id').chosen({ width: '100%', allow_single_deselect: true }).addClass('chosen-done');
        }
        if ($('#categories_id').length && !$('#categories_id').hasClass('chosen-done')) {
            $('#categories_id').chosen({ width: '100%', allow_single_deselect: true }).addClass('chosen-done');
        }
        if ($('#driver_id').length && !$('#driver_id').hasClass('chosen-done')) {
            $('#driver_id').chosen({ width: '100%', allow_single_deselect: true }).addClass('chosen-done');
        }
    });

    // Category change - load category types
    $(document).on('change', '#categories_id', function () {
        const categoryId = $(this).val();
        if (categoryId) {
            loadCategoryTypes(categoryId);
        } else {
            categoryTypes = [];
            updateAllCategoryTypeSelects();
        }
        // Clear all unit selects
        $('.unit-select').html('<option value="">Select Unit...</option>');
    });

    // Category type change - load units
    $(document).on('change', '.category-type-select', function () {
        const typeId = $(this).val();
        const row = $(this).data('row');
        const unitSelect = $(`.unit-select[data-row="${row}"]`);
        const amountInput = $(`.amount-input[data-row="${row}"]`);
        
        // Reset amount input
        amountInput.val('').attr('max', '').attr('placeholder', 'Amount').removeData('max-quantity');
        
        if (typeId) {
            loadUnitsByType(typeId, unitSelect, row);
        } else {
            unitSelect.html('<option value="">Select Unit...</option>');
        }
    });

    // Unit change - load available quantity
    $(document).on('change', '.unit-select', function () {
        const row = $(this).data('row');
        const selectedOption = $(this).find(':selected');
        const assignmentId = selectedOption.data('assignment-id');
        const amountInput = $(`.amount-input[data-row="${row}"]`);
        
        // Reset amount input
        amountInput.val('');
        
        if (assignmentId) {
            // Store assignment_id on the row for later use
            $(this).closest('.item-row').data('assignment-id', assignmentId);
            loadAvailableQuantity(assignmentId, row);
        } else {
            $(this).closest('.item-row').removeData('assignment-id');
            amountInput.attr('max', '').attr('placeholder', 'Amount').removeData('max-quantity');
        }
    });

    // Validate amount on input and update other rows
    $(document).on('input', '.amount-input', function () {
        const row = $(this).data('row');
        const maxQty = $(this).data('max-quantity');
        const currentVal = parseFloat($(this).val()) || 0;
        
        if (maxQty && currentVal > maxQty) {
            $(this).val(maxQty);
            showToast(`Maximum available quantity is ${maxQty}`, 'warning');
        }
        
        // Update max quantities for all rows with the same assignment_id
        const unitSelect = $(`.unit-select[data-row="${row}"]`);
        const assignmentId = unitSelect.find(':selected').data('assignment-id');
        if (assignmentId) {
            updateMaxQuantitiesForAssignment(assignmentId);
        }
        
        // Calculate total price
        calculateTotalPrice(row);
    });

    // Calculate total price when unit price changes
    $(document).on('input', '.unit-price-input', function () {
        const row = $(this).data('row');
        calculateTotalPrice(row);
    });

    // Function to calculate total price for a row
    function calculateTotalPrice(row) {
        const amount = parseFloat($(`.amount-input[data-row="${row}"]`).val()) || 0;
        const unitPrice = parseFloat($(`.unit-price-input[data-row="${row}"]`).val()) || 0;
        const totalPrice = amount * unitPrice;
        $(`.total-price-input[data-row="${row}"]`).val(totalPrice.toFixed(2));
    }

    // Add item row
    $(document).on('click', '#addItemBtn', function () {
        rowCounter++;
        let options = '<option value="">Select Type...</option>';
        $.each(categoryTypes, function(i, type) {
            options += `<option value="${type.type_id}">${type.type_name}</option>`;
        });
        
        const newRow = `
            <tr class="item-row" data-row="${rowCounter}">
                <td>
                    <select class="form-control category-type-select" data-row="${rowCounter}">
                        ${options}
                    </select>
                </td>
                <td>
                    <select class="form-control unit-select" data-row="${rowCounter}">
                        <option value="">Select Unit...</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control amount-input" data-row="${rowCounter}" min="1" placeholder="Amount">
                </td>
                <td>
                    <input type="number" class="form-control unit-price-input" data-row="${rowCounter}" min="0" step="0.01" placeholder="Unit Price">
                </td>
                <td>
                    <input type="number" class="form-control total-price-input" data-row="${rowCounter}" readonly placeholder="Total Price" style="background-color: #f5f5f5;">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item" data-row="${rowCounter}"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#itemsBody').append(newRow);
    });

    // Remove item row
    $(document).on('click', '.remove-item', function () {
        if ($('.item-row').length > 1) {
            const row = $(this).closest('.item-row');
            const rowNum = row.data('row');
            const unitSelect = $(`.unit-select[data-row="${rowNum}"]`);
            const assignmentId = unitSelect.find(':selected').data('assignment-id');
            
            row.remove();
            
            // Update max quantities for remaining rows with same assignment_id
            if (assignmentId) {
                updateMaxQuantitiesForAssignment(assignmentId);
            }
        } else {
            showToast('At least one item is required!', 'error');
        }
    });

    // Reset items table
    function resetItemsTable() {
        rowCounter = 0;
        categoryTypes = [];
        stockQuantities = {}; // Reset stock tracking
        $('#itemsBody').html(`
            <tr class="item-row" data-row="0">
                <td>
                    <select class="form-control category-type-select" data-row="0">
                        <option value="">Select Type...</option>
                    </select>
                </td>
                <td>
                    <select class="form-control unit-select" data-row="0">
                        <option value="">Select Unit...</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control amount-input" data-row="0" min="1" placeholder="Amount">
                </td>
                <td>
                    <input type="number" class="form-control unit-price-input" data-row="0" min="0" step="0.01" placeholder="Unit Price">
                </td>
                <td>
                    <input type="number" class="form-control total-price-input" data-row="0" readonly placeholder="Total Price" style="background-color: #f5f5f5;">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item" data-row="0"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `);
    }

    // Reset form
    function resetForm() {
        $('#warehouse_id').val('').trigger('chosen:updated');
        $('#categories_id').val('').trigger('chosen:updated');
        $('#driver_id').val('').trigger('chosen:updated');
        $('#supporting_documents').val('');
        $('#additional_info').val('');
        resetItemsTable();
    }

    // Save transfer
    $(document).on('click', '#saveTransferBtnFromAndTo', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const warehouseId = $('#warehouse_id').val();
        const categoriesId = $('#categories_id').val();
        const driverId = $('#driver_id').val();
        const supportingDocsFile = $('#supporting_documents')[0].files[0];
        const additionalInfo = $('#additional_info').val().trim();

        if (!warehouseId || !categoriesId || !driverId) {
            showToast('Please select warehouse, category and driver!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        // Collect items
        const items = [];
        let hasError = false;

        $('.item-row').each(function () {
            const row = $(this).data('row');
            const typeId = $(`.category-type-select[data-row="${row}"]`).val();
            const unitSelect = $(`.unit-select[data-row="${row}"]`);
            const unitId = unitSelect.val();
            const assignmentId = unitSelect.find(':selected').data('assignment-id');
            const amount = $(`.amount-input[data-row="${row}"]`).val();
            const unitPrice = $(`.unit-price-input[data-row="${row}"]`).val();
            const totalPrice = $(`.total-price-input[data-row="${row}"]`).val();
            const maxQty = $(`.amount-input[data-row="${row}"]`).data('max-quantity');

            if (!typeId || !unitId || !assignmentId || !amount || !unitPrice) {
                hasError = true;
                return false;
            }

            // Validate against available quantity
            if (maxQty && parseFloat(amount) > parseFloat(maxQty)) {
                showToast(`Amount exceeds available quantity (${maxQty}) for row ${parseInt(row) + 1}`, 'error');
                hasError = true;
                return false;
            }

            items.push({
                assignment_id: assignmentId,
                amount: parseInt(amount),
                unit_price: parseFloat(unitPrice),
                total_price: parseFloat(totalPrice)
            });
        });

        if (hasError || items.length === 0) {
            showToast('Please fill all item fields (including Unit Price)!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        // Validate total amounts per assignment_id don't exceed stock
        const amountsByAssignment = {};
        items.forEach(item => {
            if (!amountsByAssignment[item.assignment_id]) {
                amountsByAssignment[item.assignment_id] = 0;
            }
            amountsByAssignment[item.assignment_id] += item.amount;
        });

        for (const assignmentId in amountsByAssignment) {
            const totalAmount = amountsByAssignment[assignmentId];
            const maxStock = stockQuantities[assignmentId] || 0;
            if (totalAmount > maxStock) {
                showToast(`Total amount (${totalAmount}) exceeds available stock (${maxStock}) for a product. Please adjust quantities.`, 'error');
                setButtonLoading(btn, false);
                return;
            }
        }

        // Use FormData for file upload (station_id is taken from session on server)
        const formData = new FormData();
        formData.append('warehouse_id', warehouseId);
        formData.append('categories_id', categoriesId);
        formData.append('driver_id', driverId);
        formData.append('additional_info', additionalInfo);
        formData.append('items', JSON.stringify(items));
        
        if (supportingDocsFile) {
            formData.append('supporting_documents', supportingDocsFile);
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/create',
            method: 'POST',
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#createTransferModal').modal('hide');
                    resetForm();
                    loadTransferData();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function (xhr) {
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

    // Load transfer data
    function loadTransferData() {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/get-all?station_id=<?= $_SESSION['loc_id'] ?? '' ?>',
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                // Destroy existing DataTable
                if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                    $('#data-table-basic').DataTable().destroy();
                }
                
                $('#transferdata').empty();

                if (res.success && res.data && res.data.length > 0) {
                    $.each(res.data, function (index, record) {
                        let statusClass = 'text-warning';
                        if (record.status === 'Received') statusClass = 'text-success';
                        else if (record.status === 'Partial') statusClass = 'text-info';
                        else if (record.status === 'Rejected') statusClass = 'text-danger';

                        let actionBtns = `
                            <button class="btn btn-default btn-icon-notika viewtransfer" 
                                title="View Details"
                                data-id="${record.id}">
                                <i class="fa fa-eye"></i>
                            </button>
                        `;

                        // Show Receive button if status is Pending or Partial AND user's station is the destination
                        const userLocId = '<?= $_SESSION['loc_id'] ?? '' ?>';
                        if ((record.status === 'Pending' || record.status === 'Partial') && record.warehouse_id == userLocId) {
                            actionBtns += `
                                <button class="btn btn-success btn-icon-notika receivetransfer" 
                                    title="Receive Transfer"
                                    data-id="${record.id}">
                                    <i class="fa fa-check"></i>
                                </button>
                            `;
                        }

                        const row = `<tr>
                            <td>${index + 1}</td>
                            <td>${record.station_name || 'N/A'}</td>
                            <td>${record.warehouse_name || 'N/A'}</td>
                            <td>${record.category_name || 'N/A'}</td>
                            <td>${record.driver_name || 'N/A'}</td>
                            <td>${record.created_by_name || 'N/A'}</td>
                            <td>${new Date(record.created_at).toLocaleString()}</td>
                            <td><span class="${statusClass}">${record.status}</span></td>
                            <td>
                                <div class="button-icon-btn button-icon-btn-rd">
                                    ${actionBtns}
                                </div>
                            </td>
                        </tr>`;
                        
                        $('#transferdata').append(row);
                    });
                }

                // Reinitialize DataTable
                $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading transfers:', error);
            }
        });
    }

    // View transfer details
    $(document).on('click', '.viewtransfer', function () {
        const transferId = $(this).data('id');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/get/' + transferId,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const data = res.data;
                    const transfer = data.transfer || data;
                    const items = data.items || [];
                    
                    $('#view_station_name').text(transfer.station_name || 'N/A');
                    $('#view_warehouse_name').text(transfer.warehouse_name || 'N/A');
                    $('#view_category_name').text(transfer.category_name || 'N/A');
                    $('#view_driver_name').text(transfer.driver_name || 'N/A');
                    $('#view_driver_phone').text(transfer.driver_phone || 'N/A');
                    $('#view_driver_license').text(transfer.driver_license || 'N/A');
                    $('#view_created_by').text(transfer.created_by_name || 'N/A');
                    $('#view_created_at').text(new Date(transfer.created_at).toLocaleString());
                    
                    // Use the status field which now supports Partial
                    let status = transfer.status || 'Pending';
                    let statusClass = 'label-warning';
                    if (status === 'Received') statusClass = 'label-success';
                    else if (status === 'Partial') statusClass = 'label-info';
                    else if (status === 'Rejected') statusClass = 'label-danger';
                    $('#view_status').html(`<span class="label ${statusClass}">${status}</span>`);
                    
                    // Show document link or N/A
                    if (transfer.supporting_documents) {
                        $('#view_supporting_docs').html(`<a href="<?= App::baseUrl() ?>/Doc/product_transifer/${transfer.supporting_documents}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> View Document</a>`);
                    } else {
                        $('#view_supporting_docs').text('N/A');
                    }
                    $('#view_additional_info').text(transfer.additional_info || 'N/A');

                    // Show receive information if transfer is received or partial
                    if (transfer.received_at || transfer.status === 'Partial' || transfer.status === 'Received') {
                        $('#receive_info_row').show();
                        $('#view_received_by').text(transfer.received_by_name || 'N/A');
                        $('#view_received_at').text(transfer.received_at ? new Date(transfer.received_at).toLocaleString() : 'In Progress');
                        
                        if (transfer.receive_note) {
                            $('#view_receive_note').html(`<a href="<?= App::baseUrl() ?>/Doc/product_transifer/receive_notes/${transfer.receive_note}" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> View Document</a>`);
                        } else {
                            $('#view_receive_note').text('N/A');
                        }
                        $('#view_receive_comment').text(transfer.receive_comment || 'N/A');
                    } else {
                        $('#receive_info_row').hide();
                    }

                    // Load items with sent and received amounts
                    let itemsHtml = '';
                    if (items && items.length > 0) {
                        $.each(items, function(i, item) {
                            const sentAmount = parseFloat(item.amount || 0);
                            const receivedAmount = parseFloat(item.received_amount || 0);
                            const unitPrice = parseFloat(item.unit_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            const totalPrice = parseFloat(item.total_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            
                            // Determine item status badge
                            let itemStatus = item.item_status || 'pending';
                            let itemStatusBadge = '<span class="label label-warning">Pending</span>';
                            if (itemStatus === 'received') {
                                itemStatusBadge = '<span class="label label-success">Received</span>';
                            }
                            
                            // Highlight discrepancies between sent and received
                            let receivedClass = '';
                            let receivedDisplay = '-';
                            if (receivedAmount > 0) {
                                receivedDisplay = receivedAmount.toLocaleString();
                                if (receivedAmount < sentAmount) {
                                    receivedClass = 'text-warning'; // Less than sent
                                } else if (receivedAmount > sentAmount) {
                                    receivedClass = 'text-info'; // More than sent
                                } else {
                                    receivedClass = 'text-success'; // Equal to sent
                                }
                            }
                            
                            itemsHtml += `<tr>
                                <td>${i + 1}</td>
                                <td>${item.type_name || 'N/A'}</td>
                                <td>${item.unit_name || 'N/A'}</td>
                                <td class="text-right"><strong>${sentAmount.toLocaleString()}</strong></td>
                                <td class="text-right ${receivedClass}"><strong>${receivedDisplay}</strong></td>
                                <td class="text-right">${unitPrice}</td>
                                <td class="text-right">${totalPrice}</td>
                                <td>${itemStatusBadge}</td>
                            </tr>`;
                        });
                    } else {
                        itemsHtml = '<tr><td colspan="8" class="text-center">No items</td></tr>';
                    }
                    $('#view_items_body').html(itemsHtml);

                    $('#viewTransferModal').modal('show');
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading transfer details:', error);
                showToast('Failed to load transfer details', 'error');
            }
        });
    });

    // Show receive confirmation modal with items
    $(document).on('click', '.receivetransfer', function () {
        const transferId = $(this).data('id');
        $('#receive_transfer_id').val(transferId);
        $('#receive_note').val('');
        $('#receive_comment').val('');
        $('#receiveItemsBody').empty();
        $('#selectAllItems').prop('checked', false);
        
        // Load transfer details and items
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/get/' + transferId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    const transfer = res.data.transfer;
                    const items = res.data.items || [];
                    
                    // Set transfer info
                    $('#receive_from_station').text(transfer.station_name || 'N/A');
                    $('#receive_category').text(transfer.category_name || 'N/A');
                    $('#receive_driver').text(transfer.driver_name || 'N/A');
                    
                    // Populate items table - only show PENDING items (items can only be received once)
                    items.forEach((item, index) => {
                        // Skip already received or partially received items - each item can only be received once
                        if (item.item_status === 'received' || item.item_status === 'partial') {
                            return;
                        }
                        
                        const sentAmount = parseFloat(item.amount) || 0;
                        const unitPrice = parseFloat(item.unit_price) || 0;
                        
                        const row = `
                            <tr data-item-id="${item.id}" data-sent-amount="${sentAmount}">
                                <td class="text-center">
                                    <input type="checkbox" class="item-checkbox" data-item-id="${item.id}">
                                </td>
                                <td>${item.type_name || 'N/A'}</td>
                                <td>${item.unit_name || 'N/A'}</td>
                                <td class="text-right"><strong>${sentAmount.toLocaleString()}</strong></td>
                                <td>
                                    <input type="number" class="form-control received-amount-input" 
                                        data-item-id="${item.id}" 
                                        value="${sentAmount}" 
                                        min="0.01" 
                                        step="0.01"
                                        style="width: 100px;">
                                    <small class="text-muted">Enter actual received amount</small>
                                </td>
                                <td class="text-right">${unitPrice.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                <td><span class="label label-warning">Pending</span></td>
                            </tr>
                        `;
                        $('#receiveItemsBody').append(row);
                    });
                    
                    if ($('#receiveItemsBody tr').length === 0) {
                        $('#receiveItemsBody').append('<tr><td colspan="7" class="text-center text-muted">All items have been received</td></tr>');
                        $('#confirmReceiveBtn').prop('disabled', true);
                    } else {
                        $('#confirmReceiveBtn').prop('disabled', false);
                    }
                    
                    $('#receiveTransferModal').modal('show');
                } else {
                    showToast('Failed to load transfer details', 'error');
                }
            },
            error: function() {
                showToast('Failed to load transfer details', 'error');
            }
        });
    });

    // Select all items checkbox
    $(document).on('change', '#selectAllItems', function() {
        const isChecked = $(this).is(':checked');
        $('#receiveItemsBody .item-checkbox').prop('checked', isChecked);
    });

    // Individual item checkbox change
    $(document).on('change', '.item-checkbox', function() {
        const totalCheckboxes = $('#receiveItemsBody .item-checkbox').length;
        const checkedCheckboxes = $('#receiveItemsBody .item-checkbox:checked').length;
        $('#selectAllItems').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Validate received amount on input - only ensure it's positive (can exceed sent amount)
    $(document).on('input', '.received-amount-input', function() {
        let value = parseFloat($(this).val()) || 0;
        
        if (value < 0) {
            $(this).val(0);
        }
    });

    // Confirm receive transfer
    $(document).on('click', '#confirmReceiveBtn', function () {
        const transferId = $('#receive_transfer_id').val();
        const receiveNoteFile = $('#receive_note')[0].files[0];
        const receiveComment = $('#receive_comment').val().trim();
        const btn = this;

        // Collect selected items
        const selectedItems = [];
        $('#receiveItemsBody .item-checkbox:checked').each(function() {
            const itemId = $(this).data('item-id');
            const receivedAmount = parseFloat($('.received-amount-input[data-item-id="' + itemId + '"]').val()) || 0;
            
            if (receivedAmount > 0) {
                selectedItems.push({
                    item_id: itemId,
                    received_amount: receivedAmount
                });
            }
        });

        if (selectedItems.length === 0) {
            showToast('Please select at least one item to receive', 'error');
            return;
        }

        setButtonLoading(btn, true);

        // Use FormData for file upload
        const formData = new FormData();
        formData.append('transfer_id', transferId);
        formData.append('receive_comment', receiveComment);
        formData.append('items', JSON.stringify(selectedItems));
        
        if (receiveNoteFile) {
            formData.append('receive_note', receiveNoteFile);
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/receive',
            method: 'POST',
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#receiveTransferModal').modal('hide');
                    loadTransferData();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function (xhr) {
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

    // Show reject modal
    $(document).on('click', '.rejecttransfer', function () {
        const transferId = $(this).data('id');
        $('#reject_transfer_id').val(transferId);
        $('#reject_reason').val('');
        $('#rejectTransferModal').modal('show');
    });

    // Confirm reject
    $(document).on('click', '#confirmRejectBtn', function () {
        const btn = this;
        const transferId = $('#reject_transfer_id').val();
        const reason = $('#reject_reason').val().trim();

        if (!reason) {
            showToast('Please enter rejection reason!', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfer/reject',
            method: 'PUT',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ 
                transfer_id: transferId,
                rejected_reason: reason 
            }),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#rejectTransferModal').modal('hide');
                    loadTransferData();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function (xhr) {
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

});
</script>
