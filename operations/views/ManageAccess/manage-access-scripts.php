
<script>
$(document).ready(function () {

    // Handle Save button
$(document).on('click', '#savepermissionBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
         const payload = {
                role_id: $('#role_id_access').val(),
                menu_ids: $('#menu_id').val() || []
            };

        if (!payload.role_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
           
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/settings/createaccess',
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

// load access data 
function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/getaccessdata', function (res) {
        if (!res.success) return;

        TableData.clear();
        
        // Group data by role_id
        const grouped = {};
        $.each(res.data, function (index, item) {
            const roleId = item.role_id;
            if (!grouped[roleId]) {
                grouped[roleId] = {
                    role_name: item.role_name,
                    description: item.description,
                    menus: []
                };
            }
            grouped[roleId].menus.push({
                menu_id: item.role_menu_id,
                menu_name: item.menu_name,
                icon: item.icon
            });
        });

        // Add rows to DataTable
        let rowIndex = 1;
        $.each(grouped, function (roleId, roleData) {
            // Create menu badges HTML
            let menusHtml = '<div class="menu-tags">';
            $.each(roleData.menus, function (index, menu) {
                menusHtml += `
                    <span style="display: inline-block; margin: 2px 5px 2px 0;">
                        <i class="notika-icon ${menu.icon}"></i>
                        ${menu.menu_name}
                        <button class="btn btn-default btn-icon-notika btn-sm removeMenuBtn" 
                            title="Remove Menu"
                            data-role-id="${roleId}"
                            data-menu-id="${menu.menu_id}"
                            data-role-name="${menu.role_name}"
                            data-menu-name="${menu.menu_name}">
                            <i class="notika-icon notika-close"></i>
                        </button>
                    </span>
                `;
            });
            menusHtml += '</div>';

            // Add row to DataTable
            TableData.row.add([
                rowIndex,
                `<strong>${roleData.role_name}</strong><br>
                 <small class="text-muted">${roleData.description}</small>`,
                menusHtml
            ]);
            
            rowIndex++;
        });

        TableData.draw(false);
        
        // Initialize remove button events
        initializeRemoveButtons();
    });
}
    });




$(document).on('click', '.removeMenuBtn', function() {
    const btn = $(this);
    const roleId = $(this).data('role-id');
    const menuId = $(this).data('menu-id');
    const menuName = $(this).data('menu-name');
    
    swal({   
        title: "Are you sure?",   
        text: "You will remove " + menuName + "! This action cannot be undone.",   
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
                url: '<?= App::baseUrl() ?>/_ikawa/settings/removeaccessonrole/' + menuId,
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal("Deleted!", response.message || "Menu has been removed successfully.", "success");
                        loadData();
                        
                    } else {
                        swal("Error!", response.message || "Failed to remove menu.", "error");
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

// load access data 
function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/settings/getaccessdata', function (res) {
        if (!res.success) return;

        TableData.clear();
        
        // Group data by role_id
        const grouped = {};
        $.each(res.data, function (index, item) {
            const roleId = item.role_id;
            if (!grouped[roleId]) {
                grouped[roleId] = {
                    role_name: item.role_name,
                    description: item.description,
                    menus: []
                };
            }
            grouped[roleId].menus.push({
                menu_id: item.role_menu_id,
                menu_name: item.menu_name,
                icon: item.icon
            });
        });

        // Add rows to DataTable
        let rowIndex = 1;
        $.each(grouped, function (roleId, roleData) {
            // Create menu badges HTML
            let menusHtml = '<div class="menu-tags">';
            $.each(roleData.menus, function (index, menu) {
                menusHtml += `
                    <span style="display: inline-block; margin: 2px 5px 2px 0;">
                        <i class="notika-icon ${menu.icon}"></i>
                        ${menu.menu_name}
                        <button class="btn btn-default btn-icon-notika btn-sm removeMenuBtn" 
                            title="Remove Menu"
                            data-role-id="${roleId}"
                            data-menu-id="${menu.menu_id}"
                            data-menu-name="${menu.menu_name}">
                            <i class="notika-icon notika-close"></i>
                        </button>
                    </span>
                `;
            });
            menusHtml += '</div>';

            // Add row to DataTable
            TableData.row.add([
                rowIndex,
                `<strong>${roleData.role_name}</strong><br>
                 <small class="text-muted">${roleData.description}</small>`,
                menusHtml
            ]);
            
            rowIndex++;
        });

        TableData.draw(false);
        
        // Initialize remove button events
        initializeRemoveButtons();
    });
}

        }
    });
});


    
});

</script>

