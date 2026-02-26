// js/pages/expense-transactions-pdf.js
// Requires jsPDF and jsPDF-AutoTable from CDN

document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('export-pdf-btn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // Check jsPDF and autoTable
        let jsPDF = window.jspdf ? window.jspdf.jsPDF : window.jsPDF;
        if (!jsPDF || typeof jsPDF !== 'function') {
            alert('jsPDF library not loaded!');
            return;
        }
        if (!jsPDF.API || typeof jsPDF.API.autoTable !== 'function') {
            alert('jsPDF autoTable plugin not loaded!');
            return;
        }

        const doc = new jsPDF({orientation: 'landscape'});

        // Title and header
        doc.setFontSize(16);
        doc.text('Expense Statement Report', 14, 15);
        doc.setFontSize(10);
        let dateRange = '';
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('date_from') || urlParams.get('date_to')) {
            dateRange = 'Date Range: ';
            if (urlParams.get('date_from')) dateRange += 'From ' + urlParams.get('date_from') + ' ';
            if (urlParams.get('date_to')) dateRange += 'To ' + urlParams.get('date_to');
            doc.text(dateRange, 14, 22);
        }

        // Table headers (static for statement)
        const headers = [[
            '#', 'Transaction ID', 'Date', 'Expense Type', 'Consumer', 'Amount', 'Charges', 'Account'
        ]];

        // Table body and totals
        const table = document.getElementById('expense-transactions-table');
        let body = [];
        let totalAmount = 0;
        let totalCharges = 0;
        if (table) {
            let trs = table.querySelectorAll('tbody tr');
            trs.forEach((tr, idx) => {
                let tds = tr.querySelectorAll('td');
                // Only take the first 8 columns (statement mode)
                let row = [];
                for (let i = 0; i < 8; i++) {
                    row.push(tds[i] ? tds[i].innerText : '');
                }
                // Sum totals (Amount = 5, Charges = 6)
                if (!isNaN(parseFloat(row[5]))) totalAmount += parseFloat(row[5].replace(/,/g, ''));
                if (!isNaN(parseFloat(row[6]))) totalCharges += parseFloat(row[6].replace(/,/g, ''));
                body.push(row);
            });
        }
        // Add totals row
        if (body.length > 0) {
            let totalRow = [
                '', '', '', '', 'Totals:',
                totalAmount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}),
                totalCharges.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}),
                ''
            ];
            body.push(totalRow);
        }

        // AutoTable
        doc.autoTable({
            head: headers,
            body: body,
            startY: dateRange ? 28 : 22,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [41, 128, 185] },
            margin: { left: 14, right: 14 }
        });

        doc.save('expense_statement.pdf');
    });
});
