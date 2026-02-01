<!-- Edit Expense Category Modal -->
<div class="modal animated bounce" id="editCategoryModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Expense Category</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="edit_categ_id" id="edit_categ_id">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_categ_name" id="edit_categ_name" class="form-control" placeholder="Category Name Ex. Operational">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_description" id="edit_description" class="form-control" placeholder="Description (Optional)">
                                    </div>
                                </div>
                            </div>
                    </div>
                 </div>
             <div class="modal-footer">
                <button type="button" id="updateCategoryBtn" class="btn btn-default">Save changes</button>
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
                                    <i class="notika-icon notika-edit"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Expense Categories</h2>
                                    <p>
                                        Create, update, and manage expense categories.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#createCategoryModal" data-placement="left" title="Create New Category" class="btn"><i class="fa fa-plus"></i></button>
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
                            <h2>Expense Categories</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="categoriesdata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/expense-categories/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            // Check if category is in use
                                            $checkUrl = App::baseUrl() . '/_ikawa/expense-categories/check-in-use/' . $record['categ_id'];
                                            $checkJson =fetchApiData($checkUrl);
                                            $checkResult = json_decode($checkJson, true);
                                            $inUse = $checkResult && $checkResult['success'] && $checkResult['data']['in_use'];
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo htmlspecialchars($record['categ_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['description'] ?: '-'); ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <button
                                                 class="btn btn-default btn-icon-notika editCategoryBtn"
                                                title="Edit Category"
                                                data-id="<?= $record['categ_id'] ?>"
                                                data-categ_name="<?= htmlspecialchars($record['categ_name']) ?>"
                                                data-description="<?= htmlspecialchars($record['description']) ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>
                                            <?php if (!$inUse): ?>
                                            <button
                                                 class="btn btn-danger btn-icon-notika deleteCategoryBtn"
                                                title="Delete Category"
                                                data-id="<?= $record['categ_id'] ?>"
                                                data-categ_name="<?= htmlspecialchars($record['categ_name']) ?>">
                                                <i class="notika-icon notika-close"></i>
                                            </button>
                                            <?php endif; ?>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="4" class="text-center">No expense categories found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- Create new category modal -->
    <div class="modal fade" id="createCategoryModal" role="dialog">
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
                                        <input type="text" name="categ_name" id="categ_name" class="form-control" placeholder="Category Name Ex. Operational">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="description" id="description" class="form-control" placeholder="Description (Optional)">
                                    </div>
                                </div>
                            </div>
                    </div>
                 </div>
                 <div class="modal-footer">
                    <button type="button" id="saveCategoryBtn" class="btn btn-default">Save Expense Category</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
