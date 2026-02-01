<script>
(function() {
    'use strict';
    
    console.log('Product Mixing script loaded');

    var availableStock = [];
    var selectedItems = [];
    var categoriesData = [];

    // Initialize function - exposed globally for AJAX page loading
    window.initProductMixingPage = function() {
        console.log('initProductMixingPage called');
        
        if ($('#availableStockData').length === 0) {
            console.log('availableStockData element not found - not on this page');
            return;
        }
        
        console.log('Found availableStockData element, initializing...');
        loadAvailableStock();
        loadCategories();
    };

    // Load available stock for mixing
    function loadAvailableStock() {
        console.log('Loading available stock for mixing...');
        $('#availableStockData').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/mixing/get-available-stock',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                console.log('Stock response:', res);
                if (res.success && res.data && res.data.length > 0) {
                    availableStock = res.data;
                    renderAvailableStock();
                } else {
                    availableStock = [];
                    $('#availableStockData').html('<tr><td colspan="4" class="text-center">No stock available</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading stock:', error);
                $('#availableStockData').html('<tr><td colspan="4" class="text-center text-danger">Error loading stock: ' + error + '</td></tr>');
            }
        });
    }

    // Render available stock table
    function renderAvailableStock() {
        var tbody = $('#availableStockData');
        tbody.empty();

        if (!availableStock || availableStock.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">No stock available</td></tr>');
            return;
        }

        for (var i = 0; i < availableStock.length; i++) {
            var item = availableStock[i];
            var displayName = (item.type_name || 'N/A') + ' / ' + (item.unit_name || 'N/A');
            var availableQty = parseFloat(item.total_quantity) || 0;
            
            // Check if already selected
            var selectedItem = null;
            for (var j = 0; j < selectedItems.length; j++) {
                if (selectedItems[j].assignment_id == item.assignment_id) {
                    selectedItem = selectedItems[j];
                    break;
                }
            }
            var usedQty = selectedItem ? selectedItem.quantity : 0;
            var remainingQty = availableQty - usedQty;
            
            var stockJson = encodeURIComponent(JSON.stringify(item));
            var isDisabled = remainingQty <= 0 ? 'disabled' : '';
            var isChecked = selectedItem ? 'checked' : '';

            var row = '<tr data-assignment-id="' + item.assignment_id + '" data-stock-encoded="' + stockJson + '">' +
                '<td><input type="checkbox" class="stock-checkbox" ' + isChecked + ' ' + isDisabled + '></td>' +
                '<td>' + displayName + '</td>' +
                '<td><span class="available-qty">' + remainingQty.toFixed(2) + '</span></td>' +
                '<td><input type="number" step="0.01" min="0.01" max="' + remainingQty + '" class="form-control mix-qty-input" style="width:80px;" placeholder="0" value="' + (usedQty > 0 ? usedQty : '') + '" ' + isDisabled + '></td>' +
            '</tr>';
            
            tbody.append(row);
        }
    }

    // Load categories for output product
    function loadCategories() {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/categories/get-active-categories',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                console.log('Categories response:', res);
                if (res.success && res.data) {
                    categoriesData = res.data;
                    var options = '<option value="">Select Category</option>';
                    for (var i = 0; i < res.data.length; i++) {
                        options += '<option value="' + res.data[i].category_id + '">' + res.data[i].category_name + '</option>';
                    }
                    $('#output_category').html(options);
                }
            }
        });
    }

    // Handle category change - load type/units
    $(document).on('change', '#output_category', function() {
        var categoryId = $(this).val();
        var typeUnitSelect = $('#output_type_unit');
        
        typeUnitSelect.html('<option value="">Select Type / Unit</option>').prop('disabled', true);
        
        if (!categoryId) return;

        typeUnitSelect.html('<option value="">Loading...</option>');

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-type-units/get-type-unity-by-category/' + categoryId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                var options = '<option value="">Select Type / Unit</option>';
                if (res.success && res.data && res.data.length > 0) {
                    for (var i = 0; i < res.data.length; i++) {
                        var item = res.data[i];
                        var value = JSON.stringify({type_id: item.type_id, unit_id: item.unit_id, assignment_id: item.assignment_id});
                        options += '<option value=\'' + value + '\'>' + item.type_unit_name + '</option>';
                    }
                    typeUnitSelect.html(options).prop('disabled', false);
                } else {
                    typeUnitSelect.html('<option value="">No types available</option>');
                }
            },
            error: function() {
                typeUnitSelect.html('<option value="">Error loading</option>');
            }
        });
    });

    // Handle stock checkbox change
    $(document).on('change', '.stock-checkbox', function() {
        var row = $(this).closest('tr');
        var encodedData = row.attr('data-stock-encoded');
        var qtyInput = row.find('.mix-qty-input');
        
        if ($(this).is(':checked')) {
            var qty = parseFloat(qtyInput.val()) || 0;
            if (qty <= 0) {
                var maxQty = parseFloat(qtyInput.attr('max')) || 0;
                qtyInput.val(maxQty > 0 ? Math.min(1, maxQty) : '');
            }
        } else {
            try {
                var stockData = JSON.parse(decodeURIComponent(encodedData));
                selectedItems = selectedItems.filter(function(s) { return s.assignment_id != stockData.assignment_id; });
            } catch(e) {}
        }
        
        updateSelectedItems();
    });

    // Handle quantity input change
    $(document).on('input change', '.mix-qty-input', function() {
        var row = $(this).closest('tr');
        var checkbox = row.find('.stock-checkbox');
        var qty = parseFloat($(this).val()) || 0;
        
        if (qty > 0 && !checkbox.is(':checked')) {
            checkbox.prop('checked', true);
        } else if (qty <= 0 && checkbox.is(':checked')) {
            checkbox.prop('checked', false);
        }
        
        updateSelectedItems();
    });

    // Update selected items list
    function updateSelectedItems() {
        selectedItems = [];
        var totalQty = 0;
        
        $('#availableStockData tr').each(function() {
            var checkbox = $(this).find('.stock-checkbox');
            var qtyInput = $(this).find('.mix-qty-input');
            var encodedData = $(this).attr('data-stock-encoded');
            
            if (checkbox.is(':checked') && encodedData) {
                var qty = parseFloat(qtyInput.val()) || 0;
                if (qty > 0) {
                    try {
                        var stockData = JSON.parse(decodeURIComponent(encodedData));
                        selectedItems.push({
                            assignment_id: stockData.assignment_id,
                            type_name: stockData.type_name,
                            unit_name: stockData.unit_name,
                            quantity: qty,
                            max_quantity: parseFloat(stockData.total_quantity) || 0
                        });
                        totalQty += qty;
                    } catch(e) {}
                }
            }
        });
        
        renderSelectedItems();
        $('#totalMixQty').text(totalQty.toFixed(2));
        $('#inputTotalDisplay').text(totalQty.toFixed(2));
        $('#output_quantity').val(totalQty.toFixed(2));
        
        $('#createMixingBtn').prop('disabled', selectedItems.length < 2);
    }

    // Render selected items summary
    function renderSelectedItems() {
        var tbody = $('#selectedItemsBody');
        tbody.empty();
        
        $('#selectedCount').text(selectedItems.length);
        
        if (selectedItems.length === 0) {
            tbody.html('<tr id="noItemsRow"><td colspan="3" class="text-center text-muted">No items selected</td></tr>');
            return;
        }
        
        for (var i = 0; i < selectedItems.length; i++) {
            var item = selectedItems[i];
            var displayName = item.type_name + ' / ' + item.unit_name;
            
            var row = '<tr data-index="' + i + '">' +
                '<td>' + displayName + '</td>' +
                '<td>' + item.quantity.toFixed(2) + '</td>' +
                '<td><button class="btn btn-xs btn-danger remove-selected-btn" data-assignment-id="' + item.assignment_id + '"><i class="fa fa-times"></i></button></td>' +
            '</tr>';
            tbody.append(row);
        }
    }

    // Remove selected item
    $(document).on('click', '.remove-selected-btn', function() {
        var assignmentId = $(this).data('assignment-id');
        
        $('#availableStockData tr').each(function() {
            var encodedData = $(this).attr('data-stock-encoded');
            if (encodedData) {
                try {
                    var stockData = JSON.parse(decodeURIComponent(encodedData));
                    if (stockData.assignment_id == assignmentId) {
                        $(this).find('.stock-checkbox').prop('checked', false);
                        $(this).find('.mix-qty-input').val('');
                    }
                } catch(e) {}
            }
        });
        
        updateSelectedItems();
        renderAvailableStock();
    });

    // Select all checkbox
    $(document).on('change', '#selectAllStock', function() {
        var isChecked = $(this).is(':checked');
        $('.stock-checkbox:not(:disabled)').prop('checked', isChecked);
        
        if (isChecked) {
            $('#availableStockData tr').each(function() {
                var checkbox = $(this).find('.stock-checkbox');
                var qtyInput = $(this).find('.mix-qty-input');
                if (checkbox.is(':checked') && !qtyInput.val()) {
                    var maxQty = parseFloat(qtyInput.attr('max')) || 0;
                    qtyInput.val(maxQty);
                }
            });
        }
        
        updateSelectedItems();
    });

    // Create mixed product
    $(document).on('click', '#createMixingBtn', function() {
        var btn = $(this);
        
        if (selectedItems.length < 2) {
            alert('Please select at least 2 items to mix');
            return;
        }
        
        var outputTypeUnit = $('#output_type_unit').val();
        if (!outputTypeUnit) {
            alert('Please select output product type/unit');
            return;
        }
        
        var outputQty = parseFloat($('#output_quantity').val()) || 0;
        if (outputQty <= 0) {
            alert('Please enter valid output quantity');
            return;
        }
        
        var mixingDate = $('#mixing_date').val();
        if (!mixingDate) {
            alert('Please select mixing date');
            return;
        }
        
        var outputData;
        try {
            outputData = JSON.parse(outputTypeUnit);
        } catch(e) {
            alert('Invalid output product selection');
            return;
        }
        
        var inputItems = [];
        for (var i = 0; i < selectedItems.length; i++) {
            inputItems.push({
                assignment_id: selectedItems[i].assignment_id,
                quantity: selectedItems[i].quantity
            });
        }
        
        var mixingData = {
            input_items: inputItems,
            output: {
                type_id: outputData.type_id,
                unit_id: outputData.unit_id,
                quantity: outputQty
            },
            mixing_date: mixingDate,
            notes: $('#mixing_notes').val().trim()
        };
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating...');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/mixing/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(mixingData),
            success: function(response) {
                if (response.success) {
                    alert('Mixed product created successfully! Ref: ' + (response.data.reference_no || ''));
                    
                    selectedItems = [];
                    $('#output_category').val('');
                    $('#output_type_unit').val('').prop('disabled', true);
                    $('#output_quantity').val('');
                    $('#mixing_notes').val('');
                    
                    loadAvailableStock();
                    renderSelectedItems();
                } else {
                    alert(response.message || 'Failed to create mixed product');
                }
            },
            error: function(xhr) {
                var msg = 'Error creating mixed product';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.message) msg = resp.message;
                } catch(e) {}
                alert(msg);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-flask"></i> Create Mixed Product');
                updateSelectedItems();
            }
        });
    });

    // Load mixing history
    function loadMixingHistory() {
        $('#mixingHistoryData').html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/mixing/get-history',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                var tbody = $('#mixingHistoryData');
                tbody.empty();
                
                if (res.success && res.data && res.data.length > 0) {
                    for (var i = 0; i < res.data.length; i++) {
                        var mix = res.data[i];
                        var row = '<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td><strong>' + mix.reference_no + '</strong></td>' +
                            '<td>' + (mix.output_type_name || 'N/A') + ' / ' + (mix.output_unit_name || 'N/A') + '</td>' +
                            '<td>' + parseFloat(mix.output_quantity).toFixed(2) + '</td>' +
                            '<td>' + mix.input_count + ' items</td>' +
                            '<td>' + new Date(mix.mixing_date).toLocaleDateString() + '</td>' +
                            '<td>' + (mix.created_by || '-') + '</td>' +
                            '<td><button class="btn btn-xs btn-info view-mixing-btn" data-id="' + mix.mixing_id + '"><i class="fa fa-eye"></i></button></td>' +
                        '</tr>';
                        tbody.append(row);
                    }
                } else {
                    tbody.html('<tr><td colspan="8" class="text-center">No mixing history found</td></tr>');
                }
                
                if ($.fn.DataTable.isDataTable('#mixing-history-table')) {
                    $('#mixing-history-table').DataTable().destroy();
                }
                $('#mixing-history-table').DataTable({ pageLength: 10 });
            },
            error: function() {
                $('#mixingHistoryData').html('<tr><td colspan="8" class="text-center text-danger">Error loading history</td></tr>');
            }
        });
    }

    // View mixing details
    $(document).on('click', '.view-mixing-btn', function() {
        var mixingId = $(this).data('id');
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/mixing/get-details/' + mixingId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    var mix = res.data;
                    $('#detail_ref_no').text(mix.reference_no);
                    $('#detail_output_product').text((mix.output_type_name || 'N/A') + ' / ' + (mix.output_unit_name || 'N/A'));
                    $('#detail_output_qty').text(parseFloat(mix.output_quantity).toFixed(2));
                    $('#detail_date').text(new Date(mix.mixing_date).toLocaleDateString());
                    
                    var tbody = $('#mixingDetailsBody');
                    tbody.empty();
                    
                    if (mix.inputs && mix.inputs.length > 0) {
                        for (var i = 0; i < mix.inputs.length; i++) {
                            var input = mix.inputs[i];
                            var row = '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + (input.type_name || 'N/A') + '</td>' +
                                '<td>' + (input.unit_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(input.quantity).toFixed(2) + '</td>' +
                            '</tr>';
                            tbody.append(row);
                        }
                    } else {
                        tbody.html('<tr><td colspan="4" class="text-center">No input details</td></tr>');
                    }
                    
                    $('#viewMixingModal').modal('show');
                }
            }
        });
    });

    // Load history when modal opens
    $(document).on('show.bs.modal', '#mixingHistoryModal', function() {
        loadMixingHistory();
    });

    // Auto-initialize if already on the page
    $(document).ready(function() {
        setTimeout(function() {
            if ($('#availableStockData').length > 0) {
                console.log('Document ready - auto-initializing product mixing page');
                window.initProductMixingPage();
            }
        }, 100);
    });

    console.log('Product Mixing script ready - initProductMixingPage is available');
})();
</script>
