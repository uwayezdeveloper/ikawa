<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
            <h4 class="page-title">Dashboard</h4>
        </div>
    </div>
</div>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <?php
                        $avatarUrl = !empty($user['avatar']) ? APP_URL . '/' . $user['avatar'] : \App\Core\View::asset('images/users/user-1.jp');
                        ?>
                        <img src="<?= $avatarUrl ?>" class="avatar-lg rounded-circle" alt="user" style="object-fit: cover; width: 70px; height: 70px;" />
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">Welcome back, <?= ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? 'User') ?>! 👋</h4>
                        <p class="text-muted mb-0">
                            Here's what's happening with your account today.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="badge bg-success-subtle text-success fs-12">
                            <i class="ti ti-circle-filled fs-6 me-1"></i> Online
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

                    <!-- Charts JS -->
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const expenseLabels = <?= json_encode($charts['expense']['labels'] ?? []) ?>;
                        const expenseData = <?= json_encode($charts['expense']['data'] ?? []) ?>;

                        const invLabels = <?= json_encode($charts['invoices']['labels'] ?? []) ?>;
                        const invCounts = <?= json_encode($charts['invoices']['counts'] ?? []) ?>;

                        // Expense line chart
                        const el = document.getElementById('expenseChart');
                        if (el) {
                            const ctxE = el.getContext('2d');
                            new Chart(ctxE, {
                                type: 'line',
                                data: {
                                    labels: expenseLabels,
                                    datasets: [{
                                        label: 'Expenses',
                                        data: expenseData,
                                        borderColor: 'rgba(220,53,69,0.9)',
                                        backgroundColor: 'rgba(220,53,69,0.2)',
                                        fill: true,
                                        tension: 0.3
                                    }]
                                },
                                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                            });
                        }

                        // Invoices bar chart
                        const il = document.getElementById('invoicesChart');
                        if (il) {
                            const ctxI = il.getContext('2d');
                            new Chart(ctxI, {
                                type: 'bar',
                                data: { labels: invLabels, datasets: [{ label: 'Invoices', data: invCounts, backgroundColor: 'rgba(54,162,235,0.7)' }] },
                                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, precision: 0 } } }
                            });
                        }

                        // Accounts doughnut chart
                        const al = document.getElementById('accountsChart');
                        if (al) {
                            const accLabels = <?= json_encode($charts['accounts']['labels'] ?? []) ?>;
                            const accData = <?= json_encode($charts['accounts']['data'] ?? []) ?>;
                            const ctxA = al.getContext('2d');
                            new Chart(ctxA, {
                                type: 'doughnut',
                                data: {
                                    labels: accLabels,
                                    datasets: [{ data: accData, backgroundColor: [
                                        '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#6f42c1','#20c997'
                                    ] }]
                                },
                                options: { responsive: true, plugins: { legend: { position: 'right' } } }
                            });
                        }
                    });
                    </script>
</div>

