<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Payment Details</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loan-payments">Loan Payments</a></li>
                    <li class="breadcrumb-item active">Payment Details</li>
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
                    <h4 class="card-title mb-0">Payment Details</h4>
                    <a href="<?= APP_URL ?>/finance/loan-payments" class="btn btn-secondary btn-sm">
                        <i class="ri-arrow-left-line"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Payment Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Payment ID:</th>
                                    <td><?= $payment['p_id'] ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Amount:</th>
                                    <td><strong class="text-success">RWF <?= number_format($payment['payed_amount']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td><?= date('d M Y H:i', strtotime($payment['due_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Pay Account:</th>
                                    <td>
                                        <?= htmlspecialchars($payment['pay_account_name'] ?? 'N/A') ?>
                                        <?php if ($payment['pay_account_number'] ?? ''): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($payment['pay_account_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Receiving Account:</th>
                                    <td>
                                        <?= htmlspecialchars($payment['debited_account_name'] ?? 'N/A') ?>
                                        <?php if ($payment['debited_account_number'] ?? ''): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($payment['debited_account_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($payment['description'] ?? ''): ?>
                                <tr>
                                    <th>Description:</th>
                                    <td><?= htmlspecialchars($payment['description']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Loan Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Worker:</th>
                                    <td>
                                        <strong><?= htmlspecialchars(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?></strong>
                                        <?php if ($payment['email'] ?? ''): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($payment['email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Loan Amount:</th>
                                    <td><strong class="text-primary">RWF <?= number_format($payment['request_amount'] ?? 0) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Total Paid:</th>
                                    <td><strong class="text-success">RWF <?= number_format($payment['total_paid'] ?? 0) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Remaining Balance:</th>
                                    <td>
                                        <?php 
                                        $remaining = ($payment['request_amount'] ?? 0) - ($payment['total_paid'] ?? 0);
                                        $class = $remaining > 0 ? 'text-danger' : 'text-success';
                                        ?>
                                        <strong class="<?= $class ?>">RWF <?= number_format($remaining) ?></strong>
                                    </td>
                                </tr>
                                <?php if ($payment['loan_description'] ?? ''): ?>
                                <tr>
                                    <th>Loan Description:</th>
                                    <td><?= htmlspecialchars($payment['loan_description']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                            
                            <?php if ($remaining <= 0): ?>
                                <div class="alert alert-success">
                                    <i class="ri-check-circle-line"></i> This loan has been fully paid!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="<?= APP_URL ?>/finance/loan-payments" class="btn btn-primary">
                                    <i class="ri-arrow-left-line"></i> Back to Payments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>