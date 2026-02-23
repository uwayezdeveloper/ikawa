<?php /** Account Activity Report View */ ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= $pageTitle ?? 'Account Activity Report' ?></h5>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Select Accounts</label>
                <select name="account_ids[]" multiple class="form-select">
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (is_array($selected) && in_array($a['id'], $selected)) ? 'selected' : '' ?>><?= htmlspecialchars($a['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Hold Ctrl/Cmd to select multiple accounts.</div>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>

        <?php if (!empty($report)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Expense Count</th>
                            <th>Expense Total</th>
                            <th>Expense Charges</th>
                            <th>Recharged Total</th>
                            <th>Transfer Out</th>
                            <th>Transfer In</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['account']['account_name'] ?? 'Unknown') ?></td>
                                <td><?= (int)$row['expense_count'] ?></td>
                                <td><?= number_format($row['expense_total'], 2) ?></td>
                                <td><?= number_format($row['expense_charges'], 2) ?></td>
                                <td><?= number_format($row['recharges_total'], 2) ?></td>
                                <td><?= number_format($row['transfer_out'], 2) ?></td>
                                <td><?= number_format($row['transfer_in'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No data. Please select one or more accounts and date range, then click "Generate Report".</div>
        <?php endif; ?>
    </div>
</div>
