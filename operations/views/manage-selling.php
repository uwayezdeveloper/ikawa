<!-- Add Client Modal -->
<style>
    #addClientModal { z-index: 1060 !important; }
    #addClientModal + .modal-backdrop { z-index: 1055 !important; }
</style>
<div class="modal fade" id="addClientModal" role="dialog" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Add New Client</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Client Name *</label>
                    <input type="text" id="new_client_name" class="form-control" placeholder="Enter client name">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="new_client_phone" class="form-control" placeholder="Enter phone number">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="new_client_email" class="form-control" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea id="new_client_address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveClientBtn" class="btn btn-primary"><i class="fa fa-save"></i> Save Client</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-credit-card"></i> Record Payment</h4>
            </div>
            <div class="modal-body">
                <!-- Price Option -->
                <div class="row mg-b-15">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label><strong>Does this sale have a selling price?</strong></label>
                            <div class="radio-inline">
                                <label><input type="radio" name="has_price" value="yes" checked> Yes, I will enter prices</label>
                            </div>
                            <div class="radio-inline">
                                <label><input type="radio" name="has_price" value="no"> No, record as pending price</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Entry Section (shown when has_price = yes) -->
                <div id="priceEntrySection">
                    <div class="row mg-b-15">
                        <div class="col-lg-12">
                            <label><strong>Enter Unit Prices for Items:</strong></label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Unit Price (RWF)</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="priceEntryBody">
                                    </tbody>
                                    <tfoot>
                                        <tr class="info">
                                            <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                                            <td><strong id="calculatedTotal">0 RWF</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary (shown when has_price = no) -->
                <div id="pendingPriceSection" style="display:none;">
                    <div class="alert alert-warning">
                        <i class="fa fa-clock-o"></i> This sale will be recorded as <strong>Pending Price</strong>. You can update the prices later.
                    </div>
                    <div class="row mg-b-15">
                        <div class="col-lg-12">
                            <label><strong>Items Summary:</strong></label>
                            <div id="pendingItemsSummary"></div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Client Selection -->
                <div class="row mg-b-15">
                    <div class="col-lg-10 col-md-10 col-sm-9 col-xs-8">
                        <div class="form-group">
                            <label>Select Client</label>
                            <select id="payment_client" class="form-control chosen">
                                <option value="">-- Select Client (Optional) --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-3 col-xs-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#addClientModal">
                                <i class="fa fa-plus"></i> New
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Payment Details (shown only when has_price = yes) -->
                <div id="paymentDetailsSection">
                    <div class="row mg-b-15">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Payment Mode *</label>
                                <select id="payment_mode" class="form-control">
                                    <option value="">Select Payment Mode</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Account *</label>
                                <select id="payment_account" class="form-control" disabled>
                                    <option value="">Select Account</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mg-b-15">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Amount Received (RWF) *</label>
                                <input type="number" step="0.01" id="payment_amount_received" class="form-control" placeholder="Enter amount received">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Supporting Document (Optional)</label>
                                <input type="file" id="payment_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Upload receipt/invoice file (PDF, Image, or Document)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea id="payment_notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmPaymentBtn" class="btn btn-success"><i class="fa fa-check"></i> Confirm Sale</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Sales History Modal -->
<div class="modal fade" id="salesHistoryModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-history"></i> Sales History</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="sales-history-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Client</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Payment Mode</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="salesHistoryData">
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
                                    <i class="notika-icon notika-cart"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Selling Management</h2>
                                    <p>Sell stock items and record payments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#salesHistoryModal" class="btn btn-info"><i class="fa fa-history"></i> Sales History</button>
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
            <!-- Available Stock -->
            <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="fa fa-cubes"></i> Available Stock</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="available-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Type / Unit</th>
                                    <th>Available Qty</th>
                                    <th>Sell Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="availableStockData">
                                <tr><td colspan="5" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="fa fa-shopping-cart"></i> Cart <span id="cartBadge" class="badge badge-primary">0</span></h2>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <tr id="emptyCartRow"><td colspan="3" class="text-center text-muted">Cart is empty</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row mg-t-15">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <button type="button" id="clearCartBtn" class="btn btn-danger btn-block" disabled>
                                <i class="fa fa-trash"></i> Clear Cart
                            </button>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <button type="button" id="checkoutBtn" class="btn btn-success btn-block" disabled>
                                <i class="fa fa-check"></i> Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
