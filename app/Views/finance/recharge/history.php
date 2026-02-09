<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/account-recharge">Account Recharge</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </div>
            <h4 class="page-title">Transaction History</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="ti ti-history me-2"></i>Account Transactions
                </h4>
                <!-- <div class="d-flex gap-2">
                    <a href="<?= APP_URL ?>/finance/account-recharge" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i>New Recharge
                    </a>
                    <a href="<?= APP_URL ?>/finance/account-transfer" class="btn btn-success">
                        <i class="ti ti-transfer me-2"></i>New Transfer
                    </a>
                </div> -->
            </div>
            <div class="card-body">
                <?php if (empty($history['data'])): ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-history-off text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted mb-3">No Transaction History Found</h5>
                        <p class="text-muted mb-4">There are no recharge or transfer transactions to display.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= APP_URL ?>/finance/account-recharge" class="btn btn-primary">
                                <i class="ti ti-plus me-2"></i>Create Recharge
                            </a>
                            <a href="<?= APP_URL ?>/finance/account-transfer" class="btn btn-success">
                                <i class="ti ti-transfer me-2"></i>Create Transfer
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="ti ti-receipt text-primary fs-1 mb-2"></i>
                                    <h4 class="mb-1"><?= number_format($history['total']) ?></h4>
                                    <p class="text-muted mb-0">Total Transactions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="ti ti-currency-dollar text-success fs-1 mb-2"></i>
                                    <h4 class="mb-1 text-success">
                                        $<?= number_format(array_sum(array_column($history['data'], 'amount')), 2) ?>
                                    </h4>
                                    <p class="text-muted mb-0">Page Total</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="ti ti-chart-line text-info fs-1 mb-2"></i>
                                    <h4 class="mb-1 text-info">
                                        $<?= number_format(array_sum(array_column($history['data'], 'amount')) / count($history['data']), 2) ?>
                                    </h4>
                                    <p class="text-muted mb-0">Average Amount</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="ti ti-file-text text-warning fs-1 mb-2"></i>
                                    <h4 class="mb-1 text-warning"><?= $history['totalPages'] ?></h4>
                                    <p class="text-muted mb-0">Total Pages</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Transaction Details</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Current Balance</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = ($history['page'] - 1) * $history['perPage'];
                                foreach ($history['data'] as $recharge): 
                                    $counter++;
                                    $dueDate = new DateTime($recharge['due_date']);
                                    $now = new DateTime();
                                    $isDueToday = $dueDate->format('Y-m-d') === $now->format('Y-m-d');
                                    $isOverdue = $dueDate < $now;
                                ?>
                                    <tr>
                                        <td class="fw-medium"><?= $counter ?></td>
                                        <td>
                                            <?php if (!empty($recharge['to_account'])): ?>
                                                <!-- This is a transfer -->
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <span class="avatar-sm bg-warning-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                                                            <i class="ti ti-transfer text-warning"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">Transfer</h6>
                                                        <small class="text-muted">
                                                            From: <?= htmlspecialchars($recharge['account_name'] ?? 'N/A') ?> → 
                                                            To: <?= htmlspecialchars($recharge['to_account_name'] ?? 'N/A') ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <!-- This is a regular recharge -->
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <span class="avatar-sm bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                                                            <i class="ti ti-wallet text-primary"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0"><?= htmlspecialchars($recharge['account_name'] ?? 'N/A') ?></h6>
                                                        <?php if (!empty($recharge['account_number'])): ?>
                                                            <small class="text-muted">A/C: <?= htmlspecialchars($recharge['account_number']) ?></small>
                                                        <?php else: ?>
                                                            <small class="text-muted">No account number</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($recharge['location_name'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($recharge['to_account'])): ?>
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="ti ti-transfer me-1"></i>Transfer
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info">
                                                    <?= htmlspecialchars($recharge['payment_mode_name'] ?? 'N/A') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if (!empty($recharge['to_account'])): ?>
                                                <span class="fw-bold text-warning fs-6">
                                                    ⇄ $<?= number_format($recharge['amount'], 2) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="fw-bold text-success fs-6">
                                                    +$<?= number_format($recharge['amount'], 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-medium text-primary">
                                                $<?= number_format($recharge['current_balance'] ?? 0, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium"><?= $dueDate->format('M d, Y') ?></span>
                                                <small class="text-muted"><?= $dueDate->format('g:i A') ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="ti ti-clock-x me-1"></i>Overdue
                                                </span>
                                            <?php elseif ($isDueToday): ?>
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="ti ti-clock me-1"></i>Due Today
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ti ti-check me-1"></i>Active
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($history['totalPages'] > 1): ?>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing <?= (($history['page'] - 1) * $history['perPage']) + 1 ?> to 
                                    <?= min($history['page'] * $history['perPage'], $history['total']) ?> 
                                    of <?= number_format($history['total']) ?> entries
                                </p>
                            </div>
                            <nav>
                                <ul class="pagination pagination-rounded mb-0">
                                    <!-- Previous Page -->
                                    <?php if ($history['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $history['page'] - 1 ?>">
                                                <i class="ti ti-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php
                                    $start = max(1, $history['page'] - 2);
                                    $end = min($history['totalPages'], $history['page'] + 2);
                                    
                                    for ($i = $start; $i <= $end; $i++): 
                                    ?>
                                        <li class="page-item <?= $i == $history['page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Page -->
                                    <?php if ($history['page'] < $history['totalPages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $history['page'] + 1 ?>">
                                                <i class="ti ti-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.avatar-sm {
    width: 2.25rem;
    height: 2.25rem;
}

.pagination-rounded .page-link {
    border-radius: 50px !important;
    margin: 0 2px;
    border: none;
}

.pagination-rounded .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.card.border-0 {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>