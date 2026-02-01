<script>
$(document).ready(function () {

    let TableData;
    
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#data-table-basic')) {
            $('#data-table-basic').DataTable().destroy();
        }
        TableData = $('#data-table-basic').DataTable({
            pageLength: 10
        });
    }

    initDataTable();

    function loadData() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/category-types/get-all-category-types', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                $('#data-table-basic').DataTable().destroy();
            }
            
            $('#categorytypesdata').empty();

            $.each(res.data, function (index, record) {
                const statusClass = record.status === 'active' ? 'text-success' : 'text-danger';
                
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.category_name || 'N/A'}</td>
                    <td>${record.type_name}</td>
                    <td>${record.description || ''}</td>
                    <td><span class="${statusClass}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                    <td>
                        <div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editcategorytype"
                                title="Edit Category Type Status"
                                data-id="${record.type_id}"
                                data-category_id="${record.category_id}"
                                data-category_name="${record.category_name || 'N/A'}"
                                data-type_name="${record.type_name}"
                                data-description="${record.description || ''}"
                                data-status="${record.status}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                
                $('#categorytypesdata').append(row);
            });

            TableData = $('#data-table-basic').DataTable({
                pageLength: 10
            });
        });
    }

    // Handle Save Category Type button
    $(document).on('click', '#saveCategoryTypeBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            category_id: $('#category_id').val(),
            type_name: $('#type_name').val().trim(),
            description: $('#description').val().trim(),
            status: 'active'
        };

        if (!payload.category_id || !payload.type_name) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-types/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalthree').modal('hide');
                    $('#category_id').val('').trigger('chosen:updated');
                    $('#type_name').val('');
                    $('#description').val('');
                    loadData();
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

    // Handle Edit Category Type click
    $(document).on('click', '.editcategorytype', function() {
        const btn = $(this);
        
        $('#type_id').val(btn.data('id'));
        $('#edit_category_id').val(btn.data('category_id')).trigger('chosen:updated');
        $('#edit_type_name').val(btn.data('type_name'));
        $('#edit_description').val(btn.data('description'));
        $('#edit_status').val(btn.data('status')).trigger('chosen:updated');
        
        $('#myModalfour').modal('show');
    });

    // Handle Update Category Type button
    $(document).on('click', '#EditCategoryTypeBtn', function (e) {
        e.preventDefault();
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            type_id: $('#type_id').val(),
            category_id: $('#edit_category_id').val(),
            type_name: $('#edit_type_name').val().trim(),
            description: $('#edit_description').val().trim(),
            status: $('#edit_status').val()
        };

        if (!payload.category_id || !payload.type_name || !payload.status) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-types/update',
            method: 'PUT',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalfour').modal('hide');
                    loadData();
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
