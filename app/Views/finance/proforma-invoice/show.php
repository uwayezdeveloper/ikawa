<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= htmlspecialchars($title ?? 'Proforma Invoice') ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Finance</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/finance/proforma-invoice">Proforma Invoices</a></li>
                    <li class="breadcrumb-item active">Invoice Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-file-invoice me-2"></i>Proforma Invoice: <?= htmlspecialchars($invoice['code']) ?>
                </h5>
                <div>
                    <button onclick="downloadPDF()" class="btn btn-success me-2">
                        <i class="ti ti-download me-1"></i>Download PDF
                    </button>
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="ti ti-printer me-1"></i>Print
                    </button>
                    <a href="<?= APP_URL ?>/finance/proforma-invoice" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            
            <div class="card-body" id="invoiceContent">
                <!-- Invoice Header -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2 class="text-primary">PROFORMA INVOICE</h2>
                        <p class="mb-1"><strong>Invoice Code:</strong> <?= htmlspecialchars($invoice['code']) ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?= date('F j, Y', strtotime($invoice['created_at'])) ?></p>
                        <p class="mb-1"><strong>Invoice Type:</strong> <?= htmlspecialchars($invoice['invoice_type']) ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4>Gihanga Coffee</h4>
                        <p class="mb-0">Rwanda</p>
                    </div>
                </div>

                <hr>

                <!-- Client Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Bill To:</h5>
                        <p class="mb-1"><strong><?= htmlspecialchars($invoice['client_name']) ?></strong></p>
                        <?php if ($invoice['phone']): ?>
                            <p class="mb-1">Phone: <?= htmlspecialchars($invoice['phone']) ?></p>
                        <?php endif; ?>
                        <?php if ($invoice['email']): ?>
                            <p class="mb-1">Email: <?= htmlspecialchars($invoice['email']) ?></p>
                        <?php endif; ?>
                        <?php if ($invoice['address']): ?>
                            <p class="mb-1">Address: <?= htmlspecialchars($invoice['address']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-1"><strong>Currency:</strong> <?= htmlspecialchars($invoice['currency_name']) ?></p>
                        <p class="mb-1"><strong>Prepared By:</strong> <?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?></p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Unit</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal = 0;
                            foreach ($items as $index => $item): 
                                $subtotal += $item['total'];
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= htmlspecialchars($item['unit_symbol'] ?? $item['unit_name'] ?? 'Unit ID ' . $item['unit_id']) ?></td>
                                    <td class="text-end"><?= number_format($item['quantity'], 2) ?></td>
                                    <td class="text-end"><?= htmlspecialchars($invoice['currency_sign']) ?><?= number_format($item['price'], 2) ?></td>
                                    <td class="text-end"><strong><?= htmlspecialchars($invoice['currency_sign']) ?><?= number_format($item['total'], 2) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end">
                                    <h5 class="mb-0 text-primary">
                                        <?= htmlspecialchars($invoice['currency_sign']) ?><?= number_format($invoice['total_amount'], 2) ?>
                                    </h5>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer Notes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <hr>
                        <p class="text-muted mb-0">
                            <small>This is a proforma invoice and does not constitute a tax invoice.</small>
                        </p>
                        <p class="text-muted">
                            <small>Thank you for your business!</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Screen view margins */
.card-body#invoiceContent {
    padding: 1.5cm;
    font-size: 12pt;
}

.card-body#invoiceContent p,
.card-body#invoiceContent td,
.card-body#invoiceContent th,
.card-body#invoiceContent li,
.card-body#invoiceContent span {
    font-size: 12pt !important;
}

@media print {
    .btn, .breadcrumb, .page-title-box, .card-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
    }
    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .container-fluid, .row, .col-12 {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    /* Standard paper margins for printing and PDF */
    @page {
        margin: 1.5cm;
        size: A4;
    }
    .card-body#invoiceContent {
        padding: 0 !important;
        margin: 0 !important;
    }
    html {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<!-- jsPDF Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
function downloadPDF() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';
    button.disabled = true;
    
    const element = document.getElementById('invoiceContent');
    const invoiceCode = '<?= htmlspecialchars($invoice['code']) ?>';
    
    // Configure html2canvas
    const options = {
        scale: 3,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff'
    };
    
    html2canvas(element, options).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        
        // A4 dimensions in mm
        const pageWidth = 210;
        const pageHeight = 297;
        
        // Standard margins (15mm = 1.5 cm)
        const margin = 15;
        const contentWidth = pageWidth - (2 * margin);
        const contentHeight = pageHeight - (2 * margin);
        
        // Calculate dimensions to fit content within margins
        const imgWidth = contentWidth;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        
        let heightLeft = imgHeight;
        let position = margin;
        
        // Add first page with margins
        pdf.addImage(imgData, 'PNG', margin, position, imgWidth, imgHeight);
        heightLeft -= contentHeight;
        
        // Add additional pages if content is longer
        while (heightLeft > 0) {
            position = heightLeft - imgHeight + margin;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', margin, position, imgWidth, imgHeight);
            heightLeft -= contentHeight;
        }
        
        // Save the PDF
        pdf.save(`${invoiceCode}.pdf`);
        
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    }).catch(error => {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF. Please try again.');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>