<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Worker Loan Statement</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loans">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loans/statement">Loan Statement</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">GIHANGA COFFEE COMPANY LTD <?= date('Y') ?></h4>
                    <h5 class="text-muted mb-0">
                        Worker Loan Statement 
                        <?php if (!empty($startDate) && !empty($endDate)): ?>
                            - <?= date('M d, Y', strtotime($startDate)) ?> to <?= date('M d, Y', strtotime($endDate)) ?>
                        <?php else: ?>
                            - All Transactions
                        <?php endif; ?>
                    </h5>
                    <p class="text-muted mb-0">Accrual Basis</p>
                </div>
                <div class="text-end">
                    <button onclick="window.print()" class="btn btn-info btn-sm me-2">
                        <i class="bx bx-printer me-1"></i>Print Statement
                    </button>
                    <a href="<?= APP_URL ?>/finance/loans/statement" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>Back to Selection
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Worker Information Header -->
                <div class="bg-light px-3 py-3 border-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-1"><strong>Worker Information</strong></h6>
                            <p class="mb-0"><strong>Name:</strong> <?= htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']) ?></p>
                            <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($worker['email']) ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0"><strong>Statement Date:</strong> <?= date('m-d-Y', strtotime(date('Y-m-d'))) ?></p>
                            <p class="mb-0"><strong>Generated:</strong> <?= date('m/d/Y g:i A') ?></p>
                            <?php if (!empty($startDate) && !empty($endDate)): ?>
                                <p class="mb-0"><strong>Period:</strong> <?= date('m/d/Y', strtotime($startDate)) ?> - <?= date('m/d/Y', strtotime($endDate)) ?></p>
                            <?php else: ?>
                                <p class="mb-0"><strong>Period:</strong> All Transactions</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (empty($transactions)): ?>
                <div class="text-center py-5">
                    <i class="ti ti-file-x fs-48 text-muted mb-3"></i>
                    <h5 class="text-muted">No Loan Transactions Found</h5>
                    <?php if (!empty($startDate) && !empty($endDate)): ?>
                        <p class="text-muted">No loan transactions found for this worker in the selected date range (<?= date('M d, Y', strtotime($startDate)) ?> - <?= date('M d, Y', strtotime($endDate)) ?>).</p>
                        <p class="text-muted">Try expanding the date range or select "All Transactions" to see complete loan history.</p>
                    <?php else: ?>
                        <p class="text-muted">This worker has no loan transactions to display.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                
                <!-- Date Filter Alert -->
                <?php if (!empty($startDate) && !empty($endDate)): ?>
                <div class="alert alert-info mx-3 mt-3">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Filtered Results:</strong> Showing transactions from <?= date('M d, Y', strtotime($startDate)) ?> to <?= date('M d, Y', strtotime($endDate)) ?>. 
                    <a href="<?= APP_URL ?>/finance/loans/statement/<?= $worker['user_id'] ?>" class="alert-link">View all transactions</a>.
                </div>
                <?php endif; ?>
                
                <!-- Statement Table -->
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th width="12%" class="text-center">Date</th>
                                <th width="40%">Memo</th>
                                <th width="16%" class="text-end">Loan Taken</th>
                                <th width="16%" class="text-end">Amount Paid</th>
                                <th width="16%" class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr <?= $transaction['transaction_type'] === 'loan' ? 'class="table-warning bg-opacity-25"' : 'class="table-success bg-opacity-25"' ?>>
                                <td class="text-center">
                                    <?php 
                                    if (!empty($transaction['transaction_date'])) {
                                        echo date('m-d-Y', strtotime($transaction['transaction_date']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['memo']) ?></td>
                                <td class="text-end">
                                    <?php if ($transaction['debit_amount'] > 0): ?>
                                        <?= number_format($transaction['debit_amount'], 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($transaction['credit_amount'] > 0): ?>
                                        <?= number_format($transaction['credit_amount'], 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <strong class="<?= $transaction['running_balance'] > 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format(abs($transaction['running_balance']), 2) ?>
                                    </strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="2" class="text-end">TOTALS:</th>
                                <th class="text-end">
                                    <?php 
                                    $totalLoans = array_sum(array_column($transactions, 'debit_amount'));
                                    echo number_format($totalLoans, 2);
                                    ?>
                                </th>
                                <th class="text-end">
                                    <?php 
                                    $totalPayments = array_sum(array_column($transactions, 'credit_amount'));
                                    echo number_format($totalPayments, 2);
                                    ?>
                                </th>
                                <th class="text-end">
                                    <?php 
                                    $finalBalance = $totalLoans - $totalPayments;
                                    ?>
                                    <strong class="<?= $finalBalance > 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format(abs($finalBalance), 2) ?>
                                    </strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary Information -->
                <div class="bg-light px-3 py-3 border-top">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Total Loans Taken</h6>
                                <h4 class="text-danger mb-0">RWF <?= number_format($totalLoans, 2) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Total Payments Made</h6>
                                <h4 class="text-success mb-0">RWF <?= number_format($totalPayments, 2) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Remaining Balance</h6>
                                <h4 class="<?= $finalBalance > 0 ? 'text-danger' : 'text-success' ?> mb-0">
                                    RWF <?= number_format(abs($finalBalance), 2) ?>
                                    <?php if ($finalBalance == 0): ?>
                                        <small class="text-muted d-block">(Fully Paid)</small>
                                    <?php elseif ($finalBalance > 0): ?>
                                        <small class="text-muted d-block">(Outstanding)</small>
                                    <?php else: ?>
                                        <small class="text-muted d-block">(Overpaid)</small>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="<?= APP_URL ?>/finance/loans/statement" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Select Another Worker
            </a>
            <div>
                <button onclick="window.print()" class="btn btn-info me-2">
                    <i class="bx bx-printer me-1"></i>Print Statement
                </button>
                <a href="<?= APP_URL ?>/finance/loans" class="btn btn-primary">
                    <i class="bx bx-money me-1"></i>Manage Loans
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .btn, .breadcrumb, .page-title-right {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-header {
        border-bottom: 2px solid #dee2e6 !important;
        background: #f8f9fa !important;
    }
    .table-primary {
        background-color: #cfe2ff !important;
        border-color: #b6d7ff !important;
    }
    .table-light {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
    }
    body {
        font-size: 12px;
    }
    .table {
        font-size: 11px !important;
    }
}

/* Custom styling for better visual separation */
.table tbody tr.table-warning {
    border-left: 4px solid #ffc107;
}
.table tbody tr.table-success {
    border-left: 4px solid #198754;
}

.table-primary {
    --bs-table-bg: #cfe2ff;
    --bs-table-striped-bg: #c5d7f0;
    --bs-table-striped-color: #000;
    --bs-table-active-bg: #bacbe6;
    --bs-table-active-color: #000;
    --bs-table-hover-bg: #bfd1ec;
    --bs-table-hover-color: #000;
    color: #000;
    border-color: #b6d7ff;
}
</style>