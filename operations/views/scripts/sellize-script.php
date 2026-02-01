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

    // Initialize on page load
    initDataTable();

    // Function to load sallize from API and populate DataTable
    function loadData() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/sallize/get-all-sallize', function (res) {
            if (!res.success) return;

            // Clear and destroy existing DataTable
            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                $('#data-table-basic').DataTable().destroy();
            }
            
            // Clear tbody
            $('#sallizedata').empty();

            // Add new rows
            $.each(res.data, function (index, record) {
                const statusClass = record.status === 'active' ? 'text-success' : 
                                  (record.status === 'inactive' ? 'text-danger' : 'text-warning');
                
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.sallize_name}</td>
                    <td>${record.description || ''}</td>
                    <td><span class="${statusClass}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span></td>
                    <td>
                        <div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editsallize"
                                title="Edit Sallize"
                                data-id="${record.sallize_id}"
                                data-sallize_name="${record.sallize_name}"
                                data-description="${record.description || ''}"
                                data-status="${record.status}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                            <button class="btn btn-default btn-icon-notika deletesallize"
                                title="Delete Sallize"
                                data-id="${record.sallize_id}"
                                data-sallize_name="${record.sallize_name}">
                                <i class="notika-icon text-danger notika-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                
                $('#sallizedata').append(row);
            });

            // Reinitialize DataTable
            TableData = $('#data-table-basic').DataTable({
                pageLength: 10
            });
        });
    }

    // Load data on page load
    loadData();

    // Handle Save Sallize button
    $(document).on('click', '#saveSallizeBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            sallize_name: $('#sallize_name').val().trim(),
            description: $('#description').val().trim(),
            status: $('#status').val()
        };

        if (!payload.sallize_name || !payload.status) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/sallize/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalthree').modal('hide');
                    $('#sallize_name').val('');
                    $('#description').val('');
                    $('#status').val('').trigger('chosen:updated');
                    loadData(); // Refresh table
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

    // Handle Edit Sallize click
    $(document).on('click', '.editsallize', function() {
        const btn = $(this);
        
        $('#sallize_id').val(btn.data('id'));
        $('#edit_sallize_name').val(btn.data('sallize_name'));
        $('#edit_description').val(btn.data('description'));
        $('#edit_status').val(btn.data('status')).trigger('chosen:updated');
        
        $('#myModalfour').modal('show');
    });

    // Handle Update Sallize button
    $(document).on('click', '#EditSallizeBtn', function (e) {
        e.preventDefault();
        const btn = this;
        setButtonLoading(btn, true);
        
        const payload = {
            sallize_id: $('#sallize_id').val(),
            sallize_name: $('#edit_sallize_name').val().trim(),
            description: $('#edit_description').val().trim(),
            status: $('#edit_status').val()
        };

        if (!payload.sallize_name || !payload.status) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/sallize/update',
            method: 'PUT',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalfour').modal('hide');
                    loadData(); // Refresh table
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

    // Handle Delete Sallize
    $(document).on('click', '.deletesallize', function() {
        const btn = $(this);
        const sallizeId = btn.data('id');
        const sallizeName = btn.data('sallize_name');
        
        if (!confirm(`Are you sure you want to delete "${sallizeName}"?`)) {
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/sallize/delete',
            method: 'DELETE',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ sallize_id: sallizeId }),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    loadData(); // Refresh table
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

