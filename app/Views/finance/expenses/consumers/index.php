<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Expense Consumers') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Expense Consumers</li>
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
                    <h5 class="card-title mb-0">Expense Consumers Management</h5>
                    <a href="<?= APP_URL ?>/finance/expense-consumers/create" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Add New Consumer
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
                        <form method="GET" action="<?= APP_URL ?>/finance/expense-consumers" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" 
                                   placeholder="Search consumers..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="ti ti-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= APP_URL ?>/finance/expense-consumers" class="btn btn-outline-secondary ms-2">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted">Total: <?= $pagination['total'] ?? 0 ?> consumers</span>
                    </div>
                </div>

                <!-- Consumers Table -->
                <?php if (!empty($consumers)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Consumer Name</th>
                                    <th>Phone Number</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?>
                                <?php foreach ($consumers as $consumer): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($consumer['cons_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <i class="ti ti-phone me-1"></i><?= htmlspecialchars($consumer['phone']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-id="<?= $consumer['cons_id'] ?>"
                                                       <?= $consumer['sts'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label">
                                                    <span class="badge bg-<?= $consumer['sts'] == 1 ? 'success' : 'danger' ?>">
                                                        <?= $consumer['sts'] == 1 ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/finance/expense-consumers/<?= $consumer['cons_id'] ?>/edit" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                                        data-id="<?= $consumer['cons_id'] ?>"
                                                        data-name="<?= htmlspecialchars($consumer['cons_name']) ?>"
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
                        <nav aria-label="Consumers pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-consumers?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
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
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-consumers?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= APP_URL ?>/finance/expense-consumers?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
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
                            <i class="ti ti-users display-4 text-muted"></i>
                        </div>
                        <h5 class="text-muted">No expense consumers found</h5>
                        <p class="text-muted">
                            <?= !empty($search) ? 'No consumers match your search criteria.' : 'Start by adding your first expense consumer.' ?>
                        </p>
                        <?php if (empty($search)): ?>
                            <a href="<?= APP_URL ?>/finance/expense-consumers/create" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Add First Consumer
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
                <p>Are you sure you want to delete the consumer "<span id="consumerName"></span>"?</p>
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
            const consumerId = this.dataset.id;
            const isChecked = this.checked;
            
            fetch(`<?= APP_URL ?>/finance/expense-consumers/${consumerId}/toggle-status`, {
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
    const consumerNameSpan = document.getElementById('consumerName');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const consumerId = this.dataset.id;
            const consumerName = this.dataset.name;
            
            consumerNameSpan.textContent = consumerName;
            deleteForm.action = `<?= APP_URL ?>/finance/expense-consumers/${consumerId}/delete`;
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