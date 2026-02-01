<!-- Edit Expense Consume Modal removed -->

<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-dollar"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Record Expense Consumption</h2>
                                    <p>
                                        Track and manage expense consumption with payments.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#createExpenseConsumeModal" data-placement="left" title="Record New Expense" class="btn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    <!-- Edit Expense Consume Modal -->
                    <div class="modal fade" id="editExpenseConsumeModal" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <!-- <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Edit Expense Consumption</h4>
                                </div> -->
                                <div class="modal-body">
                                    <form id="editExpenseConsumeForm">
                                        <input type="hidden" id="edit_con_id" />

                                        <!-- Row 1: Category, Expense Type, Receipt Type -->
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Expense Category</label> -->
                                                    <select class='form-control input-sm' id="edit_expense_category">
                                                        <option value="">Select Category</option>
                                                        <?php
                                                        $categoriesUrl = App::baseUrl() . '/_ikawa/expense-categories/get-all';
                                                        $catResponse = fetchApiData($categoriesUrl);
                                                        $categories = [];
                                                        if ($catResponse !== false) {
                                                            $catDecoded = json_decode($catResponse, true);
                                                            if ($catDecoded && isset($catDecoded['success']) && $catDecoded['success'] === true) {
                                                                $categories = $catDecoded['data'] ?? [];
                                                            }
                                                        }
                                                        if (!empty($categories)):
                                                            foreach ($categories as $category):
                                                        ?>
                                                            <option value="<?= htmlspecialchars($category['categ_id']) ?>">
                                                                <?= htmlspecialchars($category['categ_name']) ?>
                                                            </option>
                                                        <?php
                                                            endforeach;
                                                        endif;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Expense Type</label> -->
                                                    <select class='form-control input-sm' id="edit_expense_id">
                                                        <option value="">Select Category First</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Receipt Type</label> -->
                                                    <select class='form-control input-sm' id="edit_receipt_type">
                                                        <option value="">Optional</option>
                                                        <?php
                                                        $receiptTypesUrl = App::baseUrl() . '/_ikawa/receipt-types/get-all';
                                                        $rtResponse = fetchApiData($receiptTypesUrl);
                                                        $receiptTypes = [];
                                                        if ($rtResponse !== false) {
                                                            $rtDecoded = json_decode($rtResponse, true);
                                                            if ($rtDecoded && isset($rtDecoded['success']) && $rtDecoded['success'] === true) {
                                                                $receiptTypes = $rtDecoded['data'] ?? [];
                                                            }
                                                        }
                                                        if (!empty($receiptTypes)):
                                                            foreach ($receiptTypes as $receiptType):
                                                        ?>
                                                            <option value="<?= htmlspecialchars($receiptType['rec_id']) ?>">
                                                                <?= htmlspecialchars($receiptType['rec_name']) ?>
                                                            </option>
                                                        <?php
                                                            endforeach;
                                                        endif;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Row 2: Amount, Charges, Consumer -->
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Amount <span class="text-danger">*</span></label> -->
                                                    <input type="number" step="0.01" id="edit_amount" class="form-control input-sm" required />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Charges</label> -->
                                                    <input type="number" step="0.01" id="edit_charges" class="form-control input-sm" value="0" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Consumer</label> -->
                                                    <select id="edit_payer_name" class="form-control input-sm">
                                                        <option value="">Select consumer</option>
                                                        <?php
                                                        $consumersUrl = App::baseUrl() . '/_ikawa/expense-consumers/get-all';
                                                        $consResponse = fetchApiData($consumersUrl);
                                                        $consumers = [];
                                                        if ($consResponse !== false) {
                                                            $consDecoded = json_decode($consResponse, true);
                                                            if ($consDecoded && isset($consDecoded['success']) && $consDecoded['success'] === true) {
                                                                $consumers = $consDecoded['data'] ?? [];
                                                            }
                                                        }
                                                        if (!empty($consumers)):
                                                            foreach ($consumers as $consumer):
                                                        ?>
                                                            <option value="<?= htmlspecialchars($consumer['cons_id']) ?>">
                                                                <?= htmlspecialchars($consumer['cons_name']) ?> - <?= htmlspecialchars($consumer['phone']) ?>
                                                            </option>
                                                        <?php
                                                            endforeach;
                                                        endif;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Row 3: Account, Date -->
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <!-- <label>Account <span class="text-danger">*</span></label> -->
                                                    <select id="edit_pay_mode" class="form-control input-sm">
                                                        <option value="">Loading accounts...</option>
                                                    </select>
                                                    <small id="edit_account_balance" class="help-block" style="margin-top: 5px;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <!-- <label>Date <span class="text-danger">*</span></label> -->
                                                    <input type="date" id="edit_recorded_date" class="form-control input-sm" required />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Row 4: Description -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <!-- <label>Description</label> -->
                                                    <textarea id="edit_description" class="form-control input-sm" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            <button type="button" id="saveEditExpenseConsumeBtn" class="btn btn-success">
                                                <i class="notika-icon notika-edit"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
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
                            <h2>Expense Consumption Records</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Expense Type</th>
                                        <th>Station</th>
                                        <th>Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Consumer</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="expenseconsumedata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/expense-consume/get-all';
                                    $json = fetchApiData($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo htmlspecialchars($record['expense_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($record['st_name'] ?? 'N/A') . ' - ' . htmlspecialchars($record['st_location'] ?? ''); ?></td>
                                                <td><?php echo number_format($record['amount'], 0); ?> RWF</td>
                                                <td><?php echo htmlspecialchars($record['payment_mode_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($record['consumer_name'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($record['recorded_date']); ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                            <?php
                                                if (session_status() == PHP_SESSION_NONE) {
                                                    session_start();
                                                }
                                                $role_id = $_SESSION['role_id'] ?? 0;
                                                // Show edit button only to admin (role_id == 1).
                                                if ($role_id == 1) {
                                            ?>
                                            <button
                                                class="btn btn-primary btn-icon-notika editExpenseConsumeBtn"
                                                title="Edit"
                                                data-id="<?= $record['con_id'] ?>"
                                                data-expense_name="<?= htmlspecialchars($record['expense_name'] ?? 'this record') ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>
                                            <?php } else { ?>
                                                <button class="btn btn-primary btn-icon-notika" disabled title="Edit disabled">
                                                    <i class="notika-icon notika-edit"></i>
                                                </button>
                                            <?php } ?>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="8" class="text-center">No expense consumption records found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- Create new expense consume modal -->
    <div class="modal fade" id="createExpenseConsumeModal" role="dialog">
        <div class="modal-dialog modal-large" style="width: 90%; max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <!-- <h4 class="modal-title">
                        <i class="notika-icon notika-finance"></i> Record Expense Consumption
                    </h4> -->
                </div>
                <div class="modal-body">
                <?php
                // Load payment modes for the cards
                $paymentModesUrl = App::baseUrl() . '/_ikawa/settings/paymentmodes';
                $pmResponse = fetchApiData($paymentModesUrl);
                $paymentModes = [];
                if ($pmResponse !== false) {
                    $pmDecoded = json_decode($pmResponse, true);
                    if ($pmDecoded && isset($pmDecoded['success']) && $pmDecoded['success'] === true) {
                        $paymentModes = $pmDecoded['data'] ?? [];
                    }
                }
                ?>
                
                <!-- Row 1: Category, Expense Type, Receipt Type -->
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class='chosen-select-act fm-cmp-mg'>
                            <!-- <label style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                                Expense Category <span class="text-danger">*</span>
                            </label> -->
                            <select class='chosen' data-placeholder='Choose Category...' name="expense_category" id="expense_category">
                                <option value="">Select Category</option>
                                <?php
                                $categoriesUrl = App::baseUrl() . '/_ikawa/expense-categories/get-all';
                                $catResponse = fetchApiData($categoriesUrl);

                                $categories = [];

                                if ($catResponse !== false) {
                                    $catDecoded = json_decode($catResponse, true);

                                    if ($catDecoded && isset($catDecoded['success']) && $catDecoded['success'] === true) {
                                        $categories = $catDecoded['data'] ?? [];
                                    }
                                }
                                ?>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['categ_id']) ?>">
                                            <?= htmlspecialchars($category['categ_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option disabled>No categories found</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class='chosen-select-act fm-cmp-mg'>
                            <!-- <label style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                                Expense Type <span class="text-danger">*</span>
                            </label> -->
                            <select class='chosen' data-placeholder='Choose Expense Type...' name="expense_id" id="expense_id" disabled>
                                <option value="">Select Category First</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class='chosen-select-act fm-cmp-mg'>
                            <!-- <label style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                                Receipt Type
                            </label> -->
                            <select class='chosen' data-placeholder='Choose Receipt Type...' name="receipt_type" id="receipt_type">
                                <!--<option value="">Optional</option>-->
                                <?php
                                $receiptTypesUrl = App::baseUrl() . '/_ikawa/receipt-types/get-all';
                                $rtResponse = fetchApiData($receiptTypesUrl);

                                $receiptTypes = [];

                                if ($rtResponse !== false) {
                                    $rtDecoded = json_decode($rtResponse, true);

                                    if ($rtDecoded && isset($rtDecoded['success']) && $rtDecoded['success'] === true) {
                                        $receiptTypes = $rtDecoded['data'] ?? [];
                                    }
                                }
                                ?>
                                <?php if (!empty($receiptTypes)): ?>
                                    <?php foreach ($receiptTypes as $receiptType): ?>
                                        <option value="<?= htmlspecialchars($receiptType['rec_id']) ?>">
                                            <?= htmlspecialchars($receiptType['rec_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option disabled>No receipt types found</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Payer and Date -->
                <div class="row" style="margin-top: 15px;">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class='chosen-select-act fm-cmp-mg'>
                            <label style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                                Consumer/Payer <span class="text-danger">*</span>
                            </label>
                            <select class='chosen' data-placeholder='Choose Payer...' name="payer_name" id="payer_name" required>
                                <option value="">Select consumer</option>
                                <?php
                                $consumersUrl = App::baseUrl() . '/_ikawa/expense-consumers/get-all';
                                $consResponse = fetchApiData($consumersUrl);

                                $consumers = [];

                                if ($consResponse !== false) {
                                    $consDecoded = json_decode($consResponse, true);

                                    if ($consDecoded && isset($consDecoded['success']) && $consDecoded['success'] === true) {
                                        $consumers = $consDecoded['data'] ?? [];
                                    }
                                }
                                ?>
                                <?php if (!empty($consumers)): ?>
                                    <?php foreach ($consumers as $consumer): ?>
                                        <option value="<?= htmlspecialchars($consumer['cons_id']) ?>">
                                            <?= htmlspecialchars($consumer['cons_name']) ?> - <?= htmlspecialchars($consumer['phone']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option disabled>No consumers found</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                            Date <span class="text-danger">*</span>
                        </label>
                        <div class="form-group ic-cmp-int" style="margin-bottom: 0;">
                            <div class="form-ic-cmp">
                                <i class="notika-icon notika-calendar"></i>
                            </div>
                            <div class="nk-int-st">
                                <input type="date" name="recorded_date" id="recorded_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Row 3: Description -->
                <div class="row" style="margin-top: 15px;">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <label style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">
                            Description
                        </label>
                        <div class="form-group">
                            <textarea class="form-control" name="description" id="description" rows="2" placeholder="Enter expense description (Optional)" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Modes Section with Inline Accounts -->
                <div class="row" style="margin-top: 10px;">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <hr style="margin: 10px 0; border-top: 2px solid #00c292;">
                        <h5 style="color: #00c292; font-weight: 600; margin-bottom: 15px;">
                            <i class="notika-icon notika-credit-card"></i> Payment Accounts
                        </h5>
                            <div class="payment-modes-container">
                                <?php if (!empty($paymentModes)): ?>
                                    <?php foreach ($paymentModes as $mode): ?>
                                        <div class="payment-mode-wrapper" data-mode-id="<?= htmlspecialchars($mode['Mode_id']) ?>" style="margin-bottom: 10px; border: 2px solid #ddd; border-radius: 6px; padding: 12px; transition: all 0.3s;">
                                            <!-- Payment Mode Header -->
                                            <div class="payment-mode-header" style="display: flex; align-items: center; cursor: pointer; padding: 5px; margin-bottom: 10px;">
                                                <i class="notika-icon notika-credit-card" style="font-size: 18px; color: #00c292; margin-right: 8px;"></i>
                                                <span style="font-size: 14px; font-weight: 600; flex: 1;"><?= htmlspecialchars($mode['Mode_names']) ?></span>
                                                <i class="fa fa-chevron-down" style="font-size: 12px; color: #999; transition: transform 0.3s;"></i>
                                            </div>
                                            
                                            <!-- Inline Accounts and Entry Section (Hidden by default) -->
                                            <div class="accounts-section" style="display: none;">
                                                <div class="row" style="align-items: flex-end;">
                                                    <!-- Account Dropdown -->
                                                    <div class="col-xs-12 col-sm-3">
                                                        <div class="form-group" style="margin-bottom: 5px;">
                                                            <!-- <label style="font-size: 11px; margin-bottom: 3px;">Account <span class="text-danger">*</span></label> -->
                                                            <select class='form-control input-sm payment-account-select' style="height: 32px; font-size: 12px;">
                                                                <option value="">Select...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Entry Form Fields Inline -->
                                                    <div class="entry-form" style="display: none; width: 100%;">
                                                        <!-- Amount -->
                                                        <div class="col-xs-6 col-sm-2">
                                                            <div class="form-group" style="margin-bottom: 5px;">
                                                                <!-- <label style="font-size: 11px; margin-bottom: 3px;">Amount <span class="text-danger">*</span></label> -->
                                                                <input type="number" class="form-control input-sm entry-amount" placeholder="0.00" min="0" step="0.01" style="height: 32px; font-size: 12px;">
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Charges -->
                                                        <div class="col-xs-6 col-sm-2">
                                                            <div class="form-group" style="margin-bottom: 5px;">
                                                                <!-- <label style="font-size: 11px; margin-bottom: 3px;">Charges</label> -->
                                                                <input type="number" class="form-control input-sm entry-charges" placeholder="0.00" min="0" step="0.01" value="0" style="height: 32px; font-size: 12px;">
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Total -->
                                                        <div class="col-xs-6 col-sm-2">
                                                            <div class="form-group" style="margin-bottom: 5px;">
                                                                <label style="font-size: 11px; margin-bottom: 3px;">Total</label>
                                                                <input type="text" class="form-control input-sm entry-total" placeholder="0.00" readonly style="height: 32px; background: #f5f5f5; font-size: 12px; font-weight: 600;">
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Add Button -->
                                                        <div class="col-xs-6 col-sm-3">
                                                            <button type="button" class="btn btn-success btn-sm add-entry-btn" style="height: 32px; width: 100%; font-size: 12px; margin-top: 17px;">
                                                                <i class="notika-icon notika-plus-symbol"></i> Add
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Balance Display -->
                                                <div class="row">
                                                    <div class="col-xs-12">
                                                        <p class="account-balance-display" style="margin: 5px 0 0 0; font-size: 11px; color: #00c292; font-weight: bold;"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-danger">No payment modes available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                 

                    <!-- Payment Entries List - Always at Bottom -->
                    <div class="row" id="payment_entries_section" style="display: none; margin-top: 20px;">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <hr>
                            <h4 style="color: #00c292;"><i class="notika-icon notika-form"></i> Payment Entries</h4>
                            <table class="table table-bordered">
                                <thead style="background: #00c292; color: white;">
                                    <tr>
                                        <th>Account</th>
                                        <th>Amount</th>
                                        <th>Charges</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="payment_entries_list">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total:</th>
                                        <th id="total_amount_sum">0.00</th>
                                        <th id="total_charges_sum">0.00</th>
                                        <th id="grand_total_sum">0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveExpenseConsumeBtn" class="btn btn-default">Save Expense Consume</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
