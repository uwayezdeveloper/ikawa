<?php
$reportData = $report ?? [];
$suppliers = $reportData['suppliers'] ?? [];
$stock = $reportData['stock'] ?? [];
$journalBank = $reportData['journal_bank'] ?? [];
$expenses = $reportData['expenses'] ?? [];
$liability = $reportData['liability'] ?? [];
$mainTotal = (float)($reportData['main_total'] ?? 0);
$cheriesTotalQuantity = (float)($stock['cheries_total_quantity'] ?? 0);
$generatedAt = date('Y-m-d H:i:s');
$debugMode = (bool)($debugMode ?? false);
$expenseDebug = $expenseDebug ?? [];
$accountDebug = $accountDebug ?? [];
$userLocationId = (int)($userLocationId ?? 0);
$effectiveLocationId = (int)($effectiveLocationId ?? 0);
$canSelectLocation = (bool)($canSelectLocation ?? false);
$locations = $locations ?? [];
$selectedLocationId = (int)($selectedLocationId ?? ($location['id'] ?? 0));
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Final Location Report</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/station-finances">Station Finances</a></li>
                <li class="breadcrumb-item active">Final Location Report</li>
            </ol>
        </nav>
    </div>
    <button id="downloadFinalReportPdfBtn" class="btn btn-danger">
        <i class="ti ti-file-type-pdf me-1"></i> Download PDF
    </button>
</div>

