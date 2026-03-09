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
    const company = payload.company || {};
    const supplier = payload.supplier || {};
    const records = Array.isArray(payload.records) ? payload.records : [];
    const productSummary = Array.isArray(payload.productSummary)
      ? payload.productSummary
      : [];

    const jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const marginLeft = 10;
    const lineHeight = 5.5;
    let y = 12;

    function money(v) {
      const n = Number(v || 0);
      return n.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    function resolvePaymentMethod(rawMethod, advanceAmount, accountAmount) {
      let method = String(rawMethod || "").toLowerCase().trim();
      if (method === "account") {
        method = "direct_pay";
      }
      if (method === "advance" || method === "direct_pay" || method === "pay_later") {
        return method;
      }

      if (Number(advanceAmount || 0) > 0) {
        return "advance";
      }
      if (Number(accountAmount || 0) > 0) {
        return "direct_pay";
      }
      return "pay_later";
    }

    function paymentMethodLabel(method, accountName) {
      if (method === "advance") {
        return "Advance";
      }
      if (method === "direct_pay") {
        return accountName ? "Direct Pay (" + accountName + ")" : "Direct Pay";
      }
      return "Pay Later";
    }

    function addLine(text, size, isBold) {
      doc.setFont("helvetica", isBold ? "bold" : "normal");
      doc.setFontSize(size || 10);

      const lines = doc.splitTextToSize(String(text || ""), pageWidth - marginLeft * 2);
      lines.forEach(function (line) {
        if (y > pageHeight - 12) {
          doc.addPage();
          y = 12;
        }
        doc.text(line, marginLeft, y);
        y += lineHeight;
      });
    }

    function formatDateCell(dateValue) {
      if (!dateValue) return "";
      const d = new Date(dateValue);
      if (!Number.isNaN(d.getTime())) {
        const day = String(d.getDate()).padStart(2, "0");
        const month = String(d.getMonth() + 1).padStart(2, "0");
        const year = d.getFullYear();
        return day + "/" + month + "/" + year;
      }
      return String(dateValue);
    }

    function buildStatementData(productRows, timelineRows) {
      function parseDateTime(value) {
        if (!value) return NaN;
        const t = new Date(value).getTime();
        return Number.isNaN(t) ? NaN : t;
      }

      function combineDateWithTime(dateValue, dateTimeValue) {
        const dateOnly = String(dateValue || "").slice(0, 10);
        const dateTimeText = String(dateTimeValue || "").trim();

        if (!dateOnly) {
          return dateTimeText || "";
        }

        if (!dateTimeText) {
          return dateOnly + " 00:00:00";
        }

        const hhmmssMatch = dateTimeText.match(/(\d{2}:\d{2}:\d{2})/);
        if (hhmmssMatch && hhmmssMatch[1]) {
          return dateOnly + " " + hhmmssMatch[1];
        }

        return dateOnly + " 00:00:00";
      }

      const productTypesMap = new Map();

      productRows.forEach(function (item) {
        const typeName = String(item.type_name || item.product_name || "Product").trim();
        if (!productTypesMap.has(typeName)) {
          productTypesMap.set(typeName, {
            name: typeName,
            symbol: String(item.unit_symbol || "").trim(),
          });
        }
      });

      const productTypes = Array.from(productTypesMap.values()).sort(function (a, b) {
        return a.name.localeCompare(b.name);
      });

      const events = [];
      let eventOrder = 0;

      timelineRows
        .filter(function (row) {
          return String(row.type || "") === "advance";
        })
        .forEach(function (row, idx) {
          const effectiveDateTime = combineDateWithTime(row.date, row.datetime || row.date);
          events.push({
            date: row.date,
            datetime: effectiveDateTime,
            action: "advance",
            amount: Number(row.debit || 0),
            sequence: eventOrder++,
          });
        });

      productRows.forEach(function (item, idx) {
        const method = resolvePaymentMethod(item.payment_method, item.advance_amount, item.account_amount);
        const payableTotal = Number(item.payable_total != null ? item.payable_total : item.payable_amount || 0);
        const purchaseLoanAmount = payableTotal > 0 ? payableTotal : (method === "pay_later" ? Number(item.total_price || 0) : 0);
        const advanceUsedAmount = Number(item.advance_amount || 0);
        const purchaseDateTime = combineDateWithTime(item.receive_date, item.created_at || item.receive_date);

        events.push({
          date: item.receive_date,
          datetime: purchaseDateTime,
          action: "purchase",
          amount: Number(item.total_price || 0),
          loanAmount: purchaseLoanAmount,
          advanceUsedAmount: advanceUsedAmount,
          amountPerKg: Number(item.unit_price || 0),
          productType: String(item.type_name || item.product_name || "Product").trim(),
          quantity: Number(item.quantity || 0),
          quantityInKg: Number(item.quantity_in_kg != null ? item.quantity_in_kg : item.quantity || 0),
          unitSymbol: String(item.unit_symbol || "").trim(),
          sequence: eventOrder++,
        });

        // Add explicit row when this purchase consumed supplier advance.
        if (advanceUsedAmount > 0) {
          events.push({
            date: item.receive_date,
            datetime: purchaseDateTime,
            action: "advance_usage",
            amount: advanceUsedAmount,
            sequence: eventOrder++,
          });
        }
      });

      timelineRows
        .filter(function (row) {
          return String(row.type || "") === "payment";
        })
        .forEach(function (row, idx) {
          events.push({
            date: row.date,
            datetime: row.datetime || row.date,
            action: "payment",
            amount: Number(row.debit || 0),
            sequence: eventOrder++,
          });
        });

      events.sort(function (a, b) {
        const ad = parseDateTime(a.datetime || a.date || 0);
        const bd = parseDateTime(b.datetime || b.date || 0);

        if (ad !== bd) {
          return ad - bd;
        }

        return Number(a.sequence || 0) - Number(b.sequence || 0);
      });

      const productTotals = {};
      productTypes.forEach(function (pt) {
        productTotals[pt.name] = 0;
      });

      let runningRemaining = 0;
      let runningStockValue = 0;
      let runningTotalKg = 0;

      const statementRows = events.map(function (event) {
        const row = {
          date: formatDateCell(event.date),
          action: event.action,
          amount: "",
          products: {},
          amountPerKg: "",
          totalAmount: "",
          remaining: "",
          totalInKg: "",
          stockValue: "",
        };

        productTypes.forEach(function (pt) {
          row.products[pt.name] = "";
        });

        if (event.action === "advance") {
          runningRemaining += event.amount;
          row.amount = money(event.amount);
          row.remaining = money(runningRemaining);
          row.totalInKg = money(runningTotalKg);
          row.stockValue = money(runningStockValue);
        } else if (event.action === "purchase") {
          runningRemaining -= Number(event.loanAmount || 0);
          runningStockValue += event.amount;
          const quantityInKg = Number(event.quantityInKg != null ? event.quantityInKg : event.quantity || 0);
          runningTotalKg += quantityInKg;

          const qtyText = money(event.quantity) + (event.unitSymbol ? " " + event.unitSymbol : "");
          row.products[event.productType] = qtyText;
          productTotals[event.productType] = (productTotals[event.productType] || 0) + Number(event.quantity || 0);

          row.amountPerKg = money(event.amountPerKg);
          row.totalAmount = money(event.amount);
          row.remaining = money(runningRemaining);
          row.totalInKg = money(runningTotalKg);
          row.stockValue = money(runningStockValue);
        } else if (event.action === "payment") {
          runningRemaining += event.amount;
          row.amount = money(event.amount);
          row.remaining = money(runningRemaining);
          row.totalInKg = money(runningTotalKg);
          row.stockValue = money(runningStockValue);
        } else if (event.action === "advance_usage") {
          runningRemaining -= event.amount;
          row.amount = money(event.amount);
          row.remaining = money(runningRemaining);
          row.totalInKg = money(runningTotalKg);
          row.stockValue = money(runningStockValue);
        }

        return row;
      });

      return {
        productTypes: productTypes,
        productTotals: productTotals,
        rows: statementRows,
      };
    }

    function drawStatementTable(statementData) {
      const tableX = marginLeft;
      const tableWidth = pageWidth - marginLeft * 2;
      const paddingX = 1.2;
      const baseRowHeight = 7;
      const lineGap = 3.1;

      const productTypes = statementData.productTypes;
      const headers = ["Date", "Description", "Payment/Advance"]
        .concat(productTypes.map(function (pt) {
          return pt.name;
        }))
        .concat(["Amount/Kg", "Total Amount", "Remaining", "Total in Kg", "Total Stock Value"]);

      const fixedWidth = 18 + 18 + 28 + 16 + 20 + 20 + 18 + 24;
      const productsArea = tableWidth - fixedWidth;
      const perProductWidth = productTypes.length
        ? Math.max(10, productsArea / productTypes.length)
        : 0;

      const colWidths = [18, 18, 28]
        .concat(productTypes.map(function () {
          return perProductWidth;
        }))
        .concat([16, 20, 20, 18, 24]);

      function textLines(text, width) {
        const lines = doc.splitTextToSize(String(text || ""), Math.max(2, width - paddingX * 2));
        return lines.length ? lines : [""];
      }

      function drawHeaderRow() {
        doc.setFont("helvetica", "bold");
        doc.setFontSize(8.2);
        doc.setDrawColor(90, 90, 90);
        doc.setLineWidth(0.22);

        const headerLines = headers.map(function (h, idx) {
          return textLines(h, colWidths[idx]);
        });
        const maxHeaderLines = Math.max.apply(
          null,
          headerLines.map(function (lines) {
            return lines.length;
          })
        );
        const headerHeight = Math.max(baseRowHeight, maxHeaderLines * lineGap + 2.6);

        let x = tableX;
        headers.forEach(function (_h, idx) {
          doc.rect(x, y - 5.1, colWidths[idx], headerHeight, "S");
          headerLines[idx].forEach(function (line, lineIdx) {
            doc.text(line, x + paddingX, y - 1.5 + lineIdx * lineGap);
          });
          x += colWidths[idx];
        });

        y += headerHeight;
      }

      function drawDataRow(cells, isBold) {
        const cellLines = cells.map(function (cell, idx) {
          return textLines(cell, colWidths[idx]);
        });
        const maxLines = Math.max.apply(
          null,
          cellLines.map(function (lines) {
            return lines.length;
          })
        );
        const rowHeight = Math.max(baseRowHeight, maxLines * lineGap + 2.2);

        if (y > pageHeight - (rowHeight + 5)) {
          doc.addPage();
          y = 12;
          drawHeaderRow();
        }

        doc.setFont("helvetica", isBold ? "bold" : "normal");
        doc.setFontSize(8);
        doc.setDrawColor(120, 120, 120);
        doc.setLineWidth(0.2);

        let x = tableX;
        cells.forEach(function (_cell, idx) {
          const alignRight = idx >= 2 && (idx < 3 || idx >= 3 + productTypes.length);
          doc.rect(x, y - 5.1, colWidths[idx], rowHeight, "S");

          cellLines[idx].forEach(function (line, lineIdx) {
            const ty = y - 1.6 + lineIdx * lineGap;
            if (alignRight) {
              const tw = doc.getTextWidth(line);
              doc.text(line, x + colWidths[idx] - paddingX - tw, ty);
            } else {
              doc.text(line, x + paddingX, ty);
            }
          });

          x += colWidths[idx];
        });

        y += rowHeight;
      }

      if (y > pageHeight - 25) {
        doc.addPage();
        y = 12;
      }

      drawHeaderRow();

      statementData.rows.forEach(function (row) {
        const actionLabel = row.action === "advance"
          ? "advance"
          : row.action === "payment"
          ? "payment"
          : row.action === "advance_usage"
          ? "paid by advance"
          : "purchase";

        const cells = [
          row.date,
          actionLabel,
          row.amount,
        ]
          .concat(
            productTypes.map(function (pt) {
              return row.products[pt.name] || "";
            })
          )
          .concat([row.amountPerKg, row.totalAmount, row.remaining, row.totalInKg, row.stockValue]);

        drawDataRow(cells, false);
      });

      const totalsCells = ["", "totals", ""]
        .concat(
          productTypes.map(function (pt) {
            return money(statementData.productTotals[pt.name] || 0);
          })
        )
        .concat(["", "", "", "", ""]);

      drawDataRow(totalsCells, true);
    }

    addLine("SUPPLIER STATEMENT", 14, true);
    addLine("Generated: " + new Date().toLocaleString(), 10, false);
    y += 2;

    const companyName =
      company.company_name || company.name || "Gihanga Coffee Company Ltd";
    const hq = company.company_address || "Gahanga Sector";

    const stationSet = new Set();
    productSummary.forEach(function (item) {
      if (item && item.location_name) {
        stationSet.add(String(item.location_name));
      }
    });
    if (stationSet.size === 0) {
      records.forEach(function (r) {
        if (r && r.location) {
          stationSet.add(String(r.location));
        }
      });
    }
    const stationText = stationSet.size ? Array.from(stationSet).join(", ") : "N/A";

    addLine("Company name: " + companyName, 11, true);
    addLine("Location: " + stationText, 10, false);
    addLine("Supplier id: " + (supplier.id || "N/A"), 10, false);
    addLine("Supplier name: " + (supplier.name || "N/A"), 10, false);
    y += 2;

    if (!records.length) {
      addLine("No data found for this supplier.", 10, false);
    } else {
      const statementData = buildStatementData(productSummary, records);
      drawStatementTable(statementData);
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
