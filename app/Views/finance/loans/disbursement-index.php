<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Loan Disbursement</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Loan Disbursement</li>
                </ol>
            </div>
        </div>
    </div>
</div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Loans Ready for Disbursement Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Outstanding Loans Ready for Disbursement</h5>
        </div>
        
        <div class="card-body">
            <?php if (empty($loans)): ?>
                <div class="text-center py-4">
                    <i class="bx bx-money-withdraw bx-lg text-muted mb-3"></i>
                    <p class="text-muted">No outstanding loans available for disbursement.</p>
                    <a href="<?= APP_URL ?>/finance/loans" class="btn btn-outline-primary">
                        <i class="bx bx-arrow-back me-2"></i>Back to All Loans
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Loan ID</th>
                                <th>Employee</th>
                                <th>Email</th>
                                <th>Request Amount</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= htmlspecialchars($loan['l_id']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-wrapper">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        <?= strtoupper(substr($loan['first_name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($loan['email']) ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            RWF <?= number_format($loan['request_amount']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px; display: inline-block;" 
                                              title="<?= htmlspecialchars($loan['description']) ?>">
                                            <?= htmlspecialchars(strlen($loan['description']) > 50 ? substr($loan['description'], 0, 50) . '...' : $loan['description']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?= ucfirst(htmlspecialchars($loan['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?= APP_URL ?>/finance/loans/disbursement/<?= $loan['l_id'] ?>">
                                                        <i class="bx bx-money-withdraw me-2"></i>Disburse Loan
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="<?= APP_URL ?>/finance/loans/<?= $loan['l_id'] ?>">
                                                        <i class="bx bx-show me-2"></i>View Details
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>