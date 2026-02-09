<?php
$title = $title ?? 'My Loans';
$loans = $loans ?? [];
$stats = $stats ?? [];
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if (!empty($item['url'])): ?>
                            <li class="breadcrumb-item"><a href="<?= $item['url'] ?>"><?= htmlspecialchars($item['name']) ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($item['name']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
            <h4 class="page-title"><?= htmlspecialchars($title) ?></h4>
        </div>
    </div>
</div>

<!-- Personal Loan Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-white-50 mb-0">Total Requests</p>
                        <h4 class="mb-0"><?= $stats['total_loans'] ?? 0 ?></h4>
                    </div>
                    <div class="align-self-center">
                        <i class="ti ti-report-money font-size-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-white-50 mb-0">Pending</p>
                        <h4 class="mb-0"><?= $stats['pending_loans'] ?? 0 ?></h4>
                    </div>
                    <div class="align-self-center">
                        <i class="ti ti-clock font-size-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-white-50 mb-0">Total Received</p>
                        <h4 class="mb-0"><?= number_format($stats['total_disbursed'] ?? 0) ?></h4>
                    </div>
                    <div class="align-self-center">
                        <i class="ti ti-currency-dollar font-size-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-white-50 mb-0">Outstanding</p>
                        <h4 class="mb-0"><?= $stats['outstanding_loans'] ?? 0 ?></h4>
                    </div>
                    <div class="align-self-center">
                        <i class="ti ti-alert-triangle font-size-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 class="card-title mb-0">My Loan Requests</h4>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <a href="<?= APP_URL ?>/finance/worker-loans/create" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i> Request New Loan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th>Request Amount</th>
                                <th>Disbursed Amount</th>
                                <th>Outstanding</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($loans)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="ti ti-folder-open font-size-48 text-muted"></i>
                                        <p class="text-muted mt-2 mb-3">No loan requests found</p>
                                        <a href="<?= APP_URL ?>/finance/worker-loans/create" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i> Request Your First Loan
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($loans as $loan): ?>
                                    <tr>
                                        <td><strong>#<?= $loan['l_id'] ?></strong></td>
                                        <td>
                                            <span class="fw-bold text-primary">
                                                <?= number_format($loan['request_amount']) ?> RWF
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                <?= number_format($loan['payed_amount']) ?> RWF
                                            </span>
                                        </td>
                                        <td>
                                            <?php $outstanding = $loan['request_amount'] - $loan['payed_amount']; ?>
                                            <span class="fw-bold <?= $outstanding > 0 ? 'text-danger' : 'text-muted' ?>">
                                                <?= number_format($outstanding) ?> RWF
                                            </span>
                                        </td>
                                        <td>
                                            <span title="<?= htmlspecialchars($loan['description']) ?>">
                                                <?= htmlspecialchars(substr($loan['description'], 0, 40)) ?>
                                                <?= strlen($loan['description']) > 40 ? '...' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'outstanding' => 'danger', 
                                                'disbursed' => 'success'
                                            ];
                                            $statusColor = $statusColors[$loan['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $statusColor ?>">
                                                <?= ucfirst($loan['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= APP_URL ?>/finance/worker-loans/<?= $loan['l_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($loans)): ?>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Loan Summary</h5>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary"><?= number_format($stats['total_requested'] ?? 0) ?> RWF</h4>
                            <p class="text-muted mb-0">Total Requested</p>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success"><?= number_format($stats['total_disbursed'] ?? 0) ?> RWF</h4>
                            <p class="text-muted mb-0">Total Received</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Loan Guidelines</h5>
                    <ul class="mb-0">
                        <li>Only one pending request at a time</li>
                        <li>All requests require approval</li>
                        <li>Outstanding loans must be repaid</li>
                        <li>Contact HR for repayment plans</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.card.bg-primary,
.card.bg-warning,
.card.bg-success,
.card.bg-info {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    padding: 6px 12px;
    font-size: 0.75rem;
}

.btn-outline-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>