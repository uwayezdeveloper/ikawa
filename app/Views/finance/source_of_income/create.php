<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/source-of-income">Source of Income</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </div>
            <h4 class="page-title"><?= htmlspecialchars($title ?? 'Add New Source of Income') ?></h4>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Source of Income Information</h5>
            </div>
            
            <div class="card-body">
                <!-- Error Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Validation Errors:</strong>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <!-- Create Form -->
                <form method="POST" action="<?= APP_URL ?>/finance/source-of-income/store">
                    <div class="mb-3">
                        <label for="in_name" class="form-label">Source Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="in_name" 
                               name="in_name" 
                               maxlength="50"
                               value="<?= htmlspecialchars($_SESSION['old_input']['in_name'] ?? '') ?>"
                               required
                               placeholder="Enter source name (e.g., Coffee Sales, Consulting Services)">
                        <small class="text-muted">Maximum 50 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="in_descr" class="form-label">Description</label>
                        <textarea class="form-control" 
                                  id="in_descr" 
                                  name="in_descr" 
                                  rows="3"
                                  maxlength="100"
                                  placeholder="Enter description (optional)"><?= htmlspecialchars($_SESSION['old_input']['in_descr'] ?? '') ?></textarea>
                        <small class="text-muted">Maximum 100 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="in_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="in_status" name="in_status" required>
                            <option value="1" <?= (($_SESSION['old_input']['in_status'] ?? 1) == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (($_SESSION['old_input']['in_status'] ?? 1) == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= APP_URL ?>/finance/source-of-income" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Save Source
                        </button>
                    </div>
                </form>
                <?php unset($_SESSION['old_input']); ?>
            </div>
        </div>
    </div>
</div>
