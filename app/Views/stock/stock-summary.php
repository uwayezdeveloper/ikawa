<?php
$permissions = $user['permissions'] ?? [];
$canView = in_array('view-stock-receives', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Stock Summary</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Stock Summary</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= APP_URL ?>/stock/receives" class="btn btn-primary">
            <i class="ti ti-package-import me-1"></i> Receive Stock
        </a>
    </div>
</div>

<!-- Stock Value by Location Cards -->
<div class="row mb-4">
    <?php if (!empty($valueByLocation)): ?>
    <?php foreach (array_slice($valueByLocation, 0, 4) as $loc): ?>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1"><?= htmlspecialchars($loc['location_name']) ?></h6>
                        <h3 class="mb-0">RWF <?= number_format($loc['total_value'], 0) ?></h3>
                        <small class="text-muted"><?= number_format($loc['total_items'], 2) ?> items ¡¤ <?= $loc['category_count'] ?> categories</small>
                    </div>
                    <div class="avatar-md bg-primary-subtle text-primary rounded-circle">
                        <i class="ti ti-building-store fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>No stock data available yet.
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="form-label mb-0">Location Type</label>
                <select class="form-select" id="filterLocationType">
                    <option value="">All Location Types</option>
                    <?php foreach ($locationTypes as $lt): ?>
                    <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Location</label>
                <select class="form-select" id="filterLocation" disabled>
                    <option value="">Select Location Type first</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary mt-4" id="resetFilters">
                    <i class="ti ti-refresh me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Summary Table -->
<div class="card" data-table data-table-rows-per-page="15">
    <div class="card-header border-light justify-content-between">
        <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>Aggregated Stock</h5>
        <div class="d-flex align-items-center gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
            <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0" id="summaryTable">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3">#</th>
                    <th data-table-sort>Location</th>
                    <th data-table-sort>Category</th>
                    <th data-table-sort>Type</th>
                    <th data-table-sort>Quantity</th>
                    <th data-table-sort>Total Value</th>
                    <th data-table-sort>Avg Price</th>
                    <th data-table-sort>Last Receive</th>
                </tr>
            </thead>
            <tbody id="summaryTableBody">
                <?php if (empty($summaries)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="ti ti-database-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No stock summary data available</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($summaries as $index => $summary): ?>
                <tr>
                    <td class="ps-3"><?= $index + 1 ?></td>
                    <td>
                        <span class="fw-medium"><?= htmlspecialchars($summary['location_name']) ?></span>
                        <br><small class="text-muted"><?= htmlspecialchars($summary['location_type_name'] ?? '') ?></small>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info"><?= htmlspecialchars($summary['category_name']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($summary['type_name']) ?></td>
                    <td>
                        <span class="fw-semibold"><?= number_format($summary['total_quantity'], 2) ?></span>
                        <small class="text-muted"><?= htmlspecialchars($summary['unit_symbol']) ?></small>
                    </td>
                    <td>
                        <span class="fw-semibold text-success">RWF <?= number_format($summary['total_value'], 0) ?></span>
                    </td>
                    <td>
                        <span class="text-muted">RWF <?= number_format($summary['avg_unit_price'], 2) ?></span>
                    </td>
                    <td>
                        <?= $summary['last_receive_date'] ? date('M d, Y', strtotime($summary['last_receive_date'])) : '-' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer py-0">
        <nav aria-label="Page navigation">
            <ul data-table-paginate class="pagination justify-content-end mb-0"></ul>
        </nav>
    </div>
</div>
