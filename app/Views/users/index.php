<!-- Page Title -->
<?php
// Get user permissions

$isSuperAdmin = ((int)($user['role_id'] ?? 0) === 1);
$menuAccess = $user['menu_identifiers'] ?? [];
$canCreate = $isSuperAdmin || in_array('users', $menuAccess);
$canEdit = $isSuperAdmin || in_array('users', $menuAccess);
$canDelete = $isSuperAdmin || in_array('users', $menuAccess);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
            <h4 class="page-title">Users Management</h4>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Users</h5>
                <?php if ($canCreate): ?>
                <a href="<?= APP_URL ?>/users/create" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Add User
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users['data'])): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No users found.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($users['data'] as $index => $u): ?>
                            <tr>
                                <td><?= (($users['current_page'] - 1) * $users['per_page']) + $index + 1 ?></td>
                                <td>
                                    
                                    <div class="d-flex align-items-center">
                                        <?php
                                        $userAvatarUrl = !empty($u['avatar']) ? APP_URL . '/' . $u['avatar'] : \App\Core\View::asset('images/users/user-1.jpg');
                                        ?>
                                        <img src="<?= $userAvatarUrl ?>" alt="user"
                                            class="avatar-sm rounded-circle me-2" style="object-fit: cover; width: 38px; height: 38px;" />
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($u['full_name']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($u['uuid']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <?= htmlspecialchars($u['role_name'] ?? 'No Role') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                            $statusColors = [
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'pending' => 'warning',
                                                'suspended' => 'danger'
                                            ];
                                            $statusColor = $statusColors[$u['status']] ?? 'secondary';
                                            ?>
                                    <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?>">
                                        <?= ucfirst($u['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/users/<?= $u['id'] ?>" class="btn btn-soft-primary btn-sm"
                                        title="View">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <?php if ($canEdit): ?>
                                    <a href="<?= APP_URL ?>/users/<?= $u['id'] ?>/edit"
                                        class="btn btn-soft-warning btn-sm" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($canDelete && $u['id'] !== ($user['id'] ?? 0)): ?>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-user" 
                                        data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['full_name']) ?>"
                                        title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($users['last_page'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?= $users['current_page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= APP_URL ?>/users?page=<?= $users['current_page'] - 1 ?>">
                                <i class="ti ti-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $users['last_page']; $i++): ?>
                        <li class="page-item <?= $i == $users['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= APP_URL ?>/users?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $users['current_page'] >= $users['last_page'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= APP_URL ?>/users?page=<?= $users['current_page'] + 1 ?>">
                                <i class="ti ti-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show SweetAlert for flash messages
    <?php if ($flashSuccess): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= addslashes($flashSuccess) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php endif; ?>

    <?php if ($flashError): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= addslashes($flashError) ?>',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
    <?php endif; ?>

    // Delete User - SweetAlert confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete-user');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            Swal.fire({
                title: 'Delete User?',
                html: `Are you sure you want to delete <strong>${name}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= APP_URL ?>/users/' + id + '/delete';
                }
            });
        });
    });
});
</script>