
<script>
$(document).ready(function () {
  // Handle Save  button
$(document).on('click', '#saveSupplierBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            full_name: $('#full_name').val().trim(),
            email: $('#email').val().trim(),
            phone: $('#phone').val().trim(),
            type: $('#type').val(),
            address: $('#address').val().trim()
        };

        if (!payload.phone || !payload.full_name) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
           
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/inventory/createsupplier',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');

                    $('#myModalthree').modal('hide');
                    $('#myModalthree input').val('');
                    $('#myModalthree select').val('').trigger('chosen:updated');
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
            setButtonLoading(btn, false); // always reset button
        }
        });

    // Initialize DataTable
    const TableData = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

   
    function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inventory/getsuppliers', function (res) {
       if (!res.success || !res.data) {
            return;
        }

        TableData.clear();

        $.each(res.data, function (index, record) {
            
            TableData.row.add([
                index + 1,
                record.full_name || '',
                record.email || '',
                record.phone || '',
                record.type || '',
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Supplier"
                        data-id="${record.sup_id}"
                        data-full_name="${record.full_name || ''}"
                        data-email="${record.email || ''}"
                        data-phone="${record.phone || ''}"
                        data-address="${record.address || ''}"
                        data-type="${record.type || ''}">
                        <i class="notika-icon notika-edit"></i>
                    </button>
                    <button class="btn btn-default btn-icon-notika deleterecord" 
                        title="Delete Supplier" 
                        data-id="${record.sup_id}" 
                        data-full_name="${record.full_name || ''}">
                        <i class="notika-icon text-danger notika-trash"></i>
                    </button>
                </div>`
            ]);
        });

        TableData.draw(false);
    })
        }
    });


$(document).on('click', '.editrecord', function() {
    const btn = $(this);
    // Fill modal fields
    $('#edit_full_name').val(btn.data('full_name'));
    $('#edit_email').val(btn.data('email'));
    $('#edit_phone').val(btn.data('phone'));
    $("#edit_address").val( btn.data('address'));
    $('#edit_type').val(btn.data('type')).trigger('chosen:updated');
    $("#sup_id").val( btn.data('id'));
    $('#myModalfour').modal('show');
});

   // Handle edit User button
$(document).on('click', '#EditsupplierBtn', function (e) {
    e.preventDefault();
      e.preventDefault();
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            full_name: $('#edit_full_name').val().trim(),
            email: $('#edit_email').val().trim(),
            phone: $('#edit_phone').val().trim(),
            address: $('#edit_address').val().trim(),
            type: $('#edit_type').val().trim(),            
            sup_id:$('#sup_id').val()
        };

        if (!payload.phone || !payload.full_name) {
            showToast('Please fill all required fields!', 'error');
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/inventory/updatesupplier',
            method: 'PUT',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');

                    $('#myModalfour').modal('hide');
                    $('#myModalfour input').val('');
                    $('#myModalfour select').val('').trigger('chosen:updated');
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
            setButtonLoading(btn, false); // always reset button
        }
        });

    
    // Initialize DataTable
    const TableData = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

   
    function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inventory/getsuppliers', function (res) {
       if (!res.success || !res.data) {
            return;
        }

        TableData.clear();

        $.each(res.data, function (index, record) {
            
            TableData.row.add([
                index + 1,
                record.full_name || '',
                record.email || '',
                record.phone || '',
                record.type || '',
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Supplier"
                        data-id="${record.sup_id}"
                        data-full_name="${record.full_name || ''}"
                        data-email="${record.email || ''}"
                        data-phone="${record.phone || ''}"
                        data-address="${record.address || ''}"
                        data-type="${record.type || ''}">
                        <i class="notika-icon notika-edit"></i>
                    </button>
                    <button class="btn btn-default btn-icon-notika deleterecord" 
                        title="Delete Supplier" 
                        data-id="${record.sup_id}" 
                        data-full_name="${record.full_name || ''}">
                        <i class="notika-icon text-danger notika-trash"></i>
                    </button>
                </div>`
            ]);
        });

        TableData.draw(false);
    })
        }

 });


 $(document).on('click', '.deleterecord', function() {
    const btn = $(this);
    const sup_id = btn.data('id');
    const full_name = btn.data('full_name') || 'this supplier'; 
    
    swal({   
        title: "Are you sure?",   
        text: "You will delete " + full_name + "! This action cannot be undone.",   
        type: "warning",   
        showCancelButton: true,   
        confirmButtonText: "Yes, delete!",
        cancelButtonText: "No, cancel!"
    }).then(function(isConfirm){
        if (isConfirm) {
            // Show loading state on button
            const originalHtml = btn.html();
            btn.html('<i class="notika-icon notika-loading"></i> Deleting...').prop('disabled', true);
            
            $.ajax({
                url: '<?= App::baseUrl() ?>/_ikawa/inventory/deletesupplier/' + sup_id,
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal("Deleted!", response.message || "Supplier has been deleted successfully.", "success");
                        loadData();
                        
                    } else {
                        swal("Error!", response.message || "Failed to delete user.", "error");
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = "Failed to delete user. Please try again.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    swal("Error!", errorMsg, "error");
                },
                complete: function() {
                    // Restore button state
                    btn.html(originalHtml).prop('disabled', false);
                }
            });

 // Initialize DataTable
    const TableData = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

   
    function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inventory/getsuppliers', function (res) {
       if (!res.success || !res.data) {
            return;
        }

        TableData.clear();

        $.each(res.data, function (index, record) {
            
            TableData.row.add([
                index + 1,
                record.full_name || '',
                record.email || '',
                record.phone || '',
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Supplier"
                        data-id="${record.sup_id}"
                        data-full_name="${record.full_name || ''}"
                        data-email="${record.email || ''}"
                        data-phone="${record.phone || ''}"
                        data-address="${record.address || ''}">
                        <i class="notika-icon notika-edit"></i>
                    </button>
                    <button class="btn btn-default btn-icon-notika deleterecord" 
                        title="Delete Supplier" 
                        data-id="${record.sup_id}" 
                        data-full_name="${record.full_name || ''}">
                        <i class="notika-icon text-danger notika-trash"></i>
                    </button>
                </div>`
            ]);
        });

        TableData.draw(false);
    })
        }

        }
    });
});
    
});

</script>

