<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Proforma Invoices') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Proforma Invoices</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-file-invoice me-2"></i>Proforma Invoices
                </h5>
                <a href="<?= APP_URL ?>/finance/proforma-invoice/create" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Create Invoice
                </a>
            </div>
            
            <div class="card-body">
                <!-- Flash Messages -->
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

                <!-- Invoices Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice Code</th>
                                <th>Client</th>
                                <th>Currency</th>
                                <th>Invoice Type</th>
                                <th>Total Amount</th>
                                <th>Prepared By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($invoices)): ?>
                                <?php foreach ($invoices as $index => $invoice): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($invoice['code']) ?></strong></td>
                                        <td><?= htmlspecialchars($invoice['client_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($invoice['currency_name'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($invoice['invoice_type']) ?></td>
                                        <td>
                                            <strong class="text-success">
                                                <?= htmlspecialchars($invoice['currency_sign'] ?? '$') ?>
                                                <?= number_format($invoice['total_amount'], 2) ?>
                                            </strong>
                                        </td>
                                        <td><?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?></td>
                                        <td><?= date('M j, Y', strtotime($invoice['created_at'])) ?></td>
                                        <td>
                                            <a href="<?= APP_URL ?>/finance/proforma-invoice/<?= $invoice['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="ti ti-inbox fs-48 text-muted mb-2 d-block"></i>
                                        <p class="text-muted">No proforma invoices found</p>
                                        <a href="<?= APP_URL ?>/finance/proforma-invoice/create" class="btn btn-sm btn-primary">
                                            <i class="ti ti-plus me-1"></i>Create First Invoice
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>