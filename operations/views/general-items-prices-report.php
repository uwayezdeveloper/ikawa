<?php
// Fetch API helper function
if (!function_exists('fetchApiData')) {
    function fetchApiData($url) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ($httpCode === 200) ? $response : false;
    }
}

// Get current filters from URL parameters (for future use if needed)
$productId = $_GET['product_id'] ?? '';
?>

<!-- Breadcrumb -->
<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-bar-chart"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>General Items Prices Report</h2>
                                    <p>View and analyze current stock prices with latest pricing information</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" id="exportPricesReportBtn" class="btn btn-success">
                                    <i class="fa fa-download"></i> Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Items Prices Report Table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Items Prices Details</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="prices-data-table-basic" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Location</th>
                                    <th>Product Category</th>
                                    <th>Product Type</th>
                                    <th>Unit</th>
                                    <th>Current Stock</th>
                                    <th>Latest Unit Price (RWF)</th>
                                    <th>Stock Value (RWF)</th>
                                    <th>Supplier</th>
                                    <th>Price Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="pricesReportData">
                                <!-- Data will be loaded via AJAX when page loads -->
                                <tr>
                                    <td colspan="11" class="text-center text-muted">
                                        <i class="fa fa-spinner fa-spin"></i> Loading prices data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Wait for jQuery to be available before running our code
function waitForJQuery(callback) {
    if (typeof jQuery !== 'undefined') {
        callback(jQuery);
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 50);
    }
}

// Initialize our prices reports functionality
waitForJQuery(function($) {
    $(document).ready(function() {
        console.log('Prices reports script loaded - jQuery available');
        
        // Better page detection - check for specific elements that exist on prices reports page
        var isPricesReportsPage = $('#prices-data-table-basic').length > 0 && 
                               $('#exportPricesReportBtn').length > 0 &&
                               $('#pricesReportData').length > 0;
        
        console.log('Is Prices Reports Page:', isPricesReportsPage);
        
        if (isPricesReportsPage) {
            console.log('General Items Prices Report page detected - initializing...');
            
            // Safe DataTable initialization with error handling
            var dataTable;
            
            // Load data immediately when page loads
            loadPricesData();
            
            function loadPricesData() {
                console.log('Loading prices data via AJAX...');
                
                // Build API URL
                var apiUrl = '<?= App::baseUrl() ?>/_ikawa/item-prices/get-all-products-latest-prices';
                console.log('API URL:', apiUrl);
                
                // Show loading state
                $('#pricesReportData').html('<tr><td colspan="11" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading prices data...</td></tr>');
                
                // AJAX call to get data
                $.ajax({
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('API Response:', response);
                        
                        if (response && response.success && response.data) {
                            var pricesData = response.data;
                            var tableRows = '';
                            
                            if (pricesData.length > 0) {
                                var totalStockValue = 0;
                                
                                $.each(pricesData, function(index, item) {
                                    var currentStock = parseFloat(item.current_stock || 0);
                                    var unitPrice = parseFloat(item.latest_unit_price || 0);
                                    var stockValue = parseFloat(item.stock_value || 0);
                                    totalStockValue += stockValue;
                                    
                                    var statusClass = item.price_status === 'approved' ? 'success' : 'warning';
                                    var statusText = (item.price_status || 'N/A').toUpperCase();
                                    
                                    tableRows += '<tr>';
                                    tableRows += '<td>' + (index + 1) + '</td>';
                                    tableRows += '<td>' + (item.location_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (item.category_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (item.type_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (item.unit_name || 'N/A') + '</td>';
                                    tableRows += '<td class="text-right">' + currentStock.toFixed(2) + '</td>';
                                    tableRows += '<td class="text-right">' + unitPrice.toLocaleString() + '</td>';
                                    tableRows += '<td class="text-right"><strong>' + stockValue.toLocaleString() + '</strong></td>';
                                    tableRows += '<td>' + (item.supplier_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (item.price_date ? new Date(item.price_date).toLocaleDateString() : 'N/A') + '</td>';
                                    tableRows += '<td class="text-center"><span class="badge badge-' + statusClass + '">' + statusText + '</span></td>';
                                    tableRows += '</tr>';
                                });
                                
                                // Add summary row
                                tableRows += '<tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">';
                                tableRows += '<td colspan="7" class="text-right"><strong>TOTAL STOCK VALUE:</strong></td>';
                                tableRows += '<td class="text-right"><strong>' + totalStockValue.toLocaleString() + ' RWF</strong></td>';
                                tableRows += '<td colspan="3" class="text-center"><strong>-</strong></td>';
                                tableRows += '</tr>';
                            } else {
                                tableRows = '<tr><td colspan="11" class="text-center">No prices data found</td></tr>';
                            }
                            
                            $('#pricesReportData').html(tableRows);
                            
                            // Initialize DataTable
                            try {
                                if ($.fn.DataTable.isDataTable('#prices-data-table-basic')) {
                                    $('#prices-data-table-basic').DataTable().destroy();
                                }
                                
                                dataTable = $('#prices-data-table-basic').DataTable({
                                    "paging": true,
                                    "lengthChange": true,
                                    "searching": true,
                                    "ordering": true,
                                    "info": true,
                                    "autoWidth": false,
                                    "responsive": true,
                                    "pageLength": 25,
                                    "order": [[2, 'asc'], [3, 'asc']] // Sort by Category, then Product Type
                                });
                                console.log('DataTable initialized successfully');
                            } catch (e) {
                                console.error('DataTable initialization failed:', e);
                            }
                            
                            console.log('Table updated with', pricesData.length, 'records');
                        } else {
                            $('#pricesReportData').html('<tr><td colspan="11" class="text-center">No prices data found</td></tr>');
                            console.log('No data received from API');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        $('#pricesReportData').html('<tr><td colspan="11" class="text-center text-danger">Error loading prices data</td></tr>');
                    }
                });
            }
            
            // Export button
            $('#exportPricesReportBtn').on('click', function(e) {
                e.preventDefault();
                console.log('Export button clicked!');
                
                var url = '<?= App::baseUrl() ?>/_ikawa/item-prices/export-products-latest-prices';
                
                console.log('Opening export URL:', url);
                window.open(url, '_blank');
            });
            
            console.log('All event handlers attached');
        } else {
            console.log('Not on general-items-prices-report page, skipping initialization');
        }
    });
});
</script>