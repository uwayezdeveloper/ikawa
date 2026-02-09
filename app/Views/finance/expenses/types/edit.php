<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Edit Expense Type') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/expense-types">Expense Types</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                    <i class="ti ti-edit me-2"></i>Edit Expense Type
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
                <form method="POST" action="<?= APP_URL ?>/finance/expense-types/<?= $expenseType['expense_id'] ?>/update" id="expenseTypeForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="expense_name" class="form-label">Expense Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="expense_name" name="expense_name" 
                                       placeholder="Enter expense name" maxlength="50" required
                                       value="<?= htmlspecialchars($_SESSION['old_input']['expense_name'] ?? $expenseType['expense_name']) ?>">
                                <div class="form-text">Maximum 50 characters</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="categ_id" class="form-label">Category</label>
                                <select class="form-control" id="categ_id" name="categ_id">
                                    <option value="">Select Category (Optional)</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['categ_id'] ?>" 
                                                <?php 
                                                $selectedCategoryId = $_SESSION['old_input']['categ_id'] ?? $expenseType['categ_id'];
                                                echo $selectedCategoryId == $category['categ_id'] ? 'selected' : '';
                                                ?>>
                                            <?= htmlspecialchars($category['categ_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select a category to group this expense type</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" placeholder="Enter expense description (optional)" 
                                          maxlength="55"><?= htmlspecialchars($_SESSION['old_input']['description'] ?? $expenseType['description'] ?? '') ?></textarea>
                                <div class="form-text">Maximum 55 characters</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="expense_status" class="form-label">Status</label>
                                <select class="form-control" id="expense_status" name="expense_status">
                                    <?php $selectedStatus = $_SESSION['old_input']['expense_status'] ?? $expenseType['expense_status']; ?>
                                    <option value="1" <?= $selectedStatus == 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $selectedStatus == 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/finance/expense-types" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Update Expense Type
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
    // Character counter for expense name
    const expenseNameInput = document.getElementById('expense_name');
    const expenseNameCounter = document.createElement('small');
    expenseNameCounter.className = 'text-muted';
    expenseNameInput.parentNode.appendChild(expenseNameCounter);
    
    function updateExpenseNameCounter() {
        const length = expenseNameInput.value.length;
        expenseNameCounter.textContent = `${length}/50 characters`;
        if (length > 45) {
            expenseNameCounter.className = 'text-warning';
        } else if (length === 50) {
            expenseNameCounter.className = 'text-danger';
        } else {
            expenseNameCounter.className = 'text-muted';
        }
    }
    
    expenseNameInput.addEventListener('input', updateExpenseNameCounter);
    updateExpenseNameCounter();
    
    // Character counter for description
    const descriptionInput = document.getElementById('description');
    const descriptionCounter = document.createElement('small');
    descriptionCounter.className = 'text-muted';
    descriptionInput.parentNode.appendChild(descriptionCounter);
    
    function updateDescriptionCounter() {
        const length = descriptionInput.value.length;
        descriptionCounter.textContent = `${length}/55 characters`;
        if (length > 50) {
            descriptionCounter.className = 'text-warning';
        } else if (length === 55) {
            descriptionCounter.className = 'text-danger';
        } else {
            descriptionCounter.className = 'text-muted';
        }
    }
    
    descriptionInput.addEventListener('input', updateDescriptionCounter);
    updateDescriptionCounter();
    
    // Form validation
    document.getElementById('expenseTypeForm').addEventListener('submit', function(e) {
        const expenseName = expenseNameInput.value.trim();
        
        if (!expenseName) {
            e.preventDefault();
            expenseNameInput.classList.add('is-invalid');
            expenseNameInput.focus();
            return false;
        }
        
        expenseNameInput.classList.remove('is-invalid');
        return true;
    });
    
    // Remove invalid class on input
    expenseNameInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>