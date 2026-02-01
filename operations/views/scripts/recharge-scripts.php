<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<script>
function loadRechargeHistory(){
    console.log('loadRechargeHistory: start');
    var loadingTimer = setTimeout(function(){
        console.warn('loadRechargeHistory: request taking long');
        $('#rechargeTableBody').html('<tr><td colspan="10" class="text-center text-warning">Loading... (taking longer than expected)</td></tr>');
    }, 4000);

    $.ajax({
        url: (typeof window.APP_BASE_URL !== 'undefined' ? window.APP_BASE_URL : '<?= App::baseUrl() ?>') + '/_ikawa/investments/get-by-location',
        method: 'GET',
        dataType: 'json',
        crossDomain: false,
        xhrFields: { withCredentials: true },
        beforeSend: function(){ console.log('loadRechargeHistory: beforeSend'); },
        success: function(json){
            console.log('loadRechargeHistory: success', json);
            clearTimeout(loadingTimer);
            $('#rechargeAlert').hide().html('');
            var tbody = $('#rechargeTableBody');
            if (!json.success || !Array.isArray(json.data) || json.data.length===0){
                tbody.html('<tr><td colspan="10" class="text-center">No records found</td></tr>');
                return;
            }
            var html = '';
            json.data.forEach(function(item, idx){
                var statusText = 'Pending';
                var statusClass = 'text-warning';
                if (item.sts == 2) {
                    statusText = 'Approved';
                    statusClass = 'text-success';
                } else if (item.sts == 1 && item.rejectorComment) {
                    statusText = 'Rejected';
                    statusClass = 'text-danger';
                }
                
                // Extract date only (YYYY-MM-DD format)
                var dateOnly = '-';
                if (item.done_at) {
                    // Handle both datetime and timestamp formats
                    dateOnly = item.done_at.split(' ')[0].substring(0, 10);
                }
                
                html += '<tr>'+
                    '<td>'+(idx+1)+'</td>'+
                    '<td>'+ (item.acc_name||'-') +'</td>'+
                    '<td><strong>'+ (Number(item.in_amount).toLocaleString()||'-') +' RWF</strong></td>'+
                    '<td>'+ (item.source||'-') +'</td>'+
                    '<td>'+ (item.description||'-') +'</td>'+
                    '<td>'+ (item.reciept||'-') +'</td>'+
                    '<td><span class="'+statusClass+'">'+ statusText +'</span></td>'+
                    '<td>'+ (item.location_name||'-') +'</td>'+
                    '<td>'+ (item.username||'-') +'</td>'+
                    '<td>'+ dateOnly +'</td>'+
                    '</tr>';
            });
            tbody.html(html);
            try{
                if ($.fn.DataTable.isDataTable('#rechargeTable')) $('#rechargeTable').DataTable().destroy();
                $('#rechargeTable').DataTable({ order:[[9,'desc']], pageLength:25 });
            }catch(e){}
        },
        error: function(xhr, status, err){
            clearTimeout(loadingTimer);
            console.error('Recharge history load error:', status, err, xhr.responseText);
            var msg = 'Load error';
            try {
                var parsed = JSON.parse(xhr.responseText || '{}');
                if (parsed && parsed.message) msg = parsed.message + ' ('+ (xhr.status||'') +')';
            } catch(e) {
                msg = (xhr.status ? ('HTTP '+xhr.status+' ') : '') + (err || 'Load error');
            }
            $('#rechargeTableBody').html('<tr><td colspan="10" class="text-center text-danger">'+msg+'</td></tr>');
            $('#rechargeAlert').show().html('<div class="alert alert-danger">'+msg+'</div>');
        }
    });
}

$(document).ready(function(){
    // bind modal submit button
    $(document).on('click', '#submitRechargeBtn', function(){
        var btn = $(this); btn.prop('disabled', true).text('Processing...');
        var form = $('#rechargeForm')[0];
        var fd = new FormData(form);
        $.ajax({
            url: (typeof window.APP_BASE_URL !== 'undefined' ? window.APP_BASE_URL : '<?= App::baseUrl() ?>') + '/_ikawa/investments/create',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            crossDomain: false,
            xhrFields: { withCredentials: true },
            success: function(json){
                if (json && json.success){
                    swal('Success', json.message || 'Recharge successful', 'success');
                    $('#rechargeAccountModal').modal('hide');
                    $('#rechargeForm')[0].reset();
                    loadRechargeHistory();
                } else {
                    swal('Error', json.message || 'Failed to recharge', 'error');
                }
            },
            error: function(xhr){
                var msg = 'Network error';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                swal('Error', msg, 'error');
            },
            complete: function(){ btn.prop('disabled', false).text('Recharge Account'); }
        });
    });

    // initial load
    loadRechargeHistory();

    // manual reload button
    $(document).on('click', '#reloadRechargeBtn', function(){
        loadRechargeHistory();
    });

    // expose for console/manual use
    window.reloadRechargeHistory = loadRechargeHistory;
});
</script>
