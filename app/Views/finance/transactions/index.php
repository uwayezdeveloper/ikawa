<?php
$permissions = $user['permissions'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Account Transactions</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                <li class="breadcrumb-item active">Transactions</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/finance/transactions" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="accountFilter" class="form-label">Account</label>
                <select class="form-select" id="accountFilter" name="account_id">
                    <option value="">All Accounts</option>
                    <?php foreach ($accounts as $account): ?>
                    <option value="<?= $account['id'] ?>" <?= $accountId == $account['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($account['account_name']) ?> - <?= htmlspecialchars($account['location_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="startDate" class="form-label">From Date</label>
                <input type="date" class="form-control" id="startDate" name="start_date" value="<?= $startDate ?>">
            </div>
            <div class="col-md-3">
                <label for="endDate" class="form-label">To Date</label>
                <input type="date" class="form-control" id="endDate" name="end_date" value="<?= $endDate ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-arrows-exchange fs-2 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0"><?= count($transactions) ?></h5>
                        <p class="text-muted mb-0">Total Transactions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-arrow-up fs-2 text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0"><?= number_format($totalCredits, 0) ?> RWF</h5>
                        <p class="text-muted mb-0">Total Credits (Money In)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ti ti-arrow-down fs-2 text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0"><?= number_format($totalDebits, 0) ?> RWF</h5>
                        <p class="text-muted mb-0">Total Debits (Money Out)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($selectedAccount): ?>
<!-- Selected Account Info -->
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="ti ti-wallet fs-4 me-2"></i>
    <div>
        <strong>Viewing:</strong> <?= htmlspecialchars($selectedAccount['account_name']) ?> 
        (<?= htmlspecialchars($selectedAccount['location_name']) ?>) - 
        <strong>Current Balance:</strong> <?= number_format($selectedAccount['balance'], 2) ?> RWF
    </div>
</div>
<?php endif; ?>

<!-- Transactions Table -->
<div class="card" data-table data-table-rows-per-page="15">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search transactions..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Type Filter -->
            <div class="app-search">
                <select data-table-filter="type" class="form-select form-control my-1 my-md-0">
                    <option value="All">Type</option>
                    <option value="Credit">Credit</option>
                    <option value="Debit">Debit</option>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
            <!-- Records Per Page -->
            <div>
                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                    <option value="10">10</option>
                    <option value="15" selected>15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Transaction #</th>
                    <th data-table-sort>Date</th>
                    <th data-table-sort>Account</th>
                    <th data-table-sort data-column="type">Type</th>
                    <th data-table-sort>Amount</th>
                    <th data-table-sort>Balance After</th>
                    <th data-table-sort>Reference</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="ti ti-receipt-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No transactions found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($transactions as $index => $txn): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <span class="badge bg-dark-subtle text-dark"><?= htmlspecialchars($txn['transaction_number']) ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= date('d M Y', strtotime($txn['transaction_date'])) ?></span>
                        <br><small class="text-muted"><?= date('H:i', strtotime($txn['created_at'])) ?></small>
                    </td>
                    <td>
                        <h6 class="mb-0"><?= htmlspecialchars($txn['account_name'] ?? '-') ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($txn['location_name'] ?? '') ?></small>
                    </td>
                    <td data-column="type">
                        <?php if ($txn['transaction_type'] === 'credit'): ?>
                        <span class="badge bg-success-subtle text-success">
                            <i class="ti ti-arrow-up me-1"></i>Credit
                        </span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger">
                            <i class="ti ti-arrow-down me-1"></i>Debit
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-semibold <?= $txn['transaction_type'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                            <?= $txn['transaction_type'] === 'credit' ? '+' : '-' ?><?= number_format($txn['amount'], 2) ?> RWF
                        </span>
                    </td>
                    <td>
                        <span class="fw-semibold"><?= number_format($txn['balance_after'], 2) ?> RWF</span>
                        <br>
                        <small class="text-muted">
                            From: <?= number_format($txn['balance_before'], 2) ?>
                        </small>
                    </td>
                    <td>
                        <?php 
                        $refLabels = [
                            'supplier_advance' => ['Supplier Advance', 'warning'],
                            'supplier_advance_refund' => ['Advance Refund', 'info'],
                            'purchase' => ['Purchase', 'primary'],
                            'sale' => ['Sale', 'success'],
                            'transfer' => ['Transfer', 'secondary'],
                            'adjustment' => ['Adjustment', 'dark']
                        ];
                        $refInfo = $refLabels[$txn['reference_type']] ?? [$txn['reference_type'], 'secondary'];
                        ?>
                        <span class="badge bg-<?= $refInfo[1] ?>-subtle text-<?= $refInfo[1] ?>"><?= $refInfo[0] ?></span>
                        <br><small class="text-muted">ID: <?= $txn['reference_id'] ?></small>
                    </td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($txn['description'] ?? '-') ?>">
                            <?= htmlspecialchars($txn['description'] ?? '-') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer border-light d-flex justify-content-between align-items-center">
        <div data-table-pagination-info></div>
        <div data-table-pagination></div>
    </div>
</div>
