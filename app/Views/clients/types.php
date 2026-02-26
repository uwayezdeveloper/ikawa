<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-suppliers', $permissions);
$canEdit = in_array('edit-suppliers', $permissions);
$canDelete = in_array('delete-suppliers', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Client Types</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/clients">Clients</a></li>
                <li class="breadcrumb-item active">Types</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Client Types Table -->
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
            <div class="app-search">
                <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                    <option value="All">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
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
                <?php if (empty($clientTypes)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 6 : 5 ?>" class="text-center py-4">
                        <i class="ti ti-users-group fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No client types found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($clientTypes as $index => $type): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($type['name']) ?></h5>
                        <small class="text-muted">ID: <?= htmlspecialchars($type['identifier']) ?></small>
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
                                data-identifier="<?= htmlspecialchars($type['identifier']) ?>"
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
                <h5 class="modal-title">Add Client Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/client-types/action" method="POST" id="addTypeForm">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="addTypeName" required placeholder="e.g., Corporate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Identifier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="identifier" id="addTypeIdentifier" required placeholder="e.g., corporate">
                        <small class="text-muted">Unique key (lowercase, no spaces)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Describe this client type..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
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
                <h5 class="modal-title">Edit Client Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/client-types/action" method="POST" id="editTypeForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Identifier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="identifier" id="editIdentifier" required>
                        <small class="text-muted">Unique key (lowercase, no spaces)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="editStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toggle Status Form -->
<form id="toggleStatusForm" action="<?= APP_URL ?>/client-types/action" method="POST" style="display:none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id" id="toggleId">
</form>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Form -->
<form id="deleteForm" action="<?= APP_URL ?>/client-types/action" method="POST" style="display:none;">
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
    const canDelete = <?= $canDelete ? 'true' : 'false' ?>;

    // Auto-generate identifier from name
    const addTypeName = document.getElementById('addTypeName');
    if (addTypeName) {
        addTypeName.addEventListener('input', function() {
            const identifier = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            document.getElementById('addTypeIdentifier').value = identifier;
        });
    }

    // Edit button click handler
    document.querySelectorAll('.btn-edit-type').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editIdentifier').value = this.dataset.identifier;
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
                text: `Set "${name}" to ${newStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= APP_URL ?>/client-types/action', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=toggle_status&id=${id}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Success', text: data.message, timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
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
                title: 'Delete Type?',
                text: `Are you sure you want to delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= APP_URL ?>/client-types/action', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${id}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        });
    });

    // ==================== IDENTIFIERS MANAGEMENT ====================

    // Manage identifiers button
    document.querySelectorAll('.btn-manage-identifiers').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const typeId = this.dataset.id;
            const typeName = this.dataset.name;

            document.getElementById('currentTypeId').value = typeId;
            document.getElementById('identifierTypeName').textContent = typeName;

            loadIdentifiers(typeId);
        });
    });

    function loadIdentifiers(typeId) {
        const tbody = document.getElementById('identifiersList');
        tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <p class="text-muted mb-0 mt-2">Loading...</p>
        </td></tr>`;

        fetch('<?= APP_URL ?>/client-types/action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=get_identifiers&type_id=${typeId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderIdentifiers(data.identifiers);
            } else {
                tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4 text-danger">
                    ${data.message}
                </td></tr>`;
            }
        });
    }

    function renderIdentifiers(identifiers) {
        const tbody = document.getElementById('identifiersList');

        if (identifiers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center py-4 text-muted">
                No identifiers added yet
            </td></tr>`;
            return;
        }

        let html = '';
        identifiers.forEach((item, index) => {
            html += `<tr data-id="${item.id}">
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.is_required == 1 ? '<span class="badge bg-warning">Required</span>' : '<span class="badge bg-secondary">Optional</span>'}</td>
                <td>${item.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</td>
                ${canEdit ? `<td class="text-center">
                    <button type="button" class="btn btn-sm btn-soft-danger btn-delete-identifier" data-id="${item.id}" data-name="${item.name}">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>` : ''}
            </tr>`;
        });

        tbody.innerHTML = html;

        // Attach delete handlers
        tbody.querySelectorAll('.btn-delete-identifier').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                Swal.fire({
                    title: 'Delete Identifier?',
                    text: `Delete "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteIdentifier(id);
                    }
                });
            });
        });
    }

    // Add identifier form
    const addIdentifierForm = document.getElementById('addIdentifierForm');
    if (addIdentifierForm) {
        addIdentifierForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const typeId = document.getElementById('currentTypeId').value;
            const name = document.getElementById('newIdentifierName').value;
            const isRequired = document.getElementById('newIdentifierRequired').value;
            const status = document.getElementById('newIdentifierStatus').value;

            fetch('<?= APP_URL ?>/client-types/action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add_identifier&type_id=${typeId}&name=${encodeURIComponent(name)}&is_required=${isRequired}&status=${status}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newIdentifierName').value = '';
                    loadIdentifiers(typeId);
                    Swal.fire({ icon: 'success', title: 'Added', timer: 1000, showConfirmButton: false });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    }

    function deleteIdentifier(id) {
        const typeId = document.getElementById('currentTypeId').value;

        fetch('<?= APP_URL ?>/client-types/action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete_identifier&id=${id}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadIdentifiers(typeId);
                Swal.fire({ icon: 'success', title: 'Deleted', timer: 1000, showConfirmButton: false });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    }

    // Handle form submissions via AJAX
    ['addTypeForm', 'editTypeForm'].forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const formAction = this.getAttribute('action');

                fetch(formAction, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            });
        }
    });
});
</script>
