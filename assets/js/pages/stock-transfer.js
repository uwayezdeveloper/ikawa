/**
 * Stock Transfer Page JavaScript
 * With unit conversion support - Production-like flow
 */

document.addEventListener("DOMContentLoaded", function () {
  const fromLocationTypeSelect = document.getElementById("fromLocationTypeSelect");
  const fromLocationSelect = document.getElementById("fromLocationSelect");
  const toLocationSelect = document.getElementById("toLocationSelect");
  const supplierSelect = document.getElementById("supplierSelect");
  const categoryTypeSelect = document.getElementById("categoryTypeSelect");
  const typeUnitSelect = document.getElementById("typeUnitSelect");
  const measurementUnitId = document.getElementById("measurementUnitId");
  const productCategoryId = document.getElementById("productCategoryId");
  const quantityInput = document.getElementById("quantityInput");
  const unitPriceInput = document.getElementById("unitPriceInput");
  const totalValue = document.getElementById("totalValue");
  const unitLabel = document.getElementById("unitLabel");
  const totalStockInfo = document.getElementById("totalStockInfo");
  const availableInfo = document.getElementById("availableInfo");

  // Track available stock
  let availableStock = 0;
  let totalInBase = 0;
  let currentUnitConversionFactor = 1;

  // Calculate total value
  function calculateTotal() {
    const qty = parseFloat(quantityInput?.value) || 0;
    const price = parseFloat(unitPriceInput?.value) || 0;
    if (totalValue) {
      totalValue.value = (qty * price).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }
  }

  if (quantityInput) quantityInput.addEventListener("input", calculateTotal);
  if (unitPriceInput) unitPriceInput.addEventListener("input", calculateTotal);

  // Reset functions
  function resetFromLocation() {
    if (supplierSelect) {
      supplierSelect.innerHTML = '<option value="">Select Location first</option>';
      supplierSelect.disabled = true;
    }
    resetFromSupplier();
  }

  function resetFromSupplier() {
    if (categoryTypeSelect) {
      categoryTypeSelect.innerHTML = '<option value="">Select Supplier first</option>';
      categoryTypeSelect.disabled = true;
    }
    if (productCategoryId) productCategoryId.value = '';
    resetFromCategoryType();
  }

  function resetFromCategoryType() {
    if (typeUnitSelect) {
      typeUnitSelect.innerHTML = '<option value="">Select Product Type first</option>';
      typeUnitSelect.disabled = true;
    }
    if (measurementUnitId) measurementUnitId.value = '';
    if (unitLabel) unitLabel.textContent = 'units';
    if (totalStockInfo) totalStockInfo.textContent = '';
    if (availableInfo) availableInfo.textContent = 'Select product and unit to see available stock';
    availableStock = 0;
    totalInBase = 0;
  }

  // From Location Type change -> Load locations
  if (fromLocationTypeSelect) {
    fromLocationTypeSelect.addEventListener("change", function () {
      const typeId = this.value;

      if (fromLocationSelect) {
        fromLocationSelect.innerHTML = '<option value="">Loading...</option>';
        fromLocationSelect.disabled = true;
      }
      resetFromLocation();

      if (!typeId) {
        if (fromLocationSelect) fromLocationSelect.innerHTML = '<option value="">Select Location Type first</option>';
        return;
      }

      fetch(window.APP_URL + "/stock/transfers/action", {
        method: "POST",
        headers: { 
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: "action=get_locations&location_type_id=" + typeId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && fromLocationSelect) {
            fromLocationSelect.innerHTML = '<option value="">Select Location</option>';
            data.data.forEach((loc) => {
              fromLocationSelect.innerHTML += '<option value="' + loc.id + '">' + loc.name + '</option>';
            });
            fromLocationSelect.disabled = false;
          }
        });
    });
  }

  // From Location change -> Load suppliers with stock
  if (fromLocationSelect) {
    fromLocationSelect.addEventListener("change", function () {
      const locationId = this.value;

      if (supplierSelect) {
        supplierSelect.innerHTML = '<option value="">Loading...</option>';
        supplierSelect.disabled = true;
      }
      resetFromSupplier();

      if (!locationId) {
        if (supplierSelect) supplierSelect.innerHTML = '<option value="">Select Location first</option>';
        return;
      }

      fetch(window.APP_URL + "/stock/transfers/action", {
        method: "POST",
        headers: { 
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: "action=get_suppliers_at_location&location_id=" + locationId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && supplierSelect) {
            supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
            data.data.forEach((supplier) => {
              supplierSelect.innerHTML += '<option value="' + supplier.id + '">' + supplier.name + '</option>';
            });
            supplierSelect.disabled = false;
          }
        });
    });
  }

  // Supplier change -> Load product types WITH STOCK INFO for this supplier
  if (supplierSelect) {
    supplierSelect.addEventListener("change", function () {
      const locationId = fromLocationSelect ? fromLocationSelect.value : '';
      const supplierId = this.value;

      if (categoryTypeSelect) {
        categoryTypeSelect.innerHTML = '<option value="">Loading...</option>';
        categoryTypeSelect.disabled = true;
      }
      resetFromCategoryType();

      if (!supplierId) {
        if (categoryTypeSelect) categoryTypeSelect.innerHTML = '<option value="">Select Supplier first</option>';
        return;
      }

      fetch(window.APP_URL + "/stock/transfers/action", {
        method: "POST",
        headers: { 
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: "action=get_supplier_product_types&location_id=" + locationId + "&supplier_id=" + supplierId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && categoryTypeSelect) {
            categoryTypeSelect.innerHTML = '<option value="">Select Product Type</option>';
            data.data.forEach((type) => {
              // Show product type with stock info: "Floatant - 100 kg"
              categoryTypeSelect.innerHTML += '<option value="' + type.id + '" data-category-id="' + type.category_id + '" data-total-base="' + type.total_in_base + '">' + type.name + ' - ' + type.stock_display + '</option>';
            });
            categoryTypeSelect.disabled = false;
          }
        });
    });
  }

  // Category Type change -> Load units (simple list, no stock display)
  if (categoryTypeSelect) {
    categoryTypeSelect.addEventListener("change", function () {
      const locationId = fromLocationSelect ? fromLocationSelect.value : '';
      const supplierId = supplierSelect ? supplierSelect.value : '';
      const categoryTypeId = this.value;
      const selectedOption = this.options[this.selectedIndex];
      const categoryId = selectedOption?.dataset.categoryId || '';
      totalInBase = parseFloat(selectedOption?.dataset.totalBase) || 0;

      // Store category_id
      if (productCategoryId) productCategoryId.value = categoryId;

      if (typeUnitSelect) {
        typeUnitSelect.innerHTML = '<option value="">Loading...</option>';
        typeUnitSelect.disabled = true;
      }
      if (unitLabel) unitLabel.textContent = 'units';
      if (totalStockInfo) totalStockInfo.textContent = '';

      if (!categoryTypeId) {
        if (typeUnitSelect) typeUnitSelect.innerHTML = '<option value="">Select Product Type first</option>';
        return;
      }

      fetch(window.APP_URL + "/stock/transfers/action", {
        method: "POST",
        headers: { 
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: "action=get_type_units_with_stock&location_id=" + locationId + "&supplier_id=" + supplierId + "&category_type_id=" + categoryTypeId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && typeUnitSelect) {
            typeUnitSelect.innerHTML = '<option value="">Select Unit</option>';
            // Just show unit names, no stock info
            data.data.units.forEach((unit) => {
              typeUnitSelect.innerHTML += '<option value="' + unit.id + '" data-measurement-unit-id="' + unit.measurement_unit_id + '" data-symbol="' + unit.symbol + '" data-conversion="' + unit.conversion_factor + '">' + unit.name + ' (' + unit.symbol + ')</option>';
            });
            typeUnitSelect.disabled = false;
          }
        });
    });
  }

  // Type Unit change -> Update available stock display
  if (typeUnitSelect) {
    typeUnitSelect.addEventListener("change", function () {
      const selectedOption = this.options[this.selectedIndex];
      const unitId = selectedOption?.dataset.measurementUnitId || '';
      const unitSymbol = selectedOption?.dataset.symbol || 'units';
      const conversionFactor = parseFloat(selectedOption?.dataset.conversion) || 1;

      // Store measurement_unit_id
      if (measurementUnitId) measurementUnitId.value = unitId;
      currentUnitConversionFactor = conversionFactor;

      // Update unit label
      if (unitLabel) unitLabel.textContent = unitSymbol;

      // Calculate available in selected unit
      availableStock = totalInBase / conversionFactor;

      if (totalStockInfo) {
        totalStockInfo.textContent = 'Total available: ' + availableStock.toFixed(2) + ' ' + unitSymbol;
      }
      if (availableInfo) {
        availableInfo.textContent = 'You can transfer up to ' + availableStock.toFixed(2) + ' ' + unitSymbol;
      }
    });
  }

  // Form validation
  const transferForm = document.getElementById("transferStockForm");
  if (transferForm) {
    transferForm.addEventListener("submit", function (e) {
      const qty = parseFloat(quantityInput?.value) || 0;

      if (qty > availableStock && availableStock > 0) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "Insufficient Stock",
          text: "You can only transfer up to " + availableStock.toFixed(2) + " units",
        });
        return false;
      }

      const fromLocation = fromLocationSelect ? fromLocationSelect.value : '';
      const toLocation = toLocationSelect ? toLocationSelect.value : '';

      if (fromLocation === toLocation) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "Invalid Transfer",
          text: "Source and destination cannot be the same",
        });
        return false;
      }
    });
  }

  // Initialize DataTable if available
  if (typeof jQuery !== "undefined" && typeof jQuery.fn.DataTable !== "undefined") {
    jQuery("#transfersTable").DataTable({
      order: [[1, "desc"]],
      pageLength: 10,
    });
  }
});
