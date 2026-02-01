
<?php
require __DIR__ . '../../../_ikawa/config/App.php'; // adjust path if needed
?>
<style>
/* Simple toast styling */
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

.toast.show {
  visibility: visible;
  opacity: 1;
  top: 50px;
}
.toast.success { background-color: #4CAF50; }
.toast.error   { background-color: #f44336; }
.login-content {
    min-height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: url('../img/1.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
    padding: 60px 0;
}

.nk-block {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    padding: 50px 40px;
    border-radius: 25px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    max-width: 450px;
    width: 100%;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.nk-block::before {
    content: 'Welcome Back';
    display: block;
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin-bottom: 30px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.input-group-addon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px 0 0 12px;
    padding: 15px 20px;
}

.input-group-addon i {
    color: white;
    font-size: 20px;
}

.nk-int-st input {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 0 12px 12px 0;
    padding: 15px 20px;
    color: #333;
}

.nk-int-st input:focus {
    background: white;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-login {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 30px auto 0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
    transition: all 0.3s ease;
}

.btn-login:hover {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 6px 25px rgba(79, 172, 254, 0.6);
}

/* Content wrapper styling */
.all-content-wrapper {
    background: transparent;
}
</style>
<div class = 'login-content'>
    
<!-- Login -->
<div class="nk-block toggled" id="l-login">
  <div class="nk-form">
    <div class="input-group">
      <span class="input-group-addon nk-ic-st-pro"><i class="notika-icon notika-support"></i></span>
      <div class="nk-int-st">
        <input type="text" class="form-control" name="username" placeholder="Username">
      </div>
    </div>

    <div class="input-group mg-t-15">
      <span class="input-group-addon nk-ic-st-pro"><i class="notika-icon notika-edit"></i></span>
      <div class="nk-int-st">
        <input type="password" class="form-control" name="password" placeholder="Password">
      </div>
    </div>

    <button type="button" id="loginBtn" class="btn btn-login btn-success btn-float">
      <i class="notika-icon notika-right-arrow"></i>
    </button>
  </div>
</div>
</div>
<div id="toast" class="toast"></div>
<script>
// Simple toast function
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;

    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// Pass PHP base URL to JS
const BASE_URL = '<?php echo App::baseUrl(); ?>';

document.getElementById('loginBtn').addEventListener('click', function () {
    const usernameInput = document.querySelector('input[name="username"]');
    const passwordInput = document.querySelector('input[name="password"]');

    const username = usernameInput.value;
    const password = passwordInput.value;

    fetch(`${BASE_URL}/_ikawa/users/login`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ username, password })
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            showToast('Login successful!', 'success');
            usernameInput.value = '';
            passwordInput.value = '';

            setTimeout(() => {
                window.location.href = `${BASE_URL}/operations/`;
            }, 1000);
        } else {
            showToast(res.message, 'error');
            passwordInput.value = '';
        }
    });
});
</script>