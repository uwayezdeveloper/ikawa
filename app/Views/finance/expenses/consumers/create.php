<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Add New Expense Consumer') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/expense-consumers">Expense Consumers</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-plus me-2"></i>Add New Expense Consumer
                </h5>
            </div>
            
            <div class="card-body">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="<?= APP_URL ?>/finance/expense-consumers/store" id="consumerForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="cons_name" class="form-label">Consumer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cons_name" name="cons_name" 
                                       placeholder="Enter consumer name" maxlength="50" required
                                       value="<?= htmlspecialchars($_SESSION['old_input']['cons_name'] ?? '') ?>">
                                <div class="form-text">Maximum 50 characters</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="Enter phone number" maxlength="50" required
                                       value="<?= htmlspecialchars($_SESSION['old_input']['phone'] ?? '') ?>">
                                <div class="form-text">Enter a valid phone number (8-15 digits)</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="sts" class="form-label">Status</label>
                                <select class="form-control" id="sts" name="sts">
                                    <option value="1" <?= ($_SESSION['old_input']['sts'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= ($_SESSION['old_input']['sts'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/finance/expense-consumers" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Save Consumer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Clear old input
unset($_SESSION['old_input']);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for consumer name
    const consNameInput = document.getElementById('cons_name');
    const consNameCounter = document.createElement('small');
    consNameCounter.className = 'text-muted';
    consNameInput.parentNode.appendChild(consNameCounter);
    
    function updateConsNameCounter() {
        const length = consNameInput.value.length;
        consNameCounter.textContent = `${length}/50 characters`;
        if (length > 45) {
            consNameCounter.className = 'text-warning';
        } else if (length === 50) {
            consNameCounter.className = 'text-danger';
        } else {
            consNameCounter.className = 'text-muted';
        }
    }
    
    consNameInput.addEventListener('input', updateConsNameCounter);
    updateConsNameCounter();
    
    // Phone number validation
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        const phone = this.value;
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        
        // Remove invalid feedback first
        this.classList.remove('is-invalid');
        let feedback = this.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
        
        // Validate phone number
        if (phone && (cleanPhone.length < 8 || cleanPhone.length > 15)) {
            this.classList.add('is-invalid');
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Phone number must be between 8-15 digits';
            this.parentNode.appendChild(feedback);
        }
    });
    
    // Form validation
    document.getElementById('consumerForm').addEventListener('submit', function(e) {
        const consName = consNameInput.value.trim();
        const phone = phoneInput.value.trim();
        
        let hasError = false;
        
        if (!consName) {
            e.preventDefault();
            consNameInput.classList.add('is-invalid');
            consNameInput.focus();
            hasError = true;
        }
        
        if (!phone) {
            e.preventDefault();
            phoneInput.classList.add('is-invalid');
            if (!hasError) phoneInput.focus();
            hasError = true;
        }
        
        if (hasError) {
            return false;
        }
        
        // Remove invalid classes
        consNameInput.classList.remove('is-invalid');
        phoneInput.classList.remove('is-invalid');
        return true;
    });
    
    // Remove invalid class on input
    [consNameInput, phoneInput].forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});
</script>