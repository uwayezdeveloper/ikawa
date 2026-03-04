(function () {
  "use strict";

  const btn = document.getElementById("downloadJournalPdfBtn");
  if (!btn) return;

  btn.addEventListener("click", function () {
    if (!window.jspdf || !window.jspdf.jsPDF) {
      alert("jsPDF failed to load. Please refresh and try again.");
      return;
    }

    const payload = window.locationJournalPayload || {};
    const rows = Array.isArray(payload.rows) ? payload.rows : [];
    const totals = payload.totals || { debit: 0, credit: 0, balance: 0 };
    const location = payload.location || {};
    const companyName = "GIHANGA COFFEE CO LTD";
    const locationCode = "GC" + String(location.id || "N/A");

    const jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const marginLeft = 8;
    const marginRight = 8;
    const tableWidth = pageWidth - marginLeft - marginRight;
    const tableRight = marginLeft + tableWidth;
    const lineHeight = 5.2;
    let y = 12;

    const headers = ["date & time", "description", "bank/cash", "debit", "credit", "balance"];
    const widthRatios = [0.14, 0.24, 0.26, 0.12, 0.12, 0.12];
    const colWidths = widthRatios.map(function (ratio) {
      return Math.floor(tableWidth * ratio * 100) / 100;
    });
    const totalCols = colWidths.reduce(function (sum, width) {
      return sum + width;
    }, 0);
    colWidths[colWidths.length - 1] = Math.floor((colWidths[colWidths.length - 1] + (tableWidth - totalCols)) * 100) / 100;

    function money(v) {
      const n = Number(v || 0);
      return n.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function writeLine(text, size, bold) {
      doc.setFont("helvetica", bold ? "bold" : "normal");
      doc.setFontSize(size || 10);
      doc.text(String(text || ""), marginLeft, y);
      y += lineHeight;
    }

    function formatDateTime(d) {
      if (!d) return "";
      const dt = new Date(d);
      if (Number.isNaN(dt.getTime())) return String(d);
      const day = String(dt.getDate()).padStart(2, "0");
      const month = String(dt.getMonth() + 1).padStart(2, "0");
      const year = dt.getFullYear();
      const hours = String(dt.getHours()).padStart(2, "0");
      const minutes = String(dt.getMinutes()).padStart(2, "0");
      const seconds = String(dt.getSeconds()).padStart(2, "0");
      return day + "/" + month + "/" + year + " " + hours + ":" + minutes + ":" + seconds;
    }

    function drawHeaderRow() {
      doc.setFont("helvetica", "bold");
      doc.setFontSize(9);
      doc.setDrawColor(80, 80, 80);
      let x = marginLeft;
      headers.forEach(function (h, idx) {
        doc.rect(x, y - 4.5, colWidths[idx], 8, "S");
        doc.text(String(h).toUpperCase(), x + 1.5, y + 0.5);
        x += colWidths[idx];
      });
      y += 8;
    }

    function drawRow(values, bold) {
      const wrapped = values.map(function (v, idx) {
        const value = String(v || "");
        if (idx >= 3) return [value];
        return doc.splitTextToSize(value, colWidths[idx] - 3);
      });

      const maxLines = wrapped.reduce(function (m, lines) {
        return Math.max(m, Array.isArray(lines) ? lines.length : 1);
      }, 1);

      const rowHeight = Math.max(8, maxLines * 4.4 + 2.2);

      if (y > pageHeight - 10 - rowHeight) {
        doc.addPage();
        y = 12;
        drawHeaderRow();
      }

      doc.setFont("helvetica", bold ? "bold" : "normal");
      doc.setFontSize(8.5);
      doc.setDrawColor(120, 120, 120);

      let x = marginLeft;
      values.forEach(function (v, idx) {
        const alignRight = idx >= 3;
        const value = String(v || "");
        doc.rect(x, y - 4.5, colWidths[idx], rowHeight, "S");
        if (alignRight) {
          const tw = doc.getTextWidth(value);
          doc.text(value, x + colWidths[idx] - 1.5 - tw, y + 0.5);
        } else {
          const lines = wrapped[idx] || [""];
          doc.text(lines, x + 1.5, y + 0.5);
        }
        x += colWidths[idx];
      });

      y += rowHeight;
    }

    doc.setFont("helvetica", "bold");
    doc.setFontSize(13);
    doc.text(companyName, pageWidth / 2, y, { align: "center" });
    y += lineHeight;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(12);
    doc.text("LOCATION JOURNAL", pageWidth / 2, y, { align: "center" });
    y += lineHeight;
    writeLine("Generated: " + new Date().toLocaleString(), 10, false);
    writeLine("Location: " + (location.name || "N/A") + " (" + locationCode + ")", 10, true);
    if (payload.dateFrom || payload.dateTo) {
      writeLine("Period: " + (payload.dateFrom || "-") + " to " + (payload.dateTo || "-"), 9, false);
    }
    y += 1;

    y += 3;

    drawHeaderRow();

    rows.forEach(function (row) {
      drawRow([
        formatDateTime(row.datetime || row.date),
        row.description || "",
        row.bank_cash || "",
        Number(row.debit || 0) > 0 ? money(row.debit) : "",
        Number(row.credit || 0) > 0 ? money(row.credit) : "",
        money(row.balance || 0),
      ], false);
    });

    drawRow([
      "",
      "Totals",
      "",
      money(totals.debit || 0),
      money(totals.credit || 0),
      money(totals.balance || 0),
    ], true);

    doc.save("Location_Journal_" + (location.name || "location").replace(/[^a-zA-Z0-9]+/g, "_") + ".pdf");
  });
})();



