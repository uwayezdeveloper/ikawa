<?php
/**
 * Account Reconciliation Main Page
 */
$pageTitle = 'Account Reconciliation';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Account Reconciliation</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-list-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">View All Accounts</h5>
                    <p class="card-text">View and manage account reconciliation status for all accounts in the system.</p>
                    <a href="<?= APP_URL ?>/reconciliation/view-all-accounts" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Accounts
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-sync-alt fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Reconcile Account</h5>
                    <p class="card-text">Perform reconciliation for individual accounts.</p>
                    <button class="btn btn-success" disabled>
                        <i class="fas fa-sync-alt me-2"></i>Coming Soon
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Reconciliation Reports</h5>
                    <p class="card-text">Generate detailed reconciliation reports and analytics.</p>
                    <button class="btn btn-info" disabled>
                        <i class="fas fa-chart-line me-2"></i>Coming Soon
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">--</h4>
                            <p class="card-text">Total Accounts</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">--</h4>
                            <p class="card-text">Reconciled</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">--</h4>
                            <p class="card-text">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">--</h4>
                            <p class="card-text">Discrepancies</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn:disabled {
    cursor: not-allowed;
}
</style>