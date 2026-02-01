<!-- Price Entry Modal -->
<div class="modal fade" id="priceEntryModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-money"></i> Enter Prices - <span id="modal_invoice_no"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row mg-b-15">
                    <div class="col-lg-4">
                        <strong>Client:</strong> <span id="modal_client_name">-</span>
                    </div>
                    <div class="col-lg-4">
                        <strong>Date:</strong> <span id="modal_sale_date">-</span>
                    </div>
                    <div class="col-lg-4">
                        <strong>Items:</strong> <span id="modal_total_items">-</span>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit Price (RWF)</th>
                                <th>Total (RWF)</th>
                            </tr>
                        </thead>
                        <tbody id="priceEntryItems">
                        </tbody>
                        <tfoot>
                            <tr class="info">
                                <td colspan="4" class="text-right"><strong>Grand Total:</strong></td>
                                <td><strong id="modal_grand_total">0 RWF</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Payment Mode *</label>
                            <select id="price_payment_mode" class="form-control">
                                <option value="">Select Payment Mode</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Account *</label>
                            <select id="price_payment_account" class="form-control" disabled>
                                <option value="">Select Account</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Amount Received (RWF) *</label>
                            <input type="number" step="0.01" id="price_amount_received" class="form-control" placeholder="Enter amount received">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" id="price_notes" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>
                </div>
                <input type="hidden" id="current_sale_id">
            </div>
            <div class="modal-footer">
                <button type="button" id="savePricesBtn" class="btn btn-success"><i class="fa fa-check"></i> Save Prices & Complete Sale</button>
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
                                    <i class="notika-icon notika-form"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Sales Price Approval</h2>
                                    <p>Add prices to pending sales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <span class="badge badge-warning" style="font-size: 14px; padding: 10px;">
                                    <i class="fa fa-clock-o"></i> Pending: <span id="pendingCount">0</span>
                                </span>
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
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="fa fa-list"></i> Sales Pending Price</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="pending-sales-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice No</th>
                                    <th>Client</th>
                                    <th>Items</th>
                                    <th>Date</th>
                                    <th>Sold By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="pendingSalesData">
                                <tr><td colspan="7" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
