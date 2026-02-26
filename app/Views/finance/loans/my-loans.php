<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">My Loan Requests</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">My Loans</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Loan History</h4>
                <a href="<?= APP_URL ?>/finance/loans/create" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    New Request
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($loans)): ?>
                <div class="text-center py-5">
                    <i class="ti ti-file-x fs-48 text-muted mb-3"></i>
                    <h5 class="text-muted">No loan requests found</h5>
                    <p class="text-muted">You haven't submitted any loan requests yet.</p>
                    <a href="<?= APP_URL ?>/finance/loans/create" class="btn btn-primary">
                        Submit Your First Request
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Request Date</th>
                                <th>Amount Requested</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td>
                                    <?php 
                                    if (!empty($loan['created_at'])) {
                                        $date = new DateTime($loan['created_at']);
                                        echo $date->format('M d, Y');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <strong>RWF <?= number_format($loan['request_amount']) ?></strong>
                                </td>
                                <td>
                                    RWF <?= number_format($loan['payed_amount']) ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($loan['status']) {
                                        'pending' => 'warning',
                                        'outstanding' => 'success',
                                        'disbursed' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= ucfirst($loan['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" 
                                         title="<?= htmlspecialchars($loan['description']) ?>">
                                        <?= htmlspecialchars($loan['description']) ?>
                                    </div>
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