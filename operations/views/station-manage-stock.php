<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add New Stock</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="stockEntryTable">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Category</th>
                                <th style="width: 30%;">Category Type / Unity</th>
                                <th style="width: 25%;">Supplier</th>
                                <th style="width: 15%;">Quantity</th>
                                <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="stockEntryBody">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
                <button type="button" id="addRowBtn" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Add Row
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveAllStockBtn" class="btn btn-primary">Save All Stock</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-windows"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Manage Station Stock</h2>
                                    <p>Add and manage stock inventory for your station</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#addStockModal" class="btn"><i class="fa fa-plus"></i> Add Stock</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Stock Records Table -->
<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Detailed Stock Records</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="detailed-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Type</th>
                                    <th>Unity</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="detailedStockData">
                                <?php
                                $loc_id = $_SESSION['loc_id'] ?? 0;
                                $apiUrl = App::baseUrl() . '/_ikawa/stock/get-detailed-stock?loc_id=' . $loc_id;
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $record) {
                                        $statusClass = $record['price_status'] === 'approved' ? 'label-success' : 'label-warning';
                                        $statusText = ucfirst($record['price_status'] ?? 'pending');
                                        $unitPrice = $record['unit_price'] > 0 ? number_format($record['unit_price'], 0, ',', ',') . ' RWF' : 'Pending';
                                        $totalPrice = $record['total_price'] > 0 ? number_format($record['total_price'], 0, ',', ',') . ' RWF' : 'Pending';
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($record['type_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['unit_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['supplier_name'] ?? 'N/A') ?></td>
                                            <td><?= $record['quantity'] ?></td>
                                            <td><?= $unitPrice ?></td>
                                            <td><?= $totalPrice ?></td>
                                            <td><span class="label <?= $statusClass ?>"><?= $statusText ?></span></td>
                                            <td><?= date('m/d/Y', strtotime($record['created_at'])) ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr><td colspan="9" class="text-center">No stock records found</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Stock Table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Station Stock Summary</h2>
                        <p>Stock grouped by category type and unit (Only approved stock counted)</p>
                    </div>
                    <div class="table-responsive">
                        <table id="summary-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Station</th>
                                    <th>Category Type</th>
                                    <th>Unit</th>
                                    <th>Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="summaryStockData">
                                <?php
                                $summaryUrl = App::baseUrl() . '/_ikawa/stock/get-summary-stock?loc_id=' . $loc_id;
                                $summaryJson = fetchApiData($summaryUrl);
                                $summaryResult = json_decode($summaryJson, true);
                                
                                if ($summaryResult && $summaryResult['success'] && !empty($summaryResult['data'])) {
                                    foreach ($summaryResult['data'] as $index => $record) {
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($record['station_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['type_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['unit_name'] ?? 'N/A') ?></td>
                                            <td><?= $record['total_quantity'] ?? 0 ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr><td colspan="5" class="text-center">No summary data found</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
