<?php include_once '../app/Views/layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Expense Categories') ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Expense Management</a></li>
                        <li class="breadcrumb-item active">Expense Categories</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Expense Categories Management</h5>
                        <a href="/expenses/categories/create" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>Add New Category
                        </a>
                    </div>
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

                    <!-- Search Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="/expenses/categories" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" 
                                       placeholder="Search categories..." 
                                       value="<?= htmlspecialchars($search ?? '') ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bx bx-search"></i>
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="/expenses/categories" class="btn btn-outline-secondary ms-2">
                                        <i class="bx bx-x"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted">Total: <?= $pagination['total'] ?? 0 ?> categories</span>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['categ_id']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($category['categ_name']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($category['description'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" 
                                                           type="checkbox" 
                                                           data-id="<?= $category['categ_id'] ?>"
                                                           <?= $category['status'] == 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label">
                                                        <span class="badge bg-<?= $category['status'] == 1 ? 'success' : 'danger' ?>">
                                                            <?= $category['status'] == 1 ? 'Active' : 'Inactive' ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/expenses/categories/<?= $category['categ_id'] ?>/edit" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger delete-btn" 
                                                            data-id="<?= $category['categ_id'] ?>"
                                                            data-name="<?= htmlspecialchars($category['categ_name']) ?>"
                                                            title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="bx bx-folder-open display-4 text-muted"></i>
                                            <p class="text-muted mt-2">No expense categories found</p>
                                            <?php if (empty($search)): ?>
                                                <a href="/expenses/categories/create" class="btn btn-primary">
                                                    Add First Category
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                        <nav aria-label="Categories pagination">
                            <ul class="pagination justify-content-center">
                                <!-- Previous Page -->
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php 
                                $start = max(1, $pagination['current_page'] - 2);
                                $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Page -->
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category "<span id="categoryName"></span>"?</p>
                <p class="text-danger"><i class="bx bx-warning"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status toggle
    const statusToggles = document.querySelectorAll('.status-toggle');
    statusToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const categoryId = this.dataset.id;
            const isChecked = this.checked;
            
            fetch(`/expenses/categories/${categoryId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: isChecked ? 1 : 0 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update badge
                    const badge = this.nextElementSibling.querySelector('.badge');
                    if (isChecked) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'badge bg-danger';
                        badge.textContent = 'Inactive';
                    }
                    
                    // Show success message
                    showAlert('success', data.message);
                } else {
                    // Revert toggle
                    this.checked = !isChecked;
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                // Revert toggle
                this.checked = !isChecked;
                showAlert('error', 'An error occurred while updating status');
                console.error('Error:', error);
            });
        });
    });
    
    // Handle delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const categoryNameSpan = document.getElementById('categoryName');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            const categoryName = this.dataset.name;
            
            categoryNameSpan.textContent = categoryName;
            deleteForm.action = `/expenses/categories/${categoryId}/delete`;
            deleteModal.show();
        });
    });
});

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    const firstCard = container.querySelector('.card');
    container.insertBefore(alertDiv, firstCard);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php include_once '../app/Views/layouts/footer.php'; ?>