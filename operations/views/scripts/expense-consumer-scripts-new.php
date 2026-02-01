
<script>
// Toast notification function (global)
function showToastConsumer(message, type) {
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

// Global function to load consumers
window.loadConsumers = function() {
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/expense-consumers/get-all',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            // Get or initialize DataTable
            var consumerTable;
            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                consumerTable = $('#data-table-basic').DataTable();
            } else {
                consumerTable = $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            }

            if (response.success && response.data) {
                consumerTable.clear();

                $.each(response.data, function (index, consumer) {
                    // Check if consumer is in use
                    $.ajax({
                        url: '<?= App::baseUrl() ?>/_ikawa/expense-consumers/check-in-use/' + consumer.cons_id,
                        method: 'GET',
                        dataType: 'json',
                        async: false,
                        success: function (checkResponse) {
                            var inUse = checkResponse.success && checkResponse.data.in_use;
                            var deleteButton = '';
                            
                            if (!inUse) {
                                deleteButton = `<button class="btn btn-danger btn-icon-notika deleteConsumerBtn"
                                    title="Delete Consumer"
                                    data-id="${consumer.cons_id}"
                                    data-cons_name="${consumer.cons_name}">
                                    <i class="notika-icon notika-close"></i>
                                </button>`;
                            }

                            consumerTable.row.add([
                                index + 1,
                                consumer.cons_name,
                                consumer.phone,
                                `<div class="button-icon-btn button-icon-btn-rd">
                                    <button class="btn btn-default btn-icon-notika editConsumerBtn"
                                        title="Edit Consumer"
                                        data-id="${consumer.cons_id}"
                                        data-cons_name="${consumer.cons_name}"
                                        data-phone="${consumer.phone}">
                                        <i class="notika-icon notika-edit"></i>
                                    </button>
                                    ${deleteButton}
                                </div>`
                            ]);
                        }
                    });
                });

                consumerTable.draw(false);
            } else {
                consumerTable.clear().draw();
            }
        },
        error: function () {
            showToastConsumer('Failed to load expense consumers', 'error');
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

    // Handle Save Consumer button
    $(document).on('click', '#saveConsumerBtn', function (e) {
        e.preventDefault();
        const btn = this;
        setButtonLoading(btn, true);
        
        const consumerData = {
            cons_name: $('#cons_name').val().trim(),
            phone: $('#phone').val().trim()
        };

        if (!consumerData.cons_name || !consumerData.phone) {
            showToastConsumer('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-consumers/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(consumerData),
            success: function (response) {
                if (response.success) {
                    showToastConsumer(response.message, 'success');
                    $('#createConsumerModal').modal('hide');
                    $('#createConsumerModal input').val('');
                    loadConsumers();
                } else {
                    showToastConsumer(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastConsumer(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Edit Consumer button click
    $(document).on('click', '.editConsumerBtn', function () {
        const consId = $(this).data('id');
        const consName = $(this).data('cons_name');
        const phone = $(this).data('phone');

        $('#edit_cons_id').val(consId);
        $('#edit_cons_name').val(consName);
        $('#edit_phone').val(phone);

        // Reset button text and data
        $('#updateConsumerBtn').html('Save changes').removeData('original-text');

        $('#editConsumerModal').modal('show');
    });

    // Handle Update Consumer button
    $(document).on('click', '#updateConsumerBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const consumerData = {
            cons_id: $('#edit_cons_id').val(),
            cons_name: $('#edit_cons_name').val().trim(),
            phone: $('#edit_phone').val().trim()
        };

        if (!consumerData.cons_name || !consumerData.phone) {
            showToastConsumer('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-consumers/update',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(consumerData),
            success: function (response) {
                if (response.success) {
                    showToastConsumer(response.message, 'success');
                    $('#editConsumerModal').modal('hide');
                    loadConsumers();
                } else {
                    showToastConsumer(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastConsumer(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Delete Consumer button
    $(document).on('click', '.deleteConsumerBtn', function (e) {
        e.preventDefault();
        const btn = $(this);
        const consId = btn.data('id');
        const consName = btn.data('cons_name') || 'this consumer';

        swal({   
            title: "Are you sure?",   
            text: "You will delete " + consName + "! This action cannot be undone.",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonText: "Yes, delete!",
            cancelButtonText: "No, cancel!"
        }).then(function(isConfirm){
            if (isConfirm) {
                const originalHtml = btn.html();
                btn.html('<i class="notika-icon notika-loading"></i> Deleting...').prop('disabled', true);
                
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/expense-consumers/delete',
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({ cons_id: consId }),
                    success: function (response) {
                        if (response.success) {
                            swal("Deleted!", response.message || "Consumer has been deleted successfully.", "success");
                            loadConsumers();
                        } else {
                            swal("Error!", response.message || "Failed to delete consumer.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMsg = "Failed to delete consumer. Please try again.";
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

    // Clear modal inputs on hide
    $('#createConsumerModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
    });

    $('#editConsumerModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
    });
});
</script>
