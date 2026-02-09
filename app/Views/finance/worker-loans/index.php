<?php
$title = $title ?? 'Worker Loans';
$loans = $loans ?? [];
$pagination = $pagination ?? [];
$stats = $stats ?? [];
$search = $search ?? '';
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-white-50 mb-0">Total Loans</p>
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
        <div class="card bg-danger text-white">
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
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-white-50 mb-0">Disbursed</p>
                        <h4 class="mb-0"><?= $stats['disbursed_loans'] ?? 0 ?></h4>
                    </div>
                    <div class="align-self-center">
                        <i class="ti ti-check font-size-24"></i>
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
                        <h4 class="card-title mb-0">Worker Loans</h4>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= APP_URL ?>/finance/worker-loans/create" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i> Request Loan
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search Form -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" 
                                   placeholder="Search loans..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= APP_URL ?>/finance/worker-loans" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Request Amount</th>
                                <th>Payed Amount</th>
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
                                        <p class="text-muted mt-2">No loans found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($loans as $loan): ?>
                                    <tr>
                                        <td><?= $loan['l_id'] ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($loan['email']) ?></small>
                                            </div>
                                        </td>
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
                                            <span title="<?= htmlspecialchars($loan['description']) ?>">
                                                <?= htmlspecialchars(substr($loan['description'], 0, 50)) ?>
                                                <?= strlen($loan['description']) > 50 ? '...' : '' ?>
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
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/finance/worker-loans/<?= $loan['l_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <?php if (in_array('manage-worker-loans', $_SESSION['user']['permissions'] ?? [])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            onclick="openUpdateStatusModal(<?= $loan['l_id'] ?>, '<?= $loan['status'] ?>', <?= $loan['payed_amount'] ?>)"
                                                            title="Update Status">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <form method="POST" action="<?= APP_URL ?>/finance/worker-loans/<?= $loan['l_id'] ?>/delete" 
                                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this loan?')">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> to 
                                <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                                of <?= $pagination['total'] ?> entries
                            </small>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
                        <label for="payed_amount" class="form-label">Payed Amount</label>
                        <input type="number" class="form-control" id="payed_amount" name="payed_amount" min="0" step="1">
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
let currentLoanId = null;

function openUpdateStatusModal(loanId, currentStatus, payedAmount) {
    currentLoanId = loanId;
    document.getElementById('status').value = currentStatus;
    document.getElementById('payed_amount').value = payedAmount;
    
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
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