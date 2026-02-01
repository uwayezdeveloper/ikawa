
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<script>
// Toast notification function (global)
function showToast(message, type) {
    var toast = $('<div class="toast ' + type + '">' + message + '</div>');
    $('body').append(toast);
    setTimeout(function () {
        toast.addClass('show');
    }, 100);
    setTimeout(function () {
        toast.removeClass('show');
        setTimeout(function () {
            toast.remove();
        }, 500);
    }, 3000);
}

// Global function to load accounts
window.loadAccounts = function() {
    // Get user's location from PHP session
    var userLocation = '<?= isset($_SESSION["loc_id"]) ? $_SESSION["loc_id"] : "" ?>';
    
    console.log('User Location ID:', userLocation);
    
    if (!userLocation) {
        showToast('User location not found', 'error');
        return;
    }
    
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/accounts/get-allbylocation?st_id=' + userLocation,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log('API Response:', response);
            
            // Get or initialize DataTable
            var accountTable;
            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                accountTable = $('#data-table-basic').DataTable();
            } else {
                accountTable = $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            }

            if (response.success && response.data) {
                accountTable.clear();

                $.each(response.data, function (index, account) {
                    console.log('Account data:', account); // Debug log
                    console.log('Location name:', account.location_name); // Debug location specifically
                    var statusBadge = account.status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-warning">Onhold</span>';
                    var canDelete = account.status == 1;
                    var editButton = account.status == 1 ? `<button class="btn btn-default btn-icon-notika editAccountBtn"
                                title="Edit Account"
                                data-id="${account.acc_id}"
                                data-acc_name="${account.acc_name}"
                                data-acc_reference_num="${account.acc_reference_num}"
                                data-mode_id="${account.mode_id}"
                                data-st_id="${account.st_id}">
                                <i class="notika-icon notika-edit"></i>
                            </button>` : '';
                    var deleteButton = canDelete ? `<button class="btn btn-danger btn-icon-notika deleteAccountBtn"
                                title="Set to Onhold"
                                data-id="${account.acc_id}"
                                data-balance="${account.balance}"
                                data-acc_name="${account.acc_name}">
                                <i class="notika-icon notika-close"></i>
                            </button>` : '';
                    var activateButton = account.status == 0 ? `<button class="btn btn-success btn-icon-notika activateAccountBtn"
                                title="Activate Account"
                                data-id="${account.acc_id}"
                                data-acc_name="${account.acc_name}">
                                <i class="notika-icon notika-checked"></i>
                            </button>` : '';
                    
                    var row = accountTable.row.add([
                        index + 1,
                        account.acc_name,
                        account.acc_reference_num,
                        account.Mode_names,
                        account.location_name || 'N/A',
                        parseInt(account.balance).toLocaleString() + ' RWF',
                        statusBadge,
                        `<div class="button-icon-btn button-icon-btn-rd">${editButton}${deleteButton}${activateButton}</div>`
                    ]).node();
                    
                    // Apply warning background for onhold accounts
                    if (account.status == 0) {
                        $(row).css('background-color', '#fff3cd');
                    }
                });

                accountTable.draw(false);
            } else {
                accountTable.clear().draw();
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            showToast('Failed to load accounts', 'error');
        }
    });
};

