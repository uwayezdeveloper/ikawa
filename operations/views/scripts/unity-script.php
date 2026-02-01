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
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/unity/get-all-unity', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                $('#data-table-basic').DataTable().destroy();
            }
            
            $('#unitydata').empty();

            $.each(res.data, function (index, record) {
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.unit_name}</td>
                    <td>
                        <div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editunity"
                                title="Edit Unity"
                                data-id="${record.unit_id}"
                                data-unit_name="${record.unit_name}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                            <button class="btn btn-default btn-icon-notika deleteunity"
                                title="Delete Unity"
                                data-id="${record.unit_id}"
                                data-unit_name="${record.unit_name}">
                                <i class="notika-icon text-danger notika-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                
                $('#unitydata').append(row);
            });

            TableData = $('#data-table-basic').DataTable({
                pageLength: 10
            });
        });
    }

    // Handle Save Unity button
    $(document).on('click', '#saveUnityBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            unit_name: $('#unit_name').val().trim()
        };

        if (!payload.unit_name) {
            showToast('Please fill unit name!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/unity/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalthree').modal('hide');
                    $('#unit_name').val('');
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

    // Handle Edit Unity click
    $(document).on('click', '.editunity', function() {
        const btn = $(this);
        
        $('#unit_id').val(btn.data('id'));
        $('#edit_unit_name').val(btn.data('unit_name'));
        
        $('#myModalfour').modal('show');
    });

    // Handle Update Unity button
    $(document).on('click', '#EditUnityBtn', function (e) {
        e.preventDefault();
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            unit_id: $('#unit_id').val(),
            unit_name: $('#edit_unit_name').val().trim()
        };

        if (!payload.unit_name) {
            showToast('Please fill unit name!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/unity/update',
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

    // Handle Delete Unity
    $(document).on('click', '.deleteunity', function() {
        const btn = $(this);
        const unitId = btn.data('id');
        const unitName = btn.data('unit_name');
        
        if (!confirm(`Are you sure you want to delete "${unitName}"?`)) {
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/unity/delete',
            method: 'DELETE',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ unit_id: unitId }),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
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
            }
        });
    });

});
</script>
