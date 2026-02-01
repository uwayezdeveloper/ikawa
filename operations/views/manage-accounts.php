<!-- Edit Account Modal -->
<div class="modal animated bounce" id="editAccountModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Account</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="edit_acc_id" id="edit_acc_id">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_acc_name" id="edit_acc_name" class="form-control" placeholder="Account Name Ex. MTN Mobile Money">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_acc_reference_num" id="edit_acc_reference_num" class="form-control" placeholder="Reference Number">
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <select class='chosen' data-placeholder='Choose Payment Mode...' name="edit_mode_id" id="edit_mode_id">
                                 <option value="">Select Payment Mode</option>
                                <?php
                                $paymentModesUrl = App::baseUrl() . '/_ikawa/settings/paymentmodes';
                                $response = fetchApiData($paymentModesUrl);

                                $paymentModes = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $paymentModes = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($paymentModes)): ?>
                                        <?php foreach ($paymentModes as $mode): ?>
                                            <option value="<?= htmlspecialchars($mode['Mode_id']) ?>">
                                                <?= htmlspecialchars($mode['Mode_names']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No payment modes found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <select class='chosen' data-placeholder='Choose Location...' name="edit_st_id" id="edit_st_id">
                                 <option value="">Select Location</option>
                                <?php
                                $locationsUrl = App::baseUrl() . '/_ikawa/settings/location';
                                $response = fetchApiData($locationsUrl);

                                $locations = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $locations = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($locations)): ?>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?= htmlspecialchars($location['loc_id']) ?>">
                                                <?= htmlspecialchars($location['location_name']) ?><?= !empty($location['description']) ? ' - ' . htmlspecialchars($location['description']) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No locations found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>
                    </div>
                 </div>
             <div class="modal-footer">
                <button type="button" id="updateAccountBtn" class="btn btn-default">Save changes</button>
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
                                    <i class="notika-icon notika-windows"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Manage Payment Accounts</h2>
                                    <p>
                                        Create, update, and manage payment accounts.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#createAccountModal" data-placement="left" title="Create New Account" class="btn"><i class="fa fa-plus"></i></button>
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
                            <h2>Payment Accounts</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account Name</th>
                                        <th>Reference Number</th>
                                        <th>Payment Mode</th>
                                        <th>Location</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="accountsdata">
                                  <?php
                                    // Use location-filtered endpoint
                                    if (session_status() === PHP_SESSION_NONE) {
                                        session_start();
                                    }
                                    $userLocationId = isset($_SESSION['loc_id']) ? $_SESSION['loc_id'] : '';
                                    
                                    if ($userLocationId) {
                                        $apiUrl = App::baseUrl() . '/_ikawa/accounts/get-allbylocation?st_id=' . $userLocationId;
                                        $json = fetchApiData($apiUrl);
                                        $result = json_decode($json, true);
                                    } else {
                                        $result = ['success' => false, 'data' => []];
                                    }
                                    
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            $statusClass = $record['status'] == 0 ? 'style="background-color: #fff3cd;"' : '';
                                            $statusText = $record['status'] == 1 ? 'Active' : 'Onhold';
                                            $statusBadge = $record['status'] == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-warning">Onhold</span>';
                                            $canDelete = $record['status'] == 1;
                                            ?>
                                            <tr <?= $statusClass ?>>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo htmlspecialchars($record['acc_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['acc_reference_num']); ?></td>
                                                <td><?php echo htmlspecialchars($record['Mode_names']); ?></td>
                                                <td><?php echo htmlspecialchars($record['location_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo number_format($record['balance']); ?> RWF</td>
                                                <td><?= $statusBadge ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <?php if ($record['status'] == 1): ?>
                                                <button
                                                 class="btn btn-default btn-icon-notika editAccountBtn"
                                                title="Edit Account"
                                                data-id="<?= $record['acc_id'] ?>"
                                                data-acc_name="<?= htmlspecialchars($record['acc_name']) ?>"
                                                data-acc_reference_num="<?= htmlspecialchars($record['acc_reference_num']) ?>"
                                                data-mode_id="<?= htmlspecialchars($record['mode_id']) ?>"
                                                data-st_id="<?= htmlspecialchars($record['st_id']) ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                            <button
                                                 class="btn btn-danger btn-icon-notika deleteAccountBtn"
                                                title="Set to Onhold"
                                                data-id="<?= $record['acc_id'] ?>"
                                                data-balance="<?= $record['balance'] ?>"
                                                data-acc_name="<?= htmlspecialchars($record['acc_name']) ?>">
                                                <i class="notika-icon notika-close"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($record['status'] == 0): ?>
                                            <button
                                                 class="btn btn-success btn-icon-notika activateAccountBtn"
                                                title="Activate Account"
                                                data-id="<?= $record['acc_id'] ?>"
                                                data-acc_name="<?= htmlspecialchars($record['acc_name']) ?>">
                                                <i class="notika-icon notika-checked"></i>
                                            </button>
                                            <?php endif; ?>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="8" class="text-center">No accounts found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- Create new account modal -->
    <div class="modal fade" id="createAccountModal" role="dialog">
        <div class="modal-dialog modal-large">
            <div class="modal-content">
              
                <div class="modal-body">
                <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="acc_name" id="acc_name" class="form-control" placeholder="Account Name Ex. MTN Mobile Money">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="acc_reference_num" id="acc_reference_num" class="form-control" placeholder="Reference Number">
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="row">
                             <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <select class='chosen' data-placeholder='Choose Payment Mode...' name="mode_id" id="mode_id">
                                 <option value="">Select Payment Mode</option>
                                <?php
                                $paymentModesUrl = App::baseUrl() . '/_ikawa/settings/paymentmodes';
                                $response = fetchApiData($paymentModesUrl);

                                $paymentModes = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $paymentModes = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($paymentModes)): ?>
                                        <?php foreach ($paymentModes as $mode): ?>
                                            <option value="<?= htmlspecialchars($mode['Mode_id']) ?>">
                                                <?= htmlspecialchars($mode['Mode_names']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No payment modes found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>
                    </div>
                 </div>
                 <div class="modal-footer">
                    <button type="button" id="saveAccountBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
