
<div class="modal animated bounce" id="myModalfour" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="loc_id" id="loc_id">
                           <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_location_name" id="edit_location_name" class="form-control" placeholder="Station Name">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_description" id="edit_description" class="form-control" placeholder="Description">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Type..." name="edit_type" id="edit_type">
                                        <option value="HQ">HQ</option>
                                        <option value="Station">Station</option>
									 </select>
                                     </div>
                                </div>
                            </div>
                        </div>
                  </div>
             <div class="modal-footer">
                <button type="button" id="EditstationBtn" class="btn btn-default">Save changes</button>
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
                                            <h2>Manage Stations</h2>
                                            <p>
                                                Create, update, and manage system stations.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                                    <div class="breadcomb-report">
                                    <button type="button" data-toggle="modal" data-target="#myModalthree" data-placement="left" title="Create New User" class="btn"><i class="fa fa-plus"></i></button>
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
                                            <h2>Stations Info</h2>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="data-table-basic" class="table table-striped usersdata">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Full Name</th>
                                                        <th>Description</th>
                                                        <th>Type</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="usersdata">
                                                <?php
                                                    $apiUrl = App::baseUrl() . '/_ikawa/settings/location';
                                                    $json = fetchApiData($apiUrl);
                                                    $result = json_decode($json, true);
                                                    if ($result && $result['success'] && !empty($result['data'])) {
                                                        foreach ($result['data'] as $index => $record) {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $index + 1 ?></td>
                                                                <td><?php echo $record['location_name']; ?></td>
                                                                <td><?php echo $record['description']; ?></td>
                                                                <td><?php echo $record['type']; ?></td>
                                                                <td>
                                                                <div class="button-icon-btn button-icon-btn-rd">
                                                                <button
                                                                class="btn btn-default btn-icon-notika editrecord"
                                                                title="Edit Station"
                                                                data-id="<?= $record['loc_id'] ?>"
                                                                data-location_name="<?= htmlspecialchars($record['location_name']) ?>"
                                                                data-description="<?= htmlspecialchars($record['description']) ?>"
                                                                data-type="<?= htmlspecialchars($record['type']) ?>">
                                                                <i class="notika-icon notika-edit"></i>
                                                            </button>
                                                        
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
                           <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="location_name" id="location_name" class="form-control" placeholder="Station Name">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="description" id="description" class="form-control" placeholder="Description">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Type..." name="type" id="type">
                                        <option value="HQ">HQ</option>
                                        <option value="Station">Station</option>
									 </select>
                                     </div>
                                </div>
                            </div>
                        </div>

                            
                    <div class="modal-footer">

                    <button type="button" id="saveLocationBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>