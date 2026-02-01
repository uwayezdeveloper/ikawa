
<script>
$(document).ready(function() {
    // Initialize DataTable if on approve-investments page
    if ($('#pendingInvestmentsTable').length) {
        $('#pendingInvestmentsTable').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]]
        });
    }

    // Initialize DataTable if on my-rejected-investments page
    if ($('#rejectedInvestmentsTable').length) {
        $('#rejectedInvestmentsTable').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]]
        });
    }

    // Reload button handlers
    $(document).on('click', '#reloadPendingBtn', function() {
        loadContent('approve-investments');
    });

    $(document).on('click', '#reloadRejectedBtn', function() {
        loadContent('my-rejected-investments');
    });

    // Approve investment button
    $(document).on('click', '.approve-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const inId = btn.attr('data-id');
        
        console.log('Approve button clicked, ID:', inId);
        
        swal({
            title: "Approve Investment?",
            text: "This will add the amount to the account balance.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            confirmButtonText: "Yes, approve it!",
            cancelButtonText: "Cancel"
        }).then(function(isConfirm) {
            if (isConfirm) {
                console.log('Sending approve request for ID:', inId);
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/investments/approve',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ in_id: parseInt(inId) }),
                    dataType: 'json',
                    success: function(res) {
                        console.log('Approve response:', res);
                        if (res.success) {
                            swal("Approved!", res.message || "Investment approved successfully", "success");
                            // Remove the approved row from table
                            btn.closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                // Check if table is empty
                                if ($('#pendingInvestmentsList tr').length === 0) {
                                    $('#pendingInvestmentsList').html('<tr><td colspan="9" class="text-center">No pending investments</td></tr>');
                                }
                            });
                        } else {
                            swal("Error", res.message || "Failed to approve investment", "error");
                        }
                    },
                    error: function(xhr) {
                        console.error('Approve error:', xhr);
                        console.error('Response text:', xhr.responseText);
                        swal("Error", "Failed to approve investment. Check console for details.", "error");
                    }
                });
            }
        });
    });

    // Reject investment button
    $(document).on('click', '.reject-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const inId = btn.attr('data-id');
        $('#reject_in_id').val(inId);
        $('#rejector_comment').val('');
        $('#rejectModal').modal('show');
    });

    // Confirm reject button
    $(document).on('click', '#confirmRejectBtn', function(e) {
        e.preventDefault();
        const inId = $('#reject_in_id').val();
        const comment = $('#rejector_comment').val().trim();

        if (!comment) {
            swal("Error", "Please provide a rejection reason", "error");
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/investments/reject',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ 
                in_id: parseInt(inId), 
                rejector_comment: comment 
            }),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#rejectModal').modal('hide');
                    swal("Rejected!", res.message || "Investment rejected successfully", "success");
                    // Remove the rejected row from table
                    $('.reject-btn[data-id="' + inId + '"]').closest('tr').fadeOut(400, function() {
                        $(this).remove();
                        // Check if table is empty
                        if ($('#pendingInvestmentsList tr').length === 0) {
                            $('#pendingInvestmentsList').html('<tr><td colspan="9" class="text-center">No pending investments</td></tr>');
                        }
                    });
                } else {
                    swal("Error", res.message || "Failed to reject investment", "error");
                }
            },
            error: function(xhr) {
                console.error(xhr);
                swal("Error", "Failed to reject investment", "error");
            }
        });
    });
});
</script>
