<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Worker Loan Statement</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/loans">Finance</a></li>
                    <li class="breadcrumb-item active">Loan Statement</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Select Worker for Loan Statement</h4>
            </div>
            <div class="card-body">
                <?php if (empty($workers)): ?>
                <div class="text-center py-5">
                    <i class="ti ti-file-x fs-48 text-muted mb-3"></i>
                    <h5 class="text-muted">No Workers with Loans Found</h5>
                    <p class="text-muted">No workers have loan records to generate statements for.</p>
                    <a href="<?= APP_URL ?>/finance/loans" class="btn btn-primary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Loans
                    </a>
                </div>
                <?php else: ?>
                
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <form action="<?= APP_URL ?>/finance/loans/statement/generate" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="user_id" class="form-label">Select Worker <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Choose a worker...</option>
                                    <?php foreach ($workers as $worker): ?>
                                    <option value="<?= $worker['id'] ?>">
                                        <?= htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']) ?> 
                                        (<?= htmlspecialchars($worker['email']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a worker.
                                </div>
                            </div>

                            <!-- Date Range Filter -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">From Date (Optional)</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                    <div class="form-text">Leave empty to show all transactions</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">To Date (Optional)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                    <div class="form-text">Leave empty to show all transactions</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="<?= APP_URL ?>/finance/loans" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Loans
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-receipt me-1"></i>Generate Statement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Access Section -->
                <hr class="my-5">
                <div class="row">
                    <div class="col-12">
                        <h5 class="mb-3">Quick Access - Workers with Recent Loan Activity</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Worker Name</th>
                                        <th>Email</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workers as $worker): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <i class="ti ti-user text-muted"></i>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($worker['email']) ?></td>
                                        <td>
                                            <a href="<?= APP_URL ?>/finance/loans/statement/<?= $worker['id'] ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bx bx-receipt me-1"></i>View Statement
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Date range validation
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    const endDateInput = document.getElementById('end_date');
    
    if (startDate) {
        endDateInput.setAttribute('min', startDate);
        if (endDateInput.value && endDateInput.value < startDate) {
            endDateInput.value = '';
        }
    } else {
        endDateInput.removeAttribute('min');
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    const endDate = this.value;
    const startDateInput = document.getElementById('start_date');
    
    if (endDate) {
        startDateInput.setAttribute('max', endDate);
        if (startDateInput.value && startDateInput.value > endDate) {
            startDateInput.value = '';
        }
    } else {
        startDateInput.removeAttribute('max');
    }
});
</script>