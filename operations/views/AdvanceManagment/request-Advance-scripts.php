<script>
$(document).ready(function () {
   $(document).on('change', '#request_type', function(){
    var requestType = $(this).val();
    
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/inadvance/get-suppliers',
        method: 'GET',
        data: {request_type: requestType},
        success: function(suppliers) {
            if (suppliers.success) {
                $('#destination_id').empty().append('<option>Select Requestor</option>');
                
                $.each(suppliers.data, function(index, supplier) {
                    var optionHtml = '<option value="' + supplier.sup_id + '">' +
                                    supplier.full_name + ' (' + (supplier.phone || 'N/A') + ')' +
                                    '</option>';
                    
                    $('#destination_id').append(optionHtml);
                });
                
                $("#destination_id").trigger("chosen:updated");
            }
        }
    });
});


$(document).on('click', '#saveinadvancePaymentBtn', function () {
    var formData = {
        request_type: $("#request_type").val(),
        destination_id: $("#destination_id").val(),
        amount: $("#amount").val().trim(),
        n_days: $("#n_days").val().trim()?? 0,
        reason: $("textarea").val().trim(),
        station_id: $("#station_id").val(),
        created_by: $("#created_by").val()
    };
    
    if (!formData.request_type || !formData.destination_id || !formData.amount || !formData.reason) {
        showToast("Please fill all required fields", "error");
        return;
    }
    
    var originalForm = $("#myModalthree .modal-body").html();
    var paymentDate;
        if (formData.n_days && formData.n_days.trim() !== '') {
            paymentDate = new Date();
            paymentDate.setDate(paymentDate.getDate() + parseInt(formData.n_days));
        } else {
            paymentDate = null; 
        }
    
    var previewHtml = `
        <div class="preview-content" style="width: 100%; max-width: 100%;">
            <div class="text-center" style="margin-bottom: 20px;">
                <h3 style="color: #333; font-weight: 600; margin: 0;">Advance Request Preview</h3>
                <p class="text-muted" style="margin: 5px 0 0 0;">Please review before submitting</p>
            </div>
            
            <div class="preview-card" style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
                <div class="row" style="margin: 0 -10px;">
                    <div class="col-md-6" style="padding: 0 10px;">
                        <div style="margin-bottom: 12px;">
                            <label style="color: #666; font-size: 13px; font-weight: 500; display: block; margin-bottom: 4px;">Request Type</label>
                            <div style="background: white; padding: 8px 12px; border-radius: 4px; border-left: 3px solid #2196F3;">
                                ${formData.request_type}
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 12px;">
                            <label style="color: #666; font-size: 13px; font-weight: 500; display: block; margin-bottom: 4px;">Requestor</label>
                            <div style="background: white; padding: 8px 12px; border-radius: 4px; border-left: 3px solid #4CAF50;">
                                ${$("#destination_id option:selected").text()}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6" style="padding: 0 10px;">
                        <div style="margin-bottom: 12px;">
                            <label style="color: #666; font-size: 13px; font-weight: 500; display: block; margin-bottom: 4px;">Advance Amount</label>
                            <div style="background: white; padding: 8px 12px; border-radius: 4px; border-left: 3px solid #FF9800; font-weight: 600; color: #333;">
                                ${parseFloat(formData.amount).toLocaleString()} RWF
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 12px;">
                            <label style="color: #666; font-size: 13px; font-weight: 500; display: block; margin-bottom: 4px;">Receive payment up to</label>
                            <div style="background: white; padding: 8px 12px; border-radius: 4px; border-left: 3px solid #9C27B0;">
                                ${paymentDate}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <label style="color: #666; font-size: 13px; font-weight: 500; display: block; margin-bottom: 4px;">Reason for Advance</label>
                    <div style="background: white; padding: 10px 12px; border-radius: 4px; border-left: 3px solid #607D8B; min-height: 60px; max-height: 150px; overflow-y: auto;">
                        ${formData.reason}
                    </div>
                </div>
            </div>
            
            <div class="text-center" style="padding-top: 15px;">
                <button id="confirmSaveBtn" class="btn btn-success" style="padding: 8px 25px; font-size: 14px; margin-right: 8px;"
                        data-destination-id="${formData.destination_id}"
                        data-amount="${formData.amount}"
                        data-n-days="${formData.n_days}"
                        data-reason="${formData.reason.replace(/"/g, '&quot;')}"
                        data-station-id="${formData.station_id}"
                        data-created-by="${formData.created_by}">
                   Confirm & Submit
                </button>
                <button id="cancelPreviewBtn" class="btn btn-default" style="padding: 8px 25px; font-size: 14px;">
                    <i class="notika-icon notika-edit"></i> Back
                </button>
            </div>
        </div>
    `;
    
    $("#myModalthree .modal-body").html(previewHtml);
    
    $(document).off('click', '#cancelPreviewBtn').on('click', '#cancelPreviewBtn', function() {
        $("#myModalthree .modal-body").html(originalForm);
        $("#request_type").val(formData.request_type).trigger("chosen:updated");
        $("#destination_id").val(formData.destination_id).trigger("chosen:updated");
        $("#amount").val(formData.amount);
        $("#n_days").val(formData.n_days);
        $("textarea").val(formData.reason);
    });
});

