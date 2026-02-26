<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Request Loan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Request Loan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Submit Loan Request</h4>
            </div>
            <div class="card-body">
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

                <form action="<?= APP_URL ?>/finance/loans/store" method="POST">
                    <div class="mb-3">
                        <label for="request_amount" class="form-label">Request Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RWF</span>
                            <input type="number" class="form-control" id="request_amount" name="request_amount" 
                                   required min="1" step="1" placeholder="Enter amount">
                        </div>
                        <small class="form-text text-muted">Enter the amount you want to borrow</small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description/Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  required placeholder="Please explain why you need this loan..."></textarea>
                        <small class="form-text text-muted">Provide details about why you need this loan</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Your loan request will be submitted with a <strong>pending</strong> status and will be reviewed by the administration.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/finance/loans/my-loans" class="btn btn-secondary">
                            <i class="ti ti-eye me-1"></i>
                            View My Loans
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>