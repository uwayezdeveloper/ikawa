
<script>
$(document).ready(function () {

    // Handle Save User button
$(document).on('click', '#saveUserBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
          const userData = {
            first_name: $('#first_name').val().trim(),
            last_name: $('#last_name').val().trim(),
            email: $('#email').val().trim(),
            username: $('#username').val().trim(),
            phone: $('#phone').val().trim(),
            role_id: $('#role_id').val(),
            gender: $('#gender').val(),
            nid: $('#nid').val().trim(),
            loc_id: $('#loc_id').val()
        };

        if (!userData.username || !userData.role_id || !userData.loc_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/users/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(userData),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');

                    $('#myModalthree').modal('hide');
                    $('#myModalthree input').val('');
                    $('#myModalthree select').val('').trigger('chosen:updated');
                    loadUsers();
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
    const userTable = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

    // Function to load users from API and populate DataTable
    function loadUsers() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/users/get-all-users', function (res) {
            if (!res.success) return;

            userTable.clear();

            $.each(res.data, function (index, user) {
                userTable.row.add([
                index + 1,
                user.first_name,
                user.last_name,
                user.username,
                user.phone,
                user.role_name,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika edituser"
                        title="Edit User"
                        data-id="${user.user_id}"
                        data-first_name="${user.first_name}"
                        data-last_name="${user.last_name}"
                        data-email="${user.email}"
                        data-username="${user.username}"
                        data-phone="${user.phone}"
                        data-role_id="${user.role_id}"
                        data-gender="${user.gender}"
                        data-nid="${user.nid}"
                        data-loc_id="${user.loc_id}"
                    >
                        <i class="notika-icon notika-edit"></i>
                    </button>
                    <button class="btn btn-default btn-icon-notika deleteuser" title="Delete User" data-id="${user.user_id}">
                        <i class="notika-icon text-danger notika-trash"></i>
                    </button>
                </div>`
            ]);
});


            userTable.draw(false);
        });
    }
    });

$(document).on('click', '.edituser', function() {
    const btn = $(this);

    // Fill modal fields
    $('#edit_first_name').val(btn.data('first_name'));
    $('#edit_last_name').val(btn.data('last_name'));
    $('#edit_email').val(btn.data('email'));
    $('#edit_username').val(btn.data('username'));
    $('#edit_phone').val(btn.data('phone'));
    $('#edit_role_id').val(btn.data('role_id')).trigger('chosen:updated');
    $('#edit_gender').val(btn.data('gender')).trigger('chosen:updated');
    $('#edit_nid').val(btn.data('nid'));
    $('#edit_loc_id').val(btn.data('loc_id')).trigger('chosen:updated');
    $("#user_id").val( btn.data('id'));
    $('#myModalfour').modal('show');
});

    // Handle edit User button
$(document).on('click', '#EditUserBtn', function (e) {
    e.preventDefault();
    const btn = this;
    setButtonLoading(btn, true);
          const userData = {
            first_name: $('#edit_first_name').val().trim(),
            last_name: $('#edit_last_name').val().trim(),
            email: $('#edit_email').val().trim(),
            username: $('#edit_username').val().trim(),
            phone: $('#edit_phone').val().trim(),
            role_id: $('#edit_role_id').val(),
            gender: $('#edit_gender').val(),
            nid: $('#edit_nid').val().trim(),
            loc_id: $('#edit_loc_id').val(),
            user_id:$('#user_id').val()
        };

        if (!userData.username || !userData.role_id || !userData.loc_id) {
            showToast('Please fill all required fields!', 'error');
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/users/update',
            method: 'PUT',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(userData),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');

                    $('#myModalfour').modal('hide');
                    $('#myModalfour input').val('');
                    $('#myModalfour select').val('').trigger('chosen:updated');
                    loadUsers();
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
    const userTable = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

    // Function to load users from API and populate DataTable
    function loadUsers() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/users/get-all-users', function (res) {
            if (!res.success) return;

            userTable.clear();

            $.each(res.data, function (index, user) {
                userTable.row.add([
                index + 1,
                user.first_name,
                user.last_name,
                user.username,
                user.phone,
                user.role_name,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika edituser"
                        title="Edit User"
                        data-id="${user.user_id}"
                        data-first_name="${user.first_name}"
                        data-last_name="${user.last_name}"
                        data-email="${user.email}"
                        data-username="${user.username}"
                        data-phone="${user.phone}"
                        data-role_id="${user.role_id}"
                        data-gender="${user.gender}"
                        data-nid="${user.nid}"
                        data-loc_id="${user.loc_id}"
                    >
                        <i class="notika-icon notika-edit"></i>
                    </button>
                    <button class="btn btn-default btn-icon-notika deleteuser" title="Delete User" data-id="${user.user_id}">
                        <i class="notika-icon text-danger notika-trash"></i>
                    </button>
                </div>`
            ]);
});


            userTable.draw(false);
        });
    }
 });


$(document).on('click', '.deleteuser', function() {
    const btn = $(this);
    const user_id = btn.data('id');
    const user_name = btn.data('first_name') || 'this user'; 
    
    swal({   
        title: "Are you sure?",   
        text: "You will delete " + user_name + "! This action cannot be undone.",   
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
                url: '<?= App::baseUrl() ?>/_ikawa/users/delete/' + user_id,
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal("Deleted!", response.message || "User has been deleted successfully.", "success");
                        loadUsers();
                        
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
    const userTable = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

    // Function to load users from API and populate DataTable
    function loadUsers() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/users/get-all-users', function (res) {
            if (!res.success) return;

            userTable.clear();

            $.each(res.data, function (index, user) {
                userTable.row.add([
                index + 1,
                user.first_name,
                user.last_name,
                user.username,
                user.phone,
                user.role_name,
                `<div class="button-icon-btn button-icon-btn-rd">
                    <button class="btn btn-default btn-icon-notika edituser"
                        title="Edit User"
                        data-id="${user.user_id}"
                        data-first_name="${user.first_name}"
                        data-last_name="${user.last_name}"
                        data-email="${user.email}"
                        data-username="${user.username}"
                        data-phone="${user.phone}"
                        data-role_id="${user.role_id}"
                        data-gender="${user.gender}"
                        data-nid="${user.nid}"
                        data-loc_id="${user.loc_id}"
                    >
                        <i class="notika-icon notika-edit"></i>
                    </button>
                    <button class="btn btn-default btn-icon-notika deleteuser" title="Delete User" data-id="${user.user_id}">
                        <i class="notika-icon text-danger notika-trash"></i>
                    </button>
                </div>`
            ]);
});


            userTable.draw(false);
        });
    }

        }
    });
});
    
});
</script>

