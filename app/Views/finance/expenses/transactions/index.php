<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Expense Transactions') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Expense Transactions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-primary-subtle rounded">
                            <span class="avatar-title text-primary">
                                <i class="ti ti-receipt fs-22"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Total Transactions</p>
                        <h5 class="mb-0"><?= number_format($stats['total_transactions'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-success-subtle rounded">
                            <span class="avatar-title text-success">
                                <i class="ti ti-currency-dollar fs-22"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Total Amount</p>
                        <h5 class="mb-0"><?= number_format($stats['total_amount'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-info-subtle rounded">
                            <span class="avatar-title text-info">
                                <i class="ti ti-trending-up fs-22"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Average Amount</p>
                        <h5 class="mb-0"><?= number_format($stats['avg_amount'] ?? 0, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-warning-subtle rounded">
                            <span class="avatar-title text-warning">
                                <i class="ti ti-check-circle fs-22"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Active Transactions</p>
                        <h5 class="mb-0"><?= number_format($stats['active_transactions'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Expense Transactions</h5>
                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>/finance/expense-transactions/export" class="btn btn-outline-success">
                            <i class="ti ti-download me-1"></i>Export
                        </a>
                        <a href="<?= APP_URL ?>/finance/expense-transactions/create" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>New Transaction
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="<?= APP_URL ?>/finance/expense-transactions" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" 
                                   placeholder="Search transactions..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="ti ti-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= APP_URL ?>/finance/expense-transactions" class="btn btn-outline-secondary ms-2">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted">Total: <?= $pagination['total'] ?? 0 ?> transactions</span>
                    </div>
                </div>

                <!-- Transactions Table -->
                <?php if (!empty($transactions)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Expense Type</th>
                                    <th>Consumer</th>
                                    <th>Amount</th>
                                    <th>Account</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($transaction['trans_id']) ?></strong>
                                        </td>
                                        <td>
                                            <small><?= date('M j, Y H:i', strtotime($transaction['pay_date'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($transaction['expense_name'] ?? 'N/A') ?></span>
                                        </td>
                                        <td>
                                            <?php if ($transaction['consumer_name']): ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($transaction['consumer_name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($transaction['consumer_phone'] ?? '') ?></small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No Consumer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="text-success"><?= number_format($transaction['amount'] ?? 0) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($transaction['account_name'] ?? 'N/A') ?></span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-id="<?= $transaction['con_id'] ?>"
                                                       <?= $transaction['status'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label">
                                                    <span class="badge bg-<?= $transaction['status'] == 1 ? 'success' : 'danger' ?>">
                                                        <?= $transaction['status'] == 1 ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/finance/expense-transactions/<?= $transaction['con_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Transactions pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-transactions?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $pagination['current_page'] - 2);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-transactions?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-transactions?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="ti ti-receipt-off display-4 text-muted"></i>
                        </div>
                        <h5 class="text-muted">No transactions found</h5>
                        <p class="text-muted">
                            <?= !empty($search) ? 'No transactions match your search criteria.' : 'Start by creating your first expense transaction.' ?>
                        </p>
                        <?php if (empty($search)): ?>
                            <a href="<?= APP_URL ?>/finance/expense-transactions/create" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Create First Transaction
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status toggle
    const statusToggles = document.querySelectorAll('.status-toggle');
    statusToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const transactionId = this.dataset.id;
            const isChecked = this.checked;
            
            fetch(`<?= APP_URL ?>/finance/expense-transactions/${transactionId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `status=${isChecked ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update badge
                    const badge = this.nextElementSibling.querySelector('.badge');
                    if (isChecked) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'badge bg-danger';
                        badge.textContent = 'Inactive';
                    }
                    
                    // Show success message
                    showAlert('success', data.message);
                } else {
                    // Revert toggle
                    this.checked = !isChecked;
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                // Revert toggle
                this.checked = !isChecked;
                showAlert('error', 'An error occurred while updating status');
                console.error('Error:', error);
            });
        });
    });
});

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.row').parentNode;
    const firstRow = container.querySelector('.row');
    container.insertBefore(alertDiv, firstRow);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>