$(document).ready(function () {

    // Button loading state
    function setButtonLoading(btn, isLoading) {
        if (isLoading) {
            // Store original text before changing
            if (!$(btn).data('original-text')) {
                $(btn).data('original-text', $(btn).html());
            }
            $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        } else {
            var originalText = $(btn).data('original-text') || 'Save';
            $(btn).prop('disabled', false).html(originalText);
        }
    }

    // Handle Save Account button
    $(document).on('click', '#saveAccountBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const accountData = {
            acc_name: $('#acc_name').val().trim(),
            acc_reference_num: $('#acc_reference_num').val().trim(),
            mode_id: $('#mode_id').val()
        };

        if (!accountData.acc_name || !accountData.acc_reference_num || !accountData.mode_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/accounts/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(accountData),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#createAccountModal').modal('hide');
                    $('#createAccountModal input').val('');
                    $('#createAccountModal select').val('').trigger('chosen:updated');
                    loadAccounts();
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
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Edit Account button click
    $(document).on('click', '.editAccountBtn', function () {
        const accId = $(this).data('id');
        const accName = $(this).data('acc_name');
        const accReferenceNum = $(this).data('acc_reference_num');
        const modeId = $(this).data('mode_id');
        const stId = $(this).data('st_id');

        $('#edit_acc_id').val(accId);
        $('#edit_acc_name').val(accName);
        $('#edit_acc_reference_num').val(accReferenceNum);
        $('#edit_mode_id').val(modeId).trigger('chosen:updated');
        $('#edit_st_id').val(stId).trigger('chosen:updated');

        // Reset button text and data
        $('#updateAccountBtn').html('Save changes').removeData('original-text');

        $('#editAccountModal').modal('show');
    });

    // Handle Update Account button
    $(document).on('click', '#updateAccountBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const accountData = {
            acc_id: $('#edit_acc_id').val(),
            acc_name: $('#edit_acc_name').val().trim(),
            acc_reference_num: $('#edit_acc_reference_num').val().trim(),
            mode_id: $('#edit_mode_id').val(),
            st_id: $('#edit_st_id').val()
        };

        if (!accountData.acc_name || !accountData.acc_reference_num || !accountData.mode_id || !accountData.st_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/accounts/update',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(accountData),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#editAccountModal').modal('hide');
                    loadAccounts();
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
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Delete Account button
    $(document).on('click', '.deleteAccountBtn', function () {
        const btn = $(this);
        const accId = btn.data('id');
        const accName = btn.data('acc_name') || 'this account';
        const balance = parseFloat(btn.data('balance')) || 0;

        swal({   
            title: "Are you sure?",   
            text: "You will set " + accName + " to onhold status. Onhold accounts cannot be used in transactions.",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonText: "Yes, set to onhold!",
            cancelButtonText: "No, cancel!"
        }).then(function(isConfirm){
            if (isConfirm) {
                // Show loading state on button
                const originalHtml = btn.html();
                btn.html('<i class="notika-icon notika-loading"></i> Processing...').prop('disabled', true);
                
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/accounts/delete/' + accId,
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            swal("Success!", response.message || "Account has been set to onhold successfully.", "success");
                            loadAccounts();
                        } else {
                            swal("Error!", response.message || "Failed to update account status.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMsg = "Failed to update account status. Please try again.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        swal("Error!", errorMsg, "error");
                    },
                    complete: function () {
                        // Restore button state
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle Activate Account button
    $(document).on('click', '.activateAccountBtn', function () {
        const btn = $(this);
        const accId = btn.data('id');
        const accName = btn.data('acc_name') || 'this account';

        swal({   
            title: "Are you sure?",   
            text: "You will reactivate " + accName + ". This account will be available for transactions again.",   
            type: "info",   
            showCancelButton: true,   
            confirmButtonText: "Yes, activate it!",
            cancelButtonText: "No, cancel!"
        }).then(function(isConfirm){
            if (isConfirm) {
                const originalHtml = btn.html();
                btn.html('<i class="notika-icon notika-loading"></i> Processing...').prop('disabled', true);
                
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/accounts/reactivate/' + accId,
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            swal("Success!", response.message || "Account has been reactivated successfully.", "success");
                            loadAccounts();
                        } else {
                            swal("Error!", response.message || "Failed to reactivate account.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMsg = "Failed to reactivate account. Please try again.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        swal("Error!", errorMsg, "error");
                    },
                    complete: function () {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            }
        });
    });

    // Clear modal on close
    $('#createAccountModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
        $(this).find('select').val('').trigger('chosen:updated');
    });

    // Load accounts on page load
    loadAccounts();

});
</script>
