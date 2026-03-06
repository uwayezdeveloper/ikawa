<?php
$credits = $credits ?? [];
$history = $history ?? [];
$sources = $sources ?? [];
$accounts = $accounts ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Autre Credit</li>
                </ol>
            </div>
            <h4 class="page-title"><?= htmlspecialchars($title ?? 'Autre Credit') ?></h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
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
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create Autre Credit</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/finance/autre-credit/store">
                    <div class="mb-3">
                        <label class="form-label">Source of Money</label>
                        <select name="source_of_money_id" class="form-select" required>
                            <option value="">Select source</option>
                            <?php foreach ($sources as $source): ?>
                                <option value="<?= (int)$source['in_id'] ?>"><?= htmlspecialchars($source['in_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account (Where money goes)</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">Select account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int)$account['id'] ?>">
                                    <?= htmlspecialchars($account['account_name']) ?>
                                    <?php if (!empty($account['location_name'])): ?>
                                        - <?= htmlspecialchars($account['location_name']) ?>
                                    <?php endif; ?>
                                    (Bal: <?= number_format((float)$account['balance'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" min="0.01" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Add Credit
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Current Credits</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>Outstanding</th>
                                <th>Target Account</th>
                                <th>Target Location</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($credits)): ?>
                                <?php foreach ($credits as $credit): ?>
                                    <?php $outstanding = (float)($credit['outstanding_amount'] ?? 0); ?>
                                    <tr>
                                        <td><?= (int)$credit['id'] ?></td>
                                        <td><?= htmlspecialchars($credit['source_name'] ?? 'N/A') ?></td>
                                        <td><?= number_format((float)$credit['amount'], 2) ?></td>
                                        <td><?= number_format($outstanding, 2) ?></td>
                                        <td><?= htmlspecialchars($credit['account_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars((string)($credit['target_location_name'] ?? 'N/A')) ?></td>
                                        <td><?= htmlspecialchars((string)($credit['due_date'] ?? '')) ?></td>
                                        <td>
                                            <?php if (($credit['status'] ?? '') === 'paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Outstanding</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (($credit['status'] ?? '') !== 'paid' && $outstanding > 0): ?>
                                                <button
                                                    class="btn btn-sm btn-outline-primary repay-btn"
                                                    type="button"
                                                    data-credit-id="<?= (int)$credit['id'] ?>"
                                                    data-outstanding="<?= htmlspecialchars((string)$outstanding) ?>"
                                                >
                                                    Repay
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">No autre credit records yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Autre Credit History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Credit ID</th>
                                <th>Action</th>
                                <th>Source of Money</th>
                                <th>Amount</th>
                                <th>Account (Money Went)</th>
                                <th>Location (Money Went)</th>
                                <th>Payed Account</th>
                                <th>Due Date</th>
                                <th>Done Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history)): ?>
                                <?php foreach ($history as $item): ?>
                                    <tr>
                                        <td><?= (int)$item['id'] ?></td>
                                        <td><?= (int)$item['autre_credit_id'] ?></td>
                                        <td><?= htmlspecialchars((string)$item['action_type']) ?></td>
                                        <td><?= htmlspecialchars((string)($item['source_name'] ?? 'N/A')) ?></td>
                                        <td><?= number_format((float)$item['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars((string)($item['target_account_name'] ?? 'N/A')) ?></td>
                                        <td><?= htmlspecialchars((string)($item['target_location_name'] ?? 'N/A')) ?></td>
                                        <td><?= htmlspecialchars((string)($item['payed_account_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string)($item['due_date'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string)($item['done_date'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string)($item['notes'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4">No history records yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="repayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Repay Autre Credit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="repayForm" method="POST">
                <div class="modal-body">
                    <div class="mb-2 text-muted" id="repayOutstandingInfo"></div>
                    <div class="mb-3">
                        <label class="form-label">Payed Account</label>
                        <select name="payed_account_id" class="form-select" required>
                            <option value="">Select account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int)$account['id'] ?>">
                                    <?= htmlspecialchars($account['account_name']) ?> (Bal: <?= number_format((float)$account['balance'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Repay Amount</label>
                        <input id="repayAmountInput" type="number" class="form-control" name="repay_amount" min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Notes (optional)</label>
                        <textarea class="form-control" name="repay_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Repayment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var repayModalElement = document.getElementById('repayModal');
    var repayModal = repayModalElement ? new bootstrap.Modal(repayModalElement) : null;
    var repayForm = document.getElementById('repayForm');
    var repayAmountInput = document.getElementById('repayAmountInput');
    var repayOutstandingInfo = document.getElementById('repayOutstandingInfo');

    document.querySelectorAll('.repay-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var creditId = this.getAttribute('data-credit-id');
            var outstanding = parseFloat(this.getAttribute('data-outstanding') || '0');

            repayForm.action = '<?= APP_URL ?>/finance/autre-credit/' + creditId + '/repay';
            repayAmountInput.value = '';
            repayAmountInput.max = String(outstanding);
            repayOutstandingInfo.textContent = 'Outstanding amount: ' + outstanding.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (repayModal) {
                repayModal.show();
            }
        });
    });
});
</script>
