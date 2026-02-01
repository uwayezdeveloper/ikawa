

<div class="modal animated bounce" id="myModalfour" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="user_id" id="user_id">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_first_name" id="edit_first_name" class="form-control" placeholder="First Name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_last_name" id="edit_last_name" class="form-control" placeholder="Last Name">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
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
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_username" id="edit_username" class="form-control" placeholder="username" required>
                                    </div>
                                </div>
                            </div>
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-phone"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="edit_phone" id="edit_phone" class="form-control" placeholder="phone number">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class = 'chosen-select-act fm-cmp-mg'>
                                <select class = 'chosen' data-placeholder = 'Choose role...' name="edit_role_id" id="edit_role_id">
                                 <option>Select Role</option>
                                <?php
                                $rolesUrl = App::baseUrl() . '/_ikawa/settings/roles';
                                $response = fetchApiData($rolesUrl);

                                $roles = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $roles = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= htmlspecialchars($role['role_id']) ?>">
                                                <?= htmlspecialchars($role['role_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No roles found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                         </div>

                         <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Gender..." name="edit_gender" id="edit_gender">
											<option value="male">Male</option>
											<option value="female">Female</option>
                                            <option value="No Gender">No Gender</option>
									</select>
                                     </div>
                                </div>
                            </div>
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="edit_nid" id="edit_nid" class="form-control" placeholder="NID/TIN">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class = 'chosen-select-act fm-cmp-mg'>
                                <select class = 'chosen' data-placeholder = 'Choose Location...' name="edit_loc_id" id="edit_loc_id">
                                 <option>Select Location</option>
                                <?php
                                $locationUrl = App::baseUrl() . '/_ikawa/settings/location';
                                $response =fetchApiData($locationUrl);

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
                                                <?= htmlspecialchars($location['location_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No location found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                    </div>
                 </div>
             <div class="modal-footer">
                <button type="button" id="EditUserBtn" class="btn btn-default">Save changes</button>
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
                                    <h2>Manage Users</h2>
                                    <p>
                                        Create, update, and manage system users and their access roles.
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
                            <h2>Users</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped usersdata">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fist Name</th>
                                        <th>Last Name</th>
                                        <th>UserName</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="usersdata">
                                  <?php
                                    $loc_id=$_SESSION['loc_id'];
                                    $apiUrl = App::baseUrl() . '/_ikawa/users/get-all-users?loc_id='.$loc_id;
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $user) {
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo $user['first_name']; ?></td>
                                                <td><?php echo $user['last_name']; ?></td>
                                                <td><?php echo $user['username']; ?></td>
                                                <td><?php echo $user['phone']; ?></td>
                                                <td><?php echo $user['role_name']; ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <button
                                                 class="btn btn-default btn-icon-notika edituser"
                                                title="Edit User"
                                                data-id="<?= $user['user_id'] ?>"
                                                data-first_name="<?= htmlspecialchars($user['first_name']) ?>"
                                                data-last_name="<?= htmlspecialchars($user['last_name']) ?>"
                                                data-email="<?= htmlspecialchars($user['email']) ?>"
                                                data-username="<?= htmlspecialchars($user['username']) ?>"
                                                data-phone="<?= htmlspecialchars($user['phone']) ?>"
                                                data-role_id="<?= $user['role_id'] ?>"
                                                data-gender="<?= $user['gender'] ?>"
                                                data-nid="<?= $user['nid'] ?>"
                                                data-loc_id="<?= $user['loc_id'] ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>

                                                <button class="btn btn-default btn-icon-notika deleteuser"  title="Delete User"
                                                  data-id="<?= $user['user_id'] ?>"
                                                  data-first_name="<?= htmlspecialchars($user['first_name']) ?>" ><i class="notika-icon text-danger notika-trash"></i></button>

                                            </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="7" class="text-center">No users found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- create new user model -->
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
                                        <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name">
                                    </div>
                                </div>
                            </div>
                             <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
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
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="username" id="username" class="form-control" placeholder="username" required>
                                    </div>
                                </div>
                            </div>
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-phone"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="phone" id="phone" class="form-control" placeholder="phone number">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class = 'chosen-select-act fm-cmp-mg'>
                                <select class = 'chosen' data-placeholder = 'Choose role...' name="role_id" id="role_id">
                                 <option>Select Role</option>
                                <?php
                                $rolesUrl = App::baseUrl() . '/_ikawa/settings/roles';
                                $response = fetchApiData($rolesUrl);

                                $roles = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $roles = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= htmlspecialchars($role['role_id']) ?>">
                                                <?= htmlspecialchars($role['role_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No roles found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                         </div>

                         <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="chosen-select-act fm-cmp-mg">
                                    <select class="chosen" data-placeholder="Choose Gender..." name="gender" id="gender">
											<option value="male">Male</option>
											<option value="female">Female</option>
                                            <option value="No Gender">No Gender</option>
									</select>
                                     </div>
                                </div>
                            </div>
                              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="nid" id="nid" class="form-control" placeholder="NID/TIN">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                <div class = 'chosen-select-act fm-cmp-mg'>
                                <select class = 'chosen' data-placeholder = 'Choose Location...' name="loc_id" id="loc_id">
                                 <option>Select Location</option>
                                <?php
                                $locationUrl = App::baseUrl() . '/_ikawa/settings/location';
                                $response = fetchApiData($locationUrl);

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
                                                <?= htmlspecialchars($location['location_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No location found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                         </div>
                    <div class="modal-footer">
                    <button type="button" id="saveUserBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

  