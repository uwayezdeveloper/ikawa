<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-supplier-types', $permissions);
$canEdit = in_array('edit-supplier-types', $permissions);
$canDelete = in_array('delete-supplier-types', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Supplier Types</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/suppliers">Suppliers</a></li>
                <li class="breadcrumb-item active">Types</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?= addslashes($_SESSION['flash_success']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
});
</script>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?= addslashes($_SESSION['flash_error']) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
});
</script>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- Supplier Types Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search types..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Status Filter -->
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                    <option value="All">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
            <!-- Records Per Page -->
            <div>
                <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
            <?php if ($canCreate): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                <i class="ti ti-plus me-1"></i> Add Type
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Type Name</th>
                    <th data-table-sort>Description</th>
                    <th>Identifiers</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($supplierTypes)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 6 : 5 ?>" class="text-center py-4">
                        <i class="ti ti-truck fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No supplier types found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($supplierTypes as $index => $type): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($type['name']) ?></h5>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($type['description'] ?? '-') ?></span>
                    </td>
                    <td>
                        <?php 
                        $identifierCount = $type['identifier_count'] ?? 0;
                        $identifierNames = $type['identifier_names'] ?? '';
                        ?>
                        <button type="button" class="btn btn-sm btn-soft-info btn-manage-identifiers"
                            data-id="<?= $type['id'] ?>" data-name="<?= htmlspecialchars($type['name']) ?>"
                            data-bs-toggle="modal" data-bs-target="#manageIdentifiersModal"
                            title="<?= htmlspecialchars($identifierNames) ?>">
                            <i class="ti ti-id me-1"></i> <?= $identifierCount ?>
                            Identifier<?= $identifierCount != 1 ? 's' : '' ?>
                        </button>
                    </td>
                    <td data-column="status">
                        <?php if ($type['status'] === 'active'): ?>
                        <span class="badge bg-success-subtle text-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-type"
                                data-id="<?= $type['id'] ?>"
                                data-name="<?= htmlspecialchars($type['name']) ?>"
                                data-description="<?= htmlspecialchars($type['description'] ?? '') ?>"
                                data-status="<?= $type['status'] ?>" data-bs-toggle="modal"
                                data-bs-target="#editTypeModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-toggle-status"
                                data-id="<?= $type['id'] ?>" data-name="<?= htmlspecialchars($type['name']) ?>"
                                data-status="<?= $type['status'] ?>">
                                <?php if ($type['status'] === 'active'): ?>
                                <i class="ti ti-ban fs-lg text-warning"></i>
                                <?php else: ?>
                                <i class="ti ti-check fs-lg text-success"></i>
                                <?php endif; ?>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-type"
                                data-id="<?= $type['id'] ?>" data-name="<?= htmlspecialchars($type['name']) ?>">
                                <i class="ti ti-trash fs-lg text-danger"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer border-light d-flex justify-content-between align-items-center">
        <div data-table-pagination-info></div>
        <div data-table-pagination></div>
    </div>
</div>

<?php if ($canCreate): ?>
<!-- Add Type Modal -->
<div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Supplier Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/suppliers/types" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Type Modal -->
<div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/suppliers/types" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toggle Status Form -->
<form id="toggleStatusForm" action="<?= APP_URL ?>/suppliers/types" method="POST" style="display: none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id" id="toggleStatusId">
</form>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Form -->
<form id="deleteForm" action="<?= APP_URL ?>/suppliers/types" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>
<?php endif; ?>

