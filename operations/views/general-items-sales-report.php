<?php
// Helper function for API calls like other working pages
if (!function_exists('fetchApiData')) {
    function fetchApiData($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

// Get filter parameters
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-d');
$productType = $_GET['product_type'] ?? '';
?>

<!-- General Items Sales Report -->
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
                                    <h2>General Items Sales Report</h2>
                                    <p>View and analyze sales data with comprehensive filtering options</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" id="exportReportBtn" class="btn btn-success">
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

<!-- Report Filters Section -->
<div class="form-element-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-element-list">
                    <div class="basic-tb-hd">
                        <h2><i class="fa fa-filter"></i> Report Filters</h2>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group ic-cmp-int">
                                <div class="form-ic-cmp">
                                    <i class="notika-icon notika-calendar"></i>
                                </div>
                                <div class="nk-int-st">
                                    <input type="date" id="fromDate" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" placeholder="From Date">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group ic-cmp-int">
                                <div class="form-ic-cmp">
                                    <i class="notika-icon notika-calendar"></i>
                                </div>
                                <div class="nk-int-st">
                                    <input type="date" id="toDate" class="form-control" value="<?= htmlspecialchars($toDate) ?>" placeholder="To Date">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-8 col-xs-12">
                            <div class="chosen-select-act fm-cmp-mg">
                                <select class="chosen" data-placeholder="Choose Product Type..." id="productTypeFilter">
                                    <option value="">All Products</option>
                                    <?php
                                    // Use direct model approach since API has session issues
                                    require_once __DIR__ . '/../../_ikawa/models/Reports.php';
                                    
                                    try {
                                        $reportsModel = new Models\Reports();
                                        $productTypes = $reportsModel->getProductTypes();
                                        
                                        if ($productTypes && is_array($productTypes)) {
                                            foreach ($productTypes as $type) {
                                                $selected = ($productType == $type['type_id']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($type['type_id']) . '" ' . $selected . '>';
                                                echo htmlspecialchars($type['type_name']);
                                                echo '</option>';
                                            }
                                        } else {
                                            echo '<option disabled>No product types found</option>';
                                        }
                                    } catch (Exception $e) {
                                        echo '<option disabled>Error loading product types</option>';
                                        error_log("Error loading product types in report: " . $e->getMessage());
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-12">
                            <div class="form-group">
                                <button type="button" id="filterReportBtn" class="btn btn-primary btn-block">
                                    <i class="fa fa-filter"></i> Apply Filters
                                </button>
                                <div class="mg-t-10">
                                    <button type="button" id="clearFiltersBtn" class="btn btn-default btn-block">
                                        <i class="fa fa-refresh"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Report Table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Sales Details</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="data-table-basic" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Sale ID</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Amount</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody id="salesReportData">
                                <!-- Data will be loaded via AJAX when filters are applied -->
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fa fa-info-circle"></i> Click "Apply Filters" to load sales data
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

// Initialize our reports functionality
waitForJQuery(function($) {
    $(document).ready(function() {
        console.log('Reports script loaded - jQuery available');
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
            
            // Filter button event - AJAX approach like expense statement
            $('#filterReportBtn').on('click', function(e) {
                e.preventDefault();
                console.log('Filter button clicked - loading data via AJAX...');
                
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();  
                var productType = $('#productTypeFilter').val();
                var locationId = <?= $_SESSION['loc_id'] ?? 1 ?>;
                
                console.log('Filter values:', {
                    fromDate: fromDate,
                    toDate: toDate,
                    productType: productType,
                    locationId: locationId
                });
                
                // Build API URL
                var apiUrl = '<?= App::baseUrl() ?>/_ikawa/reports/general-sales-report?' + 
                    'loc_id=' + encodeURIComponent(locationId) +
                    '&from_date=' + encodeURIComponent(fromDate) + 
                    '&to_date=' + encodeURIComponent(toDate);
                    
                if (productType && productType !== '') {
                    apiUrl += '&product_type=' + encodeURIComponent(productType);
                }
                
                console.log('API URL:', apiUrl);
                
                // Show loading state
                $('#filterReportBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
                $('#salesReportData').html('<tr><td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading sales data...</td></tr>');
                
                // AJAX call to get data
                $.ajax({
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('API Response:', response);
                        
                        if (response && response.success && response.data) {
                            var salesData = response.data;
                            var tableRows = '';
                            
                            if (salesData.length > 0) {
                                var totalQuantity = 0;
                                var totalAmount = 0;
                                var totalUnitPrice = 0;
                                
                                $.each(salesData, function(index, sale) {
                                    var quantity = parseFloat(sale.quantity || 0);
                                    var unitPrice = parseFloat(sale.unit_price || 0);
                                    var itemTotal = parseFloat(sale.item_total || sale.total_amount || 0);
                                    
                                    // Add to totals
                                    totalQuantity += quantity;
                                    totalUnitPrice += unitPrice;
                                    totalAmount += itemTotal;
                                    
                                    tableRows += '<tr>';
                                    tableRows += '<td>' + (index + 1) + '</td>';
                                    tableRows += '<td>' + (sale.sale_date ? new Date(sale.sale_date).toLocaleDateString() : 'N/A') + '</td>';
                                    tableRows += '<td>' + (sale.invoice_no || sale.sale_id || 'N/A') + '</td>';
                                    tableRows += '<td>' + (sale.customer_name || 'Walk-in Customer') + '</td>';
                                    tableRows += '<td>' + (sale.product_name || 'N/A') + '</td>';
                                    tableRows += '<td class="text-right">' + quantity.toFixed(2) + ' ' + (sale.unit_name || '') + '</td>';
                                    tableRows += '<td class="text-right">' + unitPrice.toLocaleString() + ' RWF</td>';
                                    tableRows += '<td class="text-right">' + itemTotal.toLocaleString() + ' RWF</td>';
                                    tableRows += '<td class="text-center">' + (sale.payment_method || 'N/A') + '</td>';
                                    tableRows += '</tr>';
                                });
                                
                                // Add summary row
                                var avgUnitPrice = salesData.length > 0 ? (totalUnitPrice / salesData.length) : 0;
                                tableRows += '<tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">';
                                tableRows += '<td colspan="5" class="text-right"><strong>TOTALS:</strong></td>';
                                tableRows += '<td class="text-right"><strong>' + totalQuantity.toFixed(2) + '</strong></td>';
                                tableRows += '<td class="text-right"><strong>' + avgUnitPrice.toLocaleString() + ' RWF (Avg)</strong></td>';
                                tableRows += '<td class="text-right"><strong>' + totalAmount.toLocaleString() + ' RWF</strong></td>';
                                tableRows += '<td class="text-center"><strong>-</strong></td>';
                                tableRows += '</tr>';
                            } else {
                                tableRows = '<tr><td colspan="9" class="text-center">No sales data found for the selected period</td></tr>';
                            }
                            
                            $('#salesReportData').html(tableRows);
                            
                            // Reinitialize DataTable
                            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                                $('#data-table-basic').DataTable().destroy();
                            }
                            $('#data-table-basic').DataTable({
                                "paging": true,
                                "lengthChange": true,
                                "searching": true,
                                "ordering": true,
                                "info": true,
                                "autoWidth": false,
                                "responsive": true
                            });
                            
                            console.log('Table updated with', salesData.length, 'records');
                        } else {
                            $('#salesReportData').html('<tr><td colspan="9" class="text-center">No sales data found</td></tr>');
                            console.log('No data received from API');
                        }
                        
                        // Reset button state in success
                        $('#filterReportBtn').prop('disabled', false).html('<i class="fa fa-filter"></i> Apply Filters');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        $('#salesReportData').html('<tr><td colspan="9" class="text-center text-danger">Error loading sales data</td></tr>');
                        
                        // Reset button state in error
                        $('#filterReportBtn').prop('disabled', false).html('<i class="fa fa-filter"></i> Apply Filters');

                    }
                });
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
});
</script>