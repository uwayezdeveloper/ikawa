<?php
// Ensure config is loaded when page is loaded dynamically
if (!class_exists('App')) {
    require_once __DIR__ . '/../../_ikawa/config/App.php';
}

// Helper function if not exists
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

$base = App::baseUrl();
$loc_id = $_SESSION['loc_id'] ?? 0;

// Preload suppliers server-side
$suppliers = [];
$suppliersUrl = $base . '/_ikawa/stock/get-stock-suppliers';
$suppliersResponse = fetchApiData($suppliersUrl);
if ($suppliersResponse) {
    $suppliersDecoded = json_decode($suppliersResponse, true);
    if ($suppliersDecoded && isset($suppliersDecoded['success']) && $suppliersDecoded['success'] === true) {
        $suppliers = $suppliersDecoded['data'] ?? [];
    }
}
?>

<!-- Header / Breadcrumb -->
<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-form"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Supplier Statement</h2>
                                    <p>View deliveries, payable, payments and outstanding balances per supplier</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 text-right">
                            <div class="breadcomb-report">
                                <button id="btnRefresh" class="btn"><i class="fa fa-refresh"></i> Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Filter</h2>
                        <p>Select supplier and date range to view statement</p>
                    </div>
                    <div style="padding: 15px;">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select id="supplierSelect" class="form-control" required>
                                        <option value="">-- Select Supplier --</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?= htmlspecialchars($supplier['sup_id']) ?>">
                                                <?= htmlspecialchars($supplier['full_name']) ?><?= !empty($supplier['type']) ? ' (' . htmlspecialchars($supplier['type']) . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" id="endDate" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button id="btnFetch" class="btn btn-success">
                                    <i class="fa fa-search"></i> Search Statement
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

            <!-- Supplier Picker Modal -->
            <div class="modal fade" id="supplierPickerModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Select Supplier</h4>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="supplierPickerTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="supplierPickerBody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Pay Supplier</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Supplier:</strong> <span id="paymentSupplierName"></span><br>
                    <strong>Total Amount to Pay:</strong> <span id="paymentTotalAmount">0 RWF</span>
                </div>
                
                <form id="paymentForm">
                    <input type="hidden" id="paymentSupplierId">
                    <input type="hidden" id="paymentUnpaidItems">
                    
                    <div class="form-group">
                        <label>Payment Accounts <span class="text-danger">*</span></label>
                        <div id="paymentAccountsContainer">
                            <!-- Payment accounts will be added here dynamically -->
                        </div>
                        <button type="button" id="btnAddPaymentAccount" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Add Another Account
                        </button>
                    </div>
                    
                    <div class="alert alert-warning" id="paymentWarning" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnSubmitPayment" class="btn btn-success">
                    <i class="fa fa-check"></i> Process Payment
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Results -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Statement Results</h2>
                    </div>
                    <div class="table-responsive">
                        <div style="margin: 10px 15px 20px;">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong>Total Payable:</strong>
                                    <span id="payable" style="font-size:16px; font-weight:bold; color:#007bff;">0.00 RWF</span>
                                </div>
                                <div class="col-lg-3">
                                    <strong>Total Paid:</strong>
                                    <span id="paid" style="font-size:16px; font-weight:bold; color:#28a745;">0.00 RWF</span>
                                </div>
                                <div class="col-lg-3">
                                    <strong>Total Remaining:</strong>
                                    <span id="remaining" style="font-size:16px; font-weight:bold; color:#dc3545;">0.00 RWF</span>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <button id="btnPaySupplier" class="btn btn-success" style="display:none;">
                                        <i class="fa fa-money"></i> Pay Supplier
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table id="data-table-basic" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody id="statementBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

