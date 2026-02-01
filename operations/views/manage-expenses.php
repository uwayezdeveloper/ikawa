<!-- Edit Expense Modal -->
<div class="modal animated bounce" id="editExpenseModal" role="dialog">
    <div class="modal-dialog modals-default">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Expense</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="edit_expense_id" id="edit_expense_id">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <!-- <i class="notika-icon notika-menu"></i> -->
                                    </div>
                                    <div>
                                        <div class='chosen-select-act fm-cmp-mg'>
                                            <select name="edit_categ_id" id="edit_categ_id" class="chosen" data-placeholder="Select Category">
                                                <option value="">Select Category</option>
                                                <?php
                                                $categUrl = App::baseUrl() . '/_ikawa/expense-categories/get-all';
                                                $categJson = fetchApiData($categUrl);
                                                $categResult = json_decode($categJson, true);
                                                if ($categResult && $categResult['success'] && !empty($categResult['data'])) {
                                                    foreach ($categResult['data'] as $category) {
                                                        echo '<option value="' . $category['categ_id'] . '">' . htmlspecialchars($category['categ_name']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_expense_name" id="edit_expense_name" class="form-control" placeholder="Expense Name Ex. Transport">
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
                <button type="button" id="updateExpenseBtn" class="btn btn-default">Save changes</button>
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
                                    <h2>Record Expenses</h2>
                                    <p>
                                        Create, update, and manage expense categories.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#createExpenseModal" data-placement="left" title="Create New Expense" class="btn"><i class="fa fa-plus"></i></button>
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
                            <h2>Expenses</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Category</th>
                                        <th>Expense Name</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="expensesdata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/expenses/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo htmlspecialchars($record['categ_name'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($record['expense_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['description'] ?: '-'); ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <button
                                                 class="btn btn-default btn-icon-notika editExpenseBtn"
                                                title="Edit Expense"
                                                data-id="<?= $record['expense_id'] ?>"
                                                data-categ_id="<?= $record['categ_id'] ?>"
                                                data-expense_name="<?= htmlspecialchars($record['expense_name']) ?>"
                                                data-description="<?= htmlspecialchars($record['description']) ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>
                                            <button
                                                 class="btn btn-danger btn-icon-notika deleteExpenseBtn"
                                                title="Delete Expense"
                                                data-id="<?= $record['expense_id'] ?>"
                                                data-expense_name="<?= htmlspecialchars($record['expense_name']) ?>">
                                                <i class="notika-icon notika-close"></i>
                                            </button>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="5" class="text-center">No expenses found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- Create new expense modal -->
    <div class="modal fade" id="createExpenseModal" role="dialog">
        <div class="modal-dialog modal-large">
            <div class="modal-content">
                <div class="modal-body">
                <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <!-- <i class="notika-icon notika-menu"></i> -->
                                    </div>
                                    <div>
                                        <div class='chosen-select-act fm-cmp-mg'>
                                            <select name="categ_id" id="categ_id" class="chosen" data-placeholder="Select Category">
                                                <option value="">Select Category</option>
                                                <?php
                                                $categUrl = App::baseUrl() . '/_ikawa/expense-categories/get-all';
                                                $categJson = fetchApiData($categUrl);
                                                $categResult = json_decode($categJson, true);
                                                if ($categResult && $categResult['success'] && !empty($categResult['data'])) {
                                                    foreach ($categResult['data'] as $category) {
                                                        echo '<option value="' . $category['categ_id'] . '">' . htmlspecialchars($category['categ_name']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="expense_name" id="expense_name" class="form-control" placeholder="Expense Name Ex. Transport">
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
                    <button type="button" id="saveExpenseBtn" class="btn btn-default">Save Expense Type</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
