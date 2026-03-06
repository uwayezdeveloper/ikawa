(function () {
  "use strict";

  const btn = document.getElementById("downloadDetailedExpensePdfBtn");
  if (!btn) return;

  function textValue(v) {
    return String(v || "").trim();
  }

  function numberValue(v) {
    return Number(v || 0);
  }

  function money(v) {
    return numberValue(v).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " FRW";
  }

  btn.addEventListener("click", function () {
    if (!window.jspdf || !window.jspdf.jsPDF) {
      alert("jsPDF failed to load. Please refresh and try again.");
      return;
    }

    const payload = window.detailedExpenseReportPayload || {};
    const rows = payload.rows || [];
    const location = payload.location || {};

    const jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4" });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 12;
    const tableWidth = pageWidth - margin * 2;
    const col1Width = 98;
    const col2Width = 44;
    const col3Width = tableWidth - col1Width - col2Width;
    let y = 14;

    function ensureSpace(heightNeeded) {
      if (y + heightNeeded <= pageHeight - margin) return;
      doc.addPage();
      y = margin;
    }

    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text("DETAILED EXPENSE REPORT", pageWidth / 2, y, { align: "center" });
    y += 8;

    doc.setFont("helvetica", "normal");
    doc.setFontSize(10);
    doc.text("Generated: " + textValue(payload.generatedAt || new Date().toLocaleString()), margin, y);
    y += 5;
    doc.text("Location: " + textValue(location.name || "N/A"), margin, y);
    y += 7;

    doc.setDrawColor(90, 90, 90);
    doc.setLineWidth(0.25);
    doc.setFillColor(235, 235, 235);
    doc.rect(margin, y, col1Width, 8, "FD");
    doc.rect(margin + col1Width, y, col2Width, 8, "FD");
    doc.rect(margin + col1Width + col2Width, y, col3Width, 8, "FD");
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    doc.text("Details", margin + 2, y + 5.2);
    doc.text("amount", margin + col1Width + col2Width - 2, y + 5.2, { align: "right" });
    doc.text("/kg", margin + col1Width + col2Width + col3Width - 2, y + 5.2, { align: "right" });
    y += 8;

    doc.setFont("helvetica", "normal");
    doc.setFontSize(9);

    rows.forEach(function (row) {
      const label = textValue(row.details || "");
      const amountText = numberValue(row.amount || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const perKg = row.per_kg;
      const perKgText = (perKg === null || typeof perKg === "undefined")
        ? "-"
        : numberValue(perKg).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const wrapped = doc.splitTextToSize(label, col1Width - 4);
      const rowHeight = Math.max(7, wrapped.length * 4 + 2);

      ensureSpace(rowHeight);

      doc.rect(margin, y, col1Width, rowHeight);
      doc.rect(margin + col1Width, y, col2Width, rowHeight);
      doc.rect(margin + col1Width + col2Width, y, col3Width, rowHeight);
      doc.text(wrapped, margin + 2, y + 4.7);
      doc.text(amountText, margin + col1Width + col2Width - 2, y + 4.7, { align: "right" });
      doc.text(perKgText, margin + col1Width + col2Width + col3Width - 2, y + 4.7, { align: "right" });
      y += rowHeight;
    });

    ensureSpace(8);
    doc.setFont("helvetica", "bold");
    doc.setFillColor(245, 245, 245);
    doc.rect(margin, y, col1Width, 8, "FD");
    doc.rect(margin + col1Width, y, col2Width, 8, "FD");
    doc.rect(margin + col1Width + col2Width, y, col3Width, 8, "FD");
    doc.text("Total", margin + 2, y + 5.2);
    doc.text(numberValue(payload.totalAmount || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }), margin + col1Width + col2Width - 2, y + 5.2, { align: "right" });
    doc.text(numberValue(payload.totalPerKg || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }), margin + col1Width + col2Width + col3Width - 2, y + 5.2, { align: "right" });

    doc.save("Detailed_Expense_Report_" + textValue(location.name || "location").replace(/[^a-zA-Z0-9]+/g, "_") + ".pdf");
  });
})();
