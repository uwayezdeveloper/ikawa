<?php
$permissions = $user['permissions'] ?? [];
$advances = $summary['advances'] ?? [];
$stock = $summary['stock'] ?? [];
$payables = $summary['payables'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Supplier Records</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/suppliers">Suppliers</a></li>
                <li class="breadcrumb-item active">Records</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <select id="supplierSelect" class="form-select" style="min-width: 300px;">
            <option value="">Select Supplier</option>
            <?php foreach ($suppliers as $supplier): ?>
            <option value="<?= $supplier['id'] ?>" <?= $supplier['id'] == $selectedSupplierId ? 'selected' : '' ?>>
                <?= htmlspecialchars($supplier['name']) ?> (<?= htmlspecialchars($supplier['type_name'] ?? 'N/A') ?>)
            </option>
            <?php endforeach; ?>
        </select>
        <?php if ($selectedSupplierId): ?>
        <button type="button" id="downloadPdfBtn" class="btn btn-danger">
            <i class="ti ti-file-type-pdf me-1"></i> Download PDF
        </button>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="ti ti-printer me-1"></i> Print
        </button>
    </div>
</div>

<?php if ($supplierInfo): ?>
<!-- Supplier Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-primary-subtle rounded me-3">
                        <i class="ti ti-user fs-2 text-primary d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($supplierInfo['name']) ?></h4>
                        <p class="text-muted mb-0">
                            <i class="ti ti-phone me-1"></i><?= htmlspecialchars($supplierInfo['phone'] ?? 'N/A') ?>
                            <?php if ($supplierInfo['email']): ?>
                            <span class="ms-3"><i class="ti ti-mail me-1"></i><?= htmlspecialchars($supplierInfo['email']) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border-end">
                            <h5 class="text-warning mb-0"><?= number_format($advances['active_advances'] ?? 0) ?></h5>
                            <small class="text-muted">Active Advances (FRW)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h5 class="text-success mb-0"><?= number_format($stock['total_stock_value'] ?? 0) ?></h5>
                            <small class="text-muted">Total Stock Value (FRW)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h5 class="text-primary mb-0"><?= number_format(($stock['paid_by_advance'] ?? 0) + ($stock['paid_by_account'] ?? 0)) ?></h5>
                            <small class="text-muted">Total Paid (FRW)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-danger mb-0"><?= number_format($payables['pending_balance'] ?? 0) ?></h5>
                        <small class="text-muted">Pending Balance (FRW)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning-subtle border-0">
            <div class="card-body">
                <h6 class="text-warning mb-3"><i class="ti ti-arrow-up-right me-2"></i>Advances Summary</h6>
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><small class="text-muted">Total Given</small></p>
                        <h5 class="mb-0"><?= number_format($advances['total_advance_amount'] ?? 0) ?></h5>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><small class="text-muted">Settled</small></p>
                        <h5 class="mb-0"><?= number_format($advances['settled_advances'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success-subtle border-0">
            <div class="card-body">
                <h6 class="text-success mb-3"><i class="ti ti-package me-2"></i>Stock Receives Summary</h6>
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><small class="text-muted">Total Receives</small></p>
                        <h5 class="mb-0"><?= $stock['total_receives'] ?? 0 ?></h5>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><small class="text-muted">Total Value</small></p>
                        <h5 class="mb-0"><?= number_format($stock['total_stock_value'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger-subtle border-0">
            <div class="card-body">
                <h6 class="text-danger mb-3"><i class="ti ti-clock-dollar me-2"></i>Payables Summary</h6>
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><small class="text-muted">Total Payable</small></p>
                        <h5 class="mb-0"><?= number_format($payables['total_payable_amount'] ?? 0) ?></h5>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><small class="text-muted">Pending</small></p>
                        <h5 class="mb-0"><?= number_format($payables['pending_balance'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products Supplied Details -->
<?php if (!empty($productSummary)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="ti ti-packages me-2"></i>Products Supplied (Detailed)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Total Value</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotalQty = 0;
                    $grandTotalValue = 0;
                    foreach ($productSummary as $item): 
                        $grandTotalQty += $item['quantity'];
                        $grandTotalValue += $item['total_price'];
                    ?>
                    <tr>
                        <td>
                            <strong><?= date('M d, Y', strtotime($item['receive_date'])) ?></strong>
                        </td>
                        <td>
                            <code class="text-dark"><?= htmlspecialchars($item['receive_number'] ?? 'RCV-' . $item['id']) ?></code>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="ti ti-leaf me-1"></i><?= htmlspecialchars($item['product_name']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($item['type_name']) ?></td>
                        <td><?= htmlspecialchars($item['location_name'] ?? '-') ?></td>
                        <td class="text-end">
                            <strong><?= number_format($item['quantity'], 2) ?></strong>
                            <small class="text-muted"><?= $item['unit_symbol'] ?></small>
                        </td>
                        <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                        <td class="text-end"><strong class="text-success"><?= number_format($item['total_price'], 2) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($item['notes'] ?? '-') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light">
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Grand Total:</td>
                        <td class="text-end"><?= number_format($grandTotalQty, 2) ?></td>
                        <td></td>
                        <td class="text-end text-success"><?= number_format($grandTotalValue, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Transaction History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ti ti-history me-2"></i>Transaction History</h5>
        <div class="d-flex gap-2">
            <select id="typeFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Types</option>
                <option value="advance">Advances</option>
                <option value="stock_receive">Stock Receives</option>
                <option value="payment">Payments</option>
            </select>
            <input type="date" id="dateFrom" class="form-control form-control-sm" style="width: 150px;" placeholder="From Date">
            <input type="date" id="dateTo" class="form-control form-control-sm" style="width: 150px;" placeholder="To Date">
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($records)): ?>
        <div class="text-center py-5 text-muted">
            <i class="ti ti-receipt-off fs-1 mb-2 d-block"></i>
            No records found for this supplier
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="recordsTable">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th class="text-end text-danger">Debit (Out)</th>
                        <th class="text-end text-success">Credit (In)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalDebit = 0;
                    $totalCredit = 0;
                    foreach ($records as $record): 
                        $totalDebit += $record['debit'];
                        $totalCredit += $record['credit'];
                    ?>
                    <tr class="record-row" data-type="<?= $record['type'] ?>" data-date="<?= $record['date'] ?>">
                        <td>
                            <strong><?= date('M d, Y', strtotime($record['date'])) ?></strong>
                            <br><small class="text-muted"><?= date('H:i', strtotime($record['datetime'])) ?></small>
                        </td>
                        <td>
                            <?php if ($record['type'] === 'advance'): ?>
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="ti ti-arrow-up-right me-1"></i>Advance
                            </span>
                            <?php elseif ($record['type'] === 'stock_receive'): ?>
                            <span class="badge bg-success-subtle text-success">
                                <i class="ti ti-package me-1"></i>Stock Receive
                            </span>
                            <?php else: ?>
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="ti ti-cash me-1"></i>Payment
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code class="text-dark"><?= htmlspecialchars($record['reference']) ?></code>
                        </td>
                        <td>
                            <?= htmlspecialchars($record['description']) ?>
                            <?php if ($record['notes']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($record['notes']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($record['location']) ?></td>
                        <td class="text-end">
                            <?php if ($record['debit'] > 0): ?>
                            <span class="text-danger fw-bold"><?= number_format($record['debit'], 2) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($record['credit'] > 0): ?>
                            <span class="text-success fw-bold"><?= number_format($record['credit'], 2) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['status'] === 'approved' || $record['status'] === 'paid'): ?>
                            <span class="badge bg-success-subtle text-success"><?= ucfirst($record['status']) ?></span>
                            <?php elseif ($record['status'] === 'pending' || $record['status'] === 'partial'): ?>
                            <span class="badge bg-warning-subtle text-warning"><?= ucfirst($record['status']) ?></span>
                            <?php elseif ($record['status'] === 'settled'): ?>
                            <span class="badge bg-info-subtle text-info">Settled</span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary"><?= ucfirst($record['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light">
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Totals:</td>
                        <td class="text-end text-danger"><?= number_format($totalDebit, 2) ?></td>
                        <td class="text-end text-success"><?= number_format($totalCredit, 2) ?></td>
                        <td>
                            <?php $balance = $totalCredit - $totalDebit; ?>
                            <span class="badge bg-<?= $balance >= 0 ? 'success' : 'danger' ?>">
                                Balance: <?= number_format($balance, 2) ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="alert alert-info">
    <i class="ti ti-info-circle me-2"></i>
    Please select a supplier to view their records.
</div>
<?php endif; ?>

<script>
window.APP_URL = '<?= APP_URL ?>';
<?php if ($supplierInfo): ?>
window.supplierRecordsPayload = {
    supplier: <?= json_encode($supplierInfo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    summary: <?= json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    records: <?= json_encode($records, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    productSummary: <?= json_encode($productSummary, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
<?php endif; ?>
</script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
