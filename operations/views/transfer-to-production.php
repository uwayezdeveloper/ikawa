<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Transfer Stock to Production</h4>
            </div>
            <div class="modal-body">
                <div class="row mg-b-15">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>From Station</label>
                            <input type="text" class="form-control" value="<?= $_SESSION['location_name'] ?? 'Current Station' ?>" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Transfer Date *</label>
                            <input type="date" id="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" id="transfer_notes" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="transferEntryTable">
                        <thead>
                            <tr>
                                <th style="width:5%;"><input type="checkbox" id="selectAllStock"></th>
                                <th>Category Type / Unit</th>
                                <th>Available Qty</th>
                                <th style="width:20%;">Transfer Qty</th>
                            </tr>
                        </thead>
                        <tbody id="availableStockBody">
                            <tr><td colspan="4" class="text-center">Loading available stock...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveTransferBtn" class="btn btn-primary">Transfer Selected</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Transfer Details Modal -->
<div class="modal fade" id="viewDetailsModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Transfer Details - <span id="detailRefNo"></span></h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Type</th>
                                <th>Unity</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="transferDetailsBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Return Transfer Modal -->
<div class="modal fade" id="returnTransferModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Return Transfer - <span id="returnRefNo"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row mg-b-15">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Return Date *</label>
                            <input type="date" id="return_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Return Reason *</label>
                            <input type="text" id="return_reason" class="form-control" placeholder="Enter reason for return">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width:5%;"><input type="checkbox" id="selectAllReturn"></th>
                                <th>Category Type / Unit</th>
                                <th>Transferred Qty</th>
                                <th style="width:15%;">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody id="returnItemsBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveReturnBtn" class="btn btn-warning">Return Selected Items</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Receive from Production Modal -->
<div class="modal fade" id="receiveProductionModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Receive from Production - <span id="receiveRefNo"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row mg-b-15">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Original Category Type / Unit</label>
                            <input type="text" id="receive_original_type" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Total Qty Sent</label>
                            <input type="text" id="receive_total_qty" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="row mg-b-15">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Receive Date *</label>
                            <input type="date" id="receive_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" id="receive_notes" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>
                </div>

                <!-- Quantity Validation Info -->
                <div class="row mg-b-15">
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="alert alert-info" style="padding: 10px; margin-bottom: 0;">
                            <strong>Max Available:</strong> <span id="receive_max_qty">0</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="alert alert-warning" style="padding: 10px; margin-bottom: 0;">
                            <strong>Total Entering:</strong> <span id="receive_total_entering" class="text-success">0</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="alert alert-success" style="padding: 10px; margin-bottom: 0;">
                            <strong>Remaining:</strong> <span id="receive_remaining" class="text-success">0</span>
                        </div>
                    </div>
                </div>

                <hr>
                <h5><i class="fa fa-cubes"></i> Production Output Items</h5>
                <p class="text-muted">Add the items received from production (total cannot exceed max available)</p>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="receiveItemsTable">
                        <thead>
                            <tr>
                                <th style="width:30%;">Category</th>
                                <th style="width:35%;">Category Type / Unity</th>
                                <th style="width:25%;">Quantity Received</th>
                                <th style="width:10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="receiveItemsBody">
                        </tbody>
                    </table>
                </div>
                <button type="button" id="addReceiveRowBtn" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Add Item
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveReceiveBtn" class="btn btn-primary">Save Received Items</button>
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
                                    <i class="notika-icon notika-sent"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Transfer to Production</h2>
                                    <p>Transfer stock from station to production facility</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#transferModal" class="btn"><i class="fa fa-exchange"></i> New Transfer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer History Table -->
<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Transfer History</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="transfer-history-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reference No</th>
                                    <th>Category Type</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="transferHistoryData">
                                <?php
                                $loc_id = $_SESSION['loc_id'] ?? 0;
                                
                                if ($loc_id == 0) {
                                    echo '<tr><td colspan="8" class="text-center text-warning">Session loc_id not set. Please login again.</td></tr>';
                                } else {
                                    require_once __DIR__ . '/../../_ikawa/models/ProductionTransfer.php';
                                    $transferModel = new \Models\ProductionTransfer();
                                    $transfers = $transferModel->getTransfersByLocation($loc_id);
                                    

                                    if ($transfers && count($transfers) > 0) {
                                        foreach ($transfers as $index => $record) {
                                            // Use detail_status for individual item status
                                            $itemStatus = $record['detail_status'] ?? $record['tracking_status'] ?? 'pending';
                                            
                                            $statusClass = $itemStatus === 'completed' ? 'label-success' : 
                                                          ($itemStatus === 'returned' ? 'label-danger' :
                                                          ($itemStatus === 'in_transit' ? 'label-info' :
                                                          ($itemStatus === 'pending' ? 'label-warning' : 'label-default')));
                                            
                                            $approveBtn = $itemStatus === 'pending' ? 
                                                '<button class="btn btn-sm btn-success approve-transfer-btn" 
                                                    data-id="' . $record['tracking_id'] . '" 
                                                    data-detail-id="' . $record['transfer_detail_id'] . '" 
                                                    data-ref="' . $record['reference_no'] . '" 
                                                    title="Approve This Item">
                                                    <i class="fa fa-check"></i>
                                                </button> ' : '';
                                            
                                            $receiveBtn = $itemStatus === 'in_transit' ? 
                                                '<button class="btn btn-sm btn-primary receive-production-btn" 
                                                    data-id="' . $record['tracking_id'] . '" 
                                                    data-detail-id="' . $record['transfer_detail_id'] . '" 
                                                    data-ref="' . $record['reference_no'] . '" 
                                                    title="Receive from Production">
                                                    <i class="fa fa-download"></i>
                                                </button> ' : '';
                                            
                                            $returnBtn = ($itemStatus !== 'returned' && $itemStatus !== 'pending' && $itemStatus !== 'completed') ? 
                                                '<button class="btn btn-sm btn-warning return-transfer-btn" 
                                                    data-id="' . $record['tracking_id'] . '" 
                                                    data-detail-id="' . $record['transfer_detail_id'] . '" 
                                                    data-ref="' . $record['reference_no'] . '" 
                                                    title="Return Transfer">
                                                    <i class="fa fa-undo"></i>
                                                </button> ' : '';
                                            
                                            $statusLabel = ucfirst(str_replace('_', ' ', $itemStatus));
                                            $categoryUnitDisplay = ($record['type_name'] ?? 'N/A') . ' / ' . ($record['unit_name'] ?? 'N/A');
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><strong><?= htmlspecialchars($record['reference_no']) ?></strong></td>
                                                <td><?= htmlspecialchars($categoryUnitDisplay) ?></td>
                                                <td><?= htmlspecialchars($record['unit_name'] ?? 'N/A') ?></td>
                                                <td><?= $record['quantity'] ?? 0 ?></td>
                                                <td><?= date('m/d/Y', strtotime($record['transfer_date'])) ?></td>
                                                <td><span class="label <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                                <td>
                                                    <?= $approveBtn ?>
                                                    <?= $receiveBtn ?>
                                                    <?= $returnBtn ?>
                                                    <button class="btn btn-sm btn-info view-details-btn" data-id="<?= $record['tracking_id'] ?>" data-ref="<?= $record['reference_no'] ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr><td colspan="8" class="text-center">No transfers found</td></tr>
                                    <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
