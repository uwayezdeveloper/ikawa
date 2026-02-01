<?php
require_once __DIR__ . '/../../_ikawa/config/App.php';
require_once __DIR__ . '/../../_ikawa/models/Investment.php';

// Load rejected investments directly from model
$rejectedInvestments = [];
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    $investmentModel = new Models\Investment();
    $rejectedInvestments = $investmentModel->getRejectedInvestmentsByUser($_SESSION['user_id']);
}
?>

<!-- Page header -->
<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-close"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>My Rejected Investments</h2>
                                    <p>View your rejected investment requests and reasons</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 text-right">
                            <div class="breadcomb-report">
                                <button type="button" id="reloadRejectedBtn" class="btn btn-default"><i class="fa fa-refresh"></i> Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejected Investments table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="data-table-list">
                    <div class="table-responsive">
                        <table id="rejectedInvestmentsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Source</th>
                                    <th>Receipt</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Rejection Reason</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody id="rejectedInvestmentsList">
                                <?php if (empty($rejectedInvestments)): ?>
                                    <tr><td colspan="9" class="text-center">No rejected investments found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($rejectedInvestments as $inv): ?>
                                        <?php
                                            $statusText = 'Pending';
                                            $statusClass = 'text-warning';
                                            if ($inv['sts'] == 2) {
                                                $statusText = 'Approved';
                                                $statusClass = 'text-success';
                                            } else if ($inv['sts'] == 1 && !empty($inv['rejectorComment'])) {
                                                $statusText = 'Rejected';
                                                $statusClass = 'text-danger';
                                            }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars(substr($inv['done_at'], 0, 10)) ?></td>
                                            <td><?= htmlspecialchars($inv['acc_name'] ?? 'N/A') ?></td>
                                            <td><strong><?= number_format($inv['in_amount']) ?> RWF</strong></td>
                                            <td><?= htmlspecialchars($inv['source'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($inv['reciept'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($inv['description'] ?? '-') ?></td>
                                            <td><span class="<?= $statusClass ?>"><?= $statusText ?></span></td>
                                            <td><span class="text-danger"><?= htmlspecialchars($inv['rejectorComment'] ?? 'No reason provided') ?></span></td>
                                            <td><?= htmlspecialchars($inv['location_name'] ?? 'N/A') ?></td>
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
</div>
