<?php
$rows = $journalRows ?? [];
$totals = $journalTotals ?? ['debit' => 0, 'credit' => 0, 'balance' => 0];

$formatJournalDateTime = static function ($value): string {
    $raw = trim((string)$value);
    if ($raw === '') {
        return '';
    }

    $raw = str_replace('T', ' ', $raw);
    $raw = preg_replace('/\.\d+$/', '', $raw) ?? $raw;

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/', $raw, $m)) {
        $year = $m[1];
        $month = $m[2];
        $day = $m[3];
        $hour = $m[4] ?? '00';
        $minute = $m[5] ?? '00';
        $second = $m[6] ?? '00';
        return $day . '/' . $month . '/' . $year . ' ' . $hour . ':' . $minute . ':' . $second;
    }

    return $raw;
};
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Location Journal</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/station-finances">Station Finances</a></li>
                <li class="breadcrumb-item active">Journal</li>
            </ol>
        </nav>
    </div>
    <button id="downloadJournalPdfBtn" class="btn btn-danger">
        <i class="ti ti-file-type-pdf me-1"></i> Download PDF
    </button>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/finance/station-finances/journal" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Location</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($location['name'] ?? 'N/A') ?>" disabled>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label mb-1">From</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars((string)($dateFrom ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label mb-1">To</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars((string)($dateTo ?? '')) ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
        <div class="text-center py-5 text-muted">No journal data found for this location.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0" id="locationJournalTable">
                <thead class="bg-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Description</th>
                        <th>Bank/Cash</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($formatJournalDateTime($row['datetime'] ?? $row['date'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['bank_cash']) ?></td>
                        <td class="text-end"><?= $row['debit'] > 0 ? number_format((float)$row['debit'], 2) : '' ?></td>
                        <td class="text-end"><?= $row['credit'] > 0 ? number_format((float)$row['credit'], 2) : '' ?></td>
                        <td class="text-end"><?= number_format((float)$row['balance'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td></td>
                        <td>Totals</td>
                        <td></td>
                        <td class="text-end"><?= number_format((float)$totals['debit'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$totals['credit'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$totals['balance'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.APP_URL = '<?= APP_URL ?>';
window.locationJournalPayload = {
    location: <?= json_encode($location ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    dateFrom: <?= json_encode($dateFrom ?? '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    dateTo: <?= json_encode($dateTo ?? '', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    rows: <?= json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    totals: <?= json_encode($totals, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
