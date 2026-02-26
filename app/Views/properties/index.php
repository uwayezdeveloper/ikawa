<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('properties', $permissions);
$canEdit = in_array('properties', $permissions);
$canDelete = in_array('properties', $permissions);
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Properties</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/properties">Property Management</a></li>
                <li class="breadcrumb-item active">Record Properties</li>
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

<!-- Properties Table -->
<div class="card" data-table data-table-rows-per-page="10">
    <div class="card-header border-light justify-content-between">
        <div class="d-flex gap-2">
            <div class="app-search">
                <input data-table-search type="search" class="form-control" placeholder="Search properties..." />
                <i class="ti ti-search app-search-icon text-muted"></i>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="me-2 fw-semibold">Filter By:</span>
            <!-- Location Filter -->
            <div class="app-search">
                <select data-table-filter="location" class="form-select form-control my-1 my-md-0">
                    <option value="All">Location</option>
                    <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location['name']) ?>"><?= htmlspecialchars($location['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="ti ti-map-pin app-search-icon text-muted"></i>
            </div>
            <!-- Property Type Filter -->
            <div class="app-search">
                <select data-table-filter="property-type" class="form-select form-control my-1 my-md-0">
                    <option value="All">Property Type</option>
                    <?php foreach ($propertyTypes as $propertyType): ?>
                    <option value="<?= htmlspecialchars($propertyType['type_name']) ?>"><?= htmlspecialchars($propertyType['type_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="ti ti-category app-search-icon text-muted"></i>
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
            <a href="<?= APP_URL ?>/properties/create" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Property
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-centered table-hover w-100 mb-0">
            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                <tr class="text-uppercase fs-xxs">
                    <th class="ps-3" style="width: 5%">#</th>
                    <th data-table-sort>Property Name</th>
                    <th data-table-sort data-column="location">Location</th>
                    <th data-table-sort data-column="property-type">Property Type</th>
                    <th data-table-sort>Value Amount</th>
                    <th data-table-sort>Description</th>
                    <th data-table-sort data-column="status">Status</th>
                    <th data-table-sort>Created Date</th>
                    <?php if ($canEdit || $canDelete): ?>
                    <th class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                <tr>
                    <td colspan="<?= ($canEdit || $canDelete) ? 9 : 8 ?>" class="text-center py-4">
                        <i class="ti ti-home fs-1 text-muted"></i>
                        <p class="text-muted mb-0">No properties found</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($properties as $index => $property): ?>
                <tr>
                    <td class="ps-3">
                        <h5 class="m-0"><?= $index + 1 ?></h5>
                    </td>
                    <td>
                        <h5 class="fs-base mb-0">
                            <a href="#" class="link-reset"><?= htmlspecialchars($property['property_name']) ?></a>
                        </h5>
                    </td>
                    <td><?= htmlspecialchars($property['location_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($property['property_type_name'] ?? 'Unknown') ?></td>
                    <td>
                        <span class="fw-semibold text-success">RWF <?= number_format($property['value_amount']) ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= htmlspecialchars($property['description'] ?: '-') ?></span>
                    </td>
                    <td>
                        <?php if ($property['status'] == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="text-muted"><?= date('M d, Y', strtotime($property['created_at'])) ?></span>
                    </td>
                    <?php if ($canEdit || $canDelete): ?>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <?php if ($canEdit): ?>
                            <button type="button" class="btn btn-default btn-icon btn-sm btn-edit-property"
                                data-id="<?= $property['property_id'] ?>"
                                data-name="<?= htmlspecialchars($property['property_name']) ?>"
                                data-location-id="<?= $property['location_id'] ?>"
                                data-type-id="<?= $property['type_id'] ?>"
                                data-value-amount="<?= $property['value_amount'] ?>"
                                data-status="<?= $property['status'] ?>"
                                data-description="<?= htmlspecialchars($property['description'] ?? '') ?>"
                                data-bs-toggle="modal" data-bs-target="#editPropertyModal">
                                <i class="ti ti-edit fs-lg"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                            <button type="button" class="btn btn-danger btn-icon btn-sm btn-delete-property"
                                data-id="<?= $property['property_id'] ?>"
                                data-name="<?= htmlspecialchars($property['property_name']) ?>">
                                <i class="ti ti-trash fs-lg"></i>
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
</div>

<!-- Edit Property Modal -->
<?php if ($canEdit): ?>
<div class="modal fade" id="editPropertyModal" tabindex="-1" aria-labelledby="editPropertyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPropertyModalLabel">Edit Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/properties">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="property_id" id="edit_property_id">
                    
                    <div class="mb-3">
                        <label for="edit_property_name" class="form-label">Property Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_property_name" name="property_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_location_id" class="form-label">Location <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_location_id" name="location_id" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $location): ?>
                            <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_type_id" class="form-label">Property Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_type_id" name="type_id" required>
                            <option value="">Select Property Type</option>
                            <?php foreach ($propertyTypes as $propertyType): ?>
                            <option value="<?= $propertyType['type_id'] ?>"><?= htmlspecialchars($propertyType['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_value_amount" class="form-label">Value Amount (RWF) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_value_amount" name="value_amount" required min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Property</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<?php if ($canDelete): ?>
<div class="modal fade" id="deletePropertyModal" tabindex="-1" aria-labelledby="deletePropertyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePropertyModalLabel">Delete Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/properties">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="property_id" id="delete_property_id">
                    <p>Are you sure you want to delete the property "<strong id="delete_property_name"></strong>"?</p>
                    <p class="text-muted small">This action will deactivate the property but won't permanently remove it from the database.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Property</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Property Modal
    const editButtons = document.querySelectorAll('.btn-edit-property');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const locationId = this.getAttribute('data-location-id');
            const typeId = this.getAttribute('data-type-id');
            const valueAmount = this.getAttribute('data-value-amount');
            const status = this.getAttribute('data-status');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit_property_id').value = id;
            document.getElementById('edit_property_name').value = name;
            document.getElementById('edit_location_id').value = locationId;
            document.getElementById('edit_type_id').value = typeId;
            document.getElementById('edit_value_amount').value = valueAmount;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_description').value = description;
        });
    });
    
    // Delete Property Modal
    const deleteButtons = document.querySelectorAll('.btn-delete-property');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('delete_property_id').value = id;
            document.getElementById('delete_property_name').textContent = name;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deletePropertyModal'));
            deleteModal.show();
        });
    });
});
</script>