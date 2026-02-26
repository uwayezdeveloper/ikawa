/**
 * Stock Summary Page JavaScript
 * Handles filtering by location type and location
 */
(function () {
  "use strict";

  const filterLocationType = document.getElementById("filterLocationType");
  const filterLocation = document.getElementById("filterLocation");
  const resetFiltersBtn = document.getElementById("resetFilters");
  const summaryTableBody = document.getElementById("summaryTableBody");

  const APP_URL = window.APP_URL || "";

  /**
   * AJAX helper
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
   * Reset select
   */
  function resetSelect(select, placeholder) {
    if (!select) return;
    select.innerHTML = '<option value="">' + placeholder + "</option>";
    select.disabled = true;
  }

  /**
   * Populate select
   */
  function populateSelect(select, options, valueProp, labelProp, placeholder) {
    if (!select) return;
    select.innerHTML = '<option value="">' + placeholder + "</option>";
    options.forEach(function (opt) {
      const option = document.createElement("option");
      option.value = opt[valueProp];
      option.textContent = opt[labelProp];
      select.appendChild(option);
    });
    select.disabled = false;
  }

  /**
   * Render summary table rows
   */
  function renderSummaryTable(data) {
    if (!summaryTableBody) return;

    if (!data || data.length === 0) {
      summaryTableBody.innerHTML =
        '<tr><td colspan="8" class="text-center py-4">' +
        '<i class="ti ti-database-off fs-1 text-muted"></i>' +
        '<p class="text-muted mb-0">No stock data found for the selected filter</p></td></tr>';
      return;
    }

    let html = "";
    data.forEach(function (item, index) {
      const lastReceiveDate = item.last_receive_date
        ? new Date(item.last_receive_date).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
          })
        : "-";

      html +=
        "<tr>" +
        '<td class="ps-3">' +
        (index + 1) +
        "</td>" +
        '<td><span class="fw-medium">' +
        escapeHtml(item.location_name) +
        "</span></td>" +
        '<td><span class="badge bg-info-subtle text-info">' +
        escapeHtml(item.category_name) +
        "</span></td>" +
        "<td>" +
        escapeHtml(item.type_name) +
        "</td>" +
        '<td><span class="fw-semibold">' +
        parseFloat(item.total_quantity).toFixed(2) +
        "</span> " +
        '<small class="text-muted">' +
        escapeHtml(item.unit_symbol || "") +
        "</small></td>" +
        '<td><span class="fw-semibold text-success">RWF ' +
        parseInt(item.total_value).toLocaleString() +
        "</span></td>" +
        '<td><span class="text-muted">RWF ' +
        parseFloat(item.avg_unit_price).toFixed(2) +
        "</span></td>" +
        "<td>" +
        lastReceiveDate +
        "</td>" +
        "</tr>";
    });

    summaryTableBody.innerHTML = html;
  }

  /**
   * Escape HTML
   */
  function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Location Type filter change
  if (filterLocationType) {
    filterLocationType.addEventListener("change", function () {
      const locationTypeId = this.value;

      resetSelect(filterLocation, "All Locations");

      if (!locationTypeId) {
        // Reload page to show all data
        window.location.reload();
        return;
      }

      // Fetch locations for dropdown
      postAjax(
        "get_locations",
        { location_type_id: locationTypeId },
        function (data) {
          populateSelect(filterLocation, data, "id", "name", "All Locations");
        },
      );

      // Fetch summary by location type
      postAjax(
        "get_summary_by_location_type",
        { location_type_id: locationTypeId },
        function (data) {
          renderSummaryTable(data);
        },
      );
    });
  }

  // Location filter change
  if (filterLocation) {
    filterLocation.addEventListener("change", function () {
      const locationId = this.value;

      if (!locationId) {
        // Get by location type instead
        const locationTypeId = filterLocationType.value;
        if (locationTypeId) {
          postAjax(
            "get_summary_by_location_type",
            { location_type_id: locationTypeId },
            function (data) {
              renderSummaryTable(data);
            },
          );
        }
        return;
      }

      // Fetch summary by specific location
      postAjax(
        "get_summary_by_location",
        { location_id: locationId },
        function (data) {
          renderSummaryTable(data);
        },
      );
    });
  }

  // Reset filters
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", function () {
      window.location.reload();
    });
  }
})();
