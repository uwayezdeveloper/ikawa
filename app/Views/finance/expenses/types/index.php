<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Expense Types') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Expense Types</li>
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
                    <h5 class="card-title mb-0">Expense Types Management</h5>
                    <a href="<?= APP_URL ?>/finance/expense-types/create" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Add New Type
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
                        <form method="GET" action="<?= APP_URL ?>/finance/expense-types" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" 
                                   placeholder="Search expense types..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="ti ti-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= APP_URL ?>/finance/expense-types" class="btn btn-outline-secondary ms-2">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted">Total: <?= $pagination['total'] ?? 0 ?> expense types</span>
                    </div>
                </div>

                <!-- Expense Types Table -->
                <?php if (!empty($expenseTypes)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expense Name</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?>
                                <?php foreach ($expenseTypes as $expenseType): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($expenseType['expense_name']) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($expenseType['categ_name']): ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($expenseType['categ_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No Category</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= !empty($expenseType['description']) ? htmlspecialchars($expenseType['description']) : '<span class="text-muted">No description</span>' ?>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-id="<?= $expenseType['expense_id'] ?>"
                                                       <?= $expenseType['expense_status'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label">
                                                    <span class="badge bg-<?= $expenseType['expense_status'] == 1 ? 'success' : 'danger' ?>">
                                                        <?= $expenseType['expense_status'] == 1 ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/finance/expense-types/<?= $expenseType['expense_id'] ?>/edit" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                                        data-id="<?= $expenseType['expense_id'] ?>"
                                                        data-name="<?= htmlspecialchars($expenseType['expense_name']) ?>"
                                                        title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Expense types pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-types?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $pagination['current_page'] - 2);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-types?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-types?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="ti ti-folder-open display-4 text-muted"></i>
                        </div>
                        <h5 class="text-muted">No expense types found</h5>
                        <p class="text-muted">
                            <?= !empty($search) ? 'No expense types match your search criteria.' : 'Start by adding your first expense type.' ?>
                        </p>
                        <?php if (empty($search)): ?>
                            <a href="<?= APP_URL ?>/finance/expense-types/create" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Add First Expense Type
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the expense type "<span id="expenseTypeName"></span>"?</p>
                <p class="text-danger"><i class="ti ti-alert-triangle"></i> This action cannot be undone.</p>
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
            const expenseTypeId = this.dataset.id;
            const isChecked = this.checked;
            
            fetch(`<?= APP_URL ?>/finance/expense-types/${expenseTypeId}/toggle-status`, {
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
    const expenseTypeNameSpan = document.getElementById('expenseTypeName');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const expenseTypeId = this.dataset.id;
            const expenseTypeName = this.dataset.name;
            
            expenseTypeNameSpan.textContent = expenseTypeName;
            deleteForm.action = `<?= APP_URL ?>/finance/expense-types/${expenseTypeId}/delete`;
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
    
    const container = document.querySelector('.row').parentNode;
    const firstRow = container.querySelector('.row');
    container.insertBefore(alertDiv, firstRow);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>