// Handle confirm button
$(document).on('click', '#confirmSaveBtn', function() {
    const btn = this;
    setButtonLoading(btn, true);
    var payload = {
        destination_id: $(this).data('destination-id'),
        amount: $(this).data('amount'),
        n_days: $(this).data('n-days'),
        reason: $(this).data('reason'),
        station_id: $(this).data('station-id'),
        created_by: $(this).data('created-by')
    };
       
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/inadvance/create',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#myModalthree').modal('hide');
                $('#myModalthree select').val('').trigger('chosen:updated');
                $('#myModalthree input').val('');
                $("#myModalthree .modal-body").html('');
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
            setButtonLoading(btn, false);
        }
    });

const TableData = $('#data-table-basic').DataTable({
    destroy: true,
    pageLength: 10
});

function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inadvance/advancelist', function (res) {
        if (!res.success || !res.data) {
            return;
        }

        TableData.clear();

        const statusColors = {
            'pending': '#fff3cd',
            'approved': '#d1ecf1', 
            'outstanding': '#f8d7da',
            'partially_cleared': '#d4edda',
            'cleared': '#28a745',
            'rejected': '#e2e3e5'
        };
        
        const statusTextColors = {
            'pending': '#856404',
            'approved': '#0c5460',
            'outstanding': '#721c24',
            'partially_cleared': '#155724',
            'cleared': '#ffffff',
            'rejected': '#383d41'
        };

        $.each(res.data, function (index, record) {
            const status = record.status || '';
            const bgColor = statusColors[status] || '#f8f9fa';
            const textColor = statusTextColors[status] || '#212529';
            const rejectedReason = record.rejected_reason || '';
            
            let badgeHTML = '';
            if (status === 'rejected' && rejectedReason) {
                badgeHTML = `<span class="badge has-tooltip" 
                                style="background-color: ${bgColor}; color: ${textColor}; padding: 5px 10px; border-radius: 12px; font-weight: 500; border: 1px solid #dee2e6; cursor: pointer;"
                                title="${rejectedReason.replace(/"/g, '&quot;')}">
                                ${status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                            </span>`;
            } else {
                badgeHTML = `<span class="badge" 
                                style="background-color: ${bgColor}; color: ${textColor}; padding: 5px 10px; border-radius: 12px; font-weight: 500; border: 1px solid #dee2e6;">
                                ${status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                            </span>`;
            }

            TableData.row.add([
                index + 1,
                record.full_name || '',
                record.phone || '',
                record.type || '',
                record.amount ? parseFloat(record.amount).toLocaleString() + ' RWF' : '0 RWF',
                record.created_at || '',
                badgeHTML
            ]);
        });

        TableData.draw(false);
        
        if (!$('#tooltip-css').length) {
            $('<style id="tooltip-css">' +
              '.has-tooltip { position: relative; display: inline-block; }' +
              '.has-tooltip:hover::after { content: attr(title); position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background-color: #333; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px; white-space: nowrap; z-index: 1000; margin-bottom: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }' +
              '.has-tooltip:hover::before { content: ""; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); border-width: 5px; border-style: solid; border-color: #333 transparent transparent transparent; z-index: 1000; margin-bottom: -5px; }' +
              '</style>').appendTo('head');
        }
    }).fail(function() {
        console.error('Failed to load advance data');
    });
}
});


   

    // reject
