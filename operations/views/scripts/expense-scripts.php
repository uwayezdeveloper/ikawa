
<script>
// Toast notification function (global)
function showToastExpense(message, type) {
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

// Global function to load expenses
window.loadExpenses = function() {
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/expenses/get-all',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            // Get or initialize DataTable
            var expenseTable;
            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                expenseTable = $('#data-table-basic').DataTable();
            } else {
                expenseTable = $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            }

            if (response.success && response.data) {
                expenseTable.clear();

                $.each(response.data, function (index, expense) {
                    expenseTable.row.add([
                        index + 1,
                        expense.categ_name || '-',
                        expense.expense_name,
                        expense.description || '-',
                        `<div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editExpenseBtn"
                                title="Edit Expense"
                                data-id="${expense.expense_id}"
                                data-categ_id="${expense.categ_id || ''}"
                                data-expense_name="${expense.expense_name}"
                                data-description="${expense.description || ''}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-icon-notika deleteExpenseBtn"
                                title="Delete Expense"
                                data-id="${expense.expense_id}"
                                data-expense_name="${expense.expense_name}">
                                <i class="notika-icon notika-close"></i>
                            </button>
                        </div>`
                    ]);
                });

                expenseTable.draw(false);
            } else {
                expenseTable.clear().draw();
            }
        },
        error: function () {
            showToastExpense('Failed to load expenses', 'error');
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
            var originalText = $(btn).data('original-text') || 'Save Expense Type';
            $(btn).prop('disabled', false).html(originalText);
        }
    }

    // Handle Save Expense button
    $(document).on('click', '#saveExpenseBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const expenseData = {
            categ_id: $('#categ_id').val(),
            expense_name: $('#expense_name').val().trim(),
            description: $('#description').val().trim()
        };

        if (!expenseData.categ_id || !expenseData.expense_name) {
            showToastExpense('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expenses/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(expenseData),
            success: function (response) {
                if (response.success) {
                    showToastExpense(response.message, 'success');
                    $('#createExpenseModal').modal('hide');
                    $('#createExpenseModal input').val('');
                    $('#createExpenseModal select').val('').trigger('chosen:updated');
                    loadExpenses();
                } else {
                    showToastExpense(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastExpense(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Edit Expense button click
    $(document).on('click', '.editExpenseBtn', function () {
        const expenseId = $(this).data('id');
        const categId = $(this).data('categ_id');
        const expenseName = $(this).data('expense_name');
        const description = $(this).data('description');

        $('#edit_expense_id').val(expenseId);
        $('#edit_categ_id').val(categId).trigger('chosen:updated');
        $('#edit_expense_name').val(expenseName);
        $('#edit_description').val(description);

        // Reset button text and data
        $('#updateExpenseBtn').html('Save changes').removeData('original-text');

        $('#editExpenseModal').modal('show');
    });

    // Handle Update Expense button
    $(document).on('click', '#updateExpenseBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const expenseData = {
            expense_id: $('#edit_expense_id').val(),
            categ_id: $('#edit_categ_id').val(),
            expense_name: $('#edit_expense_name').val().trim(),
            description: $('#edit_description').val().trim()
        };

        if (!expenseData.categ_id || !expenseData.expense_name) {
            showToastExpense('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expenses/update',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(expenseData),
            success: function (response) {
                if (response.success) {
                    showToastExpense(response.message, 'success');
                    $('#editExpenseModal').modal('hide');
                    loadExpenses();
                } else {
                    showToastExpense(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastExpense(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Delete Expense button
    $(document).on('click', '.deleteExpenseBtn', function () {
        const btn = $(this);
        const expenseId = btn.data('id');
        const expenseName = btn.data('expense_name') || 'this expense';

        swal({   
            title: "Are you sure?",   
            text: "You will delete " + expenseName + "! This action cannot be undone.",   
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
                    url: '<?= App::baseUrl() ?>/_ikawa/expenses/delete/' + expenseId,
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            swal("Deleted!", response.message || "Expense has been deleted successfully.", "success");
                            loadExpenses();
                        } else {
                            swal("Error!", response.message || "Failed to delete expense.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMsg = "Failed to delete expense. Please try again.";
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

    // Clear modal on close
    $('#createExpenseModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
    });

});
</script>
