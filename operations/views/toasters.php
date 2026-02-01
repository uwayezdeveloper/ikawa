<!-- SESSION EXPIRY MODAL -->
<div id='sessionModal' style='
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,.6);
    z-index:9999;
    display: none; /* Keep hidden initially */
'>
    <div style='
        background:#fff;
        width:400px;
        margin:15% auto;
        padding:20px;
        border-radius:6px;
        text-align:center;
    '>
        <h3>Session Expiring Soon</h3>
        
        <p>
            Your session will expire in 
            <strong id='sessionCountdown'>10</strong> seconds
        </p>
        
        <p style="font-size:12px; color:#666; margin-top:10px;">
            Click "Extend Session" to stay logged in, or you'll be automatically logged out.
        </p>
        
        <div style='margin-top:20px; display:flex; justify-content:center; gap:10px;'>
            <button id='logoutNow' style='
                background:#dc3545;
                color:#fff;
                padding:8px 16px;
                border:none;
                border-radius:4px;
                cursor:pointer;
            '>
                Logout Now
            </button>
            
            <button id="extendSession" style="
                background:#28a745;
                color:#fff;
                padding:8px 16px;
                border:none;
                border-radius:4px;
                cursor:pointer;
            ">
                Extend Session
            </button>
        </div>
    </div>
</div>

<style>
/* Toast styling */
.toast {
  visibility: hidden;
  min-width: 250px;
  margin-left: -125px;
  background-color: #333;
  color: #fff;
  text-align: center;
  border-radius: 4px;
  padding: 16px;
  position: fixed;
  z-index: 9999;
  left: 50%;
  top: 30px;
  font-size: 16px;
  opacity: 0;
  transition: opacity 0.5s, top 0.5s;
}
.toast.show { visibility: visible; opacity: 1; top: 50px; }
.toast.success { background-color: #4CAF50; }
.toast.error { background-color: #f44336; }

/* Loading spinner inside button */
.button-loading {
  pointer-events: none;
  opacity: 0.6;
}
.button-loading .spinner {
  margin-left: 8px;
  border: 2px solid #fff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  width: 14px;
  height: 14px;
  display: inline-block;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg);}
  100% { transform: rotate(360deg);}
}
</style>

<div id="toast" class="toast"></div>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;

    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// Add loading effect to a button
function setButtonLoading(button, isLoading) {
    if(isLoading) {
        button.disabled = true;
        button.classList.add('button-loading');    
    } else {
        button.disabled = false;
        button.classList.remove('button-loading');
    }
}
</script>