$(document).on('click', '.reject-btn-requested-inadvance', function() {
    const advanceId = $(this).data('id');
    const advanceName = $(this).data('name');
    const advanceType = $(this).data('type') || 'Supplier';
    const advanceAmount = $(this).data('amount') || '0';
    const advanceDate = $(this).data('created_at') || '';
    const advanceReason = $(this).data('reason') || '';
    const created_by = $(this).data('created_by') || '';
    
    
    $('#rejectAdvanceId').val(advanceId);
    $('#rejectAdvanceName').text(advanceType+' '+advanceName);
    $('#rejectAdvanceType').text(advanceType);
    $('#rejectAdvanceAmount').text(parseFloat(advanceAmount).toLocaleString());
    $('#rejectAdvanceDate').text(advanceDate);
    $('#rejectAdvanceReason').text(advanceReason);
    $('#rejectAdvanceCreator').text(created_by);
    $('#rejectionReason').val('');
    
    $('#myModalseven').modal('show');
});




     // reject save
    $(document).on('click', '#confirmRejectAdvanceBtn', function() {
        const btn = this;
    setButtonLoading(btn, true);
        var payload = {
            adv_id: $('#rejectAdvanceId').val(),
            approved_by: $('#user_id').val().trim(),
            rejected_reason: $('#rejectionReason').val().trim(),
        };
        if (!payload.adv_id || !payload.approved_by || !payload.rejected_reason) {
        showToast('Please fill all required fields!', 'error');
        setButtonLoading(btn, false);
        $('#myModalseven').modal('show');
        return; 
    }
        // Send AJAX request
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/inadvance/rejectadvance',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#myModalseven').modal('hide');
                    $('#myModalseven select').val('').trigger('chosen:updated');
                    $('#myModalseven input').val('');
                    loadNextData();
                   

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
            setButtonLoading(btn, false);
        }
        });

          // Initialize DataTable
    const TableData = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

   
  function loadNextData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inadvance/advancelistpending', function (res) {
        if (!res.success || !res.data) {
            TableData.clear().draw();
            return;
        }

        TableData.clear();

        $.each(res.data, function (index, record) {
            // Format the name cell with reason if exists
            let nameCell = `<strong>${record.full_name || ''}</strong>`;
            if (record.reason && record.reason.trim()) {
                nameCell += `<br><small class="text-muted">${record.reason}</small>`;
            }
            
            // Format the amount cell with payment due date if exists
            let amountCell = `<strong class="text-primary">${record.amount ? parseFloat(record.amount).toLocaleString() + ' RWF' : '0 RWF'}</strong>`;
            if (record.payment_on && record.payment_on.trim()) {
                const dueDate = new Date(record.payment_on).toLocaleDateString('en-GB');
                amountCell += `<br><small>Due: ${dueDate}</small>`;
            }
            
            // Format the date
            const createdDate = record.created_at ? new Date(record.created_at).toLocaleDateString('en-GB') : '';
            
            // Create action buttons
            const actionCell = `
                <div class="button-icon-btn button-icon-btn-rd">
                    <button type="button" class="btn btn-default btn-icon-notika approve-btn-requested-inadvance"
                            data-id="${record.adv_id}"
                            data-name="${record.full_name || ''}"
                            data-amount="${record.amount || 0}"
                            title="Approve Advance">
                            <i class="notika-icon notika-checked"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-icon-notika reject-btn-requested-inadvance"
                            data-id="${record.adv_id}"
                            data-name="${record.full_name || ''}"
                            data-type="${record.type || ''}"
                            data-amount="${record.amount || 0}"
                            data-created_at="${record.created_at || ''}"
                            data-created_by="${record.first_name} ${record.last_name}"
                            title="Reject Advance">
                            <i class="notika-icon notika-close"></i>
                    </button>
                </div>
            `;

            TableData.row.add([
                index + 1,
                nameCell,
                record.phone || '',
                `<span class="badge badge-info">${record.type || ''}</span>`,
                amountCell,
                createdDate,
                actionCell
            ]);
        });

        TableData.draw(false);
        
        // Rebind the button events after table is redrawn
        bindButtonEvents();
        
    }).fail(function() {
        console.error('Failed to load advance data');
        showToast('Failed to load advance data', 'error');
    });
}
    });


    // approve
 $(document).on('click', '.approve-btn-requested-inadvance', function() {
 const btn = $(this);
    const adv_id = btn.data('id');
    const user_id=$("#user_approve_advance").val();
    const full_name = btn.data('name') || 'this request'; 
    const typeName = btn.data('type') || 'Supplier'; 
    const amount = btn.data('amount') || 0; 
    const formattedAmount = parseFloat(amount).toLocaleString();
    
    swal({   
        title: "Are you sure?",   
        text: "You will approve request for: " +typeName+" "+full_name+" Value: "+formattedAmount+ " RWF. This action cannot be undone.",   
        type: "warning",   
        showCancelButton: true,   
        confirmButtonText: "Yes, approve!",
        cancelButtonText: "No, cancel!"
    }).then(function(isConfirm){
        if (isConfirm) {
        const payload = {
            adv_id: adv_id,
            user_id: user_id
        };
            $.ajax({
                url: '<?= App::baseUrl() ?>/_ikawa/inadvance/approveadvancerequest/',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal("Approved!", response.message || "Advance request has been approved successfully.", "success");
                        loadNextData();
                         setTimeout(function() {
                            showToast("Advance approved. you can continue to 'Advance Disbursement' to process payment.", "info");
                        }, 1500);
                        
                    } else {
                        swal("Error!", response.message || "Failed to approve request.", "error");
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = "Failed to approve request. Please try again.";
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

   
  function loadNextData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inadvance/advancelistpending', function (res) {
        if (!res.success || !res.data) {
            TableData.clear().draw();
            return;
        }

        TableData.clear();

        $.each(res.data, function (index, record) {
            // Format the name cell with reason if exists
            let nameCell = `<strong>${record.full_name || ''}</strong>`;
            if (record.reason && record.reason.trim()) {
                nameCell += `<br><small class="text-muted">${record.reason}</small>`;
            }
            
            // Format the amount cell with payment due date if exists
            let amountCell = `<strong class="text-primary">${record.amount ? parseFloat(record.amount).toLocaleString() + ' RWF' : '0 RWF'}</strong>`;
            if (record.payment_on && record.payment_on.trim()) {
                const dueDate = new Date(record.payment_on).toLocaleDateString('en-GB');
                amountCell += `<br><small>Due: ${dueDate}</small>`;
            }
            
            // Format the date
            const createdDate = record.created_at ? new Date(record.created_at).toLocaleDateString('en-GB') : '';
            
            // Create action buttons
            const actionCell = `
                <div class="button-icon-btn button-icon-btn-rd">
                    <button type="button" class="btn btn-default btn-icon-notika approve-btn-requested-inadvance"
                            data-id="${record.adv_id}"
                            data-name="${record.full_name || ''}"
                            data-amount="${record.amount || 0}"
                            title="Approve Advance">
                            <i class="notika-icon notika-checked"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-icon-notika reject-btn-requested-inadvance"
                            data-id="${record.adv_id}"
                            data-name="${record.full_name || ''}"
                            data-type="${record.type || ''}"
                            data-amount="${record.amount || 0}"
                            data-created_at="${record.created_at || ''}"
                            data-created_by="${record.first_name} ${record.last_name}"
                            title="Reject Advance">
                            <i class="notika-icon notika-close"></i>
                    </button>
                </div>
            `;

            TableData.row.add([
                index + 1,
                nameCell,
                record.phone || '',
                `<span class="badge badge-info">${record.type || ''}</span>`,
                amountCell,
                createdDate,
                actionCell
            ]);
        });

        TableData.draw(false);
        
        // Rebind the button events after table is redrawn
        bindButtonEvents();
        
    }).fail(function() {
        console.error('Failed to load advance data');
        showToast('Failed to load advance data', 'error');
    });
        }

        }
    });
});


    // disburse
