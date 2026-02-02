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

// Get current filters from URL parameters
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-d');
$locationId = $_GET['location_id'] ?? '';
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
                                    <h2>General Items Stock Report</h2>
                                    <p>View and analyze current stock levels with comprehensive filtering options</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" id="exportStockReportBtn" class="btn btn-success">
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
                        <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" id="fromDate" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" placeholder="From Date">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" id="toDate" class="form-control" value="<?= htmlspecialchars($toDate) ?>" placeholder="To Date">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Location</label>
                                <select id="locationFilter" class="form-control chosen" data-placeholder="All Locations">
                                    <option value="">All Locations</option>
                                    <?php
                                    try {
                                        // Fetch stations/locations using existing API
                                        $stationsUrl = App::baseUrl() . '/_ikawa/transfer/get-stations';
                                        $stationsResponse = fetchApiData($stationsUrl);
                                        if ($stationsResponse) {
                                            $stationsData = json_decode($stationsResponse, true);
                                            if ($stationsData && $stationsData['success'] && !empty($stationsData['data'])) {
                                                foreach ($stationsData['data'] as $station) {
                                                    $selected = ($locationId == $station['loc_id']) ? 'selected' : '';
                                                    echo '<option value="' . htmlspecialchars($station['loc_id']) . '" ' . $selected . '>';
                                                    echo htmlspecialchars($station['location_name']);
                                                    echo '</option>';
                                                }
                                            }
                                        }
                                    } catch (Exception $e) {
                                        error_log("Error loading stations: " . $e->getMessage());
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Product</label>
                                <select id="productFilter" class="form-control chosen" data-placeholder="All Products">
                                    <option value="">All Products</option>
                                    <?php
                                    // Use direct model approach since API has session issues
                                    require_once __DIR__ . '/../../_ikawa/models/Reports.php';
                                    
                                    try {
                                        $reportsModel = new Models\Reports();
                                        $productTypes = $reportsModel->getProductTypes();
                                        
                                        if ($productTypes && is_array($productTypes)) {
                                            foreach ($productTypes as $type) {
                                                $selected = ($productId == $type['type_id']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($type['type_id']) . '" ' . $selected . '>';
                                                echo htmlspecialchars($type['type_name']);
                                                echo '</option>';
                                            }
                                        } else {
                                            echo '<option disabled>No product types found</option>';
                                        }
                                    } catch (Exception $e) {
                                        echo '<option disabled>Error loading product types</option>';
                                        error_log("Error loading product types in stock report: " . $e->getMessage());
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" id="filterStockReportBtn" class="btn btn-primary btn-block">
                                    <i class="fa fa-filter"></i> Apply Filters
                                </button>
                                <div class="mg-t-10">
                                    <button type="button" id="clearStockFiltersBtn" class="btn btn-default btn-block">
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

<!-- Stock Report Table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Stock Details</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="stock-data-table-basic" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Location</th>
                                    <th>Product Category</th>
                                    <th>Product Type</th>
                                    <th>Unit</th>
                                    <th>Stock Quantity</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody id="stockReportData">
                                <!-- Data will be loaded via AJAX when filters are applied -->
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fa fa-info-circle"></i> Click "Apply Filters" to load stock data
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

// Initialize our stock reports functionality
waitForJQuery(function($) {
    $(document).ready(function() {
        console.log('Stock reports script loaded - jQuery available');
        
        // Better page detection - check for specific elements that exist on stock reports page
        var isStockReportsPage = $('#stock-data-table-basic').length > 0 && 
                               $('#locationFilter').length > 0 && 
                               $('#filterStockReportBtn').length > 0 &&
                               $('#stockReportData').length > 0;
        
        console.log('Is Stock Reports Page:', isStockReportsPage);
        
        if (isStockReportsPage) {
            console.log('General Items Stock Report page detected - initializing...');
            
            // Force global chosen initialization like other pages
            if ($('.chosen').length) {
                console.log('Initializing Chosen globally...');
                $('.chosen').chosen({ width: '100%' });
                console.log('Global Chosen initialization completed');
            }
            
            // Safe DataTable initialization with error handling
            try {
                // Check if table has proper structure
                var tableRows = $('#stock-data-table-basic tbody tr').length;
                var tableHeaders = $('#stock-data-table-basic thead th').length;
                console.log('Table structure check - Headers:', tableHeaders, 'Rows:', tableRows);
                
                if (tableHeaders > 0) {
                    $('#stock-data-table-basic').DataTable({
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
            
            // Filter button event - AJAX approach
            $('#filterStockReportBtn').on('click', function(e) {
                e.preventDefault();
                console.log('Filter button clicked - loading stock data via AJAX...');
                
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();  
                var locationId = $('#locationFilter').val();
                var productId = $('#productFilter').val();
                
                console.log('Filter values:', {
                    fromDate: fromDate,
                    toDate: toDate,
                    locationId: locationId,
                    productId: productId
                });
                
                // Build API URL
                var apiUrl = '<?= App::baseUrl() ?>/_ikawa/reports/general-stock-report?' + 
                    'from_date=' + encodeURIComponent(fromDate) + 
                    '&to_date=' + encodeURIComponent(toDate);
                    
                if (locationId && locationId !== '') {
                    apiUrl += '&location_id=' + encodeURIComponent(locationId);
                }
                if (productId && productId !== '') {
                    apiUrl += '&product_id=' + encodeURIComponent(productId);
                }
                
                console.log('API URL:', apiUrl);
                
                // Show loading state
                $('#filterStockReportBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
                $('#stockReportData').html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading stock data...</td></tr>');
                
                // AJAX call to get data
                $.ajax({
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('API Response:', response);
                        
                        if (response && response.success && response.data) {
                            var stockData = response.data;
                            var tableRows = '';
                            
                            if (stockData.length > 0) {
                                var totalQuantity = 0;
                                
                                $.each(stockData, function(index, stock) {
                                    var quantity = parseFloat(stock.total_quantity || 0);
                                    totalQuantity += quantity;
                                    
                                    tableRows += '<tr>';
                                    tableRows += '<td>' + (index + 1) + '</td>';
                                    tableRows += '<td>' + (stock.location_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (stock.category_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (stock.type_name || 'N/A') + '</td>';
                                    tableRows += '<td>' + (stock.unit_name || 'N/A') + '</td>';
                                    tableRows += '<td class="text-right">' + quantity.toFixed(2) + '</td>';
                                    tableRows += '<td>' + (stock.updated_at ? new Date(stock.updated_at).toLocaleDateString() : 'N/A') + '</td>';
                                    tableRows += '</tr>';
                                });
                                
                                // Add summary row
                                tableRows += '<tr style="background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #dee2e6;">';
                                tableRows += '<td colspan="5" class="text-right"><strong>TOTAL STOCK:</strong></td>';
                                tableRows += '<td class="text-right"><strong>' + totalQuantity.toFixed(2) + '</strong></td>';
                                tableRows += '<td class="text-center"><strong>-</strong></td>';
                                tableRows += '</tr>';
                            } else {
                                tableRows = '<tr><td colspan="7" class="text-center">No stock data found for the selected filters</td></tr>';
                            }
                            
                            $('#stockReportData').html(tableRows);
                            
                            // Reinitialize DataTable
                            if ($.fn.DataTable.isDataTable('#stock-data-table-basic')) {
                                $('#stock-data-table-basic').DataTable().destroy();
                            }
                            $('#stock-data-table-basic').DataTable({
                                "paging": true,
                                "lengthChange": true,
                                "searching": true,
                                "ordering": true,
                                "info": true,
                                "autoWidth": false,
                                "responsive": true
                            });
                            
                            console.log('Table updated with', stockData.length, 'records');
                        } else {
                            $('#stockReportData').html('<tr><td colspan="7" class="text-center">No stock data found</td></tr>');
                            console.log('No data received from API');
                        }
                        
                        // Reset button state in success
                        $('#filterStockReportBtn').prop('disabled', false).html('<i class="fa fa-filter"></i> Apply Filters');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        $('#stockReportData').html('<tr><td colspan="7" class="text-center text-danger">Error loading stock data</td></tr>');
                        
                        // Reset button state in error
                        $('#filterStockReportBtn').prop('disabled', false).html('<i class="fa fa-filter"></i> Apply Filters');
                    }
                });
            });
            
            // Clear filters button
            $('#clearStockFiltersBtn').on('click', function(e) {
                e.preventDefault();
                console.log('Clear filters button clicked!');
                window.location.href = '/ikawa.itectab.rw/operations/load.php?page=general-items-stock-report';
            });
            
            // Export button
            $('#exportStockReportBtn').on('click', function(e) {
                e.preventDefault();
                console.log('Export button clicked!');
                
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var locationId = $('#locationFilter').val();
                var productId = $('#productFilter').val();
                
                var url = '<?= App::baseUrl() ?>/_ikawa/reports/export-stock-report?' +
                    'from_date=' + encodeURIComponent(fromDate) +
                    '&to_date=' + encodeURIComponent(toDate) +
                    '&location_id=' + encodeURIComponent(locationId) +
                    '&product_id=' + encodeURIComponent(productId);
                
                console.log('Opening export URL:', url);
                window.open(url, '_blank');
            });
            
            console.log('All event handlers attached');
        } else {
            console.log('Not on general-items-stock-report page, skipping initialization');
        }
    });
});
</script>