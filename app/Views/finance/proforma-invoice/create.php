<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Create Proforma Invoice') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/proforma-invoice">Proforma Invoices</a></li>
                    <li class="breadcrumb-item active">Create Invoice</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-plus me-2"></i>Create Proforma Invoice
                </h5>
            </div>
            
            <div class="card-body">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="<?= APP_URL ?>/finance/proforma-invoice/store" id="invoiceForm">
                    <!-- Step 1: Client & Currency Selection -->
                    <div id="step1" class="form-step">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select class="form-control" id="client_id" name="client_id" required>
                                        <option value="">Select Client</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client['id'] ?>" 
                                                    <?= ($_SESSION['old_input']['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($client['name']) ?> 
                                                <?php if ($client['phone']): ?>
                                                    - <?= htmlspecialchars($client['phone']) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select class="form-control" id="currency_id" name="currency_id" required>
                                        <option value="">Select Currency</option>
                                        <?php foreach ($currencies as $currency): ?>
                                            <option value="<?= $currency['currency_id'] ?>" 
                                                    data-sign="<?= htmlspecialchars($currency['sign']) ?>"
                                                    <?= ($_SESSION['old_input']['currency_id'] ?? '') == $currency['currency_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($currency['curre_name']) ?> (<?= htmlspecialchars($currency['sign']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= APP_URL ?>/finance/proforma-invoice" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i>Back to List
                            </a>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Next <i class="ti ti-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Invoice Items -->
                    <div id="step2" class="form-step" style="display: none;">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Invoice Items</h5>
                                    <button type="button" class="btn btn-sm btn-success" id="addProductRow">
                                        <i class="ti ti-plus me-1"></i>Add Product
                                    </button>
                                </div>

                                <div id="productsContainer">
                                    <!-- Product rows will be added here -->
                                </div>
                            </div>

                            <!-- Summary Section -->
                            <div class="col-12">
                                <div class="card bg-light border-0 mt-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Total Amount:</h5>
                                            <h4 class="mb-0 text-primary">
                                                <span id="currencySymbol">$</span>
                                                <span id="totalAmount">0.00</span>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn">
                                <i class="ti ti-arrow-left me-1"></i>Previous
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Create Invoice
                            </button>
                        </div>
                    </div>
                    
                    <input type="hidden" name="invoice_type" value="Proforma">
                </form>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['old_input']); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryTypes = <?= json_encode($categoryTypes) ?>;
    const currencySelect = document.getElementById('currency_id');
    const clientSelect = document.getElementById('client_id');
    const productsContainer = document.getElementById('productsContainer');
    const addProductBtn = document.getElementById('addProductRow');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    let productRowCounter = 0;

    // Step navigation
    nextBtn.addEventListener('click', function() {
        // Validate step 1
        if (!clientSelect.value) {
            alert('Please select a client');
            clientSelect.focus();
            return;
        }
        if (!currencySelect.value) {
            alert('Please select a currency');
            currencySelect.focus();
            return;
        }
        
        // Move to step 2
        step1.style.display = 'none';
        step2.style.display = 'block';
        
        // Update currency symbol on products
        updateAllCurrencySymbols();
        
        // Add initial product row if none exists
        if (productRowCounter === 0) {
            addProductRow();
        }
    });

    prevBtn.addEventListener('click', function() {
        step2.style.display = 'none';
        step1.style.display = 'block';
    });

    // Update currency symbol
    function updateAllCurrencySymbols() {
        const selectedOption = currencySelect.options[currencySelect.selectedIndex];
        const sign = selectedOption.getAttribute('data-sign') || '$';
        document.getElementById('currencySymbol').textContent = sign;
        document.querySelectorAll('.currency-symbol').forEach(el => {
            el.textContent = sign;
        });
    }

    currencySelect.addEventListener('change', updateAllCurrencySymbols);

    // Add product row
    addProductBtn.addEventListener('click', addProductRow);

    function addProductRow() {
        productRowCounter++;
        const row = document.createElement('div');
        row.className = 'product-row border rounded p-3 mb-3';
        row.setAttribute('data-row', productRowCounter);
        
        const currencySign = currencySelect.options[currencySelect.selectedIndex]?.getAttribute('data-sign') || '$';
        
        row.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Product Type</label>
                    <select class="form-control product-type" name="products[]" required>
                        <option value="">Select Product</option>
                        ${categoryTypes.map(type => 
                            `<option value="${type.id}" data-name="${type.name}">
                                ${type.name}
                            </option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <select class="form-control product-unit" name="unit_ids[]" required>
                        <option value="">Select Unit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control product-quantity" name="quantities[]" 
                           placeholder="0" min="0.01" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text currency-symbol">${currencySign}</span>
                        <input type="number" class="form-control product-price" name="prices[]" 
                               placeholder="0.00" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control product-total" readonly value="0.00">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-product-row w-100">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <input type="hidden" class="product-name" name="product_names[]">
            </div>
        `;
        
        productsContainer.appendChild(row);
        
        // Add event listeners
        const typeSelect = row.querySelector('.product-type');
        const unitSelect = row.querySelector('.product-unit');
        const quantityInput = row.querySelector('.product-quantity');
        const priceInput = row.querySelector('.product-price');
        const removeBtn = row.querySelector('.remove-product-row');
        const productNameInput = row.querySelector('.product-name');
        
        typeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const productName = selectedOption.getAttribute('data-name');
            productNameInput.value = productName;
            
            // Load units for this product type
            loadUnitsForType(this.value, unitSelect);
        });
        
        quantityInput.addEventListener('input', () => calculateRowTotal(row));
        priceInput.addEventListener('input', () => calculateRowTotal(row));
        
        removeBtn.addEventListener('click', function() {
            row.remove();
            updateTotalAmount();
        });
    }

    function loadUnitsForType(typeId, unitSelect) {
        if (!typeId) {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            return;
        }
        
        fetch(`<?= APP_URL ?>/api/proforma/type-units?type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.units) {
                    unitSelect.innerHTML = '<option value="">Select Unit</option>';
                    data.units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.measurement_unit_id;
                        option.textContent = `${unit.unit_name} (${unit.unit_symbol})`;
                        unitSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading units:', error));
    }

    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.product-price').value) || 0;
        const total = quantity * price;
        
        row.querySelector('.product-total').value = total.toFixed(2);
        updateTotalAmount();
    }

    function updateTotalAmount() {
        let grandTotal = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const total = parseFloat(row.querySelector('.product-total').value) || 0;
            grandTotal += total;
        });
        
        document.getElementById('totalAmount').textContent = grandTotal.toFixed(2);
    }
});
</script>