$(document).on('click', '.disburse-btn-approved-inadvance', function() {
    const advanceId = $(this).data('id');
    const advanceName = $(this).data('name');
    const advanceType = $(this).data('type') || 'Supplier';
    const advanceAmount = $(this).data('amount') || '0';
    const approvedon = $(this).data('approved_on') || '';
    const approved_by = $(this).data('approved_by') || '';
    
    
    $('#disburseAdvanceId').val(advanceId);
    $('#disburseAdvanceName').text(advanceType+' '+advanceName);
    $('#disburseAdvanceAmount').text(parseFloat(advanceAmount).toLocaleString());
    $('#disburseAdvanceDate').text(approvedon);
    $('#approvedAdvanceCreator').text(approved_by);
    
    $('#myModalseven').modal('show');
});


function validateAccountBalance($input) {
    const $accountCard = $input.closest('.account-card');
    const accountId = $input.data('account-id');
    const amount = $accountCard.find('.disburse-amount-input').val().trim();
    const charge = $accountCard.find('.disburse-charge-input').val().trim();
    const maxAmount = $accountCard.find('.disburse-amount-input').attr('max');
    
    // Highlight if amount exists
    if (amount !== '') {
        $accountCard.addClass('border-success');
        $('#selected_account_id').val(accountId);
    } else {
        $accountCard.removeClass('border-success');
        $('#selected_account_id').val('');
    }
    
    // Validate total doesn't exceed balance
    if ((parseFloat(amount || 0) + parseFloat(charge || 0)) > parseFloat(maxAmount)) {
        showToast("Amount and Charge exceeds account balance", "error");
        $accountCard.find('.disburse-amount-input').val('');
        $accountCard.find('.disburse-charge-input').val('');
        $accountCard.removeClass('border-success');
        $('#selected_account_id').val('');
        return false;
    }
    
    return true;
}

