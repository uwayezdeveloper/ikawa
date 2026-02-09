<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-suppliers', $permissions);
$canEdit = in_array('edit-suppliers', $permissions);
$canDelete = in_array('delete-suppliers', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Suppliers</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Suppliers</li>
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
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal2-toast-custom'
        }
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
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal2-toast-custom'
        }
    });
});
</script>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- Suppliers Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search suppliers..." />
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="ti ti-plus me-1"></i> Add Supplier
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Supplier Name</th>
                    <th data-table-sort>Type</th>
                    <th data-table-sort>Phone</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 6 : 5 ?>" class="text-center py-4">
                        <i class="ti ti-truck fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No suppliers found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($suppliers as $index => $supplier): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($supplier['name']) ?></h5>
                        <?php if (!empty($supplier['contact_person'])): ?>
                        <small class="text-muted"><?= htmlspecialchars($supplier['contact_person']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span
                            class="badge bg-info-subtle text-info"><?= htmlspecialchars($supplier['supplier_type_name'] ?? '-') ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($supplier['phone'] ?? '-') ?></span>
                    </td>
                    <td data-column="status">
                        <?php if ($supplier['status'] === 'active'): ?>
                        <span class="badge bg-success-subtle text-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-supplier"
                                data-id="<?= $supplier['id'] ?>" data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                data-supplier-type-id="<?= $supplier['supplier_type_id'] ?>"
                                data-phone="<?= htmlspecialchars($supplier['phone'] ?? '') ?>"
                                data-email="<?= htmlspecialchars($supplier['email'] ?? '') ?>"
                                data-address="<?= htmlspecialchars($supplier['address'] ?? '') ?>"
                                data-contact-person="<?= htmlspecialchars($supplier['contact_person'] ?? '') ?>"
                                data-contact-phone="<?= htmlspecialchars($supplier['contact_phone'] ?? '') ?>"
                                data-status="<?= $supplier['status'] ?>" data-bs-toggle="modal"
                                data-bs-target="#editSupplierModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-toggle-status"
                                data-id="<?= $supplier['id'] ?>" data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                data-status="<?= $supplier['status'] ?>">
                                <?php if ($supplier['status'] === 'active'): ?>
                                <i class="ti ti-ban fs-lg text-warning"></i>
                                <?php else: ?>
                                <i class="ti ti-check fs-lg text-success"></i>
                                <?php endif; ?>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-supplier"
                                data-id="<?= $supplier['id'] ?>" data-name="<?= htmlspecialchars($supplier['name']) ?>">
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
<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/suppliers" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplierTypeId" class="form-label">Supplier Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="supplierTypeId" name="supplier_type_id" required>
                                <option value="">Select Type</option>
                                <?php foreach ($supplierTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                placeholder="+250 xxx xxx xxx">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="supplier@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"
                            placeholder="Physical address"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contactPerson" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contactPerson" name="contact_person"
                                placeholder="Contact person name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contactPhone" class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" id="contactPhone" name="contact_phone"
                                placeholder="Contact person phone">
                        </div>
                    </div>

                    <!-- Dynamic Identifiers Section -->
                    <div id="addIdentifiersSection" class="mb-3" style="display: none;">
                        <hr>
                        <h6 class="text-primary mb-3"><i class="ti ti-id me-1"></i> Type Identifiers</h6>
                        <div id="addIdentifiersContainer"></div>
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
                    <button type="submit" class="btn btn-primary">Create Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/suppliers" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editSupplierTypeId" class="form-label">Supplier Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editSupplierTypeId" name="supplier_type_id" required>
                                <option value="">Select Type</option>
                                <?php foreach ($supplierTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editPhone" name="phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editName" class="form-label">Supplier Name <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editContactPerson" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="editContactPerson" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editContactPhone" class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" id="editContactPhone" name="contact_phone">
                        </div>
                    </div>

                    <!-- Dynamic Identifiers Section -->
                    <div id="editIdentifiersSection" class="mb-3" style="display: none;">
                        <hr>
                        <h6 class="text-primary mb-3"><i class="ti ti-id me-1"></i> Type Identifiers</h6>
                        <div id="editIdentifiersContainer"></div>
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
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toggle Status Form -->
<form id="toggleStatusForm" action="<?= APP_URL ?>/suppliers" method="POST" style="display: none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id" id="toggleStatusId">
</form>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Form -->
<form id="deleteForm" action="<?= APP_URL ?>/suppliers" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load identifiers when supplier type is selected (Add modal)
    const supplierTypeSelect = document.getElementById('supplierTypeId');
    if (supplierTypeSelect) {
        supplierTypeSelect.addEventListener('change', function() {
            loadTypeIdentifiers(this.value, 'addIdentifiersSection', 'addIdentifiersContainer');
        });
    }

    // Load identifiers when supplier type is changed (Edit modal)
    const editSupplierTypeSelect = document.getElementById('editSupplierTypeId');
    if (editSupplierTypeSelect) {
        editSupplierTypeSelect.addEventListener('change', function() {
            loadTypeIdentifiers(this.value, 'editIdentifiersSection', 'editIdentifiersContainer');
        });
    }

    // Load identifiers for a supplier type
    function loadTypeIdentifiers(typeId, sectionId, containerId, supplierIdentifiers = {}) {
        const section = document.getElementById(sectionId);
        const container = document.getElementById(containerId);

        if (!typeId) {
            section.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        fetch('<?= APP_URL ?>/suppliers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_type_identifiers&supplier_type_id=' + typeId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.identifiers.length > 0) {
                    renderIdentifierFields(data.identifiers, containerId, supplierIdentifiers);
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                    container.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                section.style.display = 'none';
            });
    }

    // Render identifier input fields
    function renderIdentifierFields(identifiers, containerId, supplierIdentifiers = {}) {
        const container = document.getElementById(containerId);
        let html = '<div class="row">';

        identifiers.forEach((identifier, index) => {
            const value = supplierIdentifiers[identifier.id] || '';
            const required = identifier.is_required == 1;

            html += `<div class="col-md-6 mb-3">
                <label class="form-label">${escapeHtml(identifier.name)} ${required ? '<span class="text-danger">*</span>' : ''}</label>
                <input type="text" class="form-control" name="identifiers[${identifier.id}]" 
                    value="${escapeHtml(value)}" 
                    placeholder="Enter ${escapeHtml(identifier.name)}"
                    ${required ? 'required' : ''}>
            </div>`;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    // Edit button click handler
    document.querySelectorAll('.btn-edit-supplier').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const supplierId = this.dataset.id;
            const supplierTypeId = this.dataset.supplierTypeId;

            document.getElementById('editId').value = supplierId;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editSupplierTypeId').value = supplierTypeId;
            document.getElementById('editPhone').value = this.dataset.phone;
            document.getElementById('editEmail').value = this.dataset.email;
            document.getElementById('editAddress').value = this.dataset.address;
            document.getElementById('editContactPerson').value = this.dataset.contactPerson;
            document.getElementById('editContactPhone').value = this.dataset.contactPhone;
            document.getElementById('editStatus').value = this.dataset.status;

            // Load supplier identifiers
            if (supplierTypeId) {
                fetch('<?= APP_URL ?>/suppliers', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=get_supplier_identifiers&supplier_id=' + supplierId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadTypeIdentifiers(supplierTypeId, 'editIdentifiersSection',
                                'editIdentifiersContainer', data.identifiers);
                        } else {
                            loadTypeIdentifiers(supplierTypeId, 'editIdentifiersSection',
                                'editIdentifiersContainer');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadTypeIdentifiers(supplierTypeId, 'editIdentifiersSection',
                            'editIdentifiersContainer');
                    });
            }
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
    document.querySelectorAll('.btn-delete-supplier').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Supplier?',
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

    // Escape HTML helper
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Reset add modal when closed
    document.getElementById('addSupplierModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('addIdentifiersSection').style.display = 'none';
        document.getElementById('addIdentifiersContainer').innerHTML = '';
    });
});
</script>