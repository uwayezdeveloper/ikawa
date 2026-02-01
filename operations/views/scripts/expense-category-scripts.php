
<script>
// Toast notification function (global)
function showToastCategory(message, type) {
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

// Global function to load categories
window.loadCategories = function() {
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/expense-categories/get-all',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            // Get or initialize DataTable
            var categoryTable;
            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                categoryTable = $('#data-table-basic').DataTable();
            } else {
                categoryTable = $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            }

            if (response.success && response.data) {
                categoryTable.clear();

                $.each(response.data, function (index, category) {
                    // Check if category is in use
                    $.ajax({
                        url: '<?= App::baseUrl() ?>/_ikawa/expense-categories/check-in-use/' + category.categ_id,
                        method: 'GET',
                        dataType: 'json',
                        async: false,
                        success: function (checkResponse) {
                            var inUse = checkResponse.success && checkResponse.data.in_use;
                            var deleteButton = '';
                            
                            if (!inUse) {
                                deleteButton = `<button class="btn btn-danger btn-icon-notika deleteCategoryBtn"
                                    title="Delete Category"
                                    data-id="${category.categ_id}"
                                    data-categ_name="${category.categ_name}">
                                    <i class="notika-icon notika-close"></i>
                                </button>`;
                            }

                            categoryTable.row.add([
                                index + 1,
                                category.categ_name,
                                category.description || '-',
                                `<div class="button-icon-btn button-icon-btn-rd">
                                    <button class="btn btn-default btn-icon-notika editCategoryBtn"
                                        title="Edit Category"
                                        data-id="${category.categ_id}"
                                        data-categ_name="${category.categ_name}"
                                        data-description="${category.description || ''}">
                                        <i class="notika-icon notika-edit"></i>
                                    </button>
                                    ${deleteButton}
                                </div>`
                            ]);
                        }
                    });
                });

                categoryTable.draw(false);
            } else {
                categoryTable.clear().draw();
            }
        },
        error: function () {
            showToastCategory('Failed to load expense categories', 'error');
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
            var originalText = $(btn).data('original-text') || 'Save Expense Category';
            $(btn).prop('disabled', false).html(originalText);
        }
    }

    // Handle Save Category button
    $(document).on('click', '#saveCategoryBtn', function (e) {
        e.preventDefault();
        console.log('Save Category button clicked');
        const btn = this;
        setButtonLoading(btn, true);
        
        const categoryData = {
            categ_name: $('#categ_name').val().trim(),
            description: $('#description').val().trim()
        };

        console.log('Category data:', categoryData);

        if (!categoryData.categ_name) {
            showToastCategory('Please enter category name!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-categories/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(categoryData),
            success: function (response) {
                console.log('Success response:', response);
                if (response.success) {
                    showToastCategory(response.message, 'success');
                    $('#createCategoryModal').modal('hide');
                    $('#createCategoryModal input').val('');
                    loadCategories();
                } else {
                    showToastCategory(response.message, 'error');
                }
            },
            error: function (xhr) {
                console.log('Error response:', xhr);
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastCategory(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Edit Category button click
    $(document).on('click', '.editCategoryBtn', function () {
        const categId = $(this).data('id');
        const categName = $(this).data('categ_name');
        const description = $(this).data('description');

        $('#edit_categ_id').val(categId);
        $('#edit_categ_name').val(categName);
        $('#edit_description').val(description);

        // Reset button text and data
        $('#updateCategoryBtn').html('Save changes').removeData('original-text');

        $('#editCategoryModal').modal('show');
    });

    // Handle Update Category button
    $(document).on('click', '#updateCategoryBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const categoryData = {
            categ_id: $('#edit_categ_id').val(),
            categ_name: $('#edit_categ_name').val().trim(),
            description: $('#edit_description').val().trim()
        };

        if (!categoryData.categ_name) {
            showToastCategory('Please enter category name!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-categories/update',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(categoryData),
            success: function (response) {
                if (response.success) {
                    showToastCategory(response.message, 'success');
                    $('#editCategoryModal').modal('hide');
                    loadCategories();
                } else {
                    showToastCategory(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastCategory(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Delete Category button
    $(document).on('click', '.deleteCategoryBtn', function (e) {
        e.preventDefault();
        const btn = $(this);
        const categId = btn.data('id');
        const categName = btn.data('categ_name') || 'this category';

        swal({   
            title: "Are you sure?",   
            text: "You will delete " + categName + "! This action cannot be undone.",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonText: "Yes, delete!",
            cancelButtonText: "No, cancel!"
        }).then(function(isConfirm){
            if (isConfirm) {
                const originalHtml = btn.html();
                btn.html('<i class="notika-icon notika-loading"></i> Deleting...').prop('disabled', true);
                
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/expense-categories/delete',
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({ categ_id: categId }),
                    success: function (response) {
                        if (response.success) {
                            swal("Deleted!", response.message || "Category has been deleted successfully.", "success");
                            loadCategories();
                        } else {
                            swal("Error!", response.message || "Failed to delete category.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMsg = "Failed to delete category. Please try again.";
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
    $('#createCategoryModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
    });

    $('#editCategoryModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
    });
});
</script>
