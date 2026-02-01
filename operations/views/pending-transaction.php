<!-- View Transfer Details Modal -->
<div class="modal animated bounce" id="viewTransferModal" role="dialog">
    <div class="modal-dialog modals-default" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>From Station:</strong> <span id="view_station_name"></span></p>
                        <p><strong>To Warehouse:</strong> <span id="view_warehouse_name"></span></p>
                        <p><strong>Category:</strong> <span id="view_category_name"></span></p>
                        <p><strong>Driver:</strong> <span id="view_driver_name"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Driver Phone:</strong> <span id="view_driver_phone"></span></p>
                        <p><strong>License Number:</strong> <span id="view_driver_license"></span></p>
                        <p><strong>Created By:</strong> <span id="view_created_by"></span></p>
                        <p><strong>Created At:</strong> <span id="view_created_at"></span></p>
                        <p><strong>Status:</strong> <span id="view_status"></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Supporting Document:</strong> <span id="view_supporting_docs"></span></p>
                        <p><strong>Additional Info:</strong> <span id="view_additional_info"></span></p>
                    </div>
                </div>
                <div class="row" id="receive_info_row" style="display: none;">
                    <div class="col-md-12">
                        <hr>
                        <h5 class="text-success"><i class="fa fa-check-circle"></i> Receive Information</h5>
                        <p><strong>Received By:</strong> <span id="view_received_by"></span></p>
                        <p><strong>Received At:</strong> <span id="view_received_at"></span></p>
                        <p><strong>Receive Document:</strong> <span id="view_receive_note"></span></p>
                        <p><strong>Receive Comment:</strong> <span id="view_receive_comment"></span></p>
                    </div>
                </div>
                <hr>
                <h5>Transfer Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type Name</th>
                                <th>Unit</th>
                                <th>Amount</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody id="view_items_body">
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

<!-- Reject Transfer Modal -->
<div class="modal animated bounce" id="rejectTransferModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Transfer</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reject_transfer_id">
                <div class="form-group">
                    <label>Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="reject_reason" rows="4" placeholder="Enter reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmRejectBtn" class="btn btn-danger">Reject Transfer</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Receive Transfer Confirmation Modal -->
