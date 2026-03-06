<?php
$detailedExpenseRows = $detailedExpenseRows ?? [];
$detailedExpenseTotalAmount = (float)($detailedExpenseTotalAmount ?? 0);
$detailedExpenseTotalPerKg = (float)($detailedExpenseTotalPerKg ?? 0);
$cheriesQuantity = (float)($cheriesQuantity ?? 0);
$generatedAt = $generatedAt ?? date('Y-m-d H:i:s');
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Detailed Expense Report</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/station-finances">Station Finances</a></li>
                <li class="breadcrumb-item active">Detailed Expense Report</li>
            </ol>
        </nav>
    </div>
    <button id="downloadDetailedExpensePdfBtn" class="btn btn-danger">
        <i class="ti ti-file-type-pdf me-1"></i> Download PDF
    </button>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row g-2">
            <div class="col-md-6">
                <strong>Generated:</strong> <?= htmlspecialchars((string)$generatedAt) ?>
            </div>
            <div class="col-md-6">
                <strong>Location:</strong> <?= htmlspecialchars((string)($location['name'] ?? 'N/A')) ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detailed Expense Report</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Details</th>
                        <th class="text-end" style="width: 260px;">amount</th>
                        <th class="text-end" style="width: 200px;">/kg</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detailedExpenseRows)): ?>
                        <?php foreach ($detailedExpenseRows as $expenseRow): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($expenseRow['details'] ?? '')) ?></td>
                                <td class="text-end"><?= number_format((float)($expenseRow['amount'] ?? 0), 2) ?></td>
                                <td class="text-end">
                                    <?php if (($expenseRow['per_kg'] ?? null) === null): ?>
                                        -
                                    <?php else: ?>
                                        <?= number_format((float)$expenseRow['per_kg'], 2) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No records found for this location.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td>Total</td>
                        <td class="text-end"><?= number_format($detailedExpenseTotalAmount, 2) ?></td>
                        <td class="text-end"><?= number_format($detailedExpenseTotalPerKg, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
window.APP_URL = '<?= APP_URL ?>';
window.detailedExpenseReportPayload = {
    location: <?= json_encode($location ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    rows: <?= json_encode($detailedExpenseRows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    totalAmount: <?= json_encode($detailedExpenseTotalAmount, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    totalPerKg: <?= json_encode($detailedExpenseTotalPerKg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    cheriesQuantity: <?= json_encode($cheriesQuantity, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    generatedAt: <?= json_encode($generatedAt, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
