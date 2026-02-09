<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Edit Expense Category') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/expense-categories">Expense Categories</a></li>
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
                    <i class="ti ti-edit me-2"></i>Edit Expense Category
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
                <form method="POST" action="<?= APP_URL ?>/finance/expense-categories/<?= $category['categ_id'] ?>/update" id="categoryForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="categ_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="categ_name" name="categ_name" 
                                       placeholder="Enter category name" maxlength="50" required
                                       value="<?= htmlspecialchars($_SESSION['old_input']['categ_name'] ?? $category['categ_name']) ?>">
                                <div class="form-text">Maximum 50 characters</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" placeholder="Enter category description (optional)" 
                                          maxlength="50"><?= htmlspecialchars($_SESSION['old_input']['description'] ?? $category['description'] ?? '') ?></textarea>
                                <div class="form-text">Maximum 50 characters</div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <?php $currentStatus = $_SESSION['old_input']['status'] ?? $category['status'] ?>
                                    <option value="1" <?= $currentStatus == 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $currentStatus == 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= APP_URL ?>/finance/expense-categories" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to List
                        </a>
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Update Category
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('categoryForm');
    const categNameInput = document.getElementById('categ_name');
    const descriptionInput = document.getElementById('description');
    
    // Real-time validation
    categNameInput.addEventListener('input', function() {
        const value = this.value.trim();
        const feedback = this.nextElementSibling;
        
        if (value.length === 0) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            feedback.textContent = 'Category name is required';
            feedback.className = 'invalid-feedback';
        } else if (value.length > 50) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            feedback.textContent = 'Category name must not exceed 50 characters';
            feedback.className = 'invalid-feedback';
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            feedback.textContent = `${value.length}/50 characters`;
            feedback.className = 'form-text text-success';
        }
    });
    
    descriptionInput.addEventListener('input', function() {
        const value = this.value.trim();
        const feedback = this.nextElementSibling;
        
        if (value.length > 50) {
            this.classList.add('is-invalid');
            feedback.textContent = 'Description must not exceed 50 characters';
            feedback.className = 'invalid-feedback';
        } else {
            this.classList.remove('is-invalid');
            feedback.textContent = `${value.length}/50 characters`;
            feedback.className = 'form-text';
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const categName = categNameInput.value.trim();
        
        if (!categName) {
            e.preventDefault();
            categNameInput.focus();
            return false;
        }
        
        if (categName.length > 50) {
            e.preventDefault();
            categNameInput.focus();
            return false;
        }
        
        if (descriptionInput.value.length > 50) {
            e.preventDefault();
            descriptionInput.focus();
            return false;
        }
    });
});
</script>

<?php 
// Clear old input
unset($_SESSION['old_input']);
?>