<div class="modal animated bounce" id="receiveTransferModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm Receive Transfer</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="receive_transfer_id">
                <div class="text-center" style="margin-bottom: 20px;">
                    <i class="fa fa-question-circle fa-4x text-info" style="margin-bottom: 15px;"></i>
                    <h4>Are you sure you want to receive this transfer?</h4>
                </div>
                <div class="form-group">
                    <label>Receive Confirmation Document</label>
                    <input type="file" class="form-control" id="receive_note" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="text-muted">Upload document that confirms product is received (PDF, DOC, DOCX, JPG, PNG)</small>
                </div>
                <div class="form-group">
                    <label>Receive Comment</label>
                    <textarea class="form-control" id="receive_comment" rows="3" placeholder="Enter additional comments about receiving this transfer..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmReceiveBtn" class="btn btn-success">Yes, Receive</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
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
                                    <i class="notika-icon notika-edit"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Station to Warehouse Transfer</h2>
                                    <p>Transfer products from stations to warehouses</p>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#createTransferModal" title="Create New Transfer" class="btn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Transfer Records</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="data-table-basic" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>From Station</th>
                                    <th>To Warehouse</th>
                                    <th>Category</th>
                                    <th>Driver</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="transferdata">
                                <?php
                                $apiUrl = App::baseUrl() . '/_ikawa/transfer/get-all';
                                $json = @file_get_contents($apiUrl);
                                $result = json_decode($json, true);
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $record) {
                                        $statusClass = 'text-warning';
                                        if ($record['status'] === 'Received') $statusClass = 'text-success';
                                        elseif ($record['status'] === 'Rejected') $statusClass = 'text-danger';
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1 ?></td>
                                            <td><?php echo htmlspecialchars($record['station_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($record['warehouse_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($record['category_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($record['driver_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($record['created_by_name'] ?? ''); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($record['created_at'])); ?></td>
                                            <td><span class="<?php echo $statusClass; ?>"><?php echo $record['status']; ?></span></td>
                                            <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                    <button class="btn btn-default btn-icon-notika viewtransfer" 
                                                        title="View Details"
                                                        data-id="<?= $record['id'] ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <?php if ($record['status'] === 'Pending') { ?>
                                                    <button class="btn btn-success btn-icon-notika receivetransfer" 
                                                        title="Receive Transfer"
                                                        data-id="<?= $record['id'] ?>">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                    <!-- <button class="btn btn-danger btn-icon-notika rejecttransfer" 
                                                        title="Reject Transfer"
                                                        data-id="<?= $record['id'] ?>">
                                                        <i class="fa fa-times"></i>
                                                    </button> -->
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Transfer Modal -->
<?php
// Pre-load dropdown data using existing endpoints
$locationsUrl = App::baseUrl() . '/_ikawa/settings/location';
$locationsJson = @file_get_contents($locationsUrl);
$locationsResult = json_decode($locationsJson, true);
$allLocations = ($locationsResult && $locationsResult['success']) ? $locationsResult['data'] : [];

// Filter stations and warehouses
$stations = array_filter($allLocations, function($loc) {
    return isset($loc['type']) && $loc['type'] === 'Station';
});
$warehouses = array_filter($allLocations, function($loc) {
    return isset($loc['type']) && $loc['type'] === 'Warehouse';
});

$categoriesUrl = App::baseUrl() . '/_ikawa/categories/get-all-categories';
$categoriesJson = @file_get_contents($categoriesUrl);
$categoriesResult = json_decode($categoriesJson, true);
$allCategories = ($categoriesResult && $categoriesResult['success']) ? $categoriesResult['data'] : [];

// Filter only active categories
$categories = array_filter($allCategories, function($cat) {
    return isset($cat['status']) && $cat['status'] === 'active';
});

// Load drivers
$driversUrl = App::baseUrl() . '/_ikawa/drivers/get-all';
$driversJson = @file_get_contents($driversUrl);
$driversResult = json_decode($driversJson, true);
$drivers = ($driversResult && $driversResult['success']) ? $driversResult['data'] : [];
?>
<div class="modal fade" id="createTransferModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create New Transfer</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>From Station <span class="text-danger">*</span></label>
                            <div class="chosen-select-act fm-cmp-mg">
                                <select class="chosen" id="station_id" data-placeholder="Select Station...">
                                    <option value=""></option>
                                    <?php foreach ($stations as $station): ?>
                                    <option value="<?= $station['loc_id'] ?>"><?= htmlspecialchars($station['location_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>To Warehouse <span class="text-danger">*</span></label>
                            <div class="chosen-select-act fm-cmp-mg">
                                <select class="chosen" id="warehouse_id" data-placeholder="Select Warehouse...">
                                    <option value=""></option>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse['loc_id'] ?>"><?= htmlspecialchars($warehouse['location_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Category (Product) <span class="text-danger">*</span></label>
                            <div class="chosen-select-act fm-cmp-mg">
                                <select class="chosen" id="categories_id" data-placeholder="Select Category...">
                                    <option value=""></option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Driver <span class="text-danger">*</span></label>
                            <div class="chosen-select-act fm-cmp-mg">
                                <select class="chosen" id="driver_id" data-placeholder="Select Driver...">
                                    <option value=""></option>
                                    <?php foreach ($drivers as $driver): ?>
                                    <option value="<?= $driver['driver_id'] ?>"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name'] . ' (' . $driver['phone'] . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Supporting Document</label>
                            <input type="file" class="form-control" id="supporting_documents" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG</small>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Additional Info</label>
                            <textarea class="form-control" id="additional_info" rows="2" placeholder="Additional information..."></textarea>
                        </div>
                    </div>
                </div>
                
                <hr>
                <h5>Transfer Items <button type="button" class="btn btn-sm btn-primary" id="addItemBtn"><i class="fa fa-plus"></i> Add Item</button></h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th width="35%">Category Type <span class="text-danger">*</span></th>
                                <th width="25%">Unit <span class="text-danger">*</span></th>
                                <th width="25%">Amount <span class="text-danger">*</span></th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row" data-row="0">
                                <td>
                                    <select class="form-control category-type-select" data-row="0">
                                        <option value="">Select Type...</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control unit-select" data-row="0">
                                        <option value="">Select Unit...</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control amount-input" data-row="0" min="1" placeholder="Amount">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-item" data-row="0"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveTransferBtnFromAndTo" class="btn btn-primary">Create Transfer</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