<!-- Location Totals Cards (Placed Before Dashboard Stats) -->
<?php $locationCards = $locationCards ?? []; $generalLocationTotals = $generalLocationTotals ?? []; ?>
<div class="row mt-3">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <h5 class="mb-0 text-primary">Location Totals (All Stations Combined)</h5>
                    <span class="badge bg-primary-subtle text-primary fs-6">
                        Locations: <?= number_format((int)($generalLocationTotals['locations_count'] ?? 0)) ?>
                    </span>
                </div>
                <div class="row g-3">
                    <div class="col-md-3"><small class="text-muted d-block">Total Cheries Qty</small><strong><?= number_format((float)($generalLocationTotals['cheries_quantity'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Stock Value (Cat 1)</small><strong><?= number_format((float)($generalLocationTotals['stock_value'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Approvisionnement</small><strong><?= number_format((float)($generalLocationTotals['approvisionnement_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Loan</small><strong><?= number_format((float)($generalLocationTotals['loan_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Bank</small><strong><?= number_format((float)($generalLocationTotals['bank_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Caisse</small><strong><?= number_format((float)($generalLocationTotals['caisse_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Total Advances</small><strong><?= number_format((float)($generalLocationTotals['advances_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Exploitable</small><strong><?= number_format((float)($generalLocationTotals['expense_cat_1_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Non-Exploitable</small><strong><?= number_format((float)($generalLocationTotals['expense_cat_2_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Investment/Liability</small><strong><?= number_format((float)($generalLocationTotals['expense_cat_3_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Certification</small><strong><?= number_format((float)($generalLocationTotals['expense_cat_4_total'] ?? 0), 2) ?></strong></div>
                    <div class="col-md-3"><small class="text-muted d-block">Production Cost (/kg)</small><strong><?= number_format((float)($generalLocationTotals['production_cost_per_kg'] ?? 0), 2) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Location Totals</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if (!empty($locationCards)): ?>
                        <?php
                        $locationCardThemes = [
                            ['bg' => '#eef6ff', 'border' => '#b9d9ff', 'title' => '#0f4c81'],
                            ['bg' => '#effaf4', 'border' => '#bde7ce', 'title' => '#1f6f43'],
                            ['bg' => '#fff7ea', 'border' => '#f3d8a4', 'title' => '#8a5a00'],
                            ['bg' => '#f3f1ff', 'border' => '#d6cdfc', 'title' => '#4b3f8a'],
                            ['bg' => '#fff1f3', 'border' => '#f6c4cc', 'title' => '#8c2f3d'],
                            ['bg' => '#eefaf8', 'border' => '#bfe7df', 'title' => '#1f5f57'],
                        ];
                        ?>
                        <?php foreach ($locationCards as $idx => $locationCard): ?>
                            <?php $theme = $locationCardThemes[$idx % count($locationCardThemes)]; ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="rounded p-3 h-100" style="background-color: <?= $theme['bg'] ?>; border: 1px solid <?= $theme['border'] ?>;">
                                    <h6 class="mb-3 fw-bold" style="color: <?= $theme['title'] ?>; font-weight: 700;"><?= htmlspecialchars((string)($locationCard['location_name'] ?? 'N/A')) ?></h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="small text-muted">Cheries Qty</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['cheries_quantity'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Stock Value (Cat 1)</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['stock_value'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Approvisionnement</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['approvisionnement_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Loan</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['loan_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Bank</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['bank_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Caisse</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['caisse_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Total Advance</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['advances_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Exploitable</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['expense_cat_1_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Non-Exploitable</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['expense_cat_2_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Investment/Liability</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['expense_cat_3_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Certification</div>
                                            <div class="fw-semibold"><?= number_format((float)($locationCard['expense_cat_4_total'] ?? 0), 2) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Production Cost (/kg)</div>
                                            <div class="fw-bold text-primary"><?= number_format((float)($locationCard['production_cost_per_kg'] ?? 0), 2) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-muted mb-0">No location totals available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<?php $stats = $stats ?? []; $charts = $charts ?? []; ?>
<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-primary-subtle rounded">
                            <i class="ti ti-users fs-28 text-primary"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Total Users</h5>
                        <p class="mb-0 text-muted">
                            <span class="fs-24 fw-bold"><?= number_format($stats['totalUsers'] ?? 0) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-success-subtle rounded">
                            <i class="ti ti-shopping-cart fs-28 text-success"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Total Invoices</h5>
                        <p class="mb-0 text-muted">
                            <span class="fs-24 fw-bold"><?= number_format($stats['totalInvoices'] ?? 0) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-warning-subtle rounded">
                            <i class="ti ti-currency-dollar fs-28 text-warning"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Revenue</h5>
                        <p class="mb-0 text-muted">
                            <span class="fs-24 fw-bold"><?= number_format($stats['totalInvoiceAmount'] ?? 0, 2) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-info-subtle rounded">
                            <i class="ti ti-coffee fs-28 text-info"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Categories</h5>
                        <p class="mb-0 text-muted">
                            <span class="fs-24 fw-bold"><?= number_format($stats['totalCategories'] ?? 0) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats: Farmers & Suppliers -->
<div class="row mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-tertiary-subtle rounded">
                            <i class="ti ti-plant fs-28 text-success"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">Suppliers</h5>
                        <p class="mb-0 text-muted">
                            <span class="fs-24 fw-bold"><?= number_format($stats['totalSuppliers'] ?? 0) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar-md bg-secondary-subtle rounded">
                            <i class="ti ti-truck fs-28 text-primary"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">farmers</h5>
                        <p class="mb-0 text-muted">
                            <span class="fs-24 fw-bold"><?= number_format($stats['totalFarmers'] ?? 0) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Expenses â€” Last 7 days</h5>
            </div>
            <div class="card-body">
                <canvas id="expenseChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Invoices â€” Last 6 months</h5>
            </div>
            <div class="card-body">
                <canvas id="invoicesChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Bank Accounts Summary -->
<div class="row mt-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Company Cash (Bank Accounts)</h6>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h3 class="mb-0"><?= number_format($stats['totalAccounts'] ?? 0, 2) ?></h3>
                        <p class="text-muted mb-0">Total across all active accounts</p>
                    </div>
                    <div class="flex-shrink-0" style="width:120px;">
                        <canvas id="accountsChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/profile" class="btn btn-outline-primary w-100 py-3">
                            <i class="ti ti-user fs-24 d-block mb-1"></i>
                            My Profile
                        </a>
                    </div>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/users" class="btn btn-outline-success w-100 py-3">
                            <i class="ti ti-users fs-24 d-block mb-1"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/settings" class="btn btn-outline-warning w-100 py-3">
                            <i class="ti ti-settings fs-24 d-block mb-1"></i>
                            Settings
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-6 col-md-3">
                        <a href="<?= APP_URL ?>/logout" class="btn btn-outline-danger w-100 py-3">
                            <i class="ti ti-logout fs-24 d-block mb-1"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Info -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Full Name:</td>
                            <td class="fw-medium"><?= ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td class="fw-medium"><?= $user['email'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Role:</td>
                            <td>
                                <span class="badge bg-primary"><?= $user['role_name'] ?? 'N/A' ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge bg-success"><?= ucfirst($user['status'] ?? 'N/A') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since:</td>
                            <td class="fw-medium">
                                <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Session Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Last Login:</td>
                            <td class="fw-medium">
                                <?= isset($user['last_login_at']) ? date('M d, Y H:i', strtotime($user['last_login_at'])) : 'N/A' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">IP Address:</td>
                            <td class="fw-medium"><?= $_SERVER['REMOTE_ADDR'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Browser:</td>
                            <td class="fw-medium text-truncate" style="max-width: 200px;">
                                <?= $_SERVER['HTTP_USER_AGENT'] ?? 'N/A' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Session Started:</td>
                            <td class="fw-medium"><?= date('M d, Y H:i') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
