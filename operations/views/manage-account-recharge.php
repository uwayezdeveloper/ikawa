<?php
require_once __DIR__ . '/../../_ikawa/config/App.php';

// preload accounts server-side for the modal select
$accounts = [];
$loc_id = $_SESSION['loc_id'] ?? null;
$accountsUrl = $loc_id ? App::baseUrl() . '/_ikawa/accounts/get-allbylocation?st_id=' . $loc_id : App::baseUrl() . '/_ikawa/accounts/get-all';
$resp = fetchApiData($accountsUrl);
if ($resp) {
    $dec = json_decode($resp, true);
    if ($dec && !empty($dec['data'])) $accounts = $dec['data'];
}
?>

<!-- Recharge Modal -->
<div class="modal animated bounce" id="rechargeAccountModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <!-- <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Recharge Account</h4>
            </div> -->
            <div class="modal-body">
                <form id="rechargeForm" method="post" action="<?= App::baseUrl() ?>/_ikawa/investments/create">
                    <input type="hidden" name="action" value="recharge" />
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Select Account</label>
                                <select name="account_id" id="account_id" class="form-control" required>
                                    <option value="">-- Select account --</option>
                                    <?php foreach ($accounts as $a): ?>
                                        <option value="<?= htmlspecialchars($a['acc_id']) ?>"><?= htmlspecialchars($a['acc_name']) ?> - Balance: <?= number_format($a['balance']) ?> RWF</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" name="in_amount" id="in_amount" class="form-control" min="1" required />
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Source of Money</label>
                                <input type="text" name="source" id="source" class="form-control" placeholder="e.g. Bank Transfer, Cash, etc." />
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Receipt</label>
                                <input type="text" name="reciept" id="reciept" class="form-control" />
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="submitRechargeBtn" class="btn btn-success">Recharge</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Page header -->
<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-dollar"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Account Recharge</h2>
                                    <p>Recharge payment accounts and view history</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#rechargeAccountModal" class="btn btn-success"><i class="fa fa-plus"></i> New Recharge</button>
                                <button type="button" id="reloadRechargeBtn" class="btn btn-default" style="margin-left:8px;"><i class="fa fa-refresh"></i> Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd"><h2>Recharge History</h2></div>
                    <div id="rechargeAlert" style="margin:10px 15px; display:none;"></div>
                    <div class="table-responsive">
                        <table id="rechargeTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Source</th>
                                    <th>Description</th>
                                    <th>Receipt</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Done By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="rechargeTableBody"><tr><td colspan="10" class="text-center">Loading...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
