<script>
$(document).ready(function () {

    // Initialize DataTable
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
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/categories/get-all-categories', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                $('#data-table-basic').DataTable().destroy();
            }
            
            $('#categoriesdata').empty();

            $.each(res.data, function (index, record) {
                const statusClass = record.status === 'active' ? 'text-success' : 'text-danger';
                
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.category_name}</td>
                    <td>${record.description || ''}</td>
                    <td><span class="${statusClass}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                    <td>
                        <div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editcategory"
                                title="Edit Category Status"
                                data-id="${record.category_id}"
                                data-category_name="${record.category_name}"
                                data-description="${record.description || ''}"
                                data-status="${record.status}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                
                $('#categoriesdata').append(row);
            });

            TableData = $('#data-table-basic').DataTable({
                pageLength: 10
            });
        });
    }

    // Handle Save Category button
    $(document).on('click', '#saveCategoryBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            category_name: $('#category_name').val().trim(),
            description: $('#description').val().trim(),
            status: 'active'
        };

        if (!payload.category_name) {
            showToast('Please fill category name!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/categories/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalthree').modal('hide');
                    $('#category_name').val('');
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

    // Handle Edit Category click
    $(document).on('click', '.editcategory', function() {
        const btn = $(this);
        
        $('#category_id').val(btn.data('id'));
        $('#edit_category_name').val(btn.data('category_name'));
        $('#edit_description').val(btn.data('description'));
        $('#edit_status').val(btn.data('status')).trigger('chosen:updated');
        
        $('#myModalfour').modal('show');
    });

    // Handle Update Category button
    $(document).on('click', '#EditCategoryBtn', function (e) {
        e.preventDefault();
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            category_id: $('#category_id').val(),
            category_name: $('#edit_category_name').val().trim(),
            description: $('#edit_description').val().trim(),
            status: $('#edit_status').val()
        };

        if (!payload.category_name || !payload.status) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/categories/update',
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
