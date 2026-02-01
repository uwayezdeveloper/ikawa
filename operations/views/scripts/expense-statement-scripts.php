<script>
(function() {
    console.log('Expense Statement script initialized');
    
    let statementTable;
    let currentStatementData = [];
    let currentFilters = {};

    // Initialize chosen plugin for selects
    if ($('.chosen').length) {
        $('.chosen').chosen({ width: '100%' });
    }

    // Hide table by default
    $('#statement-results-section').hide();

    // Initialize DataTable
    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#statement-table')) {
            $('#statement-table').DataTable().destroy();
        }
        statementTable = $('#statement-table').DataTable({
            pageLength: 25,
            lengthChange: true,
            searching: true,
            ordering: true,
            order: [[1, 'desc']], // Sort by date desc
            autoWidth: false
        });
    }

    // Load statement data
    function loadStatementData() {
        console.log('Loading statement data...');
        const filters = {
            consumer_id: $('#filter_consumer').val() || '',
            expense_id: $('#filter_expense').val() || '',
            receipt_type: $('#filter_receipt_type').val() || '',
            date_from: $('#filter_date_from').val(),
            date_to: $('#filter_date_to').val()
        };

        currentFilters = filters;
        console.log('Filters:', filters);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/statement',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(filters),
            dataType: 'json',
            beforeSend: function() {
                $('#applyFiltersBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
            },
            success: function(response) {
                console.log('Response:', response);
                if (response.success && response.data) {
                    currentStatementData = response.data;
                    populateTable(response.data);
                    // Show the table section after data is loaded
                    $('#statement-results-section').fadeIn();
                } else {
                    showToast(response.message || 'Failed to load statement data', 'error');
                    $('#statementdata').html('<tr><td colspan="10" class="text-center">No data found</td></tr>');
                    $('#statement-results-section').fadeIn();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error);
                let msg = 'Failed to load statement data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    console.error('Response text:', xhr.responseText);
                    msg = 'Server error: ' + error;
                }
                showToast(msg, 'error');
                $('#statementdata').html('<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>');
            },
            complete: function() {
                $('#applyFiltersBtn').prop('disabled', false).html('<i class="notika-icon notika-search"></i> Apply Filters');
            }
        });
    }

    // Populate table with data
    function populateTable(data) {
        console.log('Populating table with', data.length, 'records');
        if ($.fn.DataTable.isDataTable('#statement-table')) {
            $('#statement-table').DataTable().destroy();
        }

        $('#statementdata').empty();

        let totalAmount = 0;
        let totalCharges = 0;
        let grandTotal = 0;

        if (data.length === 0) {
            $('#statementdata').html('<tr><td colspan="10" class="text-center">No records found for the selected filters</td></tr>');
        } else {
            $.each(data, function(index, record) {
                const amount = parseFloat(record.amount) || 0;
                const charges = parseFloat(record.charges) || 0;
                const total = amount + charges;

                totalAmount += amount;
                totalCharges += charges;
                grandTotal += total;

                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.pay_date || record.recorded_date || '-'}</td>
                    <td>${record.expense_name || 'N/A'}</td>
                    <td>${record.consumer_name || '-'}</td>
                    <td>${record.st_name || 'N/A'}</td>
                    <td class="amount-col">${formatNumber(amount)} RWF</td>
                    <td class="charges-col">${formatNumber(charges)} RWF</td>
                    <td class="total-col"><strong>${formatNumber(total)} RWF</strong></td>
                    <td>${record.payment_mode_name || 'N/A'}</td>
                    <td>${record.description || '-'}</td>
                </tr>`;
                $('#statementdata').append(row);
            });
        }

        // Update totals
        $('#total_amount').text(formatNumber(totalAmount) + ' RWF');
        $('#total_charges').text(formatNumber(totalCharges) + ' RWF');
        $('#grand_total').text(formatNumber(grandTotal) + ' RWF');

        // Re-initialize DataTable
        initDataTable();
        showToast('Statement loaded successfully', 'success');
    }

    // Format number with commas
    function formatNumber(num) {
        return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Export to PDF
    function exportToPDF() {
        if (currentStatementData.length === 0) {
            showToast('No data to export', 'warning');
            return;
        }

        // Load jsPDF library dynamically if not already loaded
        if (typeof jspdf === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload = function() {
                loadAutoTable(() => generatePDF());
            };
            document.head.appendChild(script);
        } else {
            generatePDF();
        }
    }

    function loadAutoTable(callback) {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js';
        script.onload = callback;
        document.head.appendChild(script);
    }

    function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Add title
        doc.setFontSize(18);
        doc.text('Expense Consumed Statement', 14, 22);
        
        // Add filter info
        doc.setFontSize(10);
        let yPos = 32;
        doc.text(`Date Range: ${currentFilters.date_from} to ${currentFilters.date_to}`, 14, yPos);
        
        // Prepare table data
        let headers = ['#', 'Date', 'Expense', 'Consumer', 'Station', 'Amount', 'Charges', 'Total', 'Payment Mode'];
        let totalAmount = 0, totalCharges = 0, grandTotal = 0;

        const rows = currentStatementData.map((record, index) => {
            const amount = parseFloat(record.amount) || 0;
            const charges = parseFloat(record.charges) || 0;
            const total = amount + charges;

            totalAmount += amount;
            totalCharges += charges;
            grandTotal += total;

            return [
                index + 1,
                record.pay_date || record.recorded_date || '-',
                record.expense_name || 'N/A',
                record.consumer_name || '-',
                record.st_name || 'N/A',
                formatNumber(amount) + ' RWF',
                formatNumber(charges) + ' RWF',
                formatNumber(total) + ' RWF',
                record.payment_mode_name || 'N/A'
            ];
        });

        // Add totals row
        let totalsRow = ['', '', '', '', 'TOTAL:', formatNumber(totalAmount) + ' RWF', formatNumber(totalCharges) + ' RWF', formatNumber(grandTotal) + ' RWF', ''];
        rows.push(totalsRow);

        // Generate table
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: yPos + 5,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [41, 128, 185] }
        });

        // Save PDF
        doc.save(`expense-statement-${currentFilters.date_from}-to-${currentFilters.date_to}.pdf`);
        showToast('PDF exported successfully', 'success');
    }

    // Export to Excel
    function exportToExcel() {
        if (currentStatementData.length === 0) {
            showToast('No data to export', 'warning');
            return;
        }

        // Load SheetJS library dynamically if not already loaded
        if (typeof XLSX === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
            script.onload = function() {
                generateExcel();
            };
            document.head.appendChild(script);
        } else {
            generateExcel();
        }
    }

    function generateExcel() {
        let headers = ['#', 'Date', 'Expense Type', 'Consumer', 'Station', 'Amount (RWF)', 'Charges (RWF)', 'Total (RWF)', 'Payment Mode', 'Description'];
        let totalAmount = 0, totalCharges = 0, grandTotal = 0;

        // Prepare data
        const data = [headers];
        
        currentStatementData.forEach((record, index) => {
            const amount = parseFloat(record.amount) || 0;
            const charges = parseFloat(record.charges) || 0;
            const total = amount + charges;

            totalAmount += amount;
            totalCharges += charges;
            grandTotal += total;

            let row = [
                index + 1,
                record.pay_date || record.recorded_date || '-',
                record.expense_name || 'N/A',
                record.consumer_name || '-',
                record.st_name || 'N/A',
                amount,
                charges,
                total,
                record.payment_mode_name || 'N/A',
                record.description || '-'
            ];

            data.push(row);
        });

        // Add totals row
        let totalsRow = ['', '', '', '', 'TOTAL:', totalAmount, totalCharges, grandTotal, '', ''];
        data.push(totalsRow);

        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Expense Statement');
        
        // Save file
        XLSX.writeFile(wb, `expense-statement-${currentFilters.date_from}-to-${currentFilters.date_to}.xlsx`);
        showToast('Excel exported successfully', 'success');
    }

    // Load SweetAlert2 dynamically (if needed) and show toast
    function loadSweetAlert(callback) {
        if (typeof Swal !== 'undefined') {
            return callback();
        }
        const script = document.createElement('script');
        // script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        script.onload = callback;
        document.head.appendChild(script);
    }

    function showToast(message, type) {
        const iconType = type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'success';
        loadSweetAlert(function() {
            try {
                Swal.fire({
                    icon: iconType,
                    title: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            } catch (e) {
                console.error('Failed to show toast:', e);
            }
        });
    }

    // Apply Filters Button - Use event delegation for dynamically loaded content
    $(document).off('click', '#applyFiltersBtn').on('click', '#applyFiltersBtn', function(e) {
        e.preventDefault();
        console.log('Apply Filters button clicked');
        loadStatementData();
    });

    // Export PDF
    $(document).off('click', '#exportPdfBtn').on('click', '#exportPdfBtn', function(e) {
        e.preventDefault();
        console.log('Export PDF clicked');
        exportToPDF();
    });

    // Export Excel
    $(document).off('click', '#exportExcelBtn').on('click', '#exportExcelBtn', function(e) {
        e.preventDefault();
        console.log('Export Excel clicked');
        exportToExcel();
    });

})();
</script>
