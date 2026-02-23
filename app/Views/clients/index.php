<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-suppliers', $permissions);
$canEdit = in_array('edit-suppliers', $permissions);
$canDelete = in_array('delete-suppliers', $permissions);

// Build client types lookup
$typeColors = ['primary', 'info', 'warning', 'success', 'secondary', 'danger'];
$clientTypesLookup = [];
$colorIndex = 0;
foreach ($clientTypes as $ct) {
    $clientTypesLookup[$ct['identifier']] = [
        'label' => $ct['name'],
        'color' => $typeColors[$colorIndex % count($typeColors)]
    ];
    $colorIndex++;
}

// Encode client types with identifiers for JavaScript
$clientTypesJson = json_encode($clientTypes);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Clients</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Clients</li>
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
        timerProgressBar: true
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
        timerProgressBar: true
    });
});
</script>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- Clients Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search clients..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Type Filter -->
            <div class="app-search">
                <select data-table-filter="type" class="form-select form-control my-1 my-md-0">
                    <option value="All">Type</option>
                    <?php foreach ($clientTypes as $ct): ?>
                    <option value="<?= htmlspecialchars($ct['name']) ?>"><?= htmlspecialchars($ct['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="ti ti-filter app-search-icon text-muted"></i>
            </div>
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
                <i class="ti ti-plus me-1"></i> Add Client
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Client Name</th>
                    <th data-table-sort data-column="type">Type</th>
                    <th data-table-sort>Phone</th>
                    <th data-table-sort>Email</th>
                    <th data-table-sort data-column="status">Status</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 7 : 6 ?>" class="text-center py-4">
                        <i class="ti ti-users fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No clients found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($clients as $index => $client): 
                    $type = $clientTypesLookup[$client['client_type']] ?? ['label' => $client['type_name'] ?? 'Unknown', 'color' => 'secondary'];
                ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0"><?= htmlspecialchars($client['name']) ?></h5>
                        <?php if (!empty($client['contact_person'])): ?>
                        <small class="text-muted"><?= htmlspecialchars($client['contact_person']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-column="type">
                        <span class="badge bg-<?= $type['color'] ?>-subtle text-<?= $type['color'] ?>">
                            <?= htmlspecialchars($type['label']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($client['phone'] ?? '-') ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($client['email'] ?? '-') ?></span>
                    </td>
                    <td data-column="status">
                        <?php if ($client['status'] === 'active'): ?>
                        <span class="badge bg-success-subtle text-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-client"
                                data-id="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['name']) ?>"
                                data-type="<?= htmlspecialchars($client['client_type']) ?>"
                                data-phone="<?= htmlspecialchars($client['phone'] ?? '') ?>"
                                data-email="<?= htmlspecialchars($client['email'] ?? '') ?>"
                                data-address="<?= htmlspecialchars($client['address'] ?? '') ?>"
                                data-contact-person="<?= htmlspecialchars($client['contact_person'] ?? '') ?>"
                                data-contact-phone="<?= htmlspecialchars($client['contact_phone'] ?? '') ?>"
                                data-notes="<?= htmlspecialchars($client['notes'] ?? '') ?>"
                                data-status="<?= $client['status'] ?>">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-toggle-status"
                                data-id="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['name']) ?>"
                                data-status="<?= $client['status'] ?>">
                                <?php if ($client['status'] === 'active'): ?>
                                <i class="ti ti-ban fs-lg text-warning"></i>
                                <?php else: ?>
                                <i class="ti ti-check fs-lg text-success"></i>
                                <?php endif; ?>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-delete-client"
                                data-id="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['name']) ?>">
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

<?php if ($canCreate || $canEdit): ?>
<!-- Add/Edit Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientModalLabel">
                    <i class="ti ti-user-plus me-2"></i>Add New Client
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/clients/action" method="POST" id="clientForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="clientId" value="">

                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Info -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="clientName" required
                                placeholder="Enter client name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="client_type" id="clientType" required>
                                <option value="">Select Type</option>
                                <?php foreach ($clientTypes as $ct): ?>
                                <option value="<?= htmlspecialchars($ct['identifier']) ?>"
                                    data-type-id="<?= $ct['id'] ?>">
                                    <?= htmlspecialchars($ct['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Contact Info -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="clientPhone"
                                placeholder="e.g., +250 788 000 000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="clientEmail"
                                placeholder="e.g., client@example.com">
                        </div>

                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="clientAddress" rows="2"
                                placeholder="Enter address"></textarea>
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" id="contactPerson"
                                placeholder="Name of contact person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" name="contact_phone" id="contactPhone"
                                placeholder="Contact person's phone">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="clientStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Dynamic Identifiers Section -->
                        <div class="col-12" id="identifiersSection" style="display: none;">
                            <hr class="my-3">
                            <h6 class="mb-3"><i class="ti ti-id me-2"></i>Type Identifiers</h6>
                            <div class="row" id="identifiersContainer">
                                <!-- Dynamic identifier fields will be loaded here -->
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="clientNotes" rows="2"
                                placeholder="Additional notes about this client"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i><span id="submitBtnText">Save Client</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    const canDelete = <?= $canDelete ? 'true' : 'false' ?>;
    const clientTypes = <?= $clientTypesJson ?>;

    const clientModal = document.getElementById('clientModal');
    const clientForm = document.getElementById('clientForm');
    const clientTypeSelect = document.getElementById('clientType');
    const identifiersSection = document.getElementById('identifiersSection');
    const identifiersContainer = document.getElementById('identifiersContainer');

    // Load identifiers when client type changes
    if (clientTypeSelect) {
        clientTypeSelect.addEventListener('change', function() {
            const typeIdentifier = this.value;
            loadIdentifiersForType(typeIdentifier);
        });
    }

    function loadIdentifiersForType(typeIdentifier, existingValues = {}) {
        if (!typeIdentifier) {
            identifiersSection.style.display = 'none';
            identifiersContainer.innerHTML = '';
            return;
        }

        // Find the type in our data
        const typeData = clientTypes.find(t => t.identifier === typeIdentifier);

        if (typeData && typeData.identifiers && typeData.identifiers.length > 0) {
            identifiersSection.style.display = 'block';

            let html = '';
            typeData.identifiers.forEach(identifier => {
                const existingValue = existingValues[identifier.id] || '';
                const required = identifier.is_required == 1;

                html += `
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            ${identifier.name}
                            ${required ? '<span class="text-danger">*</span>' : ''}
                        </label>
                        <input type="text" 
                            class="form-control" 
                            name="identifiers[${identifier.id}]" 
                            value="${existingValue}"
                            ${required ? 'required' : ''}
                            placeholder="Enter ${identifier.name}">
                    </div>
                `;
            });

            identifiersContainer.innerHTML = html;
        } else {
            identifiersSection.style.display = 'none';
            identifiersContainer.innerHTML = '';
        }
    }

    // Edit button click handler
    document.querySelectorAll('.btn-edit-client').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;

            // Fetch client data with identifier values
            fetch('<?= APP_URL ?>/clients/action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=get_client&id=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data) {
                        const c = data.data;

                        document.getElementById('clientModalLabel').innerHTML =
                            '<i class="ti ti-pencil me-2"></i>Edit Client';
                        document.getElementById('formAction').value = 'update';
                        document.getElementById('clientId').value = c.id;
                        document.getElementById('clientName').value = c.name;
                        document.getElementById('clientType').value = c.client_type;
                        document.getElementById('clientPhone').value = c.phone || '';
                        document.getElementById('clientEmail').value = c.email || '';
                        document.getElementById('clientAddress').value = c.address || '';
                        document.getElementById('contactPerson').value = c.contact_person ||
                            '';
                        document.getElementById('contactPhone').value = c.contact_phone ||
                            '';
                        document.getElementById('clientStatus').value = c.status;
                        document.getElementById('clientNotes').value = c.notes || '';
                        document.getElementById('submitBtnText').textContent =
                            'Update Client';

                        // Build existing values map
                        const existingValues = {};
                        if (c.identifier_values) {
                            c.identifier_values.forEach(iv => {
                                existingValues[iv.identifier_id] = iv.value;
                            });
                        }

                        // Load identifiers with existing values
                        loadIdentifiersForType(c.client_type, existingValues);

                        // Show modal
                        const modal = new bootstrap.Modal(clientModal);
                        modal.show();
                    }
                });
        });
    });

    // Reset modal on close
    if (clientModal) {
        clientModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('clientModalLabel').innerHTML =
                '<i class="ti ti-user-plus me-2"></i>Add New Client';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtnText').textContent = 'Save Client';
            clientForm.reset();
            identifiersSection.style.display = 'none';
            identifiersContainer.innerHTML = '';
        });
    }

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
                    fetch('<?= APP_URL ?>/clients/action', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=toggle_status&id=${id}`
                        })
                        .then(res => res.text())
                        .then(() => location.reload());
                }
            });
        });
    });

    // Delete button
    document.querySelectorAll('.btn-delete-client').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            Swal.fire({
                title: 'Delete Client?',
                text: `Are you sure you want to delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= APP_URL ?>/clients/action', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=delete&id=${id}`
                        })
                        .then(res => res.text())
                        .then(() => location.reload());
                }
            });
        });
    });
});
</script>