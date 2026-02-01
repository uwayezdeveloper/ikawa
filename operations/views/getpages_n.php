<script>
async function loadContent(page) {
    const app = document.getElementById('app');
    
    // Show loading
    app.innerHTML = createLoader(page);
    
    try {
        const response = await fetch(`load.php?page=${page}`);
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const html = await response.text();
        
        // Smooth transition
        await new Promise(resolve => setTimeout(resolve, 300));
        
        app.innerHTML = html;
        
        // Initialize components
        initializeComponents();
        
        // Fade in
        fadeIn(app);
        
    } catch (error) {
        console.error('Load error:', error);
        app.innerHTML = createErrorState(page, error.message);
    }
}

function createLoader(page) {
    const pageName = page.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    return `
        <div class="modern-loader">
            
            <div class="loader-progress">
                <div class="loader-bar"></div>
            </div>
        </div>
    `;
}

function createErrorState(page, errorMsg) {
    return `
         <div class="error-page-area">
         
        <div class="error-page-wrap" style="margin-top:15px;">
            <i class="notika-icon notika-close"></i>
            <h2>ERROR <span class="counter">404</span></h2>
            <p>Sorry, but the page you are looking for has note been found. Try checking the URL for an error, then hit the refresh button on your browser or try found something else in our system.</p>
            <button onclick="location.reload()" class="btn">Dashboard</button>
            <a href="#" class="btn error-btn-mg">Report Problem</a>
        </div>
    </div>
    `;
}

function initializeComponents() {
    if ($('.chosen').length) {
        $('.chosen').chosen({ width: '100%' });
    }
    
    if ($('#data-table-basic').length) {
        if ($.fn.DataTable.isDataTable('#data-table-basic')) {
            $('#data-table-basic').DataTable().destroy();
        }
        $('#data-table-basic').DataTable({
            pageLength: 10,
            responsive: true
        });
    }
}

function fadeIn(element) {
    element.style.opacity = '0';
    element.style.transition = 'opacity 0.5s ease';
    requestAnimationFrame(() => {
        element.style.opacity = '1';
    });
}

// click handlers
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[onclick^="loadContent"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const match = this.getAttribute('onclick').match(/loadContent\('([^']+)'\)/);
            if (match) {
                loadContent(match[1]);
            }
        });
    });
});
</script>

<style>
.modern-loader {
    text-align: center;
    padding: 60px 20px;
}

.loader-circle {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 30px;
}

.loader-spin {
    width: 100%;
    height: 100%;
    border: 4px solid rgba(52, 152, 219, 0.2);
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loader-progress {
    width: 200px;
    height: 4px;
    background: #f0f0f0;
    border-radius: 2px;
    margin: 20px auto;
    overflow: hidden;
}

.loader-bar {
    width: 60%;
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    animation: progress 2s ease-in-out infinite;
}

@keyframes progress {
    0%, 100% { transform: translateX(-100%); }
    50% { transform: translateX(100%); }
}

.modern-error {
    text-align: center;
    padding: 40px 20px;
    background: #fff8f8;
    border-radius: 10px;
    margin: 20px;
}

.error-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.error-message {
    color: #666;
    margin: 15px 0;
}

.error-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 25px;
}

.btn-retry, .btn-home {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-retry {
    background: #3498db;
    color: white;
}

.btn-home {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #dee2e6;
}

.btn-icon {
    font-size: 18px;
}
</style>