<!-- Page Title -->
<?php
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Default avatar
$avatarUrl = !empty($user['avatar']) ? APP_URL . '/' . $user['avatar'] : \App\Core\View::asset('images/users/user-1.jpg');
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active">My Profile</li>
                </ol>
            </div>
            <h4 class="page-title">My Profile</h4>
        </div>
    </div>
</div>

<?php if ($flashSuccess): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>
            <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($flashError): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Profile Content -->
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="position-relative d-inline-block mb-3">
                    <img src="<?= $avatarUrl ?>" alt="avatar" 
                         class="avatar-xxl rounded-circle" 
                         id="profileAvatar" 
                         style="object-fit: cover; width: 120px; height: 120px;" />
                    <div class="position-absolute bottom-0 end-0">
                        <button type="button" class="btn btn-primary btn-sm rounded-circle" 
                                data-bs-toggle="modal" 
                                data-bs-target="#avatarModal"
                                style="width: 35px; height: 35px; padding: 0;">
                            <i class="ti ti-camera"></i>
                        </button>
                    </div>
                </div>
                <h4 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>

                <?php if (!empty($user['role_name'])): ?>
                <span class="badge bg-primary mb-3"><?= htmlspecialchars($user['role_name']) ?></span>
                <?php endif; ?>

                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="<?= APP_URL ?>/profile/edit" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i> Edit Profile
                    </a>
                    <a href="<?= APP_URL ?>/profile/change-password" class="btn btn-outline-secondary">
                        <i class="ti ti-lock me-1"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">First Name</label>
                            <p class="fw-medium"><?= htmlspecialchars($user['first_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Last Name</label>
                            <p class="fw-medium"><?= htmlspecialchars($user['last_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="fw-medium"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone</label>
                            <p class="fw-medium"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Role</label>
                            <p>
                                <span class="badge bg-primary"><?= htmlspecialchars($user['role_name'] ?? 'No Role') ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <p>
                                <?php
                                $statusColors = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'pending' => 'warning',
                                    'suspended' => 'danger'
                                ];
                                $statusColor = $statusColors[$user['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($user['status']) ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Member Since</label>
                            <p class="fw-medium"><?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php if (!empty($user['last_login_at'])): ?>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Last Login</label>
                            <p class="fw-medium"><?= date('F j, Y g:i A', strtotime($user['last_login_at'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="avatarForm" enctype="multipart/form-data">
                    <div class="mb-3 text-center">
                        <img src="<?= $avatarUrl ?>" alt="avatar" 
                             class="avatar-xxl rounded-circle mb-3" 
                             id="avatarPreview" 
                             style="object-fit: cover; width: 150px; height: 150px;" />
                    </div>
                    <div class="mb-3">
                        <label for="avatarInput" class="form-label">Choose Image</label>
                        <input type="file" class="form-control" id="avatarInput" name="avatar" 
                               accept="image/jpeg,image/jpg,image/png,image/gif" required>
                        <small class="text-muted">Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                    </div>
                    <div id="uploadProgress" class="progress mb-3" style="display: none; height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div id="uploadMessage" class="alert" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <?php if (!empty($user['avatar'])): ?>
                <button type="button" class="btn btn-danger me-auto" id="removeAvatarBtn">
                    <i class="ti ti-trash me-1"></i> Remove
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="uploadAvatarBtn">
                    <i class="ti ti-upload me-1"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const uploadBtn = document.getElementById('uploadAvatarBtn');
    const removeBtn = document.getElementById('removeAvatarBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadMessage = document.getElementById('uploadMessage');
    const progressBar = uploadProgress.querySelector('.progress-bar');
    const avatarModal = new bootstrap.Modal(document.getElementById('avatarModal'));

    // Preview image before upload
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Upload avatar
    uploadBtn.addEventListener('click', function() {
        const formData = new FormData();
        const file = avatarInput.files[0];
        
        if (!file) {
            showMessage('Please select an image', 'danger');
            return;
        }

        formData.append('avatar', file);
        
        uploadBtn.disabled = true;
        uploadProgress.style.display = 'block';
        uploadMessage.style.display = 'none';

        const xhr = new XMLHttpRequest();
        
        // Progress tracking
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressBar.textContent = Math.round(percentComplete) + '%';
            }
        });

        xhr.addEventListener('load', function() {
            uploadBtn.disabled = false;
            uploadProgress.style.display = 'none';
            
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    showMessage(response.message, 'success');
                    // Update avatar images
                    document.getElementById('profileAvatar').src = response.avatar_url;
                    avatarPreview.src = response.avatar_url;
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                }
            } else {
                showMessage('Upload failed. Please try again.', 'danger');
            }
        });

        xhr.addEventListener('error', function() {
            uploadBtn.disabled = false;
            uploadProgress.style.display = 'none';
            showMessage('Upload failed. Please try again.', 'danger');
        });

        xhr.open('POST', '<?= APP_URL ?>/profile/avatar');
        xhr.send(formData);
    });

    // Remove avatar
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to remove your profile picture?')) {
                return;
            }

            removeBtn.disabled = true;
            
            fetch('<?= APP_URL ?>/profile/avatar/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                removeBtn.disabled = false;
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(data.message, 'danger');
                }
            })
            .catch(error => {
                removeBtn.disabled = false;
                showMessage('Failed to remove avatar. Please try again.', 'danger');
            });
        });
    }

    function showMessage(message, type) {
        uploadMessage.className = 'alert alert-' + type;
        uploadMessage.textContent = message;
        uploadMessage.style.display = 'block';
    }
});
</script>
