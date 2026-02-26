/**
 * Warehouse Processing Page JavaScript
 * New Flow: Warehouse → Product Type → Unit → Suppliers (multi-select with quantities) → Processing Step
 */

document.addEventListener("DOMContentLoaded", function () {
  // Ensure APP_URL is defined
  const APP_URL = window.APP_URL || "";
  
  // Debug logging
  console.log("=== Warehouse Processing JS Loaded ===");
  console.log("window.APP_URL:", window.APP_URL);
  console.log("APP_URL (local):", APP_URL);
  console.log("=======================================");
  
  const warehouseSelect = document.getElementById("warehouseSelect");
  const categoryTypeSelect = document.getElementById("categoryTypeSelect");
  const unitSelect = document.getElementById("unitSelect");
  const suppliersContainer = document.getElementById("suppliersContainer");
  const processingStepSelect = document.getElementById("processingStepSelect");
  const stepInfo = document.getElementById("stepInfo");
  const submitBtn = document.getElementById("submitBtn");

  // Track state
  let currentSuppliers = [];
  let currentConversionFactor = 1;
  let currentUnitSymbol = "units";
  let maxStepOrder = 0;

  // Reset functions
  function resetFromWarehouse() {
    if (categoryTypeSelect) {
      categoryTypeSelect.innerHTML =
        '<option value="">Select Warehouse first</option>';
      categoryTypeSelect.disabled = true;
    }
    resetFromCategoryType();
  }

  function resetFromCategoryType() {
    if (unitSelect) {
      unitSelect.innerHTML =
        '<option value="">Select Product Type first</option>';
      unitSelect.disabled = true;
    }
    resetFromUnit();
  }

  function resetFromUnit() {
    if (suppliersContainer) {
      suppliersContainer.innerHTML = `
        <div class="alert alert-info small py-2">
          <i class="ti ti-info-circle me-1"></i>Select warehouse, product type and unit to see available suppliers
        </div>`;
    }
    resetFromSuppliers();
  }

  function resetFromSuppliers() {
    currentSuppliers = [];
    maxStepOrder = 0;
    if (processingStepSelect) {
      processingStepSelect.innerHTML =
        '<option value="">Select suppliers first</option>';
      processingStepSelect.disabled = true;
    }
    if (stepInfo) stepInfo.textContent = "";
    if (submitBtn) submitBtn.disabled = true;
  }

  // Warehouse change -> Load product types with stock
  if (warehouseSelect) {
    warehouseSelect.addEventListener("change", function () {
      const locationId = this.value;

      if (categoryTypeSelect) {
        categoryTypeSelect.innerHTML = '<option value="">Loading...</option>';
        categoryTypeSelect.disabled = true;
      }
      resetFromCategoryType();

      if (!locationId) {
        if (categoryTypeSelect)
          categoryTypeSelect.innerHTML =
            '<option value="">Select Warehouse first</option>';
        return;
      }

      const fetchUrl = APP_URL + "/warehouse/processing/action";
      console.log("Fetching product types from:", fetchUrl);
      console.log("With location_id:", locationId);

      fetch(fetchUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: "action=get_product_types&location_id=" + locationId,
      })
        .then((response) => {
          console.log("Response status:", response.status);
          return response.json();
        })
        .then((data) => {
          console.log("Product types response:", data);
          if (data.success && categoryTypeSelect) {
            categoryTypeSelect.innerHTML =
              '<option value="">Select Product Type</option>';
            data.data.forEach((type) => {
              categoryTypeSelect.innerHTML +=
                '<option value="' +
                type.id +
                '">' +
                type.category_name +
                " - " +
                type.name +
                "</option>";
            });
            categoryTypeSelect.disabled = false;
          } else if (categoryTypeSelect) {
            const errorMsg = data.message || 'No products with stock';
            categoryTypeSelect.innerHTML =
              '<option value="">' + errorMsg + '</option>';
            console.error("Error from server:", data);
            if (data.trace) {
              console.error("Stack trace:", data.trace);
            }
          }
        })
        .catch((error) => {
          console.error("Error loading product types - Full error:", error);
          if (categoryTypeSelect)
            categoryTypeSelect.innerHTML =
              '<option value="">Error loading products - Check console</option>';
        });
    });
  }

  // Product Type change -> Load units
  if (categoryTypeSelect) {
    categoryTypeSelect.addEventListener("change", function () {
      const categoryTypeId = this.value;

      if (unitSelect) {
        unitSelect.innerHTML = '<option value="">Loading...</option>';
        unitSelect.disabled = true;
      }
      resetFromUnit();

      if (!categoryTypeId) {
        if (unitSelect)
          unitSelect.innerHTML =
            '<option value="">Select Product Type first</option>';
        return;
      }

      fetch(APP_URL + "/warehouse/processing/action", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: "action=get_type_units&category_type_id=" + categoryTypeId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && unitSelect) {
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            data.data.forEach((unit) => {
              unitSelect.innerHTML +=
                '<option value="' +
                unit.measurement_unit_id +
                '" data-symbol="' +
                unit.symbol +
                '" data-conversion="' +
                unit.conversion_factor +
                '">' +
                unit.name +
                " (" +
                unit.symbol +
                ")</option>";
            });
            unitSelect.disabled = false;
          } else if (unitSelect) {
            unitSelect.innerHTML =
              '<option value="">No units available</option>';
          }
        })
        .catch((error) => {
          console.error("Error loading units:", error);
          if (unitSelect)
            unitSelect.innerHTML =
              '<option value="">Error loading units</option>';
        });
    });
  }

  // Unit change -> Load suppliers with this product
  if (unitSelect) {
    unitSelect.addEventListener("change", function () {
      const selectedOption = this.options[this.selectedIndex];
      currentUnitSymbol = selectedOption?.dataset.symbol || "units";
      currentConversionFactor =
        parseFloat(selectedOption?.dataset.conversion) || 1;

      const locationId = warehouseSelect ? warehouseSelect.value : "";
      const categoryTypeId = categoryTypeSelect ? categoryTypeSelect.value : "";
      const unitId = this.value;

      resetFromSuppliers();

      if (!unitId) {
        if (suppliersContainer) {
          suppliersContainer.innerHTML = `
            <div class="alert alert-info small py-2">
              <i class="ti ti-info-circle me-1"></i>Select unit to see available suppliers
            </div>`;
        }
        return;
      }

      if (suppliersContainer) {
        suppliersContainer.innerHTML = `
          <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="small text-muted mt-2">Loading suppliers...</p>
          </div>`;
      }

      fetch(APP_URL + "/warehouse/processing/action", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body:
          "action=get_suppliers_for_product&location_id=" +
          locationId +
          "&category_type_id=" +
          categoryTypeId +
          "&conversion_factor=" +
          currentConversionFactor,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && suppliersContainer) {
            currentSuppliers = data.data;

            if (data.data.length === 0) {
              suppliersContainer.innerHTML = `
                <div class="alert alert-warning small py-2">
                  <i class="ti ti-alert-circle me-1"></i>No suppliers with this product at selected warehouse
                </div>`;
              return;
            }

            // Build suppliers table
            let html = `
              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Supplier</th>
                      <th>Current Step</th>
                      <th>Available</th>
                      <th style="width: 100px;">Quantity</th>
                    </tr>
                  </thead>
                  <tbody>`;

            data.data.forEach((supplier) => {
              const stepName = supplier.step_name || "Not Assigned";
              const stepBadge = supplier.step_name
                ? '<span class="badge bg-info">' + stepName + "</span>"
                : '<span class="badge bg-secondary">Not Assigned</span>';

              html += `
                <tr>
                  <td>${supplier.supplier_name}</td>
                  <td>${stepBadge}</td>
                  <td>
                    <span class="text-success fw-semibold">
                      ${supplier.available_quantity.toFixed(2)} ${currentUnitSymbol}
                    </span>
                  </td>
                  <td>
                    <input type="number" 
                           class="form-control form-control-sm supplier-qty-input" 
                           name="supplier_qty[${supplier.supplier_id}][${supplier.processing_step_id || 0}]"
                           min="0" 
                           max="${supplier.available_quantity.toFixed(2)}"
                           step="0.01"
                           placeholder="0"
                           data-supplier-id="${supplier.supplier_id}"
                           data-step-id="${supplier.processing_step_id || 0}"
                           data-step-order="${supplier.step_order || 0}"
                           data-available="${supplier.available_quantity}">
                  </td>
                </tr>`;
            });

            html += `
                  </tbody>
                </table>
              </div>
              <small class="text-muted d-block mt-2">
                <i class="ti ti-info-circle me-1"></i>Enter quantities from each supplier to include in processing
              </small>`;

            suppliersContainer.innerHTML = html;

            // Add event listeners to quantity inputs
            document
              .querySelectorAll(".supplier-qty-input")
              .forEach((input) => {
                input.addEventListener("input", onSupplierQuantityChange);
              });
          }
        })
        .catch((error) => {
          console.error("Error loading suppliers:", error);
          if (suppliersContainer) {
            suppliersContainer.innerHTML = `
              <div class="alert alert-danger small py-2">
                <i class="ti ti-alert-circle me-1"></i>Error loading suppliers
              </div>`;
          }
        });
    });
  }

  // When quantity inputs change, load available processing steps
  function onSupplierQuantityChange() {
    const inputs = document.querySelectorAll(".supplier-qty-input");
    let hasQuantity = false;
    maxStepOrder = 0;
    let totalQty = 0;

    inputs.forEach((input) => {
      const qty = parseFloat(input.value) || 0;
      const available = parseFloat(input.dataset.available) || 0;
      const stepOrder = parseInt(input.dataset.stepOrder) || 0;

      // Validate max
      if (qty > available) {
        input.value = available.toFixed(2);
      }

      if (qty > 0) {
        hasQuantity = true;
        totalQty += qty;
        // Track the maximum step order among selected suppliers
        if (stepOrder > maxStepOrder) {
          maxStepOrder = stepOrder;
        }
      }
    });

    if (!hasQuantity) {
      if (processingStepSelect) {
        processingStepSelect.innerHTML =
          '<option value="">Enter quantities first</option>';
        processingStepSelect.disabled = true;
      }
      if (stepInfo)
        stepInfo.textContent = "Enter at least one quantity to continue";
      if (submitBtn) submitBtn.disabled = true;
      return;
    }

    if (stepInfo) {
      stepInfo.textContent =
        "Total: " + totalQty.toFixed(2) + " " + currentUnitSymbol;
    }

    // Load next available processing steps
    loadNextProcessingSteps(maxStepOrder);
  }

  // Load processing steps that are after the current max step order
  function loadNextProcessingSteps(minStepOrder) {
    if (!processingStepSelect) return;

    processingStepSelect.innerHTML = '<option value="">Loading...</option>';
    processingStepSelect.disabled = true;

    fetch(APP_URL + "/warehouse/processing/action", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: "action=get_next_processing_steps&min_step_order=" + minStepOrder,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && processingStepSelect) {
          if (data.data.length === 0) {
            processingStepSelect.innerHTML =
              '<option value="">No next steps available</option>';
            if (stepInfo) {
              stepInfo.textContent +=
                " - Already at final step, no further processing possible";
            }
            if (submitBtn) submitBtn.disabled = true;
            return;
          }

          processingStepSelect.innerHTML =
            '<option value="">Select Next Step</option>';
          data.data.forEach((step) => {
            processingStepSelect.innerHTML +=
              '<option value="' + step.id + '">' + step.name + "</option>";
          });
          processingStepSelect.disabled = false;
        }
      })
      .catch((error) => {
        console.error("Error loading processing steps:", error);
        if (processingStepSelect) {
          processingStepSelect.innerHTML =
            '<option value="">Error loading steps</option>';
        }
      });
  }

  // Processing step change -> Enable submit
  if (processingStepSelect) {
    processingStepSelect.addEventListener("change", function () {
      if (submitBtn) {
        submitBtn.disabled = !this.value;
      }
    });
  }

  // Form validation before submit
  const processingForm = document.getElementById("processingForm");
  if (processingForm) {
    processingForm.addEventListener("submit", function (e) {
      const inputs = document.querySelectorAll(".supplier-qty-input");
      let hasQuantity = false;

      inputs.forEach((input) => {
        const qty = parseFloat(input.value) || 0;
        const available = parseFloat(input.dataset.available) || 0;

        if (qty > 0) {
          hasQuantity = true;
          if (qty > available) {
            e.preventDefault();
            Swal.fire({
              icon: "error",
              title: "Insufficient Stock",
              text: "Quantity exceeds available stock for one or more suppliers",
            });
            return false;
          }
        }
      });

      if (!hasQuantity) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "No Quantity",
          text: "Please enter at least one quantity to process",
        });
        return false;
      }

      if (!processingStepSelect || !processingStepSelect.value) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "No Processing Step",
          text: "Please select a processing step",
        });
        return false;
      }
    });
  }

  // Initialize DataTable if available
  if (
    typeof jQuery !== "undefined" &&
    typeof jQuery.fn.DataTable !== "undefined"
  ) {
    jQuery("#processingTable").DataTable({
      order: [[0, "desc"]],
      pageLength: 10,
    });
  }
});