// Amount input
$(document).on('input', '.disburse-amount-input', function() {
    validateAccountBalance($(this));
    updateTotalEntered();
});

// Charge input  
$(document).on('input', '.disburse-charge-input', function() {
    validateAccountBalance($(this));
    updateTotalEntered();
});

// Update total entered amount
function updateTotalEntered() {
    let total = 0;
    $('.disburse-amount-input').each(function() {
        const val = $(this).val().trim();
        if (val !== '' && !isNaN(val)) {
            total += parseFloat(val);
        }
    });
    
  $('.TotalEnteredDisburse').html(
    '<span class="text-primary"><strong>Total Entered:</strong> ' + 
    formatNumber(total) + 
    ' <span class="">RWF</span></span>'
);
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Also check on page load if any amounts already filled
$('.disburse-amount-input').each(function() {
    if ($(this).val().trim() !== '') {
        $(this).closest('.account-card').addClass('border-success');
    }
});
// Initialize total on page load
updateTotalEntered();


$(document).on('click', '#confirmDisburseAdvanceBtn', function() {
    const btn = $(this);
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    // Collect data from ALL accounts with amounts
    const disbursements = [];
    let totalDisbursed = 0;
    
    $('.account-card').each(function() {
        const $card = $(this);
        const amount = $card.find('.disburse-amount-input').val().trim();
        
        if (amount !== '' && parseFloat(amount) > 0) {
            const accountId = $card.data('account-id');
            const charge = $card.find('.disburse-charge-input').val().trim();
            const total = parseFloat(amount);
            
            disbursements.push({
                account_id: accountId,
                amount: amount,
                charge: charge || 0,
                total: total
            });
            
            totalDisbursed += total;
        }
    });
    
    if (disbursements.length === 0) {
        showToast("Please enter amount in at least one account", "error");
        btn.prop('disabled', false).html(originalText);
        $('#myModalseven').modal('show');
        return;
    }
    
    // Get requested advance amount
    const requestedAmount = parseFloat($('#disburseAdvanceAmount').text().replace(/[^0-9.-]+/g, ''));
    
    // Validate total
    if (totalDisbursed > requestedAmount) {
        showToast("Total disbursement (" + totalDisbursed + ") exceeds requested amount: " + requestedAmount + " RWF", "error");
        btn.prop('disabled', false).html(originalText);
        $('#myModalseven').modal('show');
        return;
    }

    if (totalDisbursed < requestedAmount) {
        showToast("Total disbursement (" + totalDisbursed + ") is less than requested amount: " + requestedAmount + " RWF", "error");
        btn.prop('disabled', false).html(originalText);
        $('#myModalseven').modal('show');
        return;
    }
    
    // Prepare payload
    const payload = {
        adv_id: $('#disburseAdvanceId').val(),
        user_id: $('#user_id').val(),
        station_id:$("#disburse_station_id").val(),
        disbursements: disbursements,
        total_amount: totalDisbursed,
        receipt_account: $('#receiptAccount').val().trim(),
        additional_info: $('#additiondisbursecomment').val().trim()
    };
    
    // Validate required fields
    if (!payload.receipt_account) {
        showToast("Please enter a requestor receipt account", "error");
        btn.prop('disabled', false).html(originalText);
        $('#myModalseven').modal('show');
        return;
    }
    
    // Create summary text
    let summaryText = "Disburse total " + formatNumber(totalDisbursed) + " RWF from " + disbursements.length + " account(s):\n\n";
  // Get account name from the card
        disbursements.forEach((dis, index) => {
            const $card = $(`.account-card[data-account-id="${dis.account_id}"]`);
            const accountName = $card.find('strong').first().text().trim();
            
            summaryText += (index + 1) + ". " + accountName + ": " + formatNumber(dis.amount) + " RWF";
            if (dis.charge > 0) summaryText += " + " + formatNumber(dis.charge) + " RWF charge";
            summaryText += "\n";
        });
    summaryText += "\nRecipient: " + $('#disburseAdvanceName').text()+"\n";
    summaryText += "This action can not be undergone";
    swal({   
        title: "Confirm Disbursement",   
        text: summaryText,
        type: "warning",   
        showCancelButton: true,   
        confirmButtonText: "Yes, disburse!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#28a745"
    }).then(function(isConfirm) {
        if (isConfirm) {
            $.ajax({
                url: '<?= App::baseUrl() ?>/_ikawa/inadvance/disburse',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal("Success!", response.message || "Advance disbursed successfully.", "success");
                        $('#myModalseven').modal('hide');
                        loadApprovedData(); // Reload table
                        
                        // Clear form
                        $('.account-card').removeClass('border-success');
                        $('.disburse-amount-input, .disburse-charge-input').val('');
                        $('#receiptAccount, #additiondisbursecomment').val('');
                        $('.TotalEnteredDisburse').empty();
                    } else {
                        swal("Error!", response.message || "Failed to disburse advance.", "error");
                    }
                },
                error: function(xhr) {
                    let errorMsg = "Failed to disburse advance. Please try again.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    swal("Error!", errorMsg, "error");
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
                 // Initialize DataTable
    const TableData = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

   
  function loadApprovedData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/inadvance/advancelistapproved', function(res) {
        if (!res.success || !res.data) {
            TableData.clear().draw();
            return;
        }

        TableData.clear();

        $.each(res.data, function(index, record) {
            // Format the name cell with reason if exists
            let nameCell = `<strong>${record.full_name || ''}</strong>`;
            if (record.reason && record.reason.trim()) {
                nameCell += `<br><small class="text-muted">${record.reason}</small>`;
            }
            
            // Format the amount cell with payment due date if exists
            let amountCell = `<strong class="text-primary">${record.amount ? parseFloat(record.amount).toLocaleString() + ' RWF' : '0 RWF'}</strong>`;
            if (record.payment_on && record.payment_on.trim()) {
                const dueDate = new Date(record.payment_on).toLocaleDateString('en-GB');
                amountCell += `<br><small>Due: ${dueDate}</small>`;
            }
            
            // Format dates
            const createdDate = record.created_at ? new Date(record.created_at).toLocaleDateString('en-GB') : '';
            const approvedDate = record.approved_on ? new Date(record.approved_on).toLocaleDateString('en-GB') : '';
            
            // Create disburse button
            const actionCell = `
                <div class="button-icon-btn button-icon-btn-rd">
                    <button type="button" class="btn btn-default btn-icon-notika disburse-btn-approved-inadvance"
                            data-id="${record.adv_id}"
                            data-name="${record.full_name || ''}"
                            data-approved_by="${record.first_name || ''} ${record.last_name || ''}"
                            data-type="${record.type || ''}"
                            data-amount="${record.amount || 0}"
                            data-approved_on="${record.approved_on || ''}"
                            title="Disburse Advance">
                        <i class="notika-icon notika-next"></i>
                    </button>
                </div>
            `;

            TableData.row.add([
                index + 1,
                nameCell,
                record.phone || '',
                `<span class="badge badge-info">${record.type || ''}</span>`,
                amountCell,
                createdDate,
                approvedDate,
                actionCell
            ]);
        });

        TableData.draw(false);
        
        
    }).fail(function() {
        console.error('Failed to load approved advances');
        showToast('Failed to load approved advances', 'error');
    });
}
        } else {
            btn.prop('disabled', false).html(originalText);
        }
    });
});

// Helper function to format numbers
function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


});
</script>