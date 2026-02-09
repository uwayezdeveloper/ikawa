<?php
$title = $title ?? 'Request Loan';
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <?php foreach ($breadcrumb as $item): ?>
                        <?php if (!empty($item['url'])): ?>
                            <li class="breadcrumb-item"><a href="<?= $item['url'] ?>"><?= htmlspecialchars($item['name']) ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($item['name']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
            <h4 class="page-title"><?= htmlspecialchars($title) ?></h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Request New Loan</h4>
                
                <form method="POST" action="<?= APP_URL ?>/finance/worker-loans/store" id="loanRequestForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="request_amount" class="form-label">Request Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="request_amount" name="request_amount" 
                                           min="1" step="1" required placeholder="Enter amount">
                                    <span class="input-group-text">RWF</span>
                                </div>
                                <div class="form-text">Minimum amount: 1 RWF</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description/Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          required placeholder="Please describe the purpose of this loan request..."></textarea>
                                <div class="form-text">Provide a detailed description of why you need this loan.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Loan Request Guidelines</h6>
                        <ul class="mb-0">
                            <li>You can only have one pending loan request at a time</li>
                            <li>All loan requests require management approval</li>
                            <li>Approved loans will be tracked until fully repaid</li>
                            <li>Provide a clear and detailed reason for your loan request</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/finance/worker-loans" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Back to Loans
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loanRequestForm').addEventListener('submit', function(e) {
    const amount = document.getElementById('request_amount').value;
    const description = document.getElementById('description').value.trim();
    
    if (parseInt(amount) <= 0) {
        e.preventDefault();
        alert('Request amount must be greater than 0');
        return;
    }
    
    if (description.length < 10) {
        e.preventDefault();
        alert('Please provide a more detailed description (at least 10 characters)');
        return;
    }
    
    if (!confirm('Are you sure you want to submit this loan request?')) {
        e.preventDefault();
    }
});

// Format number input
document.getElementById('request_amount').addEventListener('input', function(e) {
    let value = e.target.value;
    if (value < 0) {
        e.target.value = 0;
    }
});
</script>