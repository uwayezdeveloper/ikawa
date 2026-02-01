<!-- Edit Consumer Modal -->
<div class="modal animated bounce" id="editConsumerModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Expense Consumer</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="edit_cons_id" id="edit_cons_id">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_cons_name" id="edit_cons_name" class="form-control" placeholder="Consumer Name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-phone"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_phone" id="edit_phone" class="form-control" placeholder="Phone Number">
                                    </div>
                                </div>
                            </div>
                    </div>
                 </div>
             <div class="modal-footer">
                <button type="button" id="updateConsumerBtn" class="btn btn-default">Save changes</button>
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
                                    <i class="notika-icon notika-support"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Expense Consumers</h2>
                                    <p>
                                        Create, update, and manage expense consumers/payers.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#createConsumerModal" data-placement="left" title="Create New Consumer" class="btn"><i class="fa fa-plus"></i></button>
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
                            <h2>Expense Consumers</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Consumer Name</th>
                                        <th>Phone</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="consumersdata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/expense-consumers/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            // Check if consumer is in use
                                            $checkUrl = App::baseUrl() . '/_ikawa/expense-consumers/check-in-use/' . $record['cons_id'];
                                            $checkJson = fetchApiData($checkUrl);
                                            $checkResult = json_decode($checkJson, true);
                                            $inUse = $checkResult && $checkResult['success'] && $checkResult['data']['in_use'];
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo htmlspecialchars($record['cons_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['phone']); ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <button
                                                 class="btn btn-default btn-icon-notika editConsumerBtn"
                                                title="Edit Consumer"
                                                data-id="<?= $record['cons_id'] ?>"
                                                data-cons_name="<?= htmlspecialchars($record['cons_name']) ?>"
                                                data-phone="<?= htmlspecialchars($record['phone']) ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>
                                            <?php if (!$inUse): ?>
                                            <button
                                                 class="btn btn-danger btn-icon-notika deleteConsumerBtn"
                                                title="Delete Consumer"
                                                data-id="<?= $record['cons_id'] ?>"
                                                data-cons_name="<?= htmlspecialchars($record['cons_name']) ?>">
                                                <i class="notika-icon notika-close"></i>
                                            </button>
                                            <?php endif; ?>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="4" class="text-center">No expense consumers found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- Create new consumer modal -->
    <div class="modal fade" id="createConsumerModal" role="dialog">
        <div class="modal-dialog modal-large">
            <div class="modal-content">
                <div class="modal-body">
                <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="cons_name" id="cons_name" class="form-control" placeholder="Consumer Name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-phone"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Phone Number">
                                    </div>
                                </div>
                            </div>
                    </div>
                 </div>
                 <div class="modal-footer">
                    <button type="button" id="saveConsumerBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
