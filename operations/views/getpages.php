<script>
function loadContent(page) {
    document.querySelectorAll('.nav-tabs li').forEach(function(li) {
        li.classList.remove('active');
    });
    var clickedLink = event.currentTarget || event.srcElement;
    clickedLink.parentNode.classList.add('active');

    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {

            document.getElementById('app').innerHTML = xhr.responseText;

            // CHOSEN
            if ($('.chosen').length) {
                $('.chosen').chosen({ width: '100%' });
            }

            // DATATABLE
            // Skip auto-initialization for supplier-statement (has custom DataTable logic)
            if ($('#data-table-basic').length && !$('#supplierSelect').length) {

                // destroy if already exists
                if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                    $('#data-table-basic').DataTable().destroy();
                }

                $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            }
            // If the injected page has the recharge UI, trigger its loader if available
            try{
                if (typeof window.reloadRechargeHistory === 'function' && document.getElementById('rechargeTable')){
                    window.reloadRechargeHistory();
                }
            }catch(e){ console.warn('reloadRechargeHistory call failed', e); }
            
            // If the injected page has supplier statement, trigger its initializer
            try{
                if (typeof window.initSupplierStatement === 'function' && document.getElementById('supplierSelect')){
                    window.initSupplierStatement();
                }
            }catch(e){ console.warn('initSupplierStatement call failed', e); }
        }
        // else{
        //   document.getElementById('app').innerHTML = '<h3>Page not found</h3>';  
        // }
    };

    xhr.open('GET', 'load.php?page=' + page, true);
    xhr.send();
}
</script>