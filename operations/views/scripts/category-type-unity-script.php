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
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/category-type-units/get-all-assignments', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                $('#data-table-basic').DataTable().destroy();
            }
            
            $('#assignmentdata').empty();

            $.each(res.data, function (index, record) {
                const statusClass = record.status === 'active' ? 'text-success' : 'text-danger';
                
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.category_name || 'N/A'}</td>
                    <td>${record.type_name || 'N/A'}</td>
                    <td>${record.unit_name || 'N/A'}</td>
                    <td><span class="${statusClass}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                    <td>
                        <div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editassignment"
                                title="Edit Assignment"
                                data-id="${record.assignment_id}"
                                data-type_id="${record.type_id}"
                                data-unit_id="${record.unit_id}"
                                data-status="${record.status}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                
                $('#assignmentdata').append(row);
            });

            TableData = $('#data-table-basic').DataTable({
                pageLength: 10
            });
        });
    }

    // Handle Save Assignment button
    $(document).on('click', '#saveAssignmentBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            type_id: $('#type_id').val(),
            unit_id: $('#unit_id').val()
        };

        if (!payload.type_id || !payload.unit_id) {
            showToast('Please select both category type and unit!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-type-units/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalthree').modal('hide');
                    $('#type_id').val('').trigger('chosen:updated');
                    $('#unit_id').val('').trigger('chosen:updated');
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

    // Handle Edit Assignment click
    $(document).on('click', '.editassignment', function() {
        const btn = $(this);
        
        $('#assignment_id').val(btn.data('id'));
        $('#edit_type_id').val(btn.data('type_id')).trigger('chosen:updated');
        $('#edit_unit_id').val(btn.data('unit_id')).trigger('chosen:updated');
        $('#edit_status').val(btn.data('status')).trigger('chosen:updated');
        
        $('#myModalfour').modal('show');
    });

    // Handle Update Assignment button
    $(document).on('click', '#EditAssignmentBtn', function (e) {
        e.preventDefault();
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            assignment_id: $('#assignment_id').val(),
            type_id: $('#edit_type_id').val(),
            unit_id: $('#edit_unit_id').val(),
            status: $('#edit_status').val()
        };

        if (!payload.type_id || !payload.unit_id || !payload.status) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-type-units/update',
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
