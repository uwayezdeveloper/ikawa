<?php
/**
 * Inner Category Types Management
 */
$pageTitle = 'Inner Category Types';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Inner Category Types</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#innerCategoryTypeModal" onclick="resetForm()">
                <i class="ti ti-plus me-1"></i>Add Inner Category Type
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search inner category types..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['categ_id'] ?>"><?= htmlspecialchars($category['categ_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Inner Category Types Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="innerCategoryTypesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Inner Category Name</th>
                            <th>Category</th>
                            <th>Category Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($innerCategoryTypes)): ?>
                            <?php foreach ($innerCategoryTypes as $innerType): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($innerType['inner_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($innerType['category_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($innerType['category_type_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($innerType['description'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?php if ($innerType['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($innerType['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="editInnerCategoryType(<?= htmlspecialchars(json_encode($innerType)) ?>)" 
                                                    title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteInnerCategoryType(<?= $innerType['inner_id'] ?>, '<?= htmlspecialchars($innerType['inner_name']) ?>')" 
                                                    title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="ti ti-info-circle fs-2 text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No inner category types found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Inner Category Type Modal -->
<div class="modal fade" id="innerCategoryTypeModal" tabindex="-1" aria-labelledby="innerCategoryTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="innerCategoryTypeForm" method="POST" action="<?= APP_URL ?>/products/inner-category-types">
                <div class="modal-header">
                    <h5 class="modal-title" id="innerCategoryTypeModalLabel">Add Inner Category Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="inner_id" id="innerId">

                    <!-- Category Selection -->
                    <div class="mb-3">
                        <label for="categId" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="categId" name="categ_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['categ_id'] ?>"><?= htmlspecialchars($category['categ_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Category Type Selection (Dynamic) -->
                    <div class="mb-3">
                        <label for="typeId" class="form-label">Category Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="typeId" name="type_id" required disabled>
                            <option value="">Select Category First</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Inner Category Name -->
                    <div class="mb-3">
                        <label for="innerName" class="form-label">Inner Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="innerName" name="inner_name" 
                               maxlength="50" placeholder="Enter inner category name" required>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Maximum 50 characters</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="2" maxlength="50" placeholder="Enter description (optional)"></textarea>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Maximum 50 characters</small>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="ti ti-plus me-1"></i>Add Inner Category Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="ti ti-alert-circle text-danger fs-1 mb-3"></i>
                <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
// DOM elements
const categIdSelect = document.getElementById('categId');
const typeIdSelect = document.getElementById('typeId');
const innerCategoryTypeForm = document.getElementById('innerCategoryTypeForm');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const statusFilter = document.getElementById('statusFilter');

// Event listeners
categIdSelect.addEventListener('change', loadCategoryTypes);
searchInput.addEventListener('input', filterTable);
categoryFilter.addEventListener('change', filterTable);
statusFilter.addEventListener('change', filterTable);

// Load category types based on selected category
async function loadCategoryTypes() {
    const categoryId = categIdSelect.value;
    
    // Reset and disable type select
    typeIdSelect.innerHTML = '<option value="">Select Category First</option>';
    typeIdSelect.disabled = true;
    
    if (!categoryId) return;
    
    try {
        const response = await fetch(`<?= APP_URL ?>/products/inner-category-types/get-category-types?category_id=${categoryId}`);
        const data = await response.json();
        
        if (data.success) {
            typeIdSelect.innerHTML = '<option value="">Select Category Type</option>';
            
            data.data.forEach(type => {
                const option = document.createElement('option');
                option.value = type.type_id;
                option.textContent = type.type_name;
                typeIdSelect.appendChild(option);
            });
            
            typeIdSelect.disabled = false;
        } else {
            showToast('Error loading category types', 'error');
        }
    } catch (error) {
        console.error('Error loading category types:', error);
        showToast('Error loading category types', 'error');
    }
}

// Reset form for adding new inner category type
function resetForm() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('innerId').value = '';
    document.getElementById('innerCategoryTypeModalLabel').textContent = 'Add Inner Category Type';
    document.getElementById('submitBtn').innerHTML = '<i class="ti ti-plus me-1"></i>Add Inner Category Type';
    
    innerCategoryTypeForm.reset();
    typeIdSelect.innerHTML = '<option value="">Select Category First</option>';
    typeIdSelect.disabled = true;
    
    // Clear validation states
    clearValidationErrors();
}

// Edit inner category type
async function editInnerCategoryType(innerType) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('innerId').value = innerType.inner_id;
    document.getElementById('innerCategoryTypeModalLabel').textContent = 'Edit Inner Category Type';
    document.getElementById('submitBtn').innerHTML = '<i class="ti ti-save me-1"></i>Update Inner Category Type';
    
    // Set form values
    document.getElementById('categId').value = innerType.categ_id;
    document.getElementById('innerName').value = innerType.inner_name;
    document.getElementById('description').value = innerType.description || '';
    document.getElementById('status').checked = innerType.status == 1;
    
    // Load category types for the selected category
    await loadCategoryTypes();
    
    // Set the type value after category types are loaded
    setTimeout(() => {
        document.getElementById('typeId').value = innerType.type_id;
    }, 100);
    
    // Show modal
    new bootstrap.Modal(document.getElementById('innerCategoryTypeModal')).show();
}

// Delete inner category type
function deleteInnerCategoryType(innerId, innerName) {
    document.getElementById('deleteItemName').textContent = innerName;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
    
    document.getElementById('confirmDelete').onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= APP_URL ?>/products/inner-category-types';
        
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="inner_id" value="${innerId}">
        `;
        
        document.body.appendChild(form);
        form.submit();
    };
}

// Filter table
function filterTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value;
    const selectedStatus = statusFilter.value;
    
    const table = document.getElementById('innerCategoryTypesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        if (row.cells.length < 7) continue; // Skip message rows
        
        const innerName = row.cells[0].textContent.toLowerCase();
        const categoryName = row.cells[1].textContent.toLowerCase();
        const typeName = row.cells[2].textContent.toLowerCase();
        const description = row.cells[3].textContent.toLowerCase();
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !innerName.includes(searchTerm) && !categoryName.includes(searchTerm) && 
            !typeName.includes(searchTerm) && !description.includes(searchTerm)) {
            showRow = false;
        }
        
        // Category filter
        if (selectedCategory) {
            // This would need more sophisticated filtering based on actual category IDs
            // For now, we'll filter by category name
            if (!categoryName.includes(selectedCategory.toLowerCase())) {
                showRow = false;
            }
        }
        
        // Status filter
        if (selectedStatus !== '') {
            const statusBadge = row.cells[4].querySelector('.badge');
            const isActive = statusBadge && statusBadge.textContent.trim() === 'Active';
            if ((selectedStatus === '1' && !isActive) || (selectedStatus === '0' && isActive)) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// Clear validation errors
function clearValidationErrors() {
    const invalidInputs = document.querySelectorAll('.is-invalid');
    invalidInputs.forEach(input => {
        input.classList.remove('is-invalid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    });
}

// Show toast notification
function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : 'success'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to page and show
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}

// Form submission
innerCategoryTypeForm.addEventListener('submit', function(e) {
    clearValidationErrors();
});
</script>

<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.modal-dialog {
    max-width: 500px;
}

.table-responsive {
    border-radius: 0.375rem;
}
</style>