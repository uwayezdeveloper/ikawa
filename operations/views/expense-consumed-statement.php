<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-bar-chart"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Expense Consumed Statement</h2>
                                    <!-- <p>View and filter expense consumption records with detailed breakdown</p> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="notika-icon notika-search"></i></h2>
                    </div>
                    <div style="padding: 20px;">
                        <div class="row">
                            <!-- Consumer Filter -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                    <!-- <label style="font-size: 12px; margin-bottom: 5px;">Consumer</label> -->
                                    <select class='chosen' data-placeholder='Select Consumer...' name="filter_consumer" id="filter_consumer">
                                        <option value="">All Consumers</option>
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
                                        if (!empty($consumers)) {
                                            foreach ($consumers as $consumer) {
                                                echo '<option value="' . htmlspecialchars($consumer['cons_id']) . '">' 
                                                    . htmlspecialchars($consumer['cons_name']) . ' - ' 
                                                    . htmlspecialchars($consumer['phone']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Expense Type Filter -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                    <!-- <label style="font-size: 12px; margin-bottom: 5px;">Expense Type</label> -->
                                    <select class='chosen' data-placeholder='Select Expense Type...' name="filter_expense" id="filter_expense">
                                        <option value="">All Expense Types</option>
                                        <?php
                                        $expensesUrl = App::baseUrl() . '/_ikawa/expenses/get-all';
                                        $expResponse = fetchApiData($expensesUrl);
                                        $expenses = [];
                                        if ($expResponse !== false) {
                                            $expDecoded = json_decode($expResponse, true);
                                            if ($expDecoded && isset($expDecoded['success']) && $expDecoded['success'] === true) {
                                                $expenses = $expDecoded['data'] ?? [];
                                            }
                                        }
                                        if (!empty($expenses)) {
                                            foreach ($expenses as $expense) {
                                                echo '<option value="' . htmlspecialchars($expense['expense_id']) . '">' 
                                                    . htmlspecialchars($expense['expense_name']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Receipt Type Filter -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                    <select class='chosen' data-placeholder='Select Receipt Type...' name="filter_receipt_type" id="filter_receipt_type">
                                        <option value="">All Receipt Types</option>
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
                                        if (!empty($receiptTypes)) {
                                            foreach ($receiptTypes as $receiptType) {
                                                echo '<option value="' . htmlspecialchars($receiptType['rec_id']) . '">' 
                                                    . htmlspecialchars($receiptType['rec_name']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Date From -->
                            <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <!-- <label style="font-size: 12px; margin-bottom: 5px;">Date From</label> -->
                                    <input type="date" class="form-control" id="filter_date_from" value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <!-- Date To -->
                            <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <!-- <label style="font-size: 12px; margin-bottom: 5px;">Date To</label> -->
                                    <input type="date" class="form-control" id="filter_date_to" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Apply Filter Button -->
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-xs-12">
                                <button type="button" id="applyFiltersBtn" class="btn btn-success btn-sm">
                                    <i class="notika-icon notika-search"></i> 
                                </button>
                                <button type="button" id="exportPdfBtn" class="btn btn-default btn-sm" style="margin-left: 10px;">
                                    <i class="fa fa-file-pdf-o"></i> 
                                </button>
                                <button type="button" id="exportExcelBtn" class="btn btn-default btn-sm" style="margin-left: 10px;">
                                    <i class="fa fa-file-excel-o"></i> 
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statement Table -->
<div class="data-table-area" id="statement-results-section" style="display:none;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Expense Statement Records</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="statement-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Expense Type</th>
                                    <th>Consumer</th>
                                    <th>Station</th>
                                    <th class="amount-col">Amount</th>
                                    <th class="charges-col">Charges</th>
                                    <th class="total-col">Total</th>
                                    <th>Payment Mode</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="statementdata">
                                <tr>
                                    <td colspan="10" class="text-center">Please apply filters to view data</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f5f5f5;">
                                    <th colspan="5" class="text-right">TOTAL:</th>
                                    <th class="amount-col" id="total_amount">0.00</th>
                                    <th class="charges-col" id="total_charges">0.00</th>
                                    <th class="total-col" id="grand_total">0.00</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

