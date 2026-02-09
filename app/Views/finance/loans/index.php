<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Manage Loan Applications</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Manage Loans</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Loan Applications</h4>
            </div>
            <div class="card-body">
                <?php if (empty($loans)): ?>
                <div class="text-center py-5">
                    <i class="ti ti-file-x fs-48 text-muted mb-3"></i>
                    <h5 class="text-muted">No loan applications found</h5>
                    <p class="text-muted">No workers have submitted loan requests yet.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Worker</th>
                                <th>Email</th>
                                <th>Amount Requested</th>
                                <th>Amount Paid</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?= $loan['l_id'] ?></td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($loan['email']) ?></td>
                                <td>
                                    <strong class="text-primary">RWF <?= number_format($loan['request_amount']) ?></strong>
                                </td>
                                <td>
                                    RWF <?= number_format($loan['payed_amount']) ?>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" 
                                         title="<?= htmlspecialchars($loan['description']) ?>">
                                        <?= htmlspecialchars(substr($loan['description'], 0, 50)) ?><?= strlen($loan['description']) > 50 ? '...' : '' ?>
                                    </div>
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
                                    <div class="btn-group" role="group">
                                        <?php if ($loan['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="updateLoanStatus(<?= $loan['l_id'] ?>, 'outstanding')"
                                                title="Approve Loan">
                                            <i class="ti ti-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="updateLoanStatus(<?= $loan['l_id'] ?>, 'rejected')"
                                                title="Reject Loan">
                                            <i class="ti ti-x"></i> Reject
                                        </button>
                                        <?php elseif ($loan['status'] === 'outstanding'): ?>
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="updateLoanStatus(<?= $loan['l_id'] ?>, 'disbursed')"
                                                title="Mark as Disbursed">
                                            <i class="ti ti-cash"></i> Mark Paid
                                        </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewLoanDetails(<?= $loan['l_id'] ?>)"
                                                title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </button>
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

<!-- Status Update Form (Hidden) -->
<form id="statusUpdateForm" action="<?= APP_URL ?>/finance/loans/status" method="POST" style="display: none;">
    <input type="hidden" id="loanId" name="id" value="">
    <input type="hidden" id="newStatus" name="status" value="">
</form>

<script>
function updateLoanStatus(loanId, status) {
    if (confirm('Are you sure you want to update this loan status?')) {
        document.getElementById('loanId').value = loanId;
        document.getElementById('newStatus').value = status;
        document.getElementById('statusUpdateForm').submit();
    }
}

function viewLoanDetails(loanId) {
    window.location.href = '<?= APP_URL ?>/finance/loans/' + loanId;
}
</script>