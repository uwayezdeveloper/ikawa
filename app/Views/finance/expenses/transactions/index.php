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
                    <?php if (!empty($_GET['date_from']) || !empty($_GET['date_to'])): ?>
                    <div class="alert alert-info mb-2">
                        <strong>Date Filter:</strong>
                        <?php if (!empty($_GET['date_from'])): ?>
                            From <b><?= htmlspecialchars($_GET['date_from']) ?></b>
                        <?php endif; ?>
                        <?php if (!empty($_GET['date_to'])): ?>
                            To <b><?= htmlspecialchars($_GET['date_to']) ?></b>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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
                        <?php if (!empty($_GET['statement'])): ?>
                        <button id="export-pdf-btn" class="btn btn-outline-danger">
                            <i class="ti ti-file-download me-1"></i>Export PDF
                        </button>
                        <?php else: ?>
                        <a href="<?= APP_URL ?>/finance/expense-transactions/export" class="btn btn-outline-success">
                            <i class="ti ti-download me-1"></i>Export CSV
                        </a>
                        <?php endif; ?>
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
                    <div class="col-md-8">
                        <form method="GET" action="<?= APP_URL ?>/finance/expense-transactions" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search transactions..." 
                                       value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label mb-0">From</label>
                                <input type="date" class="form-control" name="date_from" id="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label mb-0">To</label>
                                <input type="date" class="form-control" name="date_to" id="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button class="btn btn-outline-primary w-100" type="submit">
                                    <i class="ti ti-search"></i> Filter
                                </button>
                                <?php if (!empty($search) || !empty($_GET['date_from']) || !empty($_GET['date_to'])): ?>
                                    <a href="<?= APP_URL ?>/finance/expense-transactions" class="btn btn-outline-secondary">
                                        <i class="ti ti-x"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="text-muted">Total: <?= $pagination['total'] ?? 0 ?> transactions</span>
                    </div>
                </div>

                <!-- Transactions Table -->
                <?php if (!empty($transactions)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="expense-transactions-table">
                            </div>
                            <?php if (!empty($_GET['statement'])): ?>
                                <!-- jsPDF CDN -->
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                                <!-- jsPDF AutoTable CDN -->
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
                                <script src="<?= APP_URL ?>/assets/js/pages/expense-transactions-pdf.js"></script>
                            <?php endif; ?>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Expense Type</th>
                                    <th>Consumer</th>
                                    <th>Amount</th>
                                    <th>Charges</th>
                                    <th>Account</th>
                                    <?php if (empty($_GET['statement'])): ?>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $counter = (($pagination['current_page'] - 1) * $pagination['per_page']) + 1;
                                    $totalAmount = 0;
                                    $totalCharges = 0;
                                ?>
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
                                            <?php $totalAmount += (float)($transaction['amount'] ?? 0); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                // Try to get charges from details if available, else fallback to 0
                                                $charges = 0;
                                                if (isset($transaction['charges'])) {
                                                    $charges = (float)$transaction['charges'];
                                                } elseif (isset($transaction['details']) && is_array($transaction['details'])) {
                                                    foreach ($transaction['details'] as $detail) {
                                                        if (isset($detail['action']) && $detail['action'] === 'CHARGES') {
                                                            $charges = (float)$detail['charges'];
                                                            break;
                                                        }
                                                    }
                                                }
                                                $totalCharges += $charges;
                                            ?>
                                            <span><?= number_format($charges, 2) ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($transaction['account_names'])): ?>
                                                <?php foreach ($transaction['account_names'] as $idx => $accName): ?>
                                                    <span class="badge bg-secondary mb-1">
                                                        <?= htmlspecialchars($accName) ?>
                                                        <?php 
                                                            $aid = $transaction['account_ids'][$idx] ?? null;
                                                            $charge = ($aid && isset($transaction['account_charges'][$aid])) ? $transaction['account_charges'][$aid] : 0;
                                                        ?>
                                                        <?php if ($charge > 0): ?>
                                                            <span class="small" style="color:#fff;">(Charge: <?= number_format($charge, 2) ?>)</span>
                                                        <?php endif; ?>
                                                    </span><br>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (empty($_GET['statement'])): ?>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-id="<?= $transaction['con_id'] ?>"
                                                       <?= $transaction['status'] == 1 ? 'checked' : '' ?> >
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
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!empty($_GET['statement'])): ?>
                                    <tr style="font-weight:bold;background:#f8f9fa;">
                                        <td colspan="5" class="text-end">Totals:</td>
                                        <td><?= number_format($totalAmount, 2) ?></td>
                                        <td><?= number_format($totalCharges, 2) ?></td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>
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