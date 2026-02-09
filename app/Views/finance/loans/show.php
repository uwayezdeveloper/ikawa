<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Loan Application Details</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loans">Manage Loans</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Loan Request #<?= $loan['l_id'] ?></h4>
                <a href="<?= APP_URL ?>/finance/loans" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Back to List
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Worker Information</h6>
                        <p><strong>Name:</strong> <?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($loan['email']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Loan Information</h6>
                        <p><strong>Request Date:</strong> 
                            <?php 
                            if (!empty($loan['created_at'])) {
                                $date = new DateTime($loan['created_at']);
                                echo $date->format('F d, Y \a\t g:i A');
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </p>
                        <p><strong>Status:</strong> 
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
                        </p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Amount Details</h6>
                        <p><strong>Requested Amount:</strong> <span class="text-primary fs-5">RWF <?= number_format($loan['request_amount']) ?></span></p>
                        <p><strong>Amount Paid:</strong> <span class="text-success">RWF <?= number_format($loan['payed_amount']) ?></span></p>
                        <?php if ($loan['payed_amount'] < $loan['request_amount']): ?>
                        <p><strong>Outstanding Balance:</strong> <span class="text-warning">RWF <?= number_format($loan['request_amount'] - $loan['payed_amount']) ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 class="fw-bold">Description/Reason</h6>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($loan['description'])) ?>
                    </div>
                </div>
                
                <?php if ($loan['status'] === 'pending'): ?>
                <hr>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-success" 
                            onclick="updateLoanStatus(<?= $loan['l_id'] ?>, 'outstanding')">
                        <i class="ti ti-check me-1"></i>
                        Approve Loan
                    </button>
                    <button type="button" class="btn btn-danger" 
                            onclick="updateLoanStatus(<?= $loan['l_id'] ?>, 'rejected')">
                        <i class="ti ti-x me-1"></i>
                        Reject Loan
                    </button>
                </div>
                <?php elseif ($loan['status'] === 'outstanding'): ?>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-info" 
                            onclick="updateLoanStatus(<?= $loan['l_id'] ?>, 'disbursed')">
                        <i class="ti ti-cash me-1"></i>
                        Mark as Disbursed/Paid
                    </button>
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
    const statusText = status === 'outstanding' ? 'approve' : 
                      status === 'disbursed' ? 'mark as disbursed' : 'reject';
    
    if (confirm(`Are you sure you want to ${statusText} this loan?`)) {
        document.getElementById('loanId').value = loanId;
        document.getElementById('newStatus').value = status;
        document.getElementById('statusUpdateForm').submit();
    }
}
</script>