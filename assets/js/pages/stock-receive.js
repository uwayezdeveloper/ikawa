/**
 * Stock Receive Page JavaScript
 * Handles cascade dropdowns and form interactions
 */
(function () {
  "use strict";

  // Elements
  const locationTypeSelect = document.getElementById("locationTypeSelect");
  const locationSelect = document.getElementById("locationSelect");
  const categorySelect = document.getElementById("categorySelect");
  const categoryTypeSelect = document.getElementById("categoryTypeSelect");
  const typeUnitSelect = document.getElementById("typeUnitSelect");
  const supplierSelect = document.getElementById("supplierSelect");
  const accountSelect = document.getElementById("accountSelect");
  const supplierAdvanceInfo = document.getElementById("supplierAdvanceInfo");
  const quantityInput = document.getElementById("quantityInput");
  const unitPriceInput = document.getElementById("unitPriceInput");
  const totalPriceDisplay = document.getElementById("totalPriceDisplay");
  const receiveStockForm = document.getElementById("receiveStockForm");

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
    select.disabled = true; // Start disabled, enable based on advance check
  }

  /**
   * Calculate total price and kg conversion
   * unit_price is ALWAYS price per kg
   * total = unit_price × quantity_in_kg
   */
  function calculateTotal() {
    if (!quantityInput || !unitPriceInput || !totalPriceDisplay) return;
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0; // Price per kg

    // Calculate kg conversion preview
    const kgPreview = document.getElementById("kgConversionPreview");
    const qtyInKgPreview = document.getElementById("qtyInKgPreview");
    const pricePerKgPreview = document.getElementById("pricePerKgPreview");

    let total = 0;
    let qtyInKg = 0;

    if (typeUnitSelect) {
      const selectedOption =
        typeUnitSelect.options[typeUnitSelect.selectedIndex];
      const conversionFactor =
        parseFloat(selectedOption?.dataset?.conversionFactor) || 0;

      if (conversionFactor > 0 && quantity > 0) {
        // conversion_factor is in grams, kg = 1000 grams
        qtyInKg = quantity * (conversionFactor / 1000);
        // total = unit_price (per kg) × quantity_in_kg
        total = unitPrice * qtyInKg;

        if (kgPreview && qtyInKgPreview && pricePerKgPreview) {
          qtyInKgPreview.textContent = qtyInKg.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });
          // price_per_kg = unit_price (since unit_price IS the price per kg)
          pricePerKgPreview.textContent = unitPrice.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });
          kgPreview.style.display = "block";
        }
      } else {
        // Fallback: simple calculation
        total = quantity * unitPrice;
        if (kgPreview) kgPreview.style.display = "none";
      }
    } else {
      total = quantity * unitPrice;
    }

    totalPriceDisplay.value = total.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    // Check for supplier advance after calculating total
    checkSupplierAdvance();
  }

  /**
   * Check if supplier has available advance for the total amount
   */
  function checkSupplierAdvance() {
    if (!supplierSelect || !supplierAdvanceInfo) return;

    const supplierId = supplierSelect.value;
    const quantity = parseFloat(quantityInput?.value) || 0;
    const unitPrice = parseFloat(unitPriceInput?.value) || 0;
    const totalAmount = quantity * unitPrice;

    // Clear if no supplier selected or no amount
    if (!supplierId || totalAmount <= 0) {
      supplierAdvanceInfo.innerHTML = "";
      supplierAdvanceInfo.style.display = "none";
      // Keep account disabled when no supplier/amount
      if (accountSelect) {
        accountSelect.disabled = true;
      }
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
          // Disable account select since advance covers everything
          if (accountSelect) {
            accountSelect.disabled = true;
            accountSelect.value = "";
          }
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
          // Enable account select for remaining amount
          if (accountSelect && locationSelect && locationSelect.value) {
            accountSelect.disabled = false;
          }
        } else {
          // No advance at all
          supplierAdvanceInfo.innerHTML =
            '<div class="alert alert-info mb-0 py-2">' +
            '<i class="fas fa-info-circle me-2"></i>' +
            "No advance available. Full payment (" +
            formatAmount(totalAmount) +
            " FRW) from station account or recorded as payable." +
            "</div>";
          supplierAdvanceInfo.style.display = "block";
          // Enable account select
          if (accountSelect && locationSelect && locationSelect.value) {
            accountSelect.disabled = false;
          }
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
      resetSelect(categoryTypeSelect, "Select Category Type");
      resetSelect(typeUnitSelect, "Select Unit");
      resetSelect(accountSelect, "Select Account");

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

      // Fetch accounts for this location
      postAjax("get_accounts", { location_id: locationId }, function (data) {
        populateAccountSelect(accountSelect, data, "Select Account");
        // Re-check supplier advance to update account state
        checkSupplierAdvance();
      });
    });
  }

  // Supplier Change Handler - Check for available advance
  if (supplierSelect) {
    supplierSelect.addEventListener("change", checkSupplierAdvance);
  }

  // Category Change Handler
  if (categorySelect) {
    categorySelect.addEventListener("change", function () {
      const categoryId = this.value;

      // Reset dependent selects
      resetSelect(categoryTypeSelect, "Select Category Type");
      resetSelect(typeUnitSelect, "Select Unit");

      if (!categoryId) return;

      // Fetch category types
      postAjax(
        "get_category_types",
        { category_id: categoryId },
        function (data) {
          populateSelect(
            categoryTypeSelect,
            data,
            "id",
            "name",
            "Select Category Type",
          );
        },
      );
    });
  }

  // Category Type Change Handler
  if (categoryTypeSelect) {
    categoryTypeSelect.addEventListener("change", function () {
      const categoryTypeId = this.value;

      // Reset type-unit select
      resetSelect(typeUnitSelect, "Select Unit");

      if (!categoryTypeId) return;

      // Fetch type-unit assignments (returns category_type_units.id as value)
      postAjax(
        "get_type_units",
        { category_type_id: categoryTypeId },
        function (data) {
          populateSelect(
            typeUnitSelect,
            data,
            "id",
            "unit_name",
            "Select Unit",
          );
        },
      );
    });
  }

  // Calculate total on input change
  if (quantityInput) {
    quantityInput.addEventListener("input", calculateTotal);
  }
  if (unitPriceInput) {
    unitPriceInput.addEventListener("input", calculateTotal);
  }
  // Recalculate when unit changes (for kg conversion)
  if (typeUnitSelect) {
    typeUnitSelect.addEventListener("change", calculateTotal);
  }

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
