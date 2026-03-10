/**
 * Stock Receive Page JavaScript
 * Handles cascade dropdowns, multi-item receive rows, and form interactions
 */
(function () {
  "use strict";

  // Elements
  const locationTypeSelect = document.getElementById("locationTypeSelect");
  const locationSelect = document.getElementById("locationSelect");
  const categorySelect = document.getElementById("categorySelect");
  const lineItemsContainer = document.getElementById("lineItemsContainer");
  const addReceiveItemBtn = document.getElementById("addReceiveItemBtn");
  const supplierSearchInput = document.getElementById("supplierSearchInput");
  const supplierIdInput = document.getElementById("supplierIdInput");
  const supplierOptions = document.getElementById("supplierOptions");
  const supplierSearchHelp = document.getElementById("supplierSearchHelp");
  const accountSelect = document.getElementById("accountSelect");
  const accountWrap = document.getElementById("accountWrap");
  const supplierAdvanceInfo = document.getElementById("supplierAdvanceInfo");
  const advanceInfoWrap = document.getElementById("advanceInfoWrap");
  const totalPriceDisplay = document.getElementById("totalPriceDisplay");
  const receiveStockForm = document.getElementById("receiveStockForm");
  const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
  let categoryTypeOptionsCache = [];

  // Get APP_URL from window or construct it
  const APP_URL = window.APP_URL || "";

  /**
   * AJAX helper function
   */
  function postAjax(action, data, callback) {
    const formData = new FormData();
    formData.append("action", action);
    for (const key in data) {
      formData.append(key, data[key]);
    }

    fetch(APP_URL + "/stock/receives/action", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          callback(result.data);
        } else {
          console.error("AJAX Error:", result.message);
        }
      })
      .catch((error) => {
        console.error("Fetch Error:", error);
      });
  }

  /**
   * Reset and disable a select
   */
  function resetSelect(select, placeholder) {
    if (!select) return;
    select.innerHTML = '<option value="">' + placeholder + "</option>";
    select.disabled = true;
  }

  /**
   * Populate a select with options
   */
  function populateSelect(select, options, valueProp, labelProp, placeholder) {
    if (!select) return;
    select.innerHTML = '<option value="">' + placeholder + "</option>";
    options.forEach(function (opt) {
      const option = document.createElement("option");
      option.value = opt[valueProp];
      option.textContent = opt[labelProp];
      // Store conversion_factor if available (for unit selects)
      if (opt.conversion_factor !== undefined) {
        option.dataset.conversionFactor = opt.conversion_factor;
      }
      if (opt.is_default) {
        option.selected = true;
      }
      select.appendChild(option);
    });
    select.disabled = false;
  }

  function getLineRows() {
    if (!lineItemsContainer) return [];
    return Array.from(lineItemsContainer.querySelectorAll(".receive-line-item"));
  }

  function setLineRemoveButtonsVisibility() {
    const rows = getLineRows();
    rows.forEach(function (row, index) {
      const removeBtn = row.querySelector(".remove-line-item-btn");
      if (!removeBtn) return;
      removeBtn.classList.toggle("d-none", rows.length === 1 || index === 0);
    });
  }

  function reindexLineRows() {
    const rows = getLineRows();

    rows.forEach(function (row, index) {
      row.dataset.rowIndex = String(index);

      const label = row.querySelector(".line-item-label");
      if (label) {
        label.textContent = "Item #" + (index + 1);
      }

      const categoryTypeSelect = row.querySelector(".category-type-select");
      const typeUnitSelect = row.querySelector(".type-unit-select");
      const quantityInput = row.querySelector(".quantity-input");
      const unitPriceInput = row.querySelector(".unit-price-input");

      if (categoryTypeSelect) {
        categoryTypeSelect.name = "items[" + index + "][category_type_id]";
      }
      if (typeUnitSelect) {
        typeUnitSelect.name = "items[" + index + "][category_type_unit_id]";
      }
      if (quantityInput) {
        quantityInput.name = "items[" + index + "][quantity]";
      }
      if (unitPriceInput) {
        unitPriceInput.name = "items[" + index + "][unit_price]";
      }
    });

    setLineRemoveButtonsVisibility();
  }

  function createLineItemRow(index) {
    const row = document.createElement("div");
    row.className = "border rounded p-2 receive-line-item";
    row.dataset.rowIndex = String(index);
    row.innerHTML =
      '<div class="d-flex justify-content-between align-items-center mb-2">' +
      '<small class="text-muted fw-semibold line-item-label">Item #' +
      (index + 1) +
      "</small>" +
      '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-line-item-btn">' +
      '<i class="ti ti-trash me-1"></i>Remove' +
      "</button>" +
      "</div>" +
      '<div class="mb-2">' +
      '<label class="form-label">Category Type <span class="text-danger">*</span></label>' +
      '<select class="form-select category-type-select" required>' +
      '<option value="">Select Category first</option>' +
      "</select>" +
      "</div>" +
      '<div class="mb-2">' +
      '<label class="form-label">Unit <span class="text-danger">*</span></label>' +
      '<select class="form-select type-unit-select" required disabled>' +
      '<option value="">Select Category Type first</option>' +
      "</select>" +
      "</div>" +
      '<div class="row g-2 align-items-end">' +
      '<div class="col-md-4">' +
      '<label class="form-label">Quantity <span class="text-danger">*</span></label>' +
      '<input type="number" class="form-control quantity-input" required min="0.01" step="0.01">' +
      "</div>" +
      '<div class="col-md-4">' +
      '<label class="form-label">Price/kg <span class="text-danger">*</span></label>' +
      '<input type="number" class="form-control unit-price-input" required min="0" step="0.01" placeholder="Price per kg">' +
      "</div>" +
      '<div class="col-md-4">' +
      '<label class="form-label">Line Total</label>' +
      '<input type="text" class="form-control bg-light item-total-display" readonly value="0.00">' +
      "</div>" +
      "</div>";

    if (categoryTypeOptionsCache.length > 0) {
      const categoryTypeSelect = row.querySelector(".category-type-select");
      populateSelect(
        categoryTypeSelect,
        categoryTypeOptionsCache,
        "id",
        "name",
        "Select Category Type",
      );
      categoryTypeSelect.disabled = false;
    } else {
      const categoryTypeSelect = row.querySelector(".category-type-select");
      categoryTypeSelect.disabled = true;
    }

    return row;
  }

  function resetLineUnits(row) {
    const typeUnitSelect = row.querySelector(".type-unit-select");
    if (typeUnitSelect) {
      resetSelect(typeUnitSelect, "Select Category Type first");
    }
  }

  function populateCategoryTypesForAllRows(options) {
    const rows = getLineRows();
    rows.forEach(function (row) {
      const categoryTypeSelect = row.querySelector(".category-type-select");
      if (!categoryTypeSelect) return;

      const previousValue = categoryTypeSelect.value;
      populateSelect(
        categoryTypeSelect,
        options,
        "id",
        "name",
        "Select Category Type",
      );

      if (previousValue) {
        categoryTypeSelect.value = previousValue;
        if (!categoryTypeSelect.value) {
          resetLineUnits(row);
        }
      }
    });
  }

  function resetAllLineItems() {
    if (!lineItemsContainer) return;

    const rows = getLineRows();
    rows.forEach(function (row, index) {
      if (index > 0) {
        row.remove();
      }
    });

    const firstRow = getLineRows()[0];
    if (!firstRow) return;

    const categoryTypeSelect = firstRow.querySelector(".category-type-select");
    const typeUnitSelect = firstRow.querySelector(".type-unit-select");
    const quantityInput = firstRow.querySelector(".quantity-input");
    const unitPriceInput = firstRow.querySelector(".unit-price-input");
    const itemTotalDisplay = firstRow.querySelector(".item-total-display");

    if (categoryTypeSelect) {
      resetSelect(categoryTypeSelect, "Select Category first");
      categoryTypeSelect.disabled = true;
    }
    if (typeUnitSelect) {
      resetSelect(typeUnitSelect, "Select Category Type first");
      typeUnitSelect.disabled = true;
    }
    if (quantityInput) quantityInput.value = "";
    if (unitPriceInput) unitPriceInput.value = "";
    if (itemTotalDisplay) {
      itemTotalDisplay.value = "0.00";
      itemTotalDisplay.dataset.rawTotal = "0";
    }

    reindexLineRows();
  }

  function calculateLineTotal(row) {
    const typeUnitSelect = row.querySelector(".type-unit-select");
    const quantityInput = row.querySelector(".quantity-input");
    const unitPriceInput = row.querySelector(".unit-price-input");
    const itemTotalDisplay = row.querySelector(".item-total-display");

    if (!typeUnitSelect || !quantityInput || !unitPriceInput || !itemTotalDisplay) {
      return 0;
    }

    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const selectedOption = typeUnitSelect.options[typeUnitSelect.selectedIndex];
    const conversionFactor = parseFloat(selectedOption?.dataset?.conversionFactor) || 0;

    let total = 0;
    if (conversionFactor > 0) {
      const quantityInKg = quantity * (conversionFactor / 1000);
      total = unitPrice * quantityInKg;
    } else {
      total = quantity * unitPrice;
    }

    itemTotalDisplay.dataset.rawTotal = String(total);
    itemTotalDisplay.value = total.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    return total;
  }

  function calculateGrandTotal() {
    const rows = getLineRows();
    let grandTotal = 0;

    rows.forEach(function (row) {
      const itemTotalDisplay = row.querySelector(".item-total-display");
      if (!itemTotalDisplay) return;

      const rawTotal = parseFloat(itemTotalDisplay.dataset.rawTotal || "0") || 0;
      grandTotal += rawTotal;
    });

    if (totalPriceDisplay) {
      totalPriceDisplay.value = grandTotal.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    checkSupplierAdvance();
  }

  function loadTypeUnitsForRow(row, categoryTypeId) {
    const typeUnitSelect = row.querySelector(".type-unit-select");
    if (!typeUnitSelect) return;

    resetSelect(typeUnitSelect, "Loading units...");

    if (!categoryTypeId) {
      resetSelect(typeUnitSelect, "Select Category Type first");
      calculateLineTotal(row);
      calculateGrandTotal();
      return;
    }

    postAjax(
      "get_type_units",
      { category_type_id: categoryTypeId },
      function (data) {
        populateSelect(typeUnitSelect, data, "id", "unit_name", "Select Unit");
        calculateLineTotal(row);
        calculateGrandTotal();
      },
    );
  }

  /**
   * Populate account select with balance info
   */
  function populateAccountSelect(select, accounts, placeholder) {
    if (!select) return;
    select.innerHTML = '<option value="">' + placeholder + "</option>";
    accounts.forEach(function (acc) {
      const option = document.createElement("option");
      option.value = acc.id;
      const balance = parseFloat(acc.balance).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
      option.textContent = acc.account_name + " (Balance: " + balance + " FRW)";
      option.dataset.balance = acc.balance;
      select.appendChild(option);
    });
    select.disabled = true;
  }

  function getSelectedPaymentMethod() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    return selected ? selected.value : "pay_later";
  }

  function syncSupplierIdFromInput() {
    if (!supplierSearchInput || !supplierIdInput || !supplierOptions) return;

    const typedName = String(supplierSearchInput.value || "").trim().toLowerCase();
    if (!typedName) {
      supplierIdInput.value = "";
      if (supplierSearchHelp) {
        supplierSearchHelp.className = "text-muted";
        supplierSearchHelp.textContent = "Start typing supplier name and select from suggestions.";
      }
      return;
    }

    const matchedOption = Array.from(supplierOptions.options).find(function (opt) {
      return String(opt.value || "").trim().toLowerCase() === typedName;
    });

    supplierIdInput.value = matchedOption ? String(matchedOption.dataset.id || "") : "";

    if (supplierSearchHelp) {
      if (matchedOption) {
        supplierSearchHelp.className = "text-success";
        supplierSearchHelp.textContent = "Supplier/Farmer selected for this location.";
      } else {
        supplierSearchHelp.className = "text-danger";
        supplierSearchHelp.textContent =
          'No supplier/farmer on this location who is named "' +
          String(supplierSearchInput.value || "").trim() +
          '".';
      }
    }
  }

  function setAccountEnabledState() {
    if (!accountSelect) return;
    const method = getSelectedPaymentMethod();
    const hasLocation = !!(locationSelect && locationSelect.value);
    const hasAccounts = accountSelect.options.length > 1;

    if (method === "direct_pay" && hasLocation && hasAccounts) {
      accountSelect.disabled = false;
    } else {
      accountSelect.disabled = true;
      if (method !== "direct_pay") {
        accountSelect.value = "";
      }
    }
  }

  function updatePaymentMethodUI() {
    const method = getSelectedPaymentMethod();

    if (accountWrap) {
      accountWrap.style.display = method === "direct_pay" ? "block" : "none";
    }
    if (advanceInfoWrap) {
      advanceInfoWrap.style.display = method === "advance" ? "block" : "none";
    }

    if (supplierAdvanceInfo && method !== "advance") {
      supplierAdvanceInfo.innerHTML = "";
      supplierAdvanceInfo.style.display = "none";
    }

    setAccountEnabledState();

    if (method === "direct_pay") {
      if (locationSelect && locationSelect.value) {
        postAjax("get_accounts", { location_id: locationSelect.value }, function (data) {
          populateAccountSelect(accountSelect, data, "Select Account");
          setAccountEnabledState();
        });
      }
      return;
    }

    if (method === "advance") {
      checkSupplierAdvance();
      return;
    }

    if (method === "pay_later") {
      if (accountSelect) {
        resetSelect(accountSelect, "Select Account");
      }
      if (supplierAdvanceInfo) {
        supplierAdvanceInfo.innerHTML = "";
        supplierAdvanceInfo.style.display = "none";
      }
    }
  }

  /**
   * Check if supplier has available advance for the total amount
   */
  function checkSupplierAdvance() {
    if (!supplierIdInput || !supplierAdvanceInfo) return;

    const method = getSelectedPaymentMethod();
    if (method !== "advance") {
      supplierAdvanceInfo.innerHTML = "";
      supplierAdvanceInfo.style.display = "none";
      return;
    }

    const supplierId = supplierIdInput.value;
    const totalText = totalPriceDisplay ? String(totalPriceDisplay.value || "") : "0";
    const totalAmount = parseFloat(totalText.replace(/,/g, "")) || 0;

    // Clear if no supplier selected
    if (!supplierId) {
      supplierAdvanceInfo.innerHTML = "";
      supplierAdvanceInfo.style.display = "none";
      return;
    }

    // Check for available advance
    postAjax(
      "check_supplier_advance",
      { supplier_id: supplierId, amount: totalAmount },
      function (data) {
        const formatAmount = (amt) =>
          parseFloat(amt).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });

        if (totalAmount <= 0) {
          supplierAdvanceInfo.innerHTML =
            '<div class="alert alert-info mb-0 py-2">' +
            '<i class="fas fa-info-circle me-2"></i>' +
            "Available advance: " +
            formatAmount(data.total_advance) +
            " FRW. Enter quantity and unit price to calculate deduction." +
            "</div>";
          supplierAdvanceInfo.style.display = "block";
          return;
        }

        if (data.fully_covered) {
          // Advance covers full amount
          supplierAdvanceInfo.innerHTML =
            '<div class="alert alert-success mb-0 py-2">' +
            '<i class="fas fa-check-circle me-2"></i>' +
            "<strong>Advance Covers Full Amount!</strong><br>" +
            "Total Advance: " +
            formatAmount(data.total_advance) +
            " FRW | Required: " +
            formatAmount(data.amount_requested) +
            " FRW" +
            "</div>";
          supplierAdvanceInfo.style.display = "block";
        } else if (data.has_advance) {
          // Partial advance - need account for remaining
          supplierAdvanceInfo.innerHTML =
            '<div class="alert alert-warning mb-0 py-2">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            "<strong>Partial Advance Available</strong><br>" +
            "Advance: " +
            formatAmount(data.total_advance) +
            " FRW | Required: " +
            formatAmount(data.amount_requested) +
            " FRW<br>" +
            '<span class="text-danger">Remaining: ' +
            formatAmount(data.remaining_after_advance) +
            " FRW (from account or payable)</span>" +
            "</div>";
          supplierAdvanceInfo.style.display = "block";
        } else {
          // No advance at all
          supplierAdvanceInfo.innerHTML =
            '<div class="alert alert-info mb-0 py-2">' +
            '<i class="fas fa-info-circle me-2"></i>' +
            "No advance available. Full amount (" +
            formatAmount(totalAmount) +
            " FRW) will be recorded as payable." +
            "</div>";
          supplierAdvanceInfo.style.display = "block";
        }
      },
    );
  }

  // Location Type Change Handler
  if (locationTypeSelect) {
    locationTypeSelect.addEventListener("change", function () {
      const locationTypeId = this.value;

      // Reset dependent selects
      resetSelect(locationSelect, "Select Location");
      resetSelect(categorySelect, "Select Category");
      resetSelect(accountSelect, "Select Account");
      categoryTypeOptionsCache = [];
      resetAllLineItems();

      // Clear supplier advance info
      if (supplierAdvanceInfo) {
        supplierAdvanceInfo.innerHTML = "";
        supplierAdvanceInfo.style.display = "none";
      }

      if (!locationTypeId) return;

      // Fetch locations
      postAjax(
        "get_locations",
        { location_type_id: locationTypeId },
        function (data) {
          populateSelect(locationSelect, data, "id", "name", "Select Location");
        },
      );

      // Fetch categories
      postAjax(
        "get_categories",
        { location_type_id: locationTypeId },
        function (data) {
          populateSelect(categorySelect, data, "id", "name", "Select Category");
        },
      );
    });
  }

  // Location Change Handler - Load accounts for selected location
  if (locationSelect) {
    locationSelect.addEventListener("change", function () {
      const locationId = this.value;

      // Reset account select
      resetSelect(accountSelect, "Select Account");

      if (!locationId) return;

      if (getSelectedPaymentMethod() !== "direct_pay") {
        return;
      }

      // Fetch accounts for this location
      postAjax("get_accounts", { location_id: locationId }, function (data) {
        populateAccountSelect(accountSelect, data, "Select Account");
        setAccountEnabledState();
      });
    });
  }

  // Supplier Search Handlers
  if (supplierSearchInput) {
    supplierSearchInput.addEventListener("input", function () {
      syncSupplierIdFromInput();
      checkSupplierAdvance();
    });

    supplierSearchInput.addEventListener("change", function () {
      syncSupplierIdFromInput();
      checkSupplierAdvance();
    });
  }

  if (paymentMethodInputs && paymentMethodInputs.length) {
    paymentMethodInputs.forEach(function (input) {
      input.addEventListener("change", updatePaymentMethodUI);
    });
  }

  // Category Change Handler
  if (categorySelect) {
    categorySelect.addEventListener("change", function () {
      const categoryId = this.value;

      categoryTypeOptionsCache = [];
      const rows = getLineRows();
      rows.forEach(function (row) {
        const categoryTypeSelect = row.querySelector(".category-type-select");
        const typeUnitSelect = row.querySelector(".type-unit-select");
        if (categoryTypeSelect) {
          resetSelect(categoryTypeSelect, "Select Category first");
          categoryTypeSelect.disabled = true;
        }
        if (typeUnitSelect) {
          resetSelect(typeUnitSelect, "Select Category Type first");
        }
        calculateLineTotal(row);
      });
      calculateGrandTotal();

      if (!categoryId) return;

      // Fetch category types
      postAjax(
        "get_category_types",
        { category_id: categoryId },
        function (data) {
          categoryTypeOptionsCache = Array.isArray(data) ? data : [];
          populateCategoryTypesForAllRows(categoryTypeOptionsCache);
        },
      );
    });
  }

  // Dynamic line item events
  if (lineItemsContainer) {
    lineItemsContainer.addEventListener("change", function (event) {
      const target = event.target;
      const row = target.closest(".receive-line-item");
      if (!row) return;

      if (target.classList.contains("category-type-select")) {
        loadTypeUnitsForRow(row, target.value);
        return;
      }

      if (
        target.classList.contains("type-unit-select") ||
        target.classList.contains("quantity-input") ||
        target.classList.contains("unit-price-input")
      ) {
        calculateLineTotal(row);
        calculateGrandTotal();
      }
    });

    lineItemsContainer.addEventListener("input", function (event) {
      const target = event.target;
      if (
        !target.classList.contains("quantity-input") &&
        !target.classList.contains("unit-price-input")
      ) {
        return;
      }

      const row = target.closest(".receive-line-item");
      if (!row) return;

      calculateLineTotal(row);
      calculateGrandTotal();
    });

    lineItemsContainer.addEventListener("click", function (event) {
      const removeBtn = event.target.closest(".remove-line-item-btn");
      if (!removeBtn) return;

      const row = removeBtn.closest(".receive-line-item");
      if (!row) return;

      row.remove();
      reindexLineRows();
      calculateGrandTotal();
    });
  }

  if (addReceiveItemBtn && lineItemsContainer) {
    addReceiveItemBtn.addEventListener("click", function () {
      const index = getLineRows().length;
      const row = createLineItemRow(index);
      lineItemsContainer.appendChild(row);
      reindexLineRows();
    });
  }

  if (receiveStockForm) {
    receiveStockForm.addEventListener("submit", function (e) {
      syncSupplierIdFromInput();

      if (!supplierIdInput || !supplierIdInput.value) {
        e.preventDefault();
        Swal.fire({
          icon: "warning",
          title: "Supplier Required",
          text: "Type supplier name and pick an existing supplier from suggestions.",
        });
        return;
      }

      const rows = getLineRows();
      for (let i = 0; i < rows.length; i += 1) {
        const row = rows[i];
        const typeUnitSelect = row.querySelector(".type-unit-select");
        const quantityInput = row.querySelector(".quantity-input");
        const unitPriceInput = row.querySelector(".unit-price-input");

        if (!typeUnitSelect || !typeUnitSelect.value) {
          e.preventDefault();
          Swal.fire({
            icon: "warning",
            title: "Unit Required",
            text: "Select unit for item #" + (i + 1) + ".",
          });
          return;
        }

        const quantity = parseFloat(quantityInput?.value || "0") || 0;
        if (quantity <= 0) {
          e.preventDefault();
          Swal.fire({
            icon: "warning",
            title: "Invalid Quantity",
            text: "Quantity must be greater than zero for item #" + (i + 1) + ".",
          });
          return;
        }

        const unitPrice = parseFloat(unitPriceInput?.value || "0") || 0;
        if (unitPrice <= 0) {
          e.preventDefault();
          Swal.fire({
            icon: "warning",
            title: "Invalid Price",
            text: "Price/kg must be greater than zero for item #" + (i + 1) + ".",
          });
          return;
        }
      }

      const method = getSelectedPaymentMethod();

      if (method === "direct_pay") {
        if (!locationSelect || !locationSelect.value) {
          e.preventDefault();
          Swal.fire({
            icon: "warning",
            title: "Location Required",
            text: "Select location before choosing direct pay account.",
          });
          return;
        }

        if (!accountSelect || !accountSelect.value) {
          e.preventDefault();
          Swal.fire({
            icon: "warning",
            title: "Account Required",
            text: "Direct pay requires selecting a payment account.",
          });
          return;
        }
      }
    });
  }

  reindexLineRows();
  calculateGrandTotal();
  syncSupplierIdFromInput();
  updatePaymentMethodUI();

  // Form confirmation dialogs
  document.querySelectorAll(".approve-form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      Swal.fire({
        title: "Approve Stock Receive?",
        text: "This will add the stock to the summary. This action cannot be undone.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Approve",
      }).then(function (result) {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });

  document.querySelectorAll(".cancel-form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      Swal.fire({
        title: "Cancel Stock Receive?",
        text: "This will cancel the pending receive.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ffc107",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Cancel It",
      }).then(function (result) {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });

  document.querySelectorAll(".delete-form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      Swal.fire({
        title: "Delete Stock Receive?",
        text: "This will permanently delete the receive record.",
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Delete",
      }).then(function (result) {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
})();
