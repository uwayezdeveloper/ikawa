<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Transaction Details</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/expense-transactions">Expense Transactions</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-receipt me-2"></i>Transaction: <?= htmlspecialchars($transaction['trans_id']) ?>
                </h5>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Transaction Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td><?= htmlspecialchars($transaction['trans_id']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Date & Time:</strong></td>
                                <td><?= date('M j, Y H:i:s', strtotime($transaction['pay_date'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Recorded Date:</strong></td>
                                <td><?= date('M j, Y', strtotime($transaction['recorded_date'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Expense Type:</strong></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($transaction['expense_name'] ?? 'N/A') ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td><strong class="text-success">$<?= number_format($transaction['amount']) ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-<?= $transaction['status'] == 1 ? 'success' : 'danger' ?>">
                                        <?= $transaction['status'] == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Additional Details</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Consumer:</strong></td>
                                <td>
                                    <?php if ($transaction['consumer_name']): ?>
                                        <?= htmlspecialchars($transaction['consumer_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($transaction['consumer_phone'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">No Consumer</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td><?= htmlspecialchars($transaction['location_name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Account(s):</strong></td>
                                <td>
                                    <?php if (!empty($payments)): ?>
                                        <?php if (count($payments) > 1): ?>
                                            <!-- Multiple payments -->
                                            <div class="d-flex flex-column">
                                                <?php foreach ($payments as $payment): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="badge bg-secondary me-2"><?= htmlspecialchars($payment['account_name']) ?></span>
                                                        <strong class="text-success">$<?= number_format($payment['amount'], 2) ?></strong>
                                                    </div>
                                                <?php endforeach; ?>
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <strong>Total Payments:</strong>
                                                    <strong class="text-primary">$<?= number_format(array_sum(array_column($payments, 'amount')), 2) ?></strong>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Single payment -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-secondary"><?= htmlspecialchars($payments[0]['account_name']) ?></span>
                                                <strong class="text-success">$<?= number_format($payments[0]['amount'], 2) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($transaction['account_name'] ?? 'N/A') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Receipt Type:</strong></td>
                                <td><?= htmlspecialchars($transaction['receipt_type_name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td><?= !empty($transaction['description']) ? htmlspecialchars($transaction['description']) : '<span class="text-muted">No description</span>' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Transaction Details Breakdown -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Transaction Breakdown</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($details)): ?>
                    <?php foreach ($details as $detail): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <strong><?= htmlspecialchars($detail['action']) ?></strong><br>
                                <small class="text-muted">
                                    by <?= htmlspecialchars(($detail['first_name'] ?? '') . ' ' . ($detail['last_name'] ?? '')) ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <?php if ($detail['action'] === 'AMOUNT'): ?>
                                    <strong class="text-success">$<?= number_format($detail['amount']) ?></strong>
                                <?php else: ?>
                                    <strong class="text-warning">$<?= number_format($detail['charges']) ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <strong>Total:</strong>
                        <strong class="text-primary">$<?= number_format($transaction['amount']) ?></strong>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No breakdown details available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/finance/expense-transactions" class="btn btn-outline-primary">
                        <i class="ti ti-arrow-left me-1"></i>Back to List
                    </a>
                    <button class="btn btn-outline-success" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>