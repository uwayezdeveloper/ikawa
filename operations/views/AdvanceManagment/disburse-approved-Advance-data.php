
<!-- Add this CSS for tooltip -->
<style>
.accordion .card {
    border: 1px solid rgba(0,0,0,.125);
}
.accordion .card.border-success {
    border: 2px solid #28a745 !important;
    background-color: #f8fff9;
}
.accordion .btn-link {
  
    text-decoration: none;
}
.accordion .btn-link:hover {
    text-decoration: none;
}

.accordion .btn-link:focus,
.accordion .btn-link:active {
    outline: none !important;
    box-shadow: none !important;
    border-color: transparent !important;
}
</style>




<div class="modal animated bounce" id="myModalseven" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="disburseAdvanceId">
                <input type="hidden" id="user_id" value="<?php echo $_SESSION['user_id'];?>">
                <input type="hidden" id="disburse_station_id" value="<?php echo $_SESSION['loc_id'];?>">
                
                <input type="hidden" id="selected_account_id">
                
                <div class="mb-3">
                    <p>You are going to disburse advance for: <strong id="disburseAdvanceName"></strong></p>
                    <p>Amount: <strong id="disburseAdvanceAmount"></strong> RWF | Approved On: <strong id="disburseAdvanceDate"></strong> | By: <strong id="approvedAdvanceCreator"></strong></p>
                </div>
                <?php
                $st_id = $_SESSION['loc_id'] ?? 0;
                $paymentsUrl = App::baseUrl() . '/_ikawa/accounts/get-allbylocation?st_id=' . $st_id;
                $json = fetchApiData($paymentsUrl);
                $result = json_decode($json, true);

                if ($result['success'] && !empty($result['data'])) {
                    $groupedAccounts = [];
                    foreach ($result['data'] as $account) {
                        $mode = $account['Mode_names'];
                        if (!isset($groupedAccounts[$mode])) {
                            $groupedAccounts[$mode] = [];
                        }
                        $groupedAccounts[$mode][] = $account;
                    }

                ?>
                <?php
            $modeCount = count($groupedAccounts);
            $colClass = ($modeCount < 2) ? 'col-12' : 'col-lg-6 col-md-6 col-sm-12';
            ?>
<div class="row">
    <?php foreach ($groupedAccounts as $mode => $accounts): ?>
    <div class="<?= $colClass ?> mb-3">
        <div class="payment-mode-card">
            <div class="card">
                <div class="card-header text-center text-white">
                    <h5 class="mb-0 text-center"><?= htmlspecialchars($mode) ?></h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordion-<?= str_replace(' ', '-', $mode) ?>">
                        <?php foreach ($accounts as $index => $account): 
                            $accordionId = "account-" . $account['acc_id'];
                        ?>
                        <div class="card account-card" data-account-id="<?= $account['acc_id'] ?>">
                            <div class="card-header p-0" id="heading-<?= $accordionId ?>">
                                <button class="btn btn-link text-left p-3"
                                        type="button" 
                                        data-toggle="collapse" 
                                        data-target="#collapse-<?= $accordionId ?>" 
                                        aria-expanded="false" 
                                        aria-controls="collapse-<?= $accordionId ?>">
                                    <strong><?= htmlspecialchars($account['acc_name']) ?></strong><br>
                                    <small class="text-muted">
                                        Ref: <?= htmlspecialchars($account['acc_reference_num']) ?> | 
                                        Balance: <?= number_format($account['balance']) ?> RWF
                                    </small>
                                </button>
                            </div>
                            <div id="collapse-<?= $accordionId ?>" 
                                 class="collapse" 
                                 aria-labelledby="heading-<?= $accordionId ?>" 
                                 data-parent="#accordion-<?= str_replace(' ', '-', $mode) ?>">
                                <div class="card-body">
                                    <div class="form-group">
                                        <input type="number"
                                               class="form-control disburse-amount-input" 
                                               placeholder="Amount" 
                                               max="<?= $account['balance'] ?>"
                                               data-account-id="<?= $account['acc_id'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <input type="number" 
                                               class="form-control disburse-charge-input" 
                                               placeholder="Charge"
                                               data-account-id="<?= $account['acc_id'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<div class="row">
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="form-group ic-cmp-int">
        <div class="nk-int-st" style="width: 240px;">
            <input type="text" name="receiptAccount" id="receiptAccount" class="form-control" placeholder="Receiving Account">
        </div>
    </div>
</div>
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="form-group ic-cmp-int">
        <div class="nk-int-st" style="width: 240px;">
               <input type="text" name="additiondisbursecomment" id="additiondisbursecomment" class="form-control" placeholder="Additional info (if any)">
        </div>
    </div>
</div>
</div>
<div class="row">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <span class="TotalEnteredDisburse"></span>
</div>
</div>

                <?php 
                } else {
                    echo '<div class="alert alert-warning">No accounts available</div>';
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="confirmDisburseAdvanceBtn">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
                                    <th>Approved On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="usersdata">
                                <?php
                                $loc_id=$_SESSION['loc_id'];
                                $apiUrl = App::baseUrl() . '/_ikawa/inadvance/advancelistapproved?loc_id='.$loc_id;
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
                                    <td><?php echo date('d/m/Y', strtotime($record['approved_on'])); ?></td>
                                    <td>
                                        <div class="button-icon-btn button-icon-btn-rd">
                                                <button type="button" class="btn btn-default btn-icon-notika disburse-btn-approved-inadvance"
                                                    data-id="<?php echo $record['adv_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($record['full_name']); ?>"
                                                    data-approved_by="<?php echo htmlspecialchars($record['first_name'].' '.$record['last_name']); ?>"
                                                    data-type="<?php echo htmlspecialchars($record['type']); ?>"
                                                    data-amount="<?php echo $record['amount']; ?>"
                                                    data-approved_on="<?php echo htmlspecialchars($record['approved_on']); ?>"
                                                    title="Disburse Advance">
                                                <i class="notika-icon notika-next"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else { 
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">No pending advances found</td>
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