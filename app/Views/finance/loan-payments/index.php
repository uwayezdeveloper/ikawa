<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Worker Loan Payments</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Loan Payments</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Worker Loan Payments</h4>
                    <a href="<?= APP_URL ?>/finance/loan-payments/create" class="btn btn-primary btn-sm">
                        <i class="ri-add-line"></i> Pay Loan
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info text-center">
                            <i class="ri-information-line"></i> No loan payments found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="payments-table">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Worker</th>
                                        <th>Loan Amount</th>
                                        <th>Payment Amount</th>
                                        <th>Pay Account</th>
                                        <th>Received Account</th>
                                        <th>Payment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= $payment['p_id'] ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($payment['email'] ?? '') ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-primary">RWF <?= number_format($payment['request_amount'] ?? 0) ?></strong>
                                            <br>
                                            <small class="text-muted">Total Paid: RWF <?= number_format($payment['total_paid'] ?? 0) ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-success">RWF <?= number_format($payment['payed_amount']) ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($payment['pay_account_name'] ?? 'N/A') ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($payment['pay_account_number'] ?? '') ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($payment['debited_account_name'] ?? 'N/A') ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($payment['debited_account_number'] ?? '') ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?= date('d M Y H:i', strtotime($payment['due_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/finance/loan-payments/<?= $payment['p_id'] ?>" 
                                                   class="btn btn-info btn-sm" title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </a>
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
</div>

<script>
$(document).ready(function() {
    $('#payments-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });
});
</script>