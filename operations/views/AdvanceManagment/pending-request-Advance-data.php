
<!-- Add this CSS for tooltip -->
<style>
.approve-btn {
    margin-right: 5px;
    padding: 5px 10px;
    font-size: 12px;
}
.reject-btn {
    padding: 5px 10px;
    font-size: 12px;
}
.btn-group {
    display: flex;
}
.btn-group .btn {
    flex: 1;
}
</style>




 <div class="modal animated bounce" id="myModalseven" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectAdvanceId">
                <input type="hidden" id="user_id" value="<?php echo $_SESSION['user_id'];?>">
                <p>You are going to reject advance for: <strong id="rejectAdvanceName"></strong></p>
                <p>Amount : <strong id="rejectAdvanceAmount"></strong> <strong>RWF</strong> | Requested On: <strong id="rejectAdvanceDate"></strong> | By : <strong id="rejectAdvanceCreator"></strong></p>
                <div class="form-group">
                    <textarea id="rejectionReason" class="form-control" rows="4" 
                              placeholder="Please provide a reason for rejecting this advance request..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="confirmRejectAdvanceBtn">Save changes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

 
   

<div class="data-table-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <input type="hidden" id="user_approve_advance" value="<?php echo $_SESSION['user_id'];?>">
                    <div class="data-table-list">
                        <div class="basic-tb-hd">
                            <h2>Latest Requests</h2>
                        </div>
                        <div class="table-responsive">
                           <table id="data-table-basic" class="table table-striped usersdata">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Phone</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="usersdata">
                                <?php
                                $loc_id=$_SESSION['loc_id'];
                                $apiUrl = App::baseUrl() . '/_ikawa/inadvance/advancelistpending?loc_id='.$loc_id;
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $record) {
                                ?>
                                <tr>
                                    <td><?php echo $index + 1 ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($record['full_name']); ?></strong>
                                        <?php if (!empty($record['reason'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($record['reason']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['phone']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo htmlspecialchars($record['type']); ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-primary"><?php echo number_format($record['amount']); ?> RWF</strong>
                                        <?php if (!empty($record['payment_on'])): ?>
                                        <br><small>Due: <?php echo date('d/m/Y', strtotime($record['payment_on'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($record['created_at'])); ?></td>
                                    <td>
                                        <div class="button-icon-btn button-icon-btn-rd">
                                                <button type="button" class="btn btn-default btn-icon-notika approve-btn-requested-inadvance"
                                                    data-id="<?php echo $record['adv_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($record['full_name']); ?>"
                                                    data-type="<?php echo htmlspecialchars($record['type']); ?>"
                                                    data-amount="<?php echo $record['amount']; ?>"
                                                    title="Approve Advance">
                                                <i class="notika-icon notika-checked"></i>
                                            </button>

                                                <button type="button" 
                                                    class="btn btn-default btn-icon-notika reject-btn-requested-inadvance"
                                                    data-id="<?php echo $record['adv_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($record['full_name']); ?>"
                                                    data-amount="<?php echo htmlspecialchars($record['amount']); ?>"
                                                    data-type="<?php echo htmlspecialchars($record['type']); ?>"
                                                    data-created_at="<?php echo htmlspecialchars($record['created_at']); ?>"
                                                    data-created_by="<?php echo htmlspecialchars($record['first_name'].' '.$record['last_name']); ?>"
                                                    title="Reject Advance"> 
                                                    <i class="notika-icon notika-close"></i>
                                                </button>

                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">No pending advances found</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- create new model -->
    <div class="modal fade" id="myModalthree" role="dialog">
        <div class="modal-dialog modal-large">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="station_id" id="station_id" value="<?php echo $_SESSION['loc_id'];?>">
                    <input type="hidden" name="created_by" id="created_by" value="<?php echo $_SESSION['user_id'];?>">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Requesting..." name="request_type" id="request_type">
                                            <option></option>
											<option value="Supplier">Supplier</option>
											<option value="Farmer">Farmer</option>
									</select>
                                     </div>
                                </div>
                            </div>
                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-world"></i>
                                    </div>
                                        <div class="chosen-select-act fm-cmp-mg">
                                            <select class = 'chosen' data-placeholder = 'Choose Requestor...' name="destination_id" id="destination_id">
                                          <option>Select Requestor</option>
                                     
                                </select>
                                </div>
                            </div>
                        </div>
                         </div>

                            <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-dollar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="amount" id="amount" class="form-control" placeholder="In advance Amount">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-tax"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="n_days" id="n_days" class="form-control" placeholder="Expected payment days">
                                    </div>
                                </div>
                            </div>
                          
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                         <textarea class="form-control" rows="5" placeholder="Type a reason for providing this advance...."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="modal-footer">
                    <button type="button" id="saveinadvancePaymentBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>