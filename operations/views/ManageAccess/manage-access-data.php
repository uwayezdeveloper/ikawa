
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
                                    <h2>Permissions</h2>
                                    <p>
                                        Manage user permissions.
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
                            <h2>Role and permission access</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped usersdata">
                                <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Role Name</th>
                                            <th>Access Menus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersdata">
                                        <?php
                                        $apiUrl = App::baseUrl() . '/_ikawa/settings/getaccessdata';
                                        $json = fetchApiData($apiUrl);
                                        $result = json_decode($json, true);
                                        
                                        if ($result && $result['success'] && !empty($result['data'])) {
                                            // Group data by role_id
                                            $grouped = [];
                                            foreach ($result['data'] as $item) {
                                                $roleId = $item['role_id'];
                                                if (!isset($grouped[$roleId])) {
                                                    $grouped[$roleId] = [
                                                        'role_name' => $item['role_name'],
                                                        'description' => $item['description'],
                                                        'menus' => []
                                                    ];
                                                }
                                                $grouped[$roleId]['menus'][] = [
                                                    'menu_id' => $item['role_menu_id'],
                                                    'menu_name' => $item['menu_name'],
                                                    'icon' => $item['icon'],
                                                    'url' => $item['url']
                                                ];
                                            }
                                            
                                            $i = 0;
                                            foreach ($grouped as $roleId => $roleData):
                                                $i++;
                                        ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($roleData['role_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($roleData['description']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="menu-tags">
                                                        <?php foreach ($roleData['menus'] as $menu): ?>
                                                                <i class="notika-icon <?php echo htmlspecialchars($menu['icon']); ?>"></i>
                                                                <?php echo htmlspecialchars($menu['menu_name']); ?>
                                                                <button class="btn btn-default btn-icon-notika mt-5 removeMenuBtn"  title="Remove Menu"
                                                                    data-role-id="<?php echo htmlspecialchars($roleId); ?>"
                                                                    data-menu-id="<?php echo htmlspecialchars($menu['menu_id']); ?>"
                                                                    data-menu-name="<?php echo htmlspecialchars($menu['menu_name']); ?>" style="margin-top:6px;"><i class="notika-icon notika-close"></i></button>
                                                           
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php 
                                            endforeach;
                                        } else {
                                            echo '<tr><td colspan="3" class="text-center text-muted py-4">No access permissions found</td></tr>';
                                        }
                                        ?>
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
                                <select class = 'chosen' data-placeholder = 'Choose role...' name="role_id_access" id="role_id_access">
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
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group ic-cmp-int">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-world"></i>
                            </div>
                                <div class="chosen-select-act fm-cmp-mg">
                                <select class='chosen' multiple data-placeholder='Choose menu...' name="menu_id" id="menu_id">
                                    <option>Choose menu.</option>
                                    <?php
                                    $dataUrl = App::baseUrl() . '/_ikawa/settings/menus';
                                    $response = fetchApiData($dataUrl);

                                    $menus = [];

                                    if ($response !== false) {
                                        $decoded = json_decode($response, true);

                                        if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                           $menus = $decoded['data'] ?? [];
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($menus )): ?>
                                        <?php foreach ($menus as $menu): ?>
                                            <option value="<?= htmlspecialchars($menu['menu_id']) ?>">
                                                <?= htmlspecialchars($menu['menu_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No menu found</option>
                                    <?php endif; ?>
                                </select>
                                </div>
                            </div>
                        </div>
                        </div>

                    <div class="modal-footer">

                    <button type="button" id="savepermissionBtn" class="btn btn-default">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>