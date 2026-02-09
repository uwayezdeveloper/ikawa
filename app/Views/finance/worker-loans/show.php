<?php
$title = $title ?? 'Loan Details';
$loan = $loan ?? [];
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

<?php if (empty($loan)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-alert-triangle font-size-48 text-warning"></i>
                    <h4 class="mt-3">Loan Not Found</h4>
                    <p class="text-muted">The requested loan could not be found.</p>
                    <a href="<?= APP_URL ?>/finance/worker-loans" class="btn btn-primary">Back to Loans</a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Loan Details #<?= $loan['l_id'] ?></h4>
                        <?php
                        $statusColors = [
                            'pending' => 'warning',
                            'outstanding' => 'danger',
                            'disbursed' => 'success'
                        ];
                        $statusColor = $statusColors[$loan['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $statusColor ?> fs-6">
                            <?= ucfirst($loan['status']) ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Employee Information</h6>
                            <div class="mb-3">
                                <strong>Name:</strong><br>
                                <?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong><br>
                                <?= htmlspecialchars($loan['email']) ?>
                            </div>
                            <?php if (!empty($loan['phone'])): ?>
                                <div class="mb-3">
                                    <strong>Phone:</strong><br>
                                    <?= htmlspecialchars($loan['phone']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Loan Information</h6>
                            <div class="mb-3">
                                <strong>Requested Amount:</strong><br>
                                <span class="text-primary fs-5 fw-bold">
                                    <?= number_format($loan['request_amount']) ?> RWF
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Disbursed Amount:</strong><br>
                                <span class="text-success fs-5 fw-bold">
                                    <?= number_format($loan['payed_amount']) ?> RWF
                                </span>
                            </div>
                            <?php if ($loan['request_amount'] > $loan['payed_amount']): ?>
                                <div class="mb-3">
                                    <strong>Outstanding Amount:</strong><br>
                                    <span class="text-danger fs-5 fw-bold">
                                        <?= number_format($loan['request_amount'] - $loan['payed_amount']) ?> RWF
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">Description/Purpose</h6>
                            <div class="border p-3 rounded">
                                <?= nl2br(htmlspecialchars($loan['description'])) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($loan['status'] === 'outstanding' || $loan['status'] === 'disbursed'): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="progress mb-2">
                                    <?php $percentage = $loan['request_amount'] > 0 ? ($loan['payed_amount'] / $loan['request_amount'] * 100) : 0; ?>
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $percentage ?>%" 
                                         aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?= number_format($percentage, 1) ?>% disbursed 
                                    (<?= number_format($loan['payed_amount']) ?> of <?= number_format($loan['request_amount']) ?> RWF)
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Actions</h5>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= APP_URL ?>/finance/worker-loans" class="btn btn-outline-primary">
                            <i class="ti ti-arrow-left me-1"></i> Back to Loans
                        </a>
                        
                        <?php if (in_array('manage-worker-loans', $_SESSION['user']['permissions'] ?? [])): ?>
                            <button type="button" class="btn btn-warning" 
                                    onclick="openUpdateStatusModal(<?= $loan['l_id'] ?>, '<?= $loan['status'] ?>', <?= $loan['payed_amount'] ?>)">
                                <i class="ti ti-edit me-1"></i> Update Status
                            </button>
                            
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="ti ti-trash me-1"></i> Delete Loan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($loan['status'] === 'pending'): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Pending Review</strong><br>
                            This loan request is waiting for management approval.
                        </div>
                    </div>
                </div>
            <?php elseif ($loan['status'] === 'outstanding'): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Outstanding Loan</strong><br>
                            Amount remaining: <?= number_format($loan['request_amount'] - $loan['payed_amount']) ?> RWF
                        </div>
                    </div>
                </div>
            <?php elseif ($loan['status'] === 'disbursed'): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="alert alert-success mb-0">
                            <i class="ti ti-check me-2"></i>
                            <strong>Loan Disbursed</strong><br>
                            Full amount has been disbursed.
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Loan Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="outstanding">Outstanding</option>
                            <option value="disbursed">Disbursed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payed_amount" class="form-label">Disbursed Amount</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="payed_amount" name="payed_amount" 
                                   min="0" max="<?= $loan['request_amount'] ?? 0 ?>" step="1">
                            <span class="input-group-text">RWF</span>
                        </div>
                        <div class="form-text">Maximum: <?= number_format($loan['request_amount'] ?? 0) ?> RWF</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentLoanId = <?= $loan['l_id'] ?? 0 ?>;

function openUpdateStatusModal(loanId, currentStatus, payedAmount) {
    currentLoanId = loanId;
    document.getElementById('status').value = currentStatus;
    document.getElementById('payed_amount').value = payedAmount;
    
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this loan? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= APP_URL ?>/finance/worker-loans/<?= $loan['l_id'] ?>/delete`;
        document.body.appendChild(form);
        form.submit();
    }
}

document.getElementById('updateStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        status: formData.get('status'),
        payed_amount: parseInt(formData.get('payed_amount')) || 0
    };
    
    try {
        const response = await fetch(`<?= APP_URL ?>/finance/worker-loans/${currentLoanId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('An error occurred while updating the loan status');
    }
});
</script>