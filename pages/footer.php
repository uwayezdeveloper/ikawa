   <!-- Wizard area End-->
    <!-- Start Footer area-->
    <div class="footer-copyright-area" style="margin:0px 0px 0px 0px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="footer-copy-right">
                        <p>Copyright © <?php echo date('Y'); ?>
. All rights reserved. Powered by <a href="https://itec.rw/">ITEC LTD</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <!-- End Footer area-->
<!-- jquery -->
<script src="../js/vendor/jquery-1.12.4.min.js"></script>

<!-- bootstrap JS -->
<script src="../js/bootstrap.min.js"></script>

<!-- wow JS -->
<script src="../js/wow.min.js"></script>

<!-- price-slider JS -->
<script src="../js/jquery-price-slider.js"></script>

<!-- owl.carousel JS -->
<script src="../js/owl.carousel.min.js"></script>

<!-- scrollUp JS -->
<script src="../js/jquery.scrollUp.min.js"></script>

<!-- meanmenu JS -->
<script src="../js/meanmenu/jquery.meanmenu.js"></script>

<!-- counterup JS -->
<script src="../js/counterup/jquery.counterup.min.js"></script>
<script src="../js/counterup/waypoints.min.js"></script>
<script src="../js/counterup/counterup-active.js"></script>

<!-- mCustomScrollbar JS -->
<script src="../js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>

<!-- sparkline JS -->
<script src="../js/sparkline/jquery.sparkline.min.js"></script>
<script src="../js/sparkline/sparkline-active.js"></script>

<!-- flot JS -->
<script src="../js/flot/jquery.flot.js"></script>
<script src="../js/flot/jquery.flot.resize.js"></script>
<script src="../js/flot/curvedLines.js"></script>
<script src="../js/flot/flot-active.js"></script>

<!-- knob JS -->
<script src="../js/knob/jquery.knob.js"></script>
<script src="../js/knob/jquery.appear.js"></script>
<script src="../js/knob/knob-active.js"></script>

<!-- Input Mask JS (MISSING – ADDED) -->
<script src="../js/jasny-bootstrap.min.js"></script>

<!-- icheck JS (MISSING – ADDED) -->
<script src="../js/icheck/icheck.min.js"></script>
<script src="../js/icheck/icheck-active.js"></script>

<!-- range-slider JS (MISSING – ADDED) -->
<script src="../js/rangle-slider/jquery-ui-1.10.4.custom.min.js"></script>
<script src="../js/rangle-slider/jquery-ui-touch-punch.min.js"></script>
<script src="../js/rangle-slider/rangle-active.js"></script>

<!-- datapicker JS (MISSING – ADDED) -->
<script src="../js/datapicker/bootstrap-datepicker.js"></script>
<script src="../js/datapicker/datepicker-active.js"></script>

<!-- bootstrap select JS (FIXED PATH + ADDED) -->
<script src="../js/bootstrap-select/bootstrap-select.js"></script>

<!-- color-picker JS (MISSING – ADDED) -->
<script src="../js/color-picker/farbtastic.min.js"></script>
<script src="../js/color-picker/color-picker.js"></script>

<!-- notification JS (MISSING – ADDED) -->
<script src="../js/notification/bootstrap-growl.min.js"></script>
<script src="../js/notification/notification-active.js"></script>

<!-- summernote JS (MISSING – ADDED) -->
<script src="../js/summernote/summernote-updated.min.js"></script>
<script src="../js/summernote/summernote-active.js"></script>

<!-- dropzone JS (MISSING – ADDED) -->
<script src="../js/dropzone/dropzone.js"></script>

<!-- wave JS -->
<script src="../js/wave/waves.min.js"></script>
<script src="../js/wave/wave-active.js"></script>

<!-- chosen JS (MISSING – ADDED) -->
<script src="../js/chosen/chosen.jquery.js"></script>

<!-- Chat JS -->
<script src="../js/chat/jquery.chat.js"></script>

