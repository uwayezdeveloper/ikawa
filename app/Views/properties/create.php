<?php
$permissions = $user['permissions'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Add Property</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/properties">Property Management</a></li>
                <li class="breadcrumb-item active">Add Property</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= APP_URL ?>/properties" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i> Back to Properties
        </a>
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

<!-- Add Property Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-home me-2"></i>Property Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/properties">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="property_name" class="form-label">Property Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="property_name" name="property_name" 
                                       required placeholder="Enter property name">
                                <div class="form-text">Give your property a clear, descriptive name</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                                <select class="form-select" id="location_id" name="location_id" required>
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= htmlspecialchars($location['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Choose the location where this property is situated</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type_id" class="form-label">Property Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type_id" name="type_id" required>
                                    <option value="">Select Property Type</option>
                                    <?php foreach ($propertyTypes as $propertyType): ?>
                                    <option value="<?= $propertyType['type_id'] ?>"><?= htmlspecialchars($propertyType['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Choose the type/category of this property</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="value_amount" class="form-label">Value Amount (RWF) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="value_amount" name="value_amount" 
                                       required min="0" step="1" placeholder="0">
                                <div class="form-text">Enter the property value in Rwandan Francs</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <div class="form-text">Set the current status of this property</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Enter property description (optional)"></textarea>
                        <div class="form-text">Add any additional details or notes about this property</div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= APP_URL ?>/properties" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Property
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-info-circle me-2"></i>Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-primary-subtle rounded">
                            <i class="ti ti-home text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Property Records</h6>
                        <p class="text-muted mb-0 fs-sm">Register and manage property assets with their locations and values.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-start mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-success-subtle rounded">
                            <i class="ti ti-map-pin text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Location Assignment</h6>
                        <p class="text-muted mb-0 fs-sm">Each property must be assigned to an existing location.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-start mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-warning-subtle rounded">
                            <i class="ti ti-currency bg-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Value Tracking</h6>
                        <p class="text-muted mb-0 fs-sm">Track the monetary value of each property for reporting purposes.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-info-subtle rounded">
                            <i class="ti ti-user text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Automatic Tracking</h6>
                        <p class="text-muted mb-0 fs-sm">Created by and timestamp are automatically recorded.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Properties -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-clock me-2"></i>Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="mb-1 text-primary"><?= count($locations) ?></h4>
                                <p class="text-muted mb-0 fs-sm">Locations</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div>
                                <h4 class="mb-1 text-success">New</h4>
                                <p class="text-muted mb-0 fs-sm">Property</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format value amount input
    const valueAmountInput = document.getElementById('value_amount');
    valueAmountInput.addEventListener('input', function() {
        // Remove any non-digit characters except decimal point
        this.value = this.value.replace(/[^\d]/g, '');
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const propertyName = document.getElementById('property_name').value.trim();
        const locationId = document.getElementById('location_id').value;
        const typeId = document.getElementById('type_id').value;
        const valueAmount = document.getElementById('value_amount').value;
        const status = document.getElementById('status').value;
        
        let errors = [];
        
        if (!propertyName) {
            errors.push('Property name is required');
        }
        
        if (!locationId) {
            errors.push('Location is required');
        }
        
        if (!typeId) {
            errors.push('Property type is required');
        }
        
        if (!valueAmount || valueAmount < 0) {
            errors.push('Valid value amount is required');
        }
        
        if (!status) {
            errors.push('Status is required');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errors.join('<br>'),
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>