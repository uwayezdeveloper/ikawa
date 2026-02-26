<?php
$permissions = $user['permissions'] ?? [];
$canCreate = in_array('create-stock-transfers', $permissions);
$canEdit = in_array('edit-stock-transfers', $permissions);

// Encode data for JavaScript
$warehousesJson = json_encode($warehouses);
$clientsJson = json_encode($clients);
?>

<style>
.cart-panel {
    position: sticky;
    top: 80px;
}

.stock-qty-input {
    width: 80px;
}

.cart-item {
    border-bottom: 1px solid #eee;
    padding: 8px 0;
}

.cart-item:last-child {
    border-bottom: none;
}

.add-to-cart-btn {
    min-width: 40px;
}
</style>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Warehouse Sales</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/warehouse/stock">Warehouse</a></li>
                <li class="breadcrumb-item active">Sales</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= APP_URL ?>/warehouse/sales/history" class="btn btn-outline-primary">
            <i class="ti ti-history me-1"></i> Sales History
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

<div class="row">
    <!-- Stock List -->
    <div class="col-lg-8 mb-4">
        <!-- Warehouse Selection -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label mb-0 fw-semibold">Select Warehouse</label>
                    </div>
                    <div class="col-md-8">
                        <select class="form-select" id="warehouseSelect">
                            <option value="">-- Select Warehouse --</option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="card">
            <div class="card-header border-light">
                <h5 class="card-title mb-0"><i class="ti ti-packages me-2"></i>Available Stock</h5>
                <div class="app-search ms-auto" style="width: 250px;">
                    <input type="search" class="form-control" id="stockSearch" placeholder="Search products..." />
                    <i class="ti ti-search app-search-icon text-muted"></i>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-centered mb-0" id="stockTable">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Supplier</th>
                            <th>Stock Unit</th>
                            <th>Processing</th>
                            <th class="text-end">Available</th>
                            <th class="text-center" style="width: 110px;">Sell Unit</th>
                            <th class="text-center" style="width: 90px;">Qty</th>
                            <th class="text-center" style="width: 90px;">Price</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="stockBody">
                        <tr id="noWarehouseRow">
                            <td colspan="10" class="text-center py-5">
                                <i class="ti ti-building-warehouse fs-1 text-muted"></i>
                                <p class="text-muted mb-0 mt-2">Select a warehouse to view stock</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="col-lg-4">
        <div class="card cart-panel">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 text-white"><i class="ti ti-shopping-cart me-2"></i>Cart</h5>
                <span class="badge bg-white text-primary" id="cartCount">0</span>
            </div>
            <div class="card-body p-0">
                <div id="cartEmpty" class="text-center py-5">
                    <i class="ti ti-shopping-cart-off fs-1 text-muted"></i>
                    <p class="text-muted mb-0 mt-2">Cart is empty</p>
                    <small class="text-muted">Add items from the stock list</small>
                </div>
                <div id="cartItems" class="p-3" style="max-height: 350px; overflow-y: auto; display: none;">
                    <!-- Cart items will be rendered here -->
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold">Total Items:</span>
                    <span id="cartTotalItems">0</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold fs-5">Total:</span>
                    <span class="fw-bold fs-5" id="cartTotal">0.00</span>
                </div>
                <button type="button" class="btn btn-primary w-100" id="checkoutBtn" disabled>
                    <i class="ti ti-credit-card me-1"></i> Proceed to Checkout
                </button>
                <button type="button" class="btn btn-outline-danger w-100 mt-2" id="clearCartBtn"
                    style="display: none;">
                    <i class="ti ti-trash me-1"></i> Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="ti ti-credit-card me-2"></i>Checkout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= APP_URL ?>/warehouse/sales/action" method="POST" id="checkoutForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="location_id" id="checkoutLocationId">
                <div id="checkoutItemsInput"></div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select" name="client_id" id="checkoutClient" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="sale_date" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                    </div>

                    <h6 class="mb-3"><i class="ti ti-list me-2"></i>Order Summary</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="checkoutItems"></tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold" id="checkoutTotal">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"
                            placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i> Confirm Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('warehouseSelect');
    const stockBody = document.getElementById('stockBody');
    const stockSearch = document.getElementById('stockSearch');
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartCount = document.getElementById('cartCount');
    const cartTotalItems = document.getElementById('cartTotalItems');
    const cartTotal = document.getElementById('cartTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');

    let stockData = [];
    let cart = [];
    let unitCache = {}; // Cache for compatible units

    // Load stock when warehouse changes
    warehouseSelect.addEventListener('change', function() {
        const warehouseId = this.value;
        cart = []; // Clear cart when warehouse changes
        updateCartUI();

        if (!warehouseId) {
            stockBody.innerHTML = `
                <tr id="noWarehouseRow">
                    <td colspan="10" class="text-center py-5">
                        <i class="ti ti-building-warehouse fs-1 text-muted"></i>
                        <p class="text-muted mb-0 mt-2">Select a warehouse to view stock</p>
                    </td>
                </tr>`;
            return;
        }

        stockBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span class="ms-2">Loading stock...</span>
                </td>
            </tr>`;

        fetch('<?= APP_URL ?>/warehouse/sales/action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=get_stock&location_id=${warehouseId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    stockData = data.stock;
                    renderStock(stockData);
                } else {
                    stockBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4 text-danger">
                            Failed to load stock
                        </td>
                    </tr>`;
                }
            })
            .catch(err => {
                stockBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4 text-danger">
                        Error loading stock
                    </td>
                </tr>`;
            });
    });

    // Search filter
    stockSearch.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const filtered = stockData.filter(item =>
            item.category_name.toLowerCase().includes(term) ||
            item.type_name.toLowerCase().includes(term) ||
            (item.step_name && item.step_name.toLowerCase().includes(term)) ||
            (item.supplier_name && item.supplier_name.toLowerCase().includes(term))
        );
        renderStock(filtered);
    });

    function renderStock(data) {
        if (data.length === 0) {
            stockBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="ti ti-package-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0 mt-2">No stock available</p>
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        data.forEach(item => {
            const stockKey =
                `${item.product_category_id}-${item.category_type_unit_id}-${item.processing_step_id || 0}-${item.supplier_id || 0}`;
            const itemData = JSON.stringify({
                stockKey: stockKey,
                categoryId: item.product_category_id,
                categoryTypeId: item.category_type_id,
                ctuId: item.category_type_unit_id,
                stepId: item.processing_step_id || 0,
                supplierId: item.supplier_id || 0,
                categoryName: item.category_name,
                typeName: item.type_name,
                stepName: item.step_name || '',
                supplierName: item.supplier_name || '',
                stockUnitId: item.unit_id,
                stockUnitSymbol: item.unit_symbol,
                stockUnitName: item.unit_name,
                stockConversionFactor: parseFloat(item.conversion_factor),
                maxQty: parseFloat(item.total_quantity)
            }).replace(/"/g, '&quot;');

            html += `
                <tr data-stock-key="${stockKey}" data-item='${itemData}'>
                    <td>
                        <span class="fw-semibold">${item.category_name}</span>
                    </td>
                    <td>${item.type_name}</td>
                    <td>${item.supplier_name || '<span class="text-muted">-</span>'}</td>
                    <td><span class="badge bg-info-subtle text-info">${item.unit_name} (${item.unit_symbol})</span></td>
                    <td>${item.step_name || '<span class="text-muted">-</span>'}</td>
                    <td class="text-end">
                        <span class="badge bg-success-subtle text-success">${parseFloat(item.total_quantity).toFixed(2)} ${item.unit_symbol}</span>
                    </td>
                    <td class="text-center">
                        <select class="form-select form-select-sm" id="unit-${stockKey}" style="width: 100px; margin: 0 auto;"
                            onchange="loadUnitsIfNeeded('${stockKey}', ${item.unit_id})">
                            <option value="${item.unit_id}" data-factor="${item.conversion_factor}" data-symbol="${item.unit_symbol}" selected>
                                ${item.unit_symbol}
                            </option>
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center" 
                            id="qty-${stockKey}" 
                            step="0.01" min="0.01" 
                            placeholder="0" style="width: 80px; margin: 0 auto;">
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center" 
                            id="price-${stockKey}" 
                            step="0.01" min="0" 
                            placeholder="0" style="width: 80px; margin: 0 auto;">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary add-to-cart-btn"
                            onclick="addToCart('${stockKey}')">
                            <i class="ti ti-plus"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        stockBody.innerHTML = html;

        // Load compatible units for each row
        data.forEach(item => {
            const stockKey =
                `${item.product_category_id}-${item.category_type_unit_id}-${item.processing_step_id || 0}-${item.supplier_id || 0}`;
            loadCompatibleUnits(stockKey, item.category_type_id, item.unit_id);
        });
    }

    // Load compatible units for dropdown
    window.loadUnitsIfNeeded = function(stockKey, categoryTypeId) {
        // Units already loaded on render
    };

    function loadCompatibleUnits(stockKey, categoryTypeId, currentUnitId) {
        if (unitCache[categoryTypeId]) {
            populateUnitDropdown(stockKey, unitCache[categoryTypeId], currentUnitId);
            return;
        }

        fetch('<?= APP_URL ?>/warehouse/sales/action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=get_units&category_type_id=${categoryTypeId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.units) {
                    unitCache[categoryTypeId] = data.units;
                    populateUnitDropdown(stockKey, data.units, currentUnitId);
                }
            });
    }

    function populateUnitDropdown(stockKey, units, currentUnitId) {
        const select = document.getElementById(`unit-${stockKey}`);
        if (!select) return;

        let html = '';
        units.forEach(unit => {
            const selected = unit.id == currentUnitId ? 'selected' : '';
            html += `<option value="${unit.id}" data-factor="${unit.conversion_factor}" data-symbol="${unit.symbol}" ${selected}>
                ${unit.symbol}
            </option>`;
        });
        select.innerHTML = html;
    }

    // Convert quantity between units
    function convertQuantity(qty, fromFactor, toFactor) {
        // Convert to base unit first, then to target unit
        const baseValue = qty * fromFactor;
        return baseValue / toFactor;
    }

    // Add to cart function
    window.addToCart = function(stockKey) {
        const row = document.querySelector(`tr[data-stock-key="${stockKey}"]`);
        const itemData = JSON.parse(row.dataset.item.replace(/&quot;/g, '"'));

        const unitSelect = document.getElementById(`unit-${stockKey}`);
        const qtyInput = document.getElementById(`qty-${stockKey}`);
        const priceInput = document.getElementById(`price-${stockKey}`);

        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const sellUnitId = parseInt(unitSelect.value);
        const sellUnitFactor = parseFloat(selectedOption.dataset.factor);
        const sellUnitSymbol = selectedOption.dataset.symbol;

        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        if (qty <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Quantity',
                text: 'Please enter a quantity greater than 0',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            return;
        }

        // Convert entered quantity to stock unit for validation
        const qtyInStockUnit = convertQuantity(qty, sellUnitFactor, itemData.stockConversionFactor);

        // Check if already in cart (sum quantities in stock unit)
        let existingQtyInStockUnit = 0;
        cart.forEach(item => {
            if (item.stockKey === stockKey) {
                existingQtyInStockUnit += item.qtyInStockUnit;
            }
        });

        if (qtyInStockUnit + existingQtyInStockUnit > itemData.maxQty) {
            const maxInSellUnit = convertQuantity(itemData.maxQty - existingQtyInStockUnit, itemData
                .stockConversionFactor, sellUnitFactor);
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Stock',
                text: `Maximum available: ${maxInSellUnit.toFixed(2)} ${sellUnitSymbol} (${itemData.maxQty.toFixed(2)} ${itemData.stockUnitSymbol} in stock)`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        // Add new cart item (don't merge - each entry separate for clarity)
        cart.push({
            stockKey,
            categoryId: itemData.categoryId,
            categoryTypeUnitId: itemData.ctuId,
            processingStepId: itemData.stepId,
            supplierId: itemData.supplierId,
            categoryName: itemData.categoryName,
            typeName: itemData.typeName,
            stepName: itemData.stepName,
            supplierName: itemData.supplierName,
            stockUnitSymbol: itemData.stockUnitSymbol,
            sellUnitId: sellUnitId,
            sellUnitSymbol: sellUnitSymbol,
            sellUnitFactor: sellUnitFactor,
            quantity: qty,
            qtyInStockUnit: qtyInStockUnit,
            unitPrice: price,
            total: qty * price,
            maxQty: itemData.maxQty,
            stockConversionFactor: itemData.stockConversionFactor
        });

        qtyInput.value = '';
        priceInput.value = '';
        updateCartUI();

        Swal.fire({
            icon: 'success',
            title: 'Added to Cart',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1000
        });
    };

    function updateCartUI() {
        if (cart.length === 0) {
            cartEmpty.style.display = '';
            cartItems.style.display = 'none';
            clearCartBtn.style.display = 'none';
            checkoutBtn.disabled = true;
            cartCount.textContent = '0';
            cartTotalItems.textContent = '0';
            cartTotal.textContent = '0.00';
            return;
        }

        cartEmpty.style.display = 'none';
        cartItems.style.display = '';
        clearCartBtn.style.display = '';
        checkoutBtn.disabled = false;

        let html = '';
        let totalItems = 0;
        let totalAmount = 0;

        cart.forEach((item, index) => {
            totalItems += item.quantity;
            totalAmount += item.total;
            const productName = item.stepName ?
                `${item.categoryName} - ${item.typeName} (${item.stepName})` :
                `${item.categoryName} - ${item.typeName}`;

            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">${productName}</div>
                            ${item.supplierName ? `<div class="text-muted small"><i class="ti ti-building-store me-1"></i>${item.supplierName}</div>` : ''}
                            <div class="text-muted small">
                                <span class="badge bg-info-subtle text-info me-1">${item.sellUnitSymbol}</span>
                                ${item.quantity.toFixed(2)} × ${item.unitPrice.toFixed(2)}
                            </div>
                            <div class="text-muted small fst-italic">
                                = ${item.qtyInStockUnit.toFixed(4)} ${item.stockUnitSymbol}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${item.total.toFixed(2)}</div>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeFromCart(${index})">
                                <i class="ti ti-trash fs-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        cartItems.innerHTML = html;
        cartCount.textContent = cart.length;
        cartTotalItems.textContent = totalItems.toFixed(2);
        cartTotal.textContent = totalAmount.toFixed(2);
    }

    window.removeFromCart = function(index) {
        cart.splice(index, 1);
        updateCartUI();
    };

    // Clear cart
    clearCartBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Clear Cart?',
            text: 'Remove all items from cart?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Clear'
        }).then(result => {
            if (result.isConfirmed) {
                cart = [];
                updateCartUI();
            }
        });
    });

    // Checkout
    checkoutBtn.addEventListener('click', function() {
        const warehouseId = warehouseSelect.value;
        if (!warehouseId) {
            Swal.fire('Error', 'Please select a warehouse', 'error');
            return;
        }

        document.getElementById('checkoutLocationId').value = warehouseId;

        // Render checkout items
        let itemsHtml = '';
        let inputsHtml = '';
        let total = 0;

        cart.forEach((item, index) => {
            total += item.total;
            const productName = item.stepName ?
                `${item.categoryName} - ${item.typeName} (${item.stepName})` :
                `${item.categoryName} - ${item.typeName}`;
            const supplierInfo = item.supplierName ?
                `<br><small class="text-muted"><i class="ti ti-building-store"></i> ${item.supplierName}</small>` :
                '';

            itemsHtml += `
                <tr>
                    <td>${productName}${supplierInfo}</td>
                    <td class="text-end">${item.quantity.toFixed(2)} ${item.sellUnitSymbol}</td>
                    <td class="text-end">${item.unitPrice.toFixed(2)}</td>
                    <td class="text-end">${item.total.toFixed(2)}</td>
                </tr>
            `;

            // Submit quantity converted to stock unit for proper stock deduction
            inputsHtml += `
                <input type="hidden" name="items[${index}][category_id]" value="${item.categoryId}">
                <input type="hidden" name="items[${index}][category_type_unit_id]" value="${item.categoryTypeUnitId}">
                <input type="hidden" name="items[${index}][processing_step_id]" value="${item.processingStepId}">
                <input type="hidden" name="items[${index}][supplier_id]" value="${item.supplierId || 0}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.qtyInStockUnit}">
                <input type="hidden" name="items[${index}][unit_price]" value="${item.unitPrice}">
                <input type="hidden" name="items[${index}][sell_quantity]" value="${item.quantity}">
                <input type="hidden" name="items[${index}][sell_unit_id]" value="${item.sellUnitId}">
            `;
        });

        document.getElementById('checkoutItems').innerHTML = itemsHtml;
        document.getElementById('checkoutItemsInput').innerHTML = inputsHtml;
        document.getElementById('checkoutTotal').textContent = total.toFixed(2);

        const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
        modal.show();
    });

    // Clear cart after successful checkout
    document.getElementById('checkoutForm').addEventListener('submit', function() {
        localStorage.removeItem('warehouseCart');
    });
});
</script>