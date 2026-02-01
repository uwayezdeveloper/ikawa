
<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-support"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Accounts Transfers</h2>
                                    <p>
                                        Manage accounts balance.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#myModalthree" data-placement="left" title="Create New Role" class="btn"><i class="fa fa-plus"></i></button>
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
                            <h2>Accounts Balance</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped usersdata">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account Name</th>
                                        <th>Reference</th>
                                        <th>Station</th>
                                        <th>Balance (Rwf)</th>
                                    </tr>
                                </thead>
                                <tbody id="usersdata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/accounts/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo $record['acc_name']; ?></td>
                                                <td><?php echo $record['acc_reference_num']; ?></td>
                                                <td><?php echo $record['location_name']; ?></td>
                                                <td><?php echo number_format($record['balance']); ?></td>
                                                
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="7" class="text-center">No client found</td></tr>
                                    <?php }  ?>
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
                    <input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION['user_id'] ?>">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-world"></i>
                            </div>
                                <div class="chosen-select-act fm-cmp-mg">
                                <select class='chosen' data-placeholder='Choose credit account...' name="debit_account_id" id="debit_account_id">
                                    <option>The account for which money will be deducted.</option>
                                    <?php
                                    $dataUrl = App::baseUrl() . '/_ikawa/accounts/get-all';
                                    $response = fetchApiData($dataUrl);

                                    $debitaccounts = [];

                                    if ($response !== false) {
                                        $decoded = json_decode($response, true);

                                        if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                            $debitaccounts = $decoded['data'] ?? [];
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($debitaccounts)): ?>
                                        <?php foreach ($debitaccounts as $debitaccount): ?>
                                            <option value="<?= htmlspecialchars($debitaccount['acc_id']) ?>" 
                                                    data-balance="<?= htmlspecialchars($debitaccount['balance']) ?>">
                                                <?= htmlspecialchars($debitaccount['acc_name'] . ' (Acc: ' . $debitaccount['acc_reference_num'] . ') ' . '(Bal: ' . number_format($debitaccount['balance']) . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No Accounts found</option>
                                    <?php endif; ?>
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
                                <select class='chosen' data-placeholder='Choose credit account...' name="credit_account_id" id="credit_account_id">
                                    <option>The account to which money will be transferred.</option>
                                    <?php
                                    $dataUrl = App::baseUrl() . '/_ikawa/accounts/get-all';
                                    $response = fetchApiData($dataUrl);

                                    $creditsaccounts = [];

                                    if ($response !== false) {
                                        $decoded = json_decode($response, true);

                                        if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                           $creditsaccounts  = $decoded['data'] ?? [];
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($creditsaccounts )): ?>
                                        <?php foreach ($creditsaccounts  as $creditaccount): ?>
                                            <option value="<?= htmlspecialchars($creditaccount['acc_id']) ?>" 
                                                    data-balance="<?= htmlspecialchars($creditaccount['balance']) ?>">
                                                <?= htmlspecialchars($creditaccount['acc_name'] . ' (Acc: ' . $creditaccount['acc_reference_num'] . ') ' . '(Bal: ' . number_format($creditaccount['balance']) . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No Accounts found</option>
                                    <?php endif; ?>
                                </select>
                                </div>
                            </div>
                        </div>
                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-dollar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" min="500" class="form-control" name="amount_to_transfer" id="amount_to_transfer" placeholder="Amount to Transfer min (500 frw)">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-dollar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" class="form-control" name="trans_charges" id="trans_charges" placeholder="Transfer Charges (if any)">
                                    </div>
                                </div>
                            </div>

                            </div>

                    <div class="modal-footer">

                    <button type="button" id="saveAccountTransBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>