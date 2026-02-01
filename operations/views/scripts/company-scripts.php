
<script>
$(document).ready(function () {

    // Handle Save User button
$(document).on('click', '#saveCompanyBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            cpy_full_name: $('#cpy_full_name').val().trim(),
            cpy_short_name: $('#cpy_short_name').val().trim(),
            phone: $('#phone').val().trim(),
            email: $('#email').val().trim(),
            address: $('#address').val().trim()
        };

        if (!payload.cpy_full_name || !payload.phone) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
           
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/createcompany',
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
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/getcompany', function (res) {
            if (!res.success) return;

            TableData.clear();

            $.each(res.data, function (index, record) {
                TableData.row.add([
                index + 1,
                record.cpy_full_name,
                record.cpy_short_name,
                record.email,
                record.phone,
                record.address,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Company"
                        data-id="${record.cpy_id}"
                        data-cpy_full_name="${record.cpy_full_name}"
                        data-cpy_short_name="${record.cpy_short_name}"
                        data-email="${record.email}"
                        data-phone="${record.phone}"
                        data-address="${record.address}">
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
    $('#edit_cpy_full_name').val(btn.data('cpy_full_name'));
    $('#edit_cpy_short_name').val(btn.data('cpy_short_name'));
    $('#edit_phone').val(btn.data('phone'));
    $('#edit_email').val(btn.data('email'));
    $('#edit_address').val(btn.data('address'));
    $("#cpy_id").val( btn.data('id'));
    $('#myModalfour').modal('show');
});

    // Handle edit User button
$(document).on('click', '#EditcompanyBtn', function (e) {
    e.preventDefault();
      e.preventDefault();
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            cpy_full_name: $('#edit_cpy_full_name').val().trim(),
            cpy_short_name: $('#edit_cpy_short_name').val().trim(),
            phone: $('#edit_phone').val().trim(),
            email: $('#edit_email').val().trim(),
            address: $('#edit_address').val().trim(),
            cpy_id:$('#cpy_id').val()
        };

        if (!payload.cpy_full_name || !payload.phone) {
            showToast('Please fill all required fields!', 'error');
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/updatecompany',
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
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/getcompany', function (res) {
            if (!res.success) return;

            TableData.clear();

            $.each(res.data, function (index, record) {
                TableData.row.add([
                index + 1,
                record.cpy_full_name,
                record.cpy_short_name,
                record.email,
                record.phone,
                record.address,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Company"
                        data-id="${record.cpy_id}"
                        data-cpy_full_name="${record.cpy_full_name}"
                        data-cpy_short_name="${record.cpy_short_name}"
                        data-email="${record.email}"
                        data-phone="${record.phone}"
                        data-address="${record.address}">
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

