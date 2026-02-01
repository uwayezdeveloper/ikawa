
<script>
$(document).ready(function () {

    // Handle Save User button
$(document).on('click', '#saveLocationBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            location_name: $('#location_name').val().trim(),
            description: $('#description').val().trim(),
            type: $('#type').val()
        };

        if (!payload.location_name) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
           
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/createlocation',
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
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/location', function (res) {
            if (!res.success) return;

            TableData.clear();

            $.each(res.data, function (index, record) {
                TableData.row.add([
                index + 1,
                record.location_name,
                record.description,
                record.type,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Station"
                        data-id="${record.loc_id}"
                        data-location_name="${record.location_name}"
                        data-description="${record.description}"
                        data-type="${record.type}">
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
    $('#edit_location_name').val(btn.data('location_name'));
    $('#edit_description').val(btn.data('description'));
    $('#edit_type').val(btn.data('type'));
    $("#loc_id").val( btn.data('id'));
    $('#myModalfour').modal('show');
});

    // Handle edit User button
$(document).on('click', '#EditstationBtn', function (e) {
    e.preventDefault();
      e.preventDefault();
    const btn = this;
    setButtonLoading(btn, true);
          const payload = {
            location_name: $('#edit_location_name').val().trim(),
            description: $('#edit_description').val().trim(),
            type: $('#edit_type').val(),
            loc_id:$('#loc_id').val()
        };

        if (!payload.location_name) {
            showToast('Please fill all required fields!', 'error');
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/updatelocation',
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

    // Function to load from API and populate DataTable
    function loadData() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/location', function (res) {
            if (!res.success) return;

            TableData.clear();

            $.each(res.data, function (index, record) {
                TableData.row.add([
                index + 1,
                record.location_name,
                record.description,
                record.type,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika editrecord"
                        title="Edit Station"
                        data-id="${record.loc_id}"
                        data-location_name="${record.location_name}"
                        data-description="${record.description}"
                        data-type="${record.type}">
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

