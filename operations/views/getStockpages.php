<script>
function loadContent(page) {
    console.log('Loading page:', page);
    $.ajax({
        url: '<?= App::baseUrl() ?>/operations/load.php',
        method: 'GET',
        data: { page: page },
        success: function(html) {
            $('#app').html(html);
            
            // Initialize page-specific scripts after content is loaded
            console.log('Checking for page init functions...');
            
            setTimeout(function() {
                // Initialize selling page
                if (page === 'manage-selling' && typeof window.initSellingPage === 'function') {
                    console.log('Calling initSellingPage');
                    window.initSellingPage();
                }
                
                // Initialize sales price approval page
                if (page === 'sales-price-approval' && typeof window.initPriceApprovalPage === 'function') {
                    console.log('Calling initPriceApprovalPage');
                    window.initPriceApprovalPage();
                }
                
                // Initialize product mixing page
                if (page === 'product-mixing' && typeof window.initProductMixingPage === 'function') {
                    console.log('Calling initProductMixingPage');
                    window.initProductMixingPage();
                }
            }, 200);
        },
        error: function(xhr, status, error) {
            console.log('Error loading page:', error);
            $('#app').html('<div class="alert alert-danger">Error loading page</div>');
        }
    });
}
</script>