<!-- wizard JS -->
<script src="../js/wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="../js/wizard/wizard-active.js"></script>

<!-- todo JS -->
<script src="../js/todo/jquery.todo.js"></script>

<!-- data-table JS -->
<script src="../js/data-table/jquery.dataTables.min.js"></script>
<script src="../js/data-table/data-table-act.js"></script>

<!-- plugins JS -->
<script src="../js/plugins.js"></script>

<!-- main JS -->
<script src="../js/main.js"></script>
<script src="../js/dialog/sweetalert2.min.js"></script>
<script src="../js/dialog/dialog-active.js"></script>

 <!-- cropper JS
    ============================================ -->
<script src="../js/cropper/cropper.min.js"></script>
<script src="../js/cropper/cropper-actice.js"></script>

<script>
// Session expiry countdown
function startSessionCountdown(remainingSeconds) {
    // If less than 10 seconds remain, show modal immediately
    if (remainingSeconds <= 10) {
        showSessionModal(remainingSeconds);
        return;
    }
    
    // Calculate when to show the modal (10 seconds before expiry)
    const timeToShowModal = (remainingSeconds - 10) * 1000;
    
    setTimeout(function() {
        showSessionModal(10);
    }, timeToShowModal);
}

function showSessionModal(seconds) {
    const modal = document.getElementById('sessionModal');
    const countdown = document.getElementById('sessionCountdown');
    const logoutBtn = document.getElementById('logoutNow');
    const extendBtn = document.getElementById('extendSession');
    
    modal.style.display = 'block';
    countdown.textContent = seconds;
    
    // Disable background clicks
    modal.style.pointerEvents = 'auto';
    
    let remaining = seconds;
    let timer = null;
    
    // Start countdown
    timer = setInterval(() => {
        remaining--;
        countdown.textContent = remaining;
        
        if (remaining <= 0) {
            clearInterval(timer);
            performLogout();
        }
    }, 1000);
    
    // Logout button
    logoutBtn.onclick = function() {
        clearInterval(timer);
        performLogout();
    };
    
    // Extend session button
    extendBtn.onclick = function() {
        clearInterval(timer);
        extendSession();
    };
    
    // Close modal on background click
    modal.onclick = function(e) {
        if (e.target === modal) {
            // Don't allow closing by clicking background
            return;
        }
    };
}

function performLogout() {
    // Optional: Send logout request
    fetch('<?= App::baseUrl() ?>/_ikawa/users/logout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    }).then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = `<?= App::baseUrl() ?>/public/`;
        } else {
            alert(data.message);
        }
    });
}

function extendSession() {
    const extendBtn = document.getElementById('extendSession');
    const originalText = extendBtn.textContent;
    
    // Show loading
    extendBtn.disabled = true;
    extendBtn.textContent = 'Extending...';
    extendBtn.style.opacity = '0.7';
    
    fetch('<?= App::baseUrl() ?>/_ikawa/users/extend-session', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal
            document.getElementById('sessionModal').style.display = 'none';
            
            // Show success toast
            showToast('Session extended successfully!', 'success');
            
            // Restart countdown check after 50 minutes
            setTimeout(() => {
            }, 50 * 60 * 1000); // 50 minutes
        } else {
            showToast('Failed to extend session', 'error');
            // Restart countdown from 10 seconds
            setTimeout(() => showSessionModal(10), 1000);
        }
    })
    .catch(error => {
        showToast('Error extending session', 'error');
        // Restart countdown from 10 seconds
        setTimeout(() => showSessionModal(10), 1000);
    })
    .finally(() => {
        // Restore button
        extendBtn.disabled = false;
        extendBtn.textContent = originalText;
        extendBtn.style.opacity = '1';
    });
}


// Initialize when page loads
$(document).ready(function() {
    <?php if (isset($_SESSION['session_expires_in']) && $_SESSION['session_expires_in'] > 0): ?>
    startSessionCountdown(<?= $_SESSION['session_expires_in'] ?>);
    <?php endif; ?>
});
</script>
</body>

</html>