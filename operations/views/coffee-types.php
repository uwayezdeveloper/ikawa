<!-- Edit Category Type Modal -->
<div class="modal animated bounce" id="myModalfour" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Category Type</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="type_id" id="type_id">
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <select class="chosen" data-placeholder="Choose Category..." name="edit_category_id" id="edit_category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $categoriesUrl = App::baseUrl() . '/_ikawa/categories/get-active-categories';
                            $response = fetchApiData($categoriesUrl);
                            $categories = [];
                            if ($response !== false) {
                                $decoded = json_decode($response, true);
                                if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                    $categories = $decoded['data'] ?? [];
                                }
                            }
                            ?>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No active categories found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group ic-cmp-int">
                        <div class="form-ic-cmp">
                            <i class="notika-icon notika-support"></i>
                        </div>
                        <div class="nk-int-st">
                            <input type="text" name="edit_type_name" id="edit_type_name" class="form-control" placeholder="Type Name" required>
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
                        </select>
                    </div>
                </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="EditCategoryTypeBtn" class="btn btn-default">Save changes</button>
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
                                    <h2>Manage Category Types</h2>
                                    <p>
                                        Create, update, and manage category types with their descriptions and status.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#myModalthree" data-placement="left" title="Create New Category Type" class="btn"><i class="fa fa-plus"></i></button>
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
                        <h2>Category Types List</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="data-table-basic" class="table table-striped categorytypesdata">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Type Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="categorytypesdata">
                              <?php
                                $apiUrl = App::baseUrl() . '/_ikawa/category-types/get-all-category-types';
                                $json = fetchApiData($apiUrl);
                                $result = json_decode($json, true);
                                if ($result && $result['success'] && !empty($result['data'])) {
                                    foreach ($result['data'] as $index => $categoryType) {
                                        $statusClass = $categoryType['status'] === 'active' ? 'text-success' : 'text-danger';
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1 ?></td>
                                            <td><?php echo htmlspecialchars($categoryType['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($categoryType['type_name']); ?></td>
                                            <td><?php echo htmlspecialchars($categoryType['description']); ?></td>
                                            <td><span class="<?php echo $statusClass; ?>"><?php echo ucfirst($categoryType['status']); ?></span></td>
                                            <td>
                                            <div class="button-icon-btn button-icon-btn-rd">
                                            <button
                                             class="btn btn-default btn-icon-notika editcategorytype"
                                            title="Edit Category Type Status"
                                            data-id="<?= $categoryType['type_id'] ?>"
                                            data-category_id="<?= $categoryType['category_id'] ?>"
                                            data-category_name="<?= htmlspecialchars($categoryType['category_name']) ?>"
                                            data-type_name="<?= htmlspecialchars($categoryType['type_name']) ?>"
                                            data-description="<?= htmlspecialchars($categoryType['description']) ?>"
                                            data-status="<?= $categoryType['status'] ?>">
                                            <i class="notika-icon notika-edit"></i>
                                        </button>
                                        </div>
                                        </td>
                                       </tr>
                                   <?php  }
                                } else {
                                    ?>
                                      <tr><td colspan="6" class="text-center">No category types found</td></tr>
                                <?php }  ?>
                            </tbody>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create New Category Type Modal -->
<div class="modal fade" id="myModalthree" role="dialog">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create New Category Type</h4>
            </div>
            <div class="modal-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="chosen-select-act fm-cmp-mg">
                        <select class="chosen" data-placeholder="Choose Category..." name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $categoriesUrl = App::baseUrl() . '/_ikawa/categories/get-active-categories';
                            $response =fetchApiData($categoriesUrl);
                            $categories = [];
                            if ($response !== false) {
                                $decoded = json_decode($response, true);
                                if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                    $categories = $decoded['data'] ?? [];
                                }
                            }
                            ?>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option disabled>No active categories found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group ic-cmp-int">
                        <div class="form-ic-cmp">
                            <i class="notika-icon notika-support"></i>
                        </div>
                        <div class="nk-int-st">
                            <input type="text" name="type_name" id="type_name" class="form-control" placeholder="Type Name" required>
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
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveCategoryTypeBtn" class="btn btn-default">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
