<?php
$permissions = $user['permissions'] ?? [];
$location = $financeData['location'] ?? null;
$accounts = $financeData['accounts'] ?? [];
$transactions = $financeData['transactions'] ?? [];
$payables = $financeData['payables'] ?? [];
$payableSummary = $financeData['payableSummary'] ?? [];
$advances = $financeData['advances'] ?? [];
$stockSummary = $financeData['stockSummary'] ?? [];
$totalAccountBalance = $financeData['totalAccountBalance'] ?? 0;
$totalAdvances = $financeData['totalAdvances'] ?? 0;
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Station Finances</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                <li class="breadcrumb-item active">Station Finances</li>
            </ol>
        </nav>
    </div>
    <div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= APP_URL ?>/finance/station-finances/final-report" class="btn btn-outline-primary">
                <i class="ti ti-report-analytics me-1"></i> Final Report
            </a>
            <select id="locationSelect" class="form-select" style="min-width: 250px;">
                <?php foreach ($locations as $loc): ?>
                <option value="<?= $loc['id'] ?>" <?= $loc['id'] == $selectedLocationId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($loc['type_name'] . ' - ' . $loc['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<?php if ($location): ?>
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Balance</h6>
                        <h3 class="mb-0"><?= number_format($totalAccountBalance, 2) ?> <small>FRW</small></h3>
                    </div>
                    <div class="avatar-lg bg-white bg-opacity-25 rounded">
                        <i
                            class="ti ti-wallet fs-2 text-white d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Active Advances</h6>
                        <h3 class="mb-0"><?= number_format($totalAdvances, 2) ?> <small>FRW</small></h3>
                    </div>
                    <div class="avatar-lg bg-white bg-opacity-25 rounded">
                        <i
                            class="ti ti-arrow-up-right fs-2 text-white d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Pending Payables</h6>
                        <h3 class="mb-0"><?= number_format($payableSummary['total_pending'] ?? 0, 2) ?>
                            <small>FRW</small>
                        </h3>
                    </div>
                    <div class="avatar-lg bg-white bg-opacity-25 rounded">
                        <i
                            class="ti ti-clock-dollar fs-2 text-white d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Stock Value</h6>
                        <h3 class="mb-0"><?= number_format($stockSummary['total_value'] ?? 0, 2) ?> <small>FRW</small>
                        </h3>
                    </div>
                    <div class="avatar-lg bg-white bg-opacity-25 rounded">
                        <i
                            class="ti ti-package fs-2 text-white d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Accounts Section -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-wallet me-2"></i>Accounts</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($accounts)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-wallet-off fs-1 mb-2 d-block"></i>
                    No accounts found for this location
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Account Name</th>
                                <th>Payment Mode</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($account['account_name']) ?></strong>
                                    <br><small class="text-muted"><?= $account['transaction_count'] ?>
                                        transactions</small>
                                </td>
                                <td><?= htmlspecialchars($account['payment_mode_name'] ?? 'N/A') ?></td>
                                <td class="text-end">
                                    <span
                                        class="fw-bold <?= $account['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($account['balance'], 2) ?> FRW
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="2">Total Balance</th>
                                <th class="text-end"><?= number_format($totalAccountBalance, 2) ?> FRW</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Active Advances Section -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-arrow-up-right me-2"></i>Active Supplier Advances</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($advances)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-receipt-off fs-1 mb-2 d-block"></i>
                    No active advances
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($advances as $advance): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($advance['supplier_name']) ?></strong>
                                    <br><small class="text-muted"><?= $advance['advance_number'] ?></small>
                                </td>
                                <td><?= date('M d, Y', strtotime($advance['advance_date'])) ?></td>
                                <td class="text-end">
                                    <span class="fw-bold text-warning"><?= number_format($advance['amount'], 2) ?>
                                        FRW</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="2">Total Advances</th>
                                <th class="text-end"><?= number_format($totalAdvances, 2) ?> FRW</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pending Payables Section -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-clock-dollar me-2"></i>Pending Payables</h5>
                <span class="badge bg-danger"><?= count($payables) ?> pending</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($payables)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-check-circle fs-1 mb-2 d-block text-success"></i>
                    No pending payables! All suppliers paid.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Supplier</th>
                                <th>Reference</th>
                                <th class="text-end">Remaining</th>
                                <th class="text-center">Clearance</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payables as $payable): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($payable['supplier_name']) ?></strong>
                                    <br><small class="text-muted"><?= $payable['payable_number'] ?></small>
                                </td>
                                <td>
                                    <?= $payable['receive_number'] ?>
                                    <br><small
                                        class="text-muted"><?= date('M d, Y', strtotime($payable['created_at'])) ?></small>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-danger">
                                        <?= number_format($payable['amount'] - $payable['paid_amount'], 2) ?> FRW
                                    </span>
                                    <?php if ($payable['paid_amount'] > 0): ?>
                                    <br><small class="text-success">Paid:
                                        <?= number_format($payable['paid_amount'], 2) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger">Not Cleared</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success pay-payable-btn"
                                        data-payable-id="<?= $payable['id'] ?>"
                                        data-remaining="<?= $payable['amount'] - $payable['paid_amount'] ?>"
                                        data-supplier="<?= htmlspecialchars($payable['supplier_name']) ?>">
                                        <i class="ti ti-cash"></i> Pay
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-arrows-exchange me-2"></i>Recent Transactions</h5>
                <a href="<?= APP_URL ?>/finance/transactions" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($transactions)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="ti ti-receipt-off fs-1 mb-2 d-block"></i>
                    No transactions yet
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th>Account</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($tx['account_name']) ?>
                                    <br><small
                                        class="text-muted"><?= htmlspecialchars($tx['description'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-<?= $tx['transaction_type'] === 'credit' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($tx['transaction_type']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span
                                        class="<?= $tx['transaction_type'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                                        <?= $tx['transaction_type'] === 'credit' ? '+' : '-' ?><?= number_format($tx['amount'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('M d, H:i', strtotime($tx['created_at'])) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Stock Receive Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-package me-2"></i>Stock Receive Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1"><?= $stockSummary['total_receives'] ?? 0 ?></h4>
                            <small class="text-muted">Total Receives</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1"><?= number_format($stockSummary['total_value'] ?? 0) ?></h4>
                            <small class="text-muted">Total Value (FRW)</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 border rounded bg-warning bg-opacity-10">
                            <h4 class="mb-1 text-warning"><?= number_format($stockSummary['paid_by_advance'] ?? 0) ?>
                            </h4>
                            <small class="text-muted">Paid by Advance</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 border rounded bg-primary bg-opacity-10">
                            <h4 class="mb-1 text-primary"><?= number_format($stockSummary['paid_by_account'] ?? 0) ?>
                            </h4>
                            <small class="text-muted">Paid by Account</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 border rounded bg-danger bg-opacity-10">
                            <h4 class="mb-1 text-danger"><?= number_format($stockSummary['total_payables'] ?? 0) ?></h4>
                            <small class="text-muted">As Payables</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1">
                                <span class="text-warning"><?= $stockSummary['pending_count'] ?? 0 ?></span> /
                                <span class="text-success"><?= $stockSummary['approved_count'] ?? 0 ?></span>
                            </h4>
                            <small class="text-muted">Pending / Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pay Payable Modal -->
<div class="modal fade" id="payPayableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pay Supplier Payable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payPayableForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="pay_payable">
                    <input type="hidden" name="payable_id" id="payableId">

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" id="payableSupplier" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remaining Amount</label>
                        <input type="text" id="payableRemaining" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Account <span class="text-danger">*</span></label>
                        <select name="account_id" id="payAccountSelect" class="form-select" required>
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>" data-balance="<?= $account['balance'] ?>">
                                <?= htmlspecialchars($account['account_name']) ?> (Balance:
                                <?= number_format($account['balance'], 2) ?> FRW)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="payAmount" class="form-control" step="0.01" min="0.01"
                            required>
                        <small class="text-muted">Enter amount to pay (can be partial)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-cash me-1"></i> Make Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-info">
    <i class="ti ti-info-circle me-2"></i>
    Please select a location to view its finances.
</div>
<?php endif; ?>

<script>
window.APP_URL = '<?= APP_URL ?>';
window.SELECTED_LOCATION_ID = <?= $selectedLocationId ?? 'null' ?>;
</script>