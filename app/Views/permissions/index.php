<?php
$menus = $menus ?? ['data' => [], 'total' => 0, 'page' => 1, 'totalPages' => 1];
$stats = $stats ?? ['active' => 0, 'inactive' => 0, 'total' => 0];

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="d-flex align-items-sm-center flex-sm-row flex-column my-3">
    <div class="flex-grow-1">
        <h4 class="fs-xl mb-1">Manage Menus</h4>
        <p class="text-muted mb-0">Register global menus and their identifiers for role access.</p>
    </div>
    <div class="text-end d-flex gap-2">
        <a href="<?= APP_URL ?>/permissions/roles" class="btn btn-info">
            <i class="ti ti-shield-lock me-1"></i> Role Menus
        </a>
        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addMenuModal" class="btn btn-success">
            <i class="ti ti-plus me-1"></i> Add Menu
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card"><div class="card-body"><h4 class="mb-0"><?= $stats['total'] ?></h4><p class="text-muted mb-0">Total Menus</p></div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body"><h4 class="mb-0"><?= $stats['active'] ?></h4><p class="text-muted mb-0">Active Menus</p></div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body"><h4 class="mb-0"><?= $stats['inactive'] ?></h4><p class="text-muted mb-0">Inactive Menus</p></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">All Menus</h5>
        <input type="text" class="form-control form-control-sm" id="searchMenu" placeholder="Search..." style="width: 200px;">
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0" id="menusTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Menu Name</th>
                        <th>Menu Identifier</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menus['data'])): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No menus found</td></tr>
                    <?php else: ?>
                        <?php foreach ($menus['data'] as $index => $menu): ?>
                            <tr>
                                <td><?= (($menus['page'] - 1) * 15) + $index + 1 ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($menu['menu_name']) ?></td>
                                <td><code class="text-primary"><?= htmlspecialchars($menu['menu_identifier']) ?></code></td>
                                <td>
                                    <?php if ((int) $menu['status'] === 1): ?>
                                        <span class="badge bg-success-subtle text-success px-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger px-2">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-soft-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editMenuModal"
                                                data-id="<?= $menu['menu_id'] ?>"
                                                data-name="<?= htmlspecialchars($menu['menu_name']) ?>"
                                                data-identifier="<?= htmlspecialchars($menu['menu_identifier']) ?>"
                                                data-status="<?= (int) $menu['status'] ?>">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-danger btn-sm btn-delete"
                                                data-id="<?= $menu['menu_id'] ?>"
                                                data-name="<?= htmlspecialchars($menu['menu_name']) ?>">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($menus['totalPages'] > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">Showing page <?= $menus['page'] ?> of <?= $menus['totalPages'] ?> (<?= $menus['total'] ?> total menus)</div>
                <nav aria-label="Menus pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($menus['page'] > 1): ?>
                            <li class="page-item"><a class="page-link" href="<?= APP_URL ?>/permissions?page=<?= $menus['page'] - 1 ?>"><i class="ti ti-chevron-left"></i></a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $menus['totalPages']; $i++): ?>
                            <li class="page-item <?= $i === $menus['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= APP_URL ?>/permissions?page=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($menus['page'] < $menus['totalPages']): ?>
                            <li class="page-item"><a class="page-link" href="<?= APP_URL ?>/permissions?page=<?= $menus['page'] + 1 ?>"><i class="ti ti-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= APP_URL ?>/permissions" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Menu Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="menuName" name="menu_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Menu Identifier</label>
                        <input type="text" class="form-control" id="menuIdentifier" name="menu_identifier" placeholder="Auto-generated if empty">
                        <small class="text-muted">Use global menu key only, no submenu key.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMenuForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Menu Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editMenuName" name="menu_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Menu Identifier</label>
                        <input type="text" class="form-control" id="editMenuIdentifier" name="menu_identifier">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="editMenuStatus" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($flashSuccess): ?>
    Swal.fire({ icon: 'success', title: 'Success', text: '<?= addslashes($flashSuccess) ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    <?php endif; ?>

    <?php if ($flashError): ?>
    Swal.fire({ icon: 'error', title: 'Error', text: '<?= addslashes($flashError) ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000 });
    <?php endif; ?>

    const editModal = document.getElementById('editMenuModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('editMenuName').value = btn.getAttribute('data-name');
            document.getElementById('editMenuIdentifier').value = btn.getAttribute('data-identifier');
            document.getElementById('editMenuStatus').value = btn.getAttribute('data-status');
            document.getElementById('editMenuForm').action = '<?= APP_URL ?>/permissions/' + btn.getAttribute('data-id');
        });
    }

    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            Swal.fire({
                title: 'Delete Menu?',
                text: 'Delete ' + name + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= APP_URL ?>/permissions/' + id + '/delete';
                }
            });
        });
    });

    const searchInput = document.getElementById('searchMenu');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#menusTable tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    }

    const menuName = document.getElementById('menuName');
    const menuIdentifier = document.getElementById('menuIdentifier');
    if (menuName && menuIdentifier) {
        menuName.addEventListener('input', function() {
            if (menuIdentifier.value.trim() === '') {
                menuIdentifier.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
            }
        });
    }
});
</script>
