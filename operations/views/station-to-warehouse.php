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
                        <p><strong>From Warehouse/Station:</strong> <span id="view_station_name"></span></p>
                        <p><strong>To Warehouse/Station:</strong> <span id="view_warehouse_name"></span></p>
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
                            <tr style="background: #f5f5f5;">
                                <th>#</th>
                                <th>Type Name</th>
                                <th>Unit</th>
                                <th>Sent Amount</th>
                                <th>Received Amount</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                                <th>Item Status</th>
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
    <div class="modal-dialog modals-default" style="width: 85%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-download"></i> Receive Transfer Items</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="receive_transfer_id">
                
                <!-- Transfer Info Summary -->
                <div class="row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 5px;">
                    <div class="col-md-4">
                        <strong>From:</strong> <span id="receive_from_station"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Category:</strong> <span id="receive_category"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Driver:</strong> <span id="receive_driver"></span>
                    </div>
                </div>
                
                <!-- Items Table -->
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Select items to receive and verify the received amounts. You can adjust the amount if different from sent quantity.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="receiveItemsTable">
                        <thead>
                            <tr style="background: #5c6bc0; color: white;">
                                <th width="5%">
                                    <input type="checkbox" id="selectAllItems" title="Select All">
                                </th>
                                <th width="20%">Type Name</th>
                                <th width="12%">Unit</th>
                                <th width="15%">Sent Amount</th>
                                <th width="18%">Received Amount</th>
                                <th width="15%">Unit Price</th>
                                <th width="15%">Status</th>
                            </tr>
                        </thead>
                        <tbody id="receiveItemsBody">
                            <!-- Items loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Receive Confirmation Document</label>
                            <input type="file" class="form-control" id="receive_note" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Upload document that confirms product is received (PDF, DOC, DOCX, JPG, PNG)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Receive Comment</label>
                            <textarea class="form-control" id="receive_comment" rows="3" placeholder="Enter additional comments about receiving this transfer..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmReceiveBtn" class="btn btn-success"><i class="fa fa-check"></i> Receive Selected Items</button>
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
                                    <h2>Transfer products</h2>
                                    <p>Transfer products between stations and warehouses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#createTransferModal" title="Create New Transfer" class="btn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
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
                                    <th>From</th>
                                    <th>To</th>
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
                                // Pass station_id as parameter since file_get_contents doesn't share browser session
                                $userLocId = $_SESSION['loc_id'] ?? '';
                                $apiUrl = App::baseUrl() . '/_ikawa/transfer/get-all?station_id=' . urlencode($userLocId);
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $record) {
                                        $statusClass = 'text-warning';
                                        if ($record['status'] === 'Received') $statusClass = 'text-success';
                                        elseif ($record['status'] === 'Partial') $statusClass = 'text-info';
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
                                                    
                                                
                                                <?php if (($record['status'] === 'Pending' || $record['status'] === 'Partial') && $record['warehouse_id'] == $userLocId) { ?>
                                                    <button class="btn btn-success btn-icon-notika receivetransfer" 
                                                        title="Receive Transfer"
                                                        data-id="<?= $record['id'] ?>">
                                                        <i class="fa fa-check"></i>
                                                    </button>
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
$locationsJson = fetchApiData($locationsUrl);
$locationsResult = json_decode($locationsJson, true);
$allLocations = ($locationsResult && $locationsResult['success']) ? $locationsResult['data'] : [];

// Get user's station name from session loc_id
$userStationName = 'N/A';
$userLocId = $_SESSION['loc_id'] ?? null;
if ($userLocId && !empty($allLocations)) {
    foreach ($allLocations as $loc) {
        if ($loc['loc_id'] == $userLocId) {
            $userStationName = $loc['location_name'];
            break;
        }
    }
}

// Filter destinations: all locations EXCEPT user's own station
$destinations = array_filter($allLocations, function($loc) use ($userLocId) {
    // Exclude user's own station
    return $loc['loc_id'] != $userLocId;
});

// Separate stations and warehouses for optgroup display
$otherStations = array_filter($destinations, function($loc) {
    return isset($loc['type']) && $loc['type'] === 'Station';
});
$warehouses = array_filter($destinations, function($loc) {
    return isset($loc['type']) && $loc['type'] === 'Warehouse';
});

$categoriesUrl = App::baseUrl() . '/_ikawa/categories/get-all-categories';
$categoriesJson = fetchApiData($categoriesUrl);
$categoriesResult = json_decode($categoriesJson, true);
$allCategories = ($categoriesResult && $categoriesResult['success']) ? $categoriesResult['data'] : [];

// Filter only active categories
$categories = array_filter($allCategories, function($cat) {
    return isset($cat['status']) && $cat['status'] === 'active';
});

// Load drivers
$driversUrl = App::baseUrl() . '/_ikawa/drivers/get-all';
$driversJson = fetchApiData($driversUrl);
$driversResult = json_decode($driversJson, true);
$drivers = ($driversResult && $driversResult['success']) ? $driversResult['data'] : [];
?>

<div class="modal animated" id="createTransferModal" role="dialog">
    <div class="modal-dialog modals-default" style="width: 95%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>From Station/Warehouse</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($userStationName) ?>" readonly style="background-color: #f5f5f5; font-weight: bold;">
                            <small class="text-muted">Your assigned station</small>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <!-- <label>To Destination <span class="text-danger">*</span></label> -->
                            <div class="chosen-select-act fm-cmp-mg">
                                <select class="chosen" id="warehouse_id" data-placeholder="Select Destination (Station/Warehouse)...">
                                    <option value=""></option>
                                    <?php if (!empty($otherStations)): ?>
                                    <optgroup label="Stations">
                                        <?php foreach ($otherStations as $station): ?>
                                        <option value="<?= $station['loc_id'] ?>"><?= htmlspecialchars($station['location_name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endif; ?>
                                    <?php if (!empty($warehouses)): ?>
                                    <optgroup label="Warehouses">
                                        <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['loc_id'] ?>"><?= htmlspecialchars($warehouse['location_name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <!-- <label>Category (Product) <span class="text-danger">*</span></label> -->
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
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <!-- <label>Driver <span class="text-danger">*</span></label> -->
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
                            <!-- <label>Supporting Document</label> -->
                            <input type="file" class="form-control" id="supporting_documents" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG</small>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <!-- <label>Additional Info</label> -->
                            <textarea class="form-control" id="additional_info" rows="2" placeholder="Additional information..."></textarea>
                        </div>
                    </div>
                </div>
                
                <hr>
                <h5> <button type="button" class="btn btn-sm btn-primary" id="addItemBtn"><i class="fa fa-plus"></i> Add Item</button></h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <!-- <thead>
                            <tr>
                                <th width="35%">Category Type <span class="text-danger">*</span></th>
                                <th width="25%">Unit <span class="text-danger">*</span></th>
                                <th width="25%">Amount <span class="text-danger">*</span></th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead> -->
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
                                    <input type="number" class="form-control unit-price-input" data-row="0" min="0" step="0.01" placeholder="Unit Price">
                                </td>
                                <td>
                                    <input type="number" class="form-control total-price-input" data-row="0" readonly placeholder="Total Price" style="background-color: #f5f5f5;">
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
