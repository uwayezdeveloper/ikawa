
<script>
$(document).ready(function () {

    // Handle Save User button
$(document).on('click', '#saveRoleBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            role_name: $('#role_name').val().trim(),
            description: $('#description').val().trim()
        };

        if (!payload.role_name) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
           
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/createrole',
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

    // Function to load users from API and populate DataTable
    function loadData() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/roles', function (res) {
            if (!res.success) return;

            TableData.clear();

            $.each(res.data, function (index, record) {
                TableData.row.add([
                index + 1,
                record.role_name,
                record.description,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Role"
                        data-id="${record.role_id}"
                        data-role_name="${record.role_name}"
                        data-description="${record.description}">
                        <i class="notika-icon notika-edit"></i>
                    </button>
                </div>`
            ]);
});


            TableData.draw(false);
        });
    }
    });

$(document).on('click', '.editrecord', function() {
    const btn = $(this);
    // Fill modal fields
    $('#edit_role_name').val(btn.data('role_name'));
    $('#edit_description').val(btn.data('description'));
    $("#role_id").val( btn.data('id'));
    $('#myModalfour').modal('show');
});

    // Handle edit User button
$(document).on('click', '#EditroleBtn', function (e) {
    e.preventDefault();
      e.preventDefault();
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            role_name: $('#edit_role_name').val().trim(),
            description: $('#edit_description').val().trim(),
            role_id:$('#role_id').val()
        };

        if (!payload.role_name) {
            showToast('Please fill all required fields!', 'error');
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/updateroles',
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

    // Function to load users from API and populate DataTable
    function loadData() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/roles', function (res) {
            if (!res.success) return;

            TableData.clear();

            $.each(res.data, function (index, record) {
                TableData.row.add([
                index + 1,
                record.role_name,
                record.description,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Role"
                        data-id="${record.role_id}"
                        data-role_name="${record.role_name}"
                        data-description="${record.description}">
                        <i class="notika-icon notika-edit"></i>
                    </button>
                </div>`
            ]);
});


            TableData.draw(false);
        });
    }

 });



    
});

</script>

