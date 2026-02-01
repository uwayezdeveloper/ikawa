<script>
// Disable Counter Up plugin on reports page to prevent conflicts
if (typeof $.fn.counterUp !== 'undefined') {
    console.log('Disabling Counter Up to prevent conflicts');
    $.fn.counterUp = function() { return this; };
}

$(document).ready(function() {
    console.log('Reports script loaded - checking for general-items-sales-report page');
    console.log('Current URL:', window.location.href);
    console.log('Table element found:', $('#data-table-basic').length > 0);
    console.log('Product type filter found:', $('#productTypeFilter').length > 0);
    console.log('Filter button found:', $('#filterReportBtn').length > 0);
    
    // Better page detection - check for specific elements that exist on reports page
    var isReportsPage = $('#data-table-basic').length > 0 && 
                       $('#productTypeFilter').length > 0 && 
                       $('#filterReportBtn').length > 0 &&
                       $('#salesReportData').length > 0;
    
    console.log('Is Reports Page:', isReportsPage);
    
    if (isReportsPage) {
        console.log('General Items Sales Report page detected - initializing...');
        
        // Force global chosen initialization like other pages
        if ($('.chosen').length) {
            console.log('Initializing Chosen globally...');
            $('.chosen').chosen({ width: '100%' });
            console.log('Global Chosen initialization completed');
        }
        
        // Safe DataTable initialization with error handling
        try {
            // Check if table has proper structure
            var tableRows = $('#data-table-basic tbody tr').length;
            var tableHeaders = $('#data-table-basic thead th').length;
            console.log('Table structure check - Headers:', tableHeaders, 'Rows:', tableRows);
            
            if (tableHeaders > 0) {
                $('#data-table-basic').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "destroy": true // Allow reinitialization
                });
                console.log('DataTable initialized successfully');
            } else {
                console.log('Table structure incomplete - skipping DataTable init');
            }
        } catch (e) {
            console.error('DataTable initialization failed:', e);
        }
        
        // Filter button event - simple and reliable approach
        $('#filterReportBtn').on('click', function(e) {
            e.preventDefault();
            console.log('Filter button clicked!');
            
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();  
            var productType = $('#productTypeFilter').val();
            
            console.log('Filter values collected:', {
                fromDate: fromDate,
                toDate: toDate,
                productType: productType
            });
            
            // Build parameters
            var params = [];
            params.push('page=general-items-sales-report');
            if (fromDate) params.push('from_date=' + encodeURIComponent(fromDate));
            if (toDate) params.push('to_date=' + encodeURIComponent(toDate));
            if (productType && productType !== '') params.push('product_type=' + encodeURIComponent(productType));
            
            // Navigate to load.php with parameters
            var newUrl = '/ikawa.itectab.rw/operations/load.php?' + params.join('&');
            console.log('Redirecting to:', newUrl);
            
            window.location.href = newUrl;
        });
        
        // Clear filters button
        $('#clearFiltersBtn').on('click', function(e) {
            e.preventDefault();
            console.log('Clear filters button clicked!');
            window.location.href = '/ikawa.itectab.rw/operations/load.php?page=general-items-sales-report';
        });
        
        // Export button
        $('#exportReportBtn').on('click', function(e) {
            e.preventDefault();
            console.log('Export button clicked!');
            
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var productType = $('#productTypeFilter').val();
            
            var url = '<?= App::baseUrl() ?>/_ikawa/reports/export-sales-report?' +
                'from_date=' + encodeURIComponent(fromDate) +
                '&to_date=' + encodeURIComponent(toDate) +
                '&product_type=' + encodeURIComponent(productType);
            
            console.log('Opening export URL:', url);
            window.open(url, '_blank');
        });
        
        console.log('All event handlers attached');
    } else {
        console.log('Not on general-items-sales-report page, skipping initialization');
    }
});
</script>