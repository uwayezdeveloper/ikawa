<script>
$(document).ready(function () {
    function filterAccounts() {
        let debit = $("#debit_account_id").val();
        let credit = $("#credit_account_id").val();

        $("#credit_account_id option").prop('disabled', false).show();
        $("#debit_account_id option").prop('disabled', false).show();

        if (debit) {
            $("#credit_account_id option[value='" + debit + "']").prop('disabled', true).hide();
        }

        if (credit) {
            $("#debit_account_id option[value='" + credit + "']").prop('disabled', true).hide();
        }
        
        $("#debit_account_id, #credit_account_id").trigger("chosen:updated");
    }

    $(document).on('change', '#debit_account_id, #credit_account_id', filterAccounts);


    $(document).on('click', '#saveAccountTransBtn', function () {
    const btn = this;
    setButtonLoading(btn, true);
        const payload = {
                debit_account_id: $('#debit_account_id').val(),
                credit_account_id: $('#credit_account_id').val(),
                amount_to_transfer: parseFloat($('#amount_to_transfer').val().trim()),
                trans_charges: parseFloat($('#trans_charges').val().trim()),
                balance: parseFloat($("#debit_account_id option:selected").data('balance')) || 0,
                user_id:$("#user_id").val().trim()
            };

        if (!payload.debit_account_id || !payload.credit_account_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }
        else if(payload.amount_to_transfer<500){
            showToast('Minimum Amount is 500', 'error')
            setButtonLoading(btn, false);
            return; 
        }
        else if((payload.amount_to_transfer+payload.trans_charges)>payload.balance){
            showToast('Insufficient balance for debit account', 'error')
            setButtonLoading(btn, false);
            return; 
        }
        else if(payload.trans_charges>payload.amount_to_transfer){
           showToast('Can not be processed charges is higher than amount', 'error')
            setButtonLoading(btn, false);
            return; 
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/financial/createtransfer',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
        success: function (response) {
            if (response.success) {
                showToast(response.message, 'success');
                $('#myModalthree').modal('hide');
                 $('#myModalthree input').val('');                
                // Reset and reload dropdowns
                $("#debit_account_id").empty().append('<option>The account for which money will be deducted.</option>');
                $("#credit_account_id").empty().append('<option>The account to which money will be transferred.</option>');
                
                // Reload accounts data
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/accounts/get-all',
                    method: 'GET',
                    success: function(accountsData) {
                        if (accountsData.success) {
                            // Populate both dropdowns
                            $.each(accountsData.data, function(index, account) {
                                var optionHtml = '<option value="' + account.acc_id + '" data-balance="' + account.balance + '">' +
                                                account.acc_name + ' (Acc: ' + account.acc_reference_num + ') (Bal: ' + parseFloat(account.balance).toLocaleString() + ')' +
                                                '</option>';
                                
                                $('#debit_account_id').append(optionHtml);
                                $('#credit_account_id').append(optionHtml);
                            });
                            
                            // Update chosen dropdowns
                            $("#debit_account_id").trigger("chosen:updated");
                            $("#credit_account_id").trigger("chosen:updated");
                        }
                    },
                    error: function() {
                        // Still update chosen even if accounts reload fails
                        $("#debit_account_id, #credit_account_id").trigger("chosen:updated");
                    }
                });
                
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

     // Initialize DataTable
    const TableData = $('#data-table-basic').DataTable({
        destroy: true,
        pageLength: 10
    });

   
    function loadData() {
    $.getJSON('<?= App::baseUrl() ?>/_ikawa/accounts/get-all', function (res) {
       if (!res.success || !res.data) {
            return;
        }

        TableData.clear();

        $.each(res.data, function (index, record) {
            
            TableData.row.add([
                index + 1,
                record.acc_name || '',
                record.acc_reference_num || '',
                record.location_name || '',
                parseFloat(record.balance || 0).toLocaleString() || '0'
            ]);
        });

        TableData.draw(false);
    })
        }
    });
    
});
</script>