<!-- Manage Identifiers Modal -->
<div class="modal fade" id="manageIdentifiersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Identifiers - <span id="identifierTypeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="currentTypeId">

                <!-- Add Identifier Form -->
                <?php if ($canEdit): ?>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Add New Identifier</h6>
                        <form id="addIdentifierForm">
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Identifier Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="newIdentifierName"
                                        placeholder="e.g., TIN Number, License Number" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Required?</label>
                                    <select class="form-select" id="newIdentifierRequired">
                                        <option value="0">Optional</option>
                                        <option value="1">Required</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="newIdentifierStatus">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-plus me-1"></i> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Identifiers List -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Identifier Name</th>
                                <th>Required</th>
                                <th>Status</th>
                                <?php if ($canEdit): ?>
                                <th class="text-center">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="identifiersList">
                            <tr>
                                <td colspan="<?= $canEdit ? 5 : 4 ?>" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mb-0 mt-2">Loading identifiers...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;

    // Edit button click handler
    document.querySelectorAll('.btn-edit-type').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editDescription').value = this.dataset.description;
            document.getElementById('editStatus').value = this.dataset.status;
        });
    });

    // Toggle status button
    document.querySelectorAll('.btn-toggle-status').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const status = this.dataset.status;
            const newStatus = status === 'active' ? 'inactive' : 'active';

            Swal.fire({
                title: 'Change Status?',
                text: 'Are you sure you want to set "' + name + '" as ' + newStatus +
                    '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('toggleStatusId').value = id;
                    document.getElementById('toggleStatusForm').submit();
                }
            });
        });
    });

    // Delete button
    document.querySelectorAll('.btn-delete-type').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Supplier Type?',
                text: 'Are you sure you want to delete "' + name +
                    '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        });
    });

    // Manage Identifiers button
    document.querySelectorAll('.btn-manage-identifiers').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const typeId = this.dataset.id;
            const typeName = this.dataset.name;

            document.getElementById('currentTypeId').value = typeId;
            document.getElementById('identifierTypeName').textContent = typeName;

            loadIdentifiers(typeId);
        });
    });

    // Load identifiers for a supplier type
    function loadIdentifiers(typeId) {
        const tbody = document.getElementById('identifiersList');
        tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mb-0 mt-2">Loading identifiers...</p>
        </td></tr>`;

        fetch('<?= APP_URL ?>/suppliers/types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_identifiers&supplier_type_id=' + typeId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderIdentifiers(data.identifiers);
                } else {
                    tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4 text-danger">
                    <i class="ti ti-alert-circle fs-1"></i>
                    <p class="mb-0">Error loading identifiers</p>
                </td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4 text-danger">
                <i class="ti ti-alert-circle fs-1"></i>
                <p class="mb-0">Error loading identifiers</p>
            </td></tr>`;
            });
    }

    // Render identifiers table
    function renderIdentifiers(identifiers) {
        const tbody = document.getElementById('identifiersList');

        if (identifiers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4">
                <i class="ti ti-id fs-1 text-muted"></i>
                <p class="text-muted mb-0">No identifiers defined for this type</p>
            </td></tr>`;
            return;
        }

        let html = '';
        identifiers.forEach((identifier, index) => {
            html += `<tr data-id="${identifier.id}">
                <td>${index + 1}</td>
                <td>
                    <span class="identifier-name">${escapeHtml(identifier.name)}</span>
                </td>
                <td>
                    ${identifier.is_required == 1 
                        ? '<span class="badge bg-danger-subtle text-danger">Required</span>' 
                        : '<span class="badge bg-secondary-subtle text-secondary">Optional</span>'}
                </td>
                <td>
                    ${identifier.status === 'active' 
                        ? '<span class="badge bg-success-subtle text-success">Active</span>' 
                        : '<span class="badge bg-danger-subtle text-danger">Inactive</span>'}
                </td>
                ${canEdit ? `<td class="text-center">
                    <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-identifier" 
                        data-id="${identifier.id}" 
                        data-name="${escapeHtml(identifier.name)}"
                        data-required="${identifier.is_required}"
                        data-status="${identifier.status}">
                        <i class="ti ti-edit fs-lg"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-icon btn-sm btn-toggle-identifier"
                        data-id="${identifier.id}"
                        data-name="${escapeHtml(identifier.name)}"
                        data-status="${identifier.status}">
                        ${identifier.status === 'active' 
                            ? '<i class="ti ti-ban fs-lg text-warning"></i>' 
                            : '<i class="ti ti-check fs-lg text-success"></i>'}
                    </button>
                    <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-identifier"
                        data-id="${identifier.id}"
                        data-name="${escapeHtml(identifier.name)}">
                        <i class="ti ti-trash fs-lg text-danger"></i>
                    </button>
                </td>` : ''}
            </tr>`;
        });

        tbody.innerHTML = html;
        attachIdentifierEventListeners();
    }

    // Attach event listeners to identifier buttons
    function attachIdentifierEventListeners() {
        // Edit identifier
        document.querySelectorAll('.btn-edit-identifier').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const required = this.dataset.required;
                const status = this.dataset.status;

                Swal.fire({
                    title: 'Edit Identifier',
                    html: `
                        <div class="text-start">
                            <div class="mb-3">
                                <label class="form-label">Identifier Name <span class="text-danger">*</span></label>
                                <input type="text" id="swal-name" class="form-control" value="${name}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Required?</label>
                                <select id="swal-required" class="form-select">
                                    <option value="0" ${required == 0 ? 'selected' : ''}>Optional</option>
                                    <option value="1" ${required == 1 ? 'selected' : ''}>Required</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select id="swal-status" class="form-select">
                                    <option value="active" ${status === 'active' ? 'selected' : ''}>Active</option>
                                    <option value="inactive" ${status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    preConfirm: () => {
                        const newName = document.getElementById('swal-name').value;
                        if (!newName) {
                            Swal.showValidationMessage(
                                'Identifier name is required');
                            return false;
                        }
                        return {
                            name: newName,
                            is_required: document.getElementById('swal-required')
                                .value,
                            status: document.getElementById('swal-status').value
                        };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        updateIdentifier(id, result.value);
                    }
                });
            });
        });

        // Toggle identifier status
        document.querySelectorAll('.btn-toggle-identifier').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const status = this.dataset.status;
                const newStatus = status === 'active' ? 'inactive' : 'active';

                Swal.fire({
                    title: 'Change Status?',
                    text: `Set "${name}" as ${newStatus}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        toggleIdentifierStatus(id);
                    }
                });
            });
        });

        // Delete identifier
        document.querySelectorAll('.btn-delete-identifier').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                Swal.fire({
                    title: 'Delete Identifier?',
                    text: `Are you sure you want to delete "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        deleteIdentifier(id);
                    }
                });
            });
        });
    }

    // Add identifier form submission
    const addIdentifierForm = document.getElementById('addIdentifierForm');
    if (addIdentifierForm) {
        addIdentifierForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const typeId = document.getElementById('currentTypeId').value;
            const name = document.getElementById('newIdentifierName').value;
            const isRequired = document.getElementById('newIdentifierRequired').value;
            const status = document.getElementById('newIdentifierStatus').value;

            if (!name.trim()) {
                Swal.fire('Error', 'Identifier name is required', 'error');
                return;
            }

            fetch('<?= APP_URL ?>/suppliers/types', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add_identifier&supplier_type_id=${typeId}&name=${encodeURIComponent(name)}&is_required=${isRequired}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        document.getElementById('newIdentifierName').value = '';
                        loadIdentifiers(typeId);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to add identifier', 'error');
                });
        });
    }

    // Update identifier
    function updateIdentifier(id, data) {
        const typeId = document.getElementById('currentTypeId').value;

        fetch('<?= APP_URL ?>/suppliers/types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_identifier&id=${id}&name=${encodeURIComponent(data.name)}&is_required=${data.is_required}&status=${data.status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    loadIdentifiers(typeId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to update identifier', 'error');
            });
    }

    // Toggle identifier status
    function toggleIdentifierStatus(id) {
        const typeId = document.getElementById('currentTypeId').value;

        fetch('<?= APP_URL ?>/suppliers/types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_identifier_status&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    loadIdentifiers(typeId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to toggle status', 'error');
            });
    }

    // Delete identifier
    function deleteIdentifier(id) {
        const typeId = document.getElementById('currentTypeId').value;

        fetch('<?= APP_URL ?>/suppliers/types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_identifier&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    loadIdentifiers(typeId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Failed to delete identifier', 'error');
            });
    }

    // Escape HTML helper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>