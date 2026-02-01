

<div class="modal animated bounce" id="myModalfour" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="sup_id" id="sup_id">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_full_name" id="edit_full_name" class="form-control" placeholder="Full Name">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-mail"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" class="form-control" name="edit_email" id="edit_email" placeholder="Email Address">
                                    </div>
                                </div>
                            </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-phone"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="edit_phone" id="edit_phone" class="form-control" placeholder="phone number">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-house"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_address" id="edit_address" class="form-control" placeholder="Home Address">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Type..." name="edit_type" id="edit_type">
											<option value="Supplier">Supplier</option>
											<option value="Farmer">Farmer</option>
									</select>
                                     </div>
                                </div>
                            </div>
                        </div>
                 </div>
             <div class="modal-footer">
                <button type="button" id="EditsupplierBtn" class="btn btn-default">Save changes</button>
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
                                    <h2>Manage Suppliers</h2>
                                    <p>
                                        Create, update, and manage system suppliers.
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
                            <h2>Suppliers Info</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped usersdata">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>email</th>
                                        <th>Phone</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="usersdata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/inventory/getsuppliers';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo $record['full_name']; ?></td>
                                                <td><?php echo $record['email']; ?></td>
                                                <td><?php echo $record['phone']; ?></td>
                                                <td><?php echo $record['type']; ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <button
                                                class="btn btn-default btn-icon-notika editrecord"
                                                title="Edit Supplier"
                                                data-id="<?= $record['sup_id'] ?>"
                                                data-full_name="<?= htmlspecialchars($record['full_name']) ?>"
                                                data-email="<?= htmlspecialchars($record['email']) ?>"
                                                data-phone="<?= htmlspecialchars($record['phone']) ?>"
                                                data-address="<?= htmlspecialchars($record['address']) ?>"
                                                data-type="<?= htmlspecialchars($record['type']) ?>">
                                                <i class="notika-icon notika-edit"></i>
                                              </button>
                                              <button class="btn btn-default btn-icon-notika deleterecord"  title="Delete Supplier"
                                                  data-id="<?= $record['sup_id'] ?>"
                                                  data-full_name="<?= htmlspecialchars($record['full_name']) ?>" ><i class="notika-icon text-danger notika-trash"></i></button>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="7" class="text-center">No roles found</td></tr>
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
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Full Name">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-mail"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" class="form-control" name="email" id="email" placeholder="Email Address">
                                    </div>
                                </div>
                            </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-phone"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="phone" id="phone" class="form-control" placeholder="phone number">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-house"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="address" id="address" class="form-control" placeholder="Home Address">
                                    </div>
                                </div>
                            </div>

                             <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Type..." name="type" id="type">
											<option value="Supplier">Supplier</option>
											<option value="Farmer">Farmer</option>
									</select>
                                     </div>
                                </div>
                            </div>
                        </div>
                    <div class="modal-footer">

                    <button type="button" id="saveSupplierBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>