<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-checked"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Stock Price Approval</h2>
                                    <p>Approve unit prices for pending stock entries</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" id="approveSelectedBtn" class="btn btn-success" disabled>
                                    <i class="fa fa-check"></i> Approve Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Stock Table -->
<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Pending Stock Entries</h2>
                        <p>Enter unit price and approve each stock entry</p>
                    </div>
                    <div class="table-responsive">
                        <table id="pending-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllPending"></th>
                                    <th>#</th>
                                    <th>Station</th>
                                    <th>Category Type</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Unit Price (RWF)</th>
                                    <th>Total Price</th>
                                    <th>Recorded By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="pendingStockData">
                                <?php
                                $apiUrl = App::baseUrl() . '/_ikawa/stock/get-pending';
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $record) {
                                        $recordedBy = ($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '');
                                        ?>
                                        <tr data-id="<?= $record['stock_detail_id'] ?>">
                                            <td><input type="checkbox" class="select-stock" data-id="<?= $record['stock_detail_id'] ?>"></td>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($record['station_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['type_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['unit_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($record['supplier_name'] ?? 'N/A') ?></td>
                                            <td class="quantity-cell"><?= $record['quantity'] ?></td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control unit-price-input" 
                                                       data-id="<?= $record['stock_detail_id'] ?>" 
                                                       data-quantity="<?= $record['quantity'] ?>"
                                                       placeholder="Enter price" style="width: 120px;">
                                            </td>
                                            <td class="total-price-cell">0 RWF</td>
                                            <td><?= htmlspecialchars(trim($recordedBy)) ?></td>
                                            <td><?= date('m/d/Y', strtotime($record['created_at'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm approve-single-btn" 
                                                        data-id="<?= $record['stock_detail_id'] ?>" disabled>
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr><td colspan="12" class="text-center">No pending stock entries</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
