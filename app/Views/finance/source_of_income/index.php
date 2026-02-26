<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item active">Source of Income</li>
                </ol>
            </div>
            <h4 class="page-title"><?= htmlspecialchars($title ?? 'Source of Income') ?></h4>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Source of Income Management</h5>
                    <a href="<?= APP_URL ?>/finance/source-of-income/create" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Add New Source
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
                        <form method="GET" action="<?= APP_URL ?>/finance/source-of-income" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" 
                                   placeholder="Search sources..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="ti ti-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= APP_URL ?>/finance/source-of-income" class="btn btn-outline-secondary ms-2">
                                    <i class="ti ti-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted">Total: <?= $pagination['total'] ?? 0 ?> sources</span>
                    </div>
                </div>

                <!-- Sources Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Source Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sources)): ?>
                                <?php foreach ($sources as $source): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($source['in_id']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($source['in_name']) ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($source['in_descr'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-id="<?= $source['in_id'] ?>"
                                                       <?= $source['in_status'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label">
                                                    <span class="badge bg-<?= $source['in_status'] == 1 ? 'success' : 'danger' ?>">
                                                        <?= $source['in_status'] == 1 ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d H:i', strtotime($source['reg_date'] ?? '')) ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= APP_URL ?>/finance/source-of-income/<?= $source['in_id'] ?>/edit" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                                        data-id="<?= $source['in_id'] ?>"
                                                        data-name="<?= htmlspecialchars($source['in_name']) ?>"
                                                        title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="ti ti-folder-open display-4 text-muted"></i>
                                        <p class="text-muted mt-2">No sources of income found</p>
                                        <?php if (empty($search)): ?>
                                            <a href="<?= APP_URL ?>/finance/source-of-income/create" class="btn btn-primary">
                                                Add First Source
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
                    <nav aria-label="Sources pagination">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the source "<span id="sourceName"></span>"?</p>
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
    // Status toggle
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const badge = this.nextElementSibling.querySelector('.badge');
            
            fetch('<?= APP_URL ?>/finance/source-of-income/' + id + '/toggle-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    badge.className = 'badge bg-' + (data.status == 1 ? 'success' : 'danger');
                    badge.textContent = data.status == 1 ? 'Active' : 'Inactive';
                } else {
                    alert('Failed to update status');
                    this.checked = !this.checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
                this.checked = !this.checked;
            });
        });
    });
    
    // Delete button
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            document.getElementById('sourceName').textContent = name;
            document.getElementById('deleteForm').action = '<?= APP_URL ?>/finance/source-of-income/' + id + '/delete';
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
    
    // Handle delete form submission
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch(this.action, {
            method: 'POST',
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to delete');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    });
});
</script>