<?php if ($canSelectLocation): ?>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/finance/station-finances/final-report" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="location_id" class="form-label mb-1">Location</label>
                <select id="location_id" name="location_id" class="form-select">
                    <?php foreach ($locations as $loc): ?>
                    <option value="<?= (int)$loc['id'] ?>" <?= (int)$loc['id'] === $selectedLocationId ? 'selected' : '' ?>>
                        <?= htmlspecialchars(($loc['type_name'] ?? 'Location') . ' - ' . ($loc['name'] ?? 'N/A')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($debugMode): ?>
            <input type="hidden" name="debug" value="1">
            <?php endif; ?>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><?= htmlspecialchars($location['name'] ?? 'N/A') ?></h5>
                <small class="text-muted">Location final report summary</small>
            </div>
            <span class="badge bg-primary-subtle text-primary fs-6">
                Total: <?= number_format($mainTotal, 2) ?> FRW
            </span>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row g-2">
            <div class="col-md-4">
                <strong>Generated:</strong> <?= htmlspecialchars($generatedAt) ?>
            </div>
            <div class="col-md-4">
                <strong>Location:</strong> <?= htmlspecialchars($location['name'] ?? 'N/A') ?>
            </div>
            <div class="col-md-4">
                <strong>cheries:</strong> <?= number_format($cheriesTotalQuantity, 2) ?>
            </div>
        </div>
    </div>
</div>

<?php if ($debugMode): ?>
<div class="card border-warning mb-3">
    <div class="card-header bg-warning-subtle">
        <h6 class="mb-0">Debug - Expense Diagnostics</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div><strong>User location_id</strong></div>
                <div><?= $userLocationId ?></div>
            </div>
            <div class="col-md-3">
                <div><strong>Effective location_id (used by report)</strong></div>
                <div><?= $effectiveLocationId ?></div>
            </div>
            <div class="col-md-3">
                <div><strong>Current Database</strong></div>
                <div><?= htmlspecialchars((string)($expenseDebug['database_name'] ?? '')) ?></div>
            </div>
            <div class="col-md-3">
                <div><strong>Location ID</strong></div>
                <div><?= (int)($expenseDebug['location_id'] ?? 0) ?></div>
            </div>
            <div class="col-md-3">
                <div><strong>Active rows (station_id = location_id, status=1)</strong></div>
                <div><?= (int)($expenseDebug['active_for_location']['cnt'] ?? 0) ?> rows / <?= number_format((float)($expenseDebug['active_for_location']['total'] ?? 0), 2) ?> FRW</div>
            </div>
            <div class="col-md-3">
                <div><strong>Any status rows (station_id = location_id)</strong></div>
                <div><?= (int)($expenseDebug['any_status_for_location']['cnt'] ?? 0) ?> rows / <?= number_format((float)($expenseDebug['any_status_for_location']['total'] ?? 0), 2) ?> FRW</div>
            </div>
            <div class="col-md-3">
                <div><strong>Orphan expense types</strong></div>
                <div><?= (int)($expenseDebug['orphans']['cnt'] ?? 0) ?> rows / <?= number_format((float)($expenseDebug['orphans']['total'] ?? 0), 2) ?> FRW</div>
            </div>
            <div class="col-md-3">
                <div><strong>Global expenses (all rows)</strong></div>
                <div><?= (int)($expenseDebug['global_expense_stats']['cnt'] ?? 0) ?> rows / <?= number_format((float)($expenseDebug['global_expense_stats']['total'] ?? 0), 2) ?> FRW</div>
            </div>
            <div class="col-md-3">
                <div><strong>Global expenses (status=1)</strong></div>
                <div><?= (int)($expenseDebug['global_active_expense_stats']['cnt'] ?? 0) ?> rows / <?= number_format((float)($expenseDebug['global_active_expense_stats']['total'] ?? 0), 2) ?> FRW</div>
            </div>
        </div>

        <hr>
        <h6 class="mb-2">Journal/Bank Diagnostics</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div><strong>Identifiers Column Exists</strong></div>
                <div><?= !empty($accountDebug['has_identifiers']) ? 'Yes' : 'No' ?></div>
            </div>
            <div class="col-md-3">
                <div><strong>Classification Mode</strong></div>
                <div><?= htmlspecialchars((string)($accountDebug['classification_mode'] ?? '')) ?></div>
            </div>
            <div class="col-md-3">
                <div><strong>Journal Total (computed)</strong></div>
                <div><?= number_format((float)($accountDebug['totals']['journal'] ?? 0), 2) ?> FRW</div>
            </div>
            <div class="col-md-3">
                <div><strong>Bank Total (computed)</strong></div>
                <div><?= number_format((float)($accountDebug['totals']['bank'] ?? 0), 2) ?> FRW</div>
            </div>
            <div class="col-md-3">
                <div><strong>Unknown Total (unmapped)</strong></div>
                <div><?= number_format((float)($accountDebug['totals']['unknown'] ?? 0), 2) ?> FRW</div>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Formula Check</th>
                        <th class="text-end">Bank</th>
                        <th class="text-end">Journal</th>
                        <th class="text-end">Unknown</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total balance (all statuses)</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['total_balance_all_status'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Total balance (status = active)</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['total_balance_active_only'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Strict identifiers (all statuses)</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['strict_identifiers_all_status']['bank'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['strict_identifiers_all_status']['journal'] ?? 0), 2) ?></td>
                        <td class="text-end">-</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['strict_identifiers_all_status']['total'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Strict identifiers (status = active)</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['strict_identifiers_active_only']['bank'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['strict_identifiers_active_only']['journal'] ?? 0), 2) ?></td>
                        <td class="text-end">-</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['strict_identifiers_active_only']['total'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Flexible identifiers (all statuses)</td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['flex_identifiers_all_status']['bank'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['flex_identifiers_all_status']['journal'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['flex_identifiers_all_status']['unknown'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($accountDebug['formula_checks']['flex_identifiers_all_status']['total'] ?? 0), 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($accountDebug['status_breakdown'] ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($row['status'] ?? 'NULL')) ?></td>
                        <td class="text-end"><?= (int)($row['cnt'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($accountDebug['identifier_breakdown'])): ?>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>identifiers</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($accountDebug['identifier_breakdown'] ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($row['identifiers'] ?? 'NULL')) ?></td>
                        <td class="text-end"><?= (int)($row['cnt'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>status</th>
                        <th>identifiers</th>
                        <th>payment_mode</th>
                        <th class="text-end">balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($accountDebug['accounts'] ?? []) as $row): ?>
                    <tr>
                        <td><?= (int)($row['id'] ?? 0) ?></td>
                        <td>
                            <?= htmlspecialchars((string)($row['account_name'] ?? '')) ?>
                            <?php if (!empty($row['account_number'])): ?>
                                (<?= htmlspecialchars((string)$row['account_number']) ?>)
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string)($row['status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['identifiers'] ?? 'NULL')) ?></td>
                        <td><?= htmlspecialchars((string)($row['payment_mode_name'] ?? '')) ?></td>
                        <td class="text-end"><?= number_format((float)($row['balance'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Top station_id (status=1)</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenseDebug['top_stations'] ?? []) as $row): ?>
                    <tr>
                        <td><?= (int)($row['station_id'] ?? 0) ?></td>
                        <td class="text-end"><?= (int)($row['cnt'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenseDebug['status_breakdown'] ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($row['status'] ?? 'NULL')) ?></td>
                        <td class="text-end"><?= (int)($row['cnt'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>expense_id in consume</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenseDebug['expense_id_breakdown'] ?? []) as $row): ?>
                    <tr>
                        <td><?= (int)($row['expense_id'] ?? 0) ?></td>
                        <td class="text-end"><?= (int)($row['cnt'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>categ_id</th>
                        <th>categ_name</th>
                        <th>expense_name</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenseDebug['by_category'] ?? []) as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($row['categ_id'] ?? 'NULL')) ?></td>
                        <td><?= htmlspecialchars((string)($row['categ_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['expense_name'] ?? '')) ?></td>
                        <td class="text-end"><?= (int)($row['cnt'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>expense_id (master)</th>
                        <th>categ_id</th>
                        <th>categ_name</th>
                        <th>expense_name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenseDebug['expense_type_map'] ?? []) as $row): ?>
                    <tr>
                        <td><?= (int)($row['expense_id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($row['categ_id'] ?? 'NULL')) ?></td>
                        <td><?= htmlspecialchars((string)($row['categ_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['expense_name'] ?? '')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>con_id</th>
                        <th>expense_id</th>
                        <th>station_id</th>
                        <th class="text-end">amount</th>
                        <th>status</th>
                        <th>recorded_date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($expenseDebug['recent_rows'] ?? []) as $row): ?>
                    <tr>
                        <td><?= (int)($row['con_id'] ?? 0) ?></td>
                        <td><?= (int)($row['expense_id'] ?? 0) ?></td>
                        <td><?= (int)($row['station_id'] ?? 0) ?></td>
                        <td class="text-end"><?= number_format((float)($row['amount'] ?? 0), 2) ?></td>
                        <td><?= htmlspecialchars((string)($row['status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['recorded_date'] ?? '')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
window.APP_URL = '<?= APP_URL ?>';
window.finalLocationReportPayload = {
    location: <?= json_encode($location ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    report: <?= json_encode($reportData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    generatedAt: <?= json_encode($generatedAt, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 22%">Section</th>
                        <th>Description</th>
                        <th style="width: 22%" class="text-end">Amount (FRW)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-primary">
                        <td rowspan="3" class="fw-bold align-middle">Suppliers</td>
                        <td>Advance</td>
                        <td class="text-end"><?= number_format((float)($suppliers['advance'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Loan</td>
                        <td class="text-end"><?= number_format((float)($suppliers['loan_transfer'] ?? 0), 2) ?></td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>Total</td>
                        <td class="text-end"><?= number_format((float)($suppliers['total'] ?? 0), 2) ?></td>
                    </tr>

                    <tr class="table-success">
                        <td rowspan="2" class="fw-bold align-middle">Stock</td>
                        <td>Stock value</td>
                        <td class="text-end"><?= number_format((float)($stock['stock_value'] ?? 0), 2) ?></td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>Total</td>
                        <td class="text-end"><?= number_format((float)($stock['total'] ?? 0), 2) ?></td>
                    </tr>

                    <tr class="table-info">
                        <td rowspan="3" class="fw-bold align-middle">Journal/Bank</td>
                        <td>Journal </td>
                        <td class="text-end"><?= number_format((float)($journalBank['journal'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Bank </td>
                        <td class="text-end"><?= number_format((float)($journalBank['bank'] ?? 0), 2) ?></td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>Total</td>
                        <td class="text-end"><?= number_format((float)($journalBank['total'] ?? 0), 2) ?></td>
                    </tr>

                    <tr class="table-warning">
                        <td rowspan="5" class="fw-bold align-middle">Expenses</td>
                        <td>Exploatable </td>
                        <td class="text-end"><?= number_format((float)($expenses['exploitable'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Non exploatable </td>
                        <td class="text-end"><?= number_format((float)($expenses['non_exploitable'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Investment/Liability </td>
                        <td class="text-end"><?= number_format((float)($expenses['investment_liability'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Certification </td>
                        <td class="text-end"><?= number_format((float)($expenses['certification'] ?? 0), 2) ?></td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>Total</td>
                        <td class="text-end"><?= number_format((float)($expenses['total'] ?? 0), 2) ?></td>
                    </tr>
                </tbody>
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td colspan="2">Final Total</td>
                        <td class="text-end"><?= number_format($mainTotal, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Liability Account</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 28%">Description</th>
                        <th style="width: 18%" class="text-end">Amount (FRW)</th>
                        <th style="width: 26%">Names</th>
                        <th style="width: 14%" class="text-end">Amount (FRW)</th>
                        <th style="width: 14%" class="text-end">Autre Credit (FRW)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Approvisionnement (HQ account-transfer history)</td>
                        <td class="text-end"><?= number_format((float)($liability['approvisionnement'] ?? 0), 2) ?></td>
                        <td>Loan </td>
                        <td class="text-end"><?= number_format((float)($liability['supplier_loans'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($liability['autre_credit'] ?? 0), 2) ?></td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="4">Liability Total</td>
                        <td class="text-end"><?= number_format((float)($liability['total'] ?? 0), 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

