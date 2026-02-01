<!-- Mixing History Modal -->
<div class="modal fade" id="mixingHistoryModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-history"></i> Mixing History</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="mixing-history-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Output Product</th>
                                <th>Output Qty</th>
                                <th>Input Items</th>
                                <th>Date</th>
                                <th>Created By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="mixingHistoryData">
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

<!-- View Mixing Details Modal -->
<div class="modal fade" id="viewMixingModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> Mixing Details - <span id="detail_ref_no"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row mg-b-15">
                    <div class="col-md-4">
                        <strong>Output Product:</strong> <span id="detail_output_product">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Output Quantity:</strong> <span id="detail_output_qty">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Date:</strong> <span id="detail_date">-</span>
                    </div>
                </div>
                <hr>
                <h5>Input Products Used:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Type</th>
                                <th>Unit</th>
                                <th>Quantity Used</th>
                            </tr>
                        </thead>
                        <tbody id="mixingDetailsBody">
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

<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-meanas"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Product Mixing</h2>
                                    <p>Combine multiple stock items into a new product</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#mixingHistoryModal" class="btn btn-info">
                                    <i class="fa fa-history"></i> Mixing History
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <!-- Available Stock for Mixing -->
            <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="fa fa-cubes"></i> Available Stock for Mixing</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="available-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllStock"></th>
                                    <th>Category Type / Unit</th>
                                    <th>Available Qty</th>
                                    <th>Mix Qty</th>
                                </tr>
                            </thead>
                            <tbody id="availableStockData">
                                <tr><td colspan="4" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mixing Configuration -->
            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="fa fa-flask"></i> Mixing Configuration</h2>
                    </div>
                    
                    <!-- Selected Items Summary -->
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h4 class="panel-title">Selected Items <span class="badge" id="selectedCount">0</span></h4>
                        </div>
                        <div class="panel-body" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-condensed" id="selectedItemsTable">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="selectedItemsBody">
                                    <tr id="noItemsRow"><td colspan="3" class="text-center text-muted">No items selected</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="info">
                                        <td><strong>Total Quantity:</strong></td>
                                        <td colspan="2"><strong id="totalMixQty">0</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Output Product Configuration -->
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h4 class="panel-title"><i class="fa fa-arrow-right"></i> Output Product</h4>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Category *</label>
                                <select id="output_category" class="form-control">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Product Type / Unit *</label>
                                <select id="output_type_unit" class="form-control" disabled>
                                    <option value="">Select Type / Unit</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Output Quantity *</label>
                                <input type="number" step="0.01" id="output_quantity" class="form-control" placeholder="Enter output quantity">
                                <small class="text-muted">Total input: <span id="inputTotalDisplay">0</span></small>
                            </div>
                            <div class="form-group">
                                <label>Mixing Date *</label>
                                <input type="date" id="mixing_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea id="mixing_notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="createMixingBtn" class="btn btn-success btn-block" disabled>
                        <i class="fa fa-flask"></i> Create Mixed Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
