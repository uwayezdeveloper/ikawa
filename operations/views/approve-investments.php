<?php
require_once __DIR__ . '/../../_ikawa/config/App.php';

// Load pending investments directly on page load
$pendingInvestments = [];
$apiUrl = App::baseUrl() . '/_ikawa/investments/pending';
$response = fetchApiData($apiUrl);
if ($response !== false) {
    $decoded = json_decode($response, true);
    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
        $pendingInvestments = $decoded['data'] ?? [];
    }
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
                                    <i class="notika-icon notika-checked"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Approve Investments</h2>
                                    <p>Review and approve or reject pending investment requests</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 text-right">
                            <div class="breadcomb-report">
                                <button type="button" id="reloadPendingBtn" class="btn btn-default"><i class="fa fa-refresh"></i> Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Investments table -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="data-table-list">
                    <div class="table-responsive">
                        <table id="pendingInvestmentsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Source</th>
                                    <th>Receipt</th>
                                    <th>Description</th>
                                    <th>Requested By</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pendingInvestmentsList">
                                <?php if (empty($pendingInvestments)): ?>
                                    <tr><td colspan="9" class="text-center">No pending investments</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pendingInvestments as $inv): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(substr($inv['done_at'], 0, 10)) ?></td>
                                            <td>
                                                <?= htmlspecialchars($inv['acc_name'] ?? 'N/A') ?>
                                                <small class="text-muted">(ID: <?= $inv['account_id'] ?>)</small>
                                            </td>
                                            <td><strong><?= number_format($inv['in_amount']) ?> RWF</strong></td>
                                            <td><?= htmlspecialchars($inv['source'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($inv['reciept'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($inv['description'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($inv['username'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($inv['location_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <button class="btn btn-success btn-sm approve-btn" data-id="<?= $inv['in_id'] ?>" data-account-id="<?= $inv['account_id'] ?>" title="Approve"><i class="fa fa-check"></i> Approve</button>
                                                <button class="btn btn-danger btn-sm reject-btn" data-id="<?= $inv['in_id'] ?>" title="Reject"><i class="fa fa-times"></i> Reject</button>
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
</div>

<!-- Reject Modal -->
<div class="modal animated bounce" id="rejectModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Investment</h4>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="reject_in_id" />
                    <div class="form-group">
                        <label>Rejection Reason *</label>
                        <textarea id="rejector_comment" class="form-control" rows="4" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmRejectBtn" class="btn btn-danger">Confirm Rejection</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
