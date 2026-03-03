<?php
/**
 * View All Accounts - Reconciliation
 */ 
$pageTitle = 'View All Accounts - Reconciliation';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <a href="<?= APP_URL ?>/reconciliation" class="text-decoration-none me-2">
                <i class="fas fa-arrow-left"></i>
            </a>
            View All Accounts
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Filter -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Search accounts..." id="accountSearch">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="reconciliationFilter">
                <option value="">All Reconciliation Status</option>
                <option value="reconciled">Reconciled</option>
                <option value="pending">Pending</option>
                <option value="discrepancy">Discrepancy</option>
            </select>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="accountsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Current Balance</th>
                            <th>Currency</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Reconciliation Status</th>
                            <th>Last Reconciled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($account['account_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= htmlspecialchars($account['account_number']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold <?= $account['current_balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $account['currency'] . number_format($account['current_balance'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($account['currency']) ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($account['location']) ?>
                                    </td>
                                    <td>
                                        <?php if ($account['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-warning';
                                        if ($account['reconciliation_status'] === 'Reconciled') $statusClass = 'bg-success';
                                        elseif ($account['reconciliation_status'] === 'Discrepancy') $statusClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $account['reconciliation_status'] ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($account['last_reconciled']) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    title="View Details" onclick="viewAccountDetails(<?= $account['account_id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success" 
                                                    title="Reconcile" onclick="reconcileAccount(<?= $account['account_id'] ?>)">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" 
                                                    title="History" onclick="viewHistory(<?= $account['account_id'] ?>)">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No accounts found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <h6>Total Accounts</h6>
                            <h4 class="text-primary"><?= count($accounts) ?></h4>
                        </div>
                        <div class="col-md-2">
                            <h6>Active Accounts</h6>
                            <h4 class="text-success"><?= count(array_filter($accounts, fn($a) => $a['status'] === 'active')) ?></h4>
                        </div>
                        <div class="col-md-2">
                            <h6>Pending Reconciliation</h6>
                            <h4 class="text-warning"><?= count(array_filter($accounts, fn($a) => $a['reconciliation_status'] === 'Pending')) ?></h4>
                        </div>
                        <div class="col-md-3">
                            <h6>Total Balance</h6>
                            <h4 class="text-info">
                                $<?= number_format(array_sum(array_column($accounts, 'current_balance')), 2) ?>
                            </h4>
                        </div>
                        <div class="col-md-3">
                            <h6>Last Updated</h6>
                            <p class="mb-0 text-muted"><?= date('Y-m-d H:i:s') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Search functionality
document.getElementById('accountSearch').addEventListener('input', function() {
    filterTable();
});

// Filter functionality
document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('reconciliationFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('accountSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const reconciliationFilter = document.getElementById('reconciliationFilter').value;
    const table = document.getElementById('accountsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const accountName = row.cells[0].textContent.toLowerCase();
        const accountNumber = row.cells[1].textContent.toLowerCase();
        const status = row.cells[5].textContent.toLowerCase();
        const reconciliationStatus = row.cells[6].textContent.toLowerCase();

        let showRow = true;

        // Search filter
        if (searchTerm && !accountName.includes(searchTerm) && !accountNumber.includes(searchTerm)) {
            showRow = false;
        }

        // Status filter
        if (statusFilter && !status.includes(statusFilter)) {
            showRow = false;
        }

        // Reconciliation filter
        if (reconciliationFilter && !reconciliationStatus.includes(reconciliationFilter)) {
            showRow = false;
        }

        row.style.display = showRow ? '' : 'none';
    }
}

// Action functions (placeholders for future implementation)
function viewAccountDetails(accountId) {
    alert('View details for account ID: ' + accountId + '\nThis feature will be implemented later.');
}

function reconcileAccount(accountId) {
    alert('Reconcile account ID: ' + accountId + '\nThis feature will be implemented later.');
}

function viewHistory(accountId) {
    alert('View history for account ID: ' + accountId + '\nThis feature will be implemented later.');
}
</script>

<style>
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table-hover tbody tr:hover td {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>