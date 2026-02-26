<?php
$permissions = $user['permissions'] ?? [];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Warehouse Stock</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Warehouse Stock</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <select id="warehouseSelect" class="form-select" style="min-width: 250px;">
            <option value="">Select Warehouse</option>
            <?php foreach ($warehouses as $wh): ?>
            <option value="<?= $wh['id'] ?>" <?= $wh['id'] == $selectedWarehouseId ? 'selected' : '' ?>>
                <?= htmlspecialchars($wh['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <a href="<?= APP_URL ?>/warehouse/incoming<?= $selectedWarehouseId ? '?warehouse_id=' . $selectedWarehouseId : '' ?>"
            class="btn btn-primary">
            <i class="ti ti-truck-delivery me-1"></i> Incoming Transfers
        </a>
    </div>
</div>

<?php if ($selectedWarehouseId && $warehouseInfo): ?>

<!-- Warehouse Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-primary-subtle rounded me-3">
                        <i
                            class="ti ti-building-warehouse fs-2 text-primary d-flex align-items-center justify-content-center h-100"></i>
                    </div>
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($warehouseInfo['name']) ?></h4>
                        <p class="text-muted mb-0">
                            <span class="badge bg-secondary-subtle text-secondary">
                                <?= htmlspecialchars($warehouseInfo['type_name']) ?>
                            </span>
                            <?php if (!empty($warehouseInfo['address'])): ?>
                            <span class="ms-2"><i
                                    class="ti ti-map-pin me-1"></i><?= htmlspecialchars($warehouseInfo['address']) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <?php 
                $totalItems = array_sum(array_column($warehouseStock, 'total_quantity'));
                $totalValue = array_sum(array_column($warehouseStock, 'total_value'));
                ?>
                <div class="d-flex justify-content-md-end gap-4">
                    <div>
                        <small class="text-muted d-block">Total Products</small>
                        <h4 class="mb-0"><?= count($warehouseStock) ?></h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Items</small>
                        <h4 class="mb-0"><?= number_format($totalItems, 2) ?></h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Value</small>
                        <h4 class="mb-0 text-success"><?= number_format($totalValue, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-packages me-2"></i>Current Stock</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="stockTable">
                <thead class="bg-light">
                    <tr>
                        <th>Supplier</th>
                        <th>Processing Step</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Unit</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Avg Price</th>
                        <th class="text-end">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($warehouseStock)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="ti ti-package-off fs-2 d-block mb-2"></i>
                            No stock in this warehouse
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $currentSupplier = null;
                    foreach ($warehouseStock as $stock): 
                        $showSupplier = $currentSupplier !== $stock['supplier_name'];
                        if ($showSupplier) $currentSupplier = $stock['supplier_name'];
                    ?>
                    <tr>
                        <td>
                            <?php if ($stock['supplier_name']): ?>
                            <span class="badge bg-info-subtle text-info">
                                <i class="ti ti-building me-1"></i><?= htmlspecialchars($stock['supplier_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($stock['processing_step_name']): ?>
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="ti ti-tool me-1"></i><?= $stock['processing_step_order'] ?>.
                                <?= htmlspecialchars($stock['processing_step_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="ti ti-leaf me-1"></i><?= htmlspecialchars($stock['category_name']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($stock['type_name']) ?></td>
                        <td><?= htmlspecialchars($stock['unit_name']) ?> (<?= $stock['unit_symbol'] ?>)</td>
                        <td class="text-end">
                            <strong><?= number_format($stock['total_quantity'], 2) ?></strong>
                            <small class="text-muted"><?= $stock['unit_symbol'] ?></small>
                        </td>
                        <td class="text-end"><?= number_format($stock['avg_unit_price'], 2) ?></td>
                        <td class="text-end">
                            <strong class="text-success"><?= number_format($stock['total_value'], 2) ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($warehouseStock)): ?>
                <tfoot class="bg-light">
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Totals:</td>
                        <td class="text-end"><?= number_format($totalItems, 2) ?></td>
                        <td></td>
                        <td class="text-end text-success"><?= number_format($totalValue, 2) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-info">
    <i class="ti ti-info-circle me-2"></i>
    Please select a warehouse to view stock.
</div>
<?php endif; ?>

<script>
window.APP_URL = '<?= APP_URL ?>';
document.getElementById('warehouseSelect')?.addEventListener('change', function() {
    if (this.value) {
        window.location.href = window.APP_URL + '/warehouse/stock?warehouse_id=' + this.value;
    }
});
</script>