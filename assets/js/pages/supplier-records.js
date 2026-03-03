(function () {
  "use strict";

  const supplierSelect = document.getElementById("supplierSelect");
  if (supplierSelect) {
    supplierSelect.addEventListener("change", function () {
      if (this.value) {
        window.location.href = window.APP_URL + "/suppliers/records?supplier_id=" + this.value;
      }
    });
  }

  const typeFilter = document.getElementById("typeFilter");
  const dateFrom = document.getElementById("dateFrom");
  const dateTo = document.getElementById("dateTo");

  function applyFilters() {
    const rows = document.querySelectorAll("#recordsTable .record-row");
    if (!rows.length) return;

    const selectedType = (typeFilter?.value || "").trim();
    const fromDate = (dateFrom?.value || "").trim();
    const toDate = (dateTo?.value || "").trim();

    rows.forEach(function (row) {
      const rowType = row.getAttribute("data-type") || "";
      const rowDate = row.getAttribute("data-date") || "";

      let visible = true;

      if (selectedType && rowType !== selectedType) {
        visible = false;
      }

      if (fromDate && rowDate < fromDate) {
        visible = false;
      }

      if (toDate && rowDate > toDate) {
        visible = false;
      }

      row.style.display = visible ? "" : "none";
    });
  }

  if (typeFilter) typeFilter.addEventListener("change", applyFilters);
  if (dateFrom) dateFrom.addEventListener("change", applyFilters);
  if (dateTo) dateTo.addEventListener("change", applyFilters);

  const downloadBtn = document.getElementById("downloadPdfBtn");
  if (!downloadBtn) return;

  downloadBtn.addEventListener("click", function () {
    if (!window.jspdf || !window.jspdf.jsPDF) {
      alert("jsPDF failed to load. Please refresh and try again.");
      return;
    }

    const payload = window.supplierRecordsPayload || {};
    const supplier = payload.supplier || {};
    const summary = payload.summary || {};
    const records = Array.isArray(payload.records) ? payload.records : [];
    const productSummary = Array.isArray(payload.productSummary)
      ? payload.productSummary
      : [];

    const jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF({ unit: "mm", format: "a4" });

    const pageHeight = doc.internal.pageSize.getHeight();
    const marginLeft = 12;
    const lineHeight = 6;
    let y = 12;

    function money(v) {
      const n = Number(v || 0);
      return n.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    function addLine(text, size, isBold) {
      doc.setFont("helvetica", isBold ? "bold" : "normal");
      doc.setFontSize(size || 10);

      const lines = doc.splitTextToSize(String(text || ""), 185);
      lines.forEach(function (line) {
        if (y > pageHeight - 12) {
          doc.addPage();
          y = 12;
        }
        doc.text(line, marginLeft, y);
        y += lineHeight;
      });
    }

    addLine("SUPPLIER RECORDS", 14, true);
    addLine("Generated: " + new Date().toLocaleString(), 10, false);
    y += 2;

    addLine("Supplier", 12, true);
    addLine("Name: " + (supplier.name || "N/A"), 10, false);
    addLine("Phone: " + (supplier.phone || "N/A"), 10, false);
    addLine("Email: " + (supplier.email || "N/A"), 10, false);
    addLine("Address: " + (supplier.address || "N/A"), 10, false);
    y += 2;

    const advances = payload.summary?.advances || {};
    const stock = payload.summary?.stock || {};
    const payables = payload.summary?.payables || {};

    addLine("Summary", 12, true);
    addLine("Total Advances: " + money(advances.total_advance_amount), 10, false);
    addLine("Total Stock Value: " + money(stock.total_stock_value), 10, false);
    addLine("Pending Balance: " + money(payables.pending_balance), 10, false);
    y += 2;

    addLine("Products Supplied", 12, true);
    if (!productSummary.length) {
      addLine("No product records.", 10, false);
    } else {
      productSummary.forEach(function (item, index) {
        addLine(
          (index + 1) +
            ". " +
            (item.receive_date || "") +
            " | " +
            (item.product_name || "") +
            " | " +
            (item.type_name || "") +
            " | Qty: " +
            money(item.quantity) +
            " " +
            (item.unit_symbol || "") +
            " | Total: " +
            money(item.total_price),
          9,
          false
        );
      });
    }
    y += 2;

    addLine("Transaction History", 12, true);
    if (!records.length) {
      addLine("No transactions.", 10, false);
    } else {
      records.forEach(function (row, index) {
        addLine(
          (index + 1) +
            ". " +
            (row.date || "") +
            " | " +
            String(row.type || "").replace(/_/g, " ") +
            " | Ref: " +
            (row.reference || "-") +
            " | Debit: " +
            money(row.debit) +
            " | Credit: " +
            money(row.credit),
          9,
          false
        );
      });
    }

    const fileName =
      "Supplier_Records_" +
      String(supplier.name || "supplier")
        .replace(/[^a-zA-Z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "") +
      "_" +
      new Date().toISOString().slice(0, 10) +
      ".pdf";

    doc.save(fileName);
  });
})();
