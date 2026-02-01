<div class="modal animated bounce" id="myModalfour" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Sallize</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="sallize_id" id="sallize_id">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group ic-cmp-int">
                        <div class="form-ic-cmp">
                            <i class="notika-icon notika-support"></i>
                        </div>
                        <div class="nk-int-st">
                            <input type="text" name="edit_sallize_name" id="edit_sallize_name" class="form-control" placeholder="Sallize Name" required>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group ic-cmp-int">
                        <div class="form-ic-cmp">
                            <i class="notika-icon notika-edit"></i>
                        </div>
                        <div class="nk-int-st">
                            <textarea name="edit_description" id="edit_description" class="form-control" rows="4" placeholder="Description"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <select class="chosen" data-placeholder="Choose Status..." name="edit_status" id="edit_status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="EditSallizeBtn" class="btn btn-default">Save changes</button>
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
                                    <h2>Manage Sallize</h2>
                                    <p>
                                        Create, update, and manage sallize items with their descriptions and status.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#myModalthree" data-placement="left" title="Create New Sallize" class="btn"><i class="fa fa-plus"></i></button>
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
                        <h2>Sallize List</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="data-table-basic" class="table table-striped sallizedata">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sallize Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="sallizedata">
                              <?php
                                $apiUrl = App::baseUrl() . '/_ikawa/sallize/get-all-sallize';
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $sallize) {
                                        $statusClass = $sallize['status'] === 'active' ? 'text-success' : ($sallize['status'] === 'inactive' ? 'text-danger' : 'text-warning');
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1 ?></td>
                                            <td><?php echo htmlspecialchars($sallize['sallize_name']); ?></td>
                                            <td><?php echo htmlspecialchars($sallize['description']); ?></td>
                                            <td><span class="<?php echo $statusClass; ?>"><?php echo ucfirst($sallize['status']); ?></span></td>
                                            <td>
                                            <div class="button-icon-btn button-icon-btn-rd">
                                            <button
                                             class="btn btn-default btn-icon-notika editsallize"
                                            title="Edit Sallize"
                                            data-id="<?= $sallize['sallize_id'] ?>"
                                            data-sallize_name="<?= htmlspecialchars($sallize['sallize_name']) ?>"
                                            data-description="<?= htmlspecialchars($sallize['description']) ?>"
                                            data-status="<?= $sallize['status'] ?>">
                                            <i class="notika-icon notika-edit"></i>
                                        </button>

                                            <button class="btn btn-default btn-icon-notika deletesallize"  title="Delete Sallize"
                                              data-id="<?= $sallize['sallize_id'] ?>"
                                              data-sallize_name="<?= htmlspecialchars($sallize['sallize_name']) ?>" ><i class="notika-icon text-danger notika-trash"></i></button>

                                        </div>
                                        </td>
                                       </tr>
                                   <?php  }
                                } else {
                                    ?>
                                      <tr><td colspan="5" class="text-center">No sallize found</td></tr>
                                <?php }  ?>
                            </tbody>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create New Sallize Modal -->
<div class="modal fade" id="myModalthree" role="dialog">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create New Sallize</h4>
            </div>
            <div class="modal-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group ic-cmp-int">
                        <div class="form-ic-cmp">
                            <i class="notika-icon notika-support"></i>
                        </div>
                        <div class="nk-int-st">
                            <input type="text" name="sallize_name" id="sallize_name" class="form-control" placeholder="Sallize Name" required>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group ic-cmp-int">
                        <div class="form-ic-cmp">
                            <i class="notika-icon notika-edit"></i>
                        </div>
                        <div class="nk-int-st">
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Description"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <select class="chosen" data-placeholder="Choose Status..." name="status" id="status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveSallizeBtn" class="btn btn-default">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

