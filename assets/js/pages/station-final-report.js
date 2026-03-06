(function () {
  "use strict";

  const btn = document.getElementById("downloadFinalReportPdfBtn");
  if (!btn) return;

  function money(v) {
    const n = Number(v || 0);
    return n.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " FRW";
  }

  function textValue(v) {
    return String(v || "").trim();
  }

  function numberValue(v) {
    return Number(v || 0);
  }

  btn.addEventListener("click", function () {
    if (!window.jspdf || !window.jspdf.jsPDF) {
      alert("jsPDF failed to load. Please refresh and try again.");
      return;
    }

    const payload = window.finalLocationReportPayload || {};
    const report = payload.report || {};
    const location = payload.location || {};

    const suppliers = report.suppliers || {};
    const stock = report.stock || {};
    const journalBank = report.journal_bank || {};
    const expenses = report.expenses || {};
    const liability = report.liability || {};

    const jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 10;
    const contentWidth = pageWidth - margin * 2;
    const right = margin + contentWidth;
    const paddingX = 2;
    const baseFontSize = 9;
    let y = 10;

    function ensureSpace(required) {
      if (y + required <= pageHeight - margin) return;
      doc.addPage();
      y = margin;
    }

    function drawCell(x, topY, width, height, text, options) {
      const opts = options || {};
      const align = opts.align || "left";
      const bold = !!opts.bold;
      const fill = opts.fill || null;

      doc.setDrawColor(90, 90, 90);
      doc.setLineWidth(0.2);
      if (fill) {
        doc.setFillColor(fill[0], fill[1], fill[2]);
        doc.rect(x, topY, width, height, "FD");
      } else {
        doc.rect(x, topY, width, height, "S");
      }

      doc.setFont("helvetica", bold ? "bold" : "normal");
      doc.setFontSize(baseFontSize);

      const usableWidth = Math.max(4, width - paddingX * 2);
      const lines = Array.isArray(text) ? text : doc.splitTextToSize(textValue(text), usableWidth);
      const firstLineY = topY + 4.3;

      if (align === "right") {
        const printable = (lines && lines.length) ? lines[0] : "";
        const tw = doc.getTextWidth(printable);
        doc.text(printable, x + width - paddingX - tw, firstLineY);
      } else if (align === "center") {
        const printable = (lines && lines.length) ? lines[0] : "";
        const tw = doc.getTextWidth(printable);
        doc.text(printable, x + (width - tw) / 2, firstLineY);
      } else {
        doc.text(lines, x + paddingX, firstLineY);
      }
    }

    function drawTable(columns, rows, tableTitle) {
      const tableTop = y;
      const titleHeight = 6;
      const headerHeight = 7;
      const bodyHeights = rows.map(function (row) {
        let maxLines = 1;
        for (let i = 0; i < row.cells.length; i += 1) {
          const cell = row.cells[i];
          if (cell.align === "right") continue;
          const lines = doc.splitTextToSize(textValue(cell.text), Math.max(4, columns[i].width - paddingX * 2));
          if (lines.length > maxLines) maxLines = lines.length;
        }
        return Math.max(6.5, maxLines * 3.9 + 2.2);
      });

      const bodyHeight = bodyHeights.reduce(function (sum, h) { return sum + h; }, 0);
      const tableHeight = titleHeight + headerHeight + bodyHeight;
      ensureSpace(tableHeight + 3);

      doc.setDrawColor(70, 70, 70);
      doc.setLineWidth(0.25);
      doc.setFillColor(240, 240, 240);
      doc.rect(margin, y, contentWidth, titleHeight, "FD");
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10);
      doc.text(textValue(tableTitle), margin + 2, y + 4.2);
      y += titleHeight;

      let x = margin;
      columns.forEach(function (col) {
        drawCell(x, y, col.width, headerHeight, col.title, {
          align: col.align || "left",
          bold: true,
          fill: [230, 230, 230],
        });
        x += col.width;
      });
      y += headerHeight;

      rows.forEach(function (row, rowIndex) {
        const rowHeight = bodyHeights[rowIndex];
        let cellX = margin;
        row.cells.forEach(function (cell, colIndex) {
          drawCell(cellX, y, columns[colIndex].width, rowHeight, cell.text, {
            align: cell.align || columns[colIndex].align || "left",
            bold: !!row.bold,
            fill: row.fill || null,
          });
          cellX += columns[colIndex].width;
        });
        y += rowHeight;
      });

      y = Math.max(y, tableTop + tableHeight) + 3;
    }

    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text("GIHANGA COFFEE CO LTD", pageWidth / 2, y + 2, { align: "center" });
    y += 8;

    doc.setFont("helvetica", "bold");
    doc.setFontSize(13);
    doc.text("FINAL LOCATION REPORT", pageWidth / 2, y, { align: "center" });
    y += 6;

    doc.setDrawColor(120, 120, 120);
    doc.setLineWidth(0.25);
    doc.rect(margin, y, contentWidth, 12, "S");
    doc.setFont("helvetica", "normal");
    doc.setFontSize(9.5);
    doc.text("Generated: " + textValue(payload.generatedAt || new Date().toLocaleString()), margin + 2, y + 4.5);
    doc.text("Location: " + textValue(location.name || "N/A"), margin + 2, y + 9.2);
    y += 16;

    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    doc.text("cheries: " + Number(stock.cheries_total_quantity || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }), margin + 2, y - 1);
    y += 5;

    const mainColumns = [
      { title: "Section", width: 55 },
      { title: "Description", width: 167 },
      { title: "Amount (FRW)", width: 55, align: "right" },
    ];

    const mainRows = [
      { cells: [{ text: "Suppliers" }, { text: "Advance (active advances in this location)" }, { text: money(suppliers.advance || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Loan (transfer from one station to another)" }, { text: money(suppliers.loan_transfer || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Total" }, { text: money(suppliers.total || 0), align: "right" }], bold: true, fill: [247, 247, 247] },

      { cells: [{ text: "Stock" }, { text: "Stock value" }, { text: money(stock.stock_value || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Total" }, { text: money(stock.total || 0), align: "right" }], bold: true, fill: [247, 247, 247] },

      { cells: [{ text: "Journal / Bank" }, { text: "Journal (accounts where identifiers = 2)" }, { text: money(journalBank.journal || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Bank (accounts where identifiers = 1)" }, { text: money(journalBank.bank || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Total" }, { text: money(journalBank.total || 0), align: "right" }], bold: true, fill: [247, 247, 247] },

      { cells: [{ text: "Expenses" }, { text: "Exploitable (categ_id = 1)" }, { text: money(expenses.exploitable || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Non exploitable (categ_id = 2)" }, { text: money(expenses.non_exploitable || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Investment/Liability (categ_id = 3)" }, { text: money(expenses.investment_liability || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Certification (categ_id = 4)" }, { text: money(expenses.certification || 0), align: "right" }] },
      { cells: [{ text: "" }, { text: "Total" }, { text: money(expenses.total || 0), align: "right" }], bold: true, fill: [247, 247, 247] },
    ];

    drawTable(mainColumns, mainRows, "Main Report Summary");

    const liabilityColumns = [
      { title: "Description", width: 86 },
      { title: "Amount (FRW)", width: 42, align: "right" },
      { title: "Names", width: 71 },
      { title: "Amount (FRW)", width: 42, align: "right" },
      { title: "Autre Credit (FRW)", width: 36, align: "right" },
    ];

    const liabilityRows = [
      {
        cells: [
          { text: "Approvisionnement (HQ account-transfer history)" },
          { text: money(liability.approvisionnement || 0), align: "right" },
          { text: "Loan (loans we have to suppliers)" },
          { text: money(liability.supplier_loans || 0), align: "right" },
          { text: money(liability.autre_credit || 0), align: "right" },
        ],
      },
      {
        cells: [
          { text: "Liability Total", align: "right" },
          { text: "" },
          { text: "" },
          { text: "" },
          { text: money(liability.total || 0), align: "right" },
        ],
        bold: true,
        fill: [247, 247, 247],
      },
    ];

    drawTable(liabilityColumns, liabilityRows, "Liability Account");

    ensureSpace(10);
    doc.setDrawColor(50, 50, 50);
    doc.setLineWidth(0.3);
    doc.setFillColor(230, 230, 230);
    doc.rect(margin, y, contentWidth, 9, "FD");
    doc.setFont("helvetica", "bold");
    doc.setFontSize(11);
    doc.text("FINAL TOTAL", margin + 2, y + 5.8);
    const finalAmount = money(numberValue(report.main_total));
    const finalAmountWidth = doc.getTextWidth(finalAmount);
    doc.text(finalAmount, right - 2 - finalAmountWidth, y + 5.8);

    doc.save("Final_Location_Report_" + textValue(location.name || "location").replace(/[^a-zA-Z0-9]+/g, "_") + ".pdf");
  });
})();
