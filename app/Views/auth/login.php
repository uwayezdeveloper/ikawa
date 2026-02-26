<div class="auth-box overflow-hidden align-items-center d-flex">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-5 col-md-6 col-sm-8">
                <div class="card p-4">
                    <div class="auth-brand text-center mb-2">
                        <a href="<?= APP_URL ?>" class="logo-dark">
                            <img src="<?= \App\Core\View::asset('images/logo-black.png') ?>" alt="dark logo" />
                        </a>
                        <a href="<?= APP_URL ?>" class="logo-light">
                            <img src="<?= \App\Core\View::asset('images/logo.png') ?>" alt="logo" />
                        </a>
                        <h4 class="fw-bold text-dark mt-3">Welcome to <?= APP_NAME ?> 👋</h4>
                        <p class="text-muted w-lg-75 mx-auto">
                            Let's get you signed in. Enter your email and password to continue.
                        </p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form action="<?= APP_URL ?>/login" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">
                                Email address
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input
                                    type="email"
                                    class="form-control <?= isset($_SESSION['errors']['email']) ? 'is-invalid' : '' ?>"
                                    id="userEmail"
                                    name="email"
                                    placeholder="you@example.com"
                                    value="<?= $_SESSION['old']['email'] ?? '' ?>"
                                    required
                                />
                                <?php if (isset($_SESSION['errors']['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= implode('<br>', $_SESSION['errors']['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="userPassword" class="form-label">
                                Password
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control <?= isset($_SESSION['errors']['password']) ? 'is-invalid' : '' ?>"
                                    id="userPassword"
                                    name="password"
                                    placeholder="••••••••"
                                    required
                                />
                                <?php if (isset($_SESSION['errors']['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= implode('<br>', $_SESSION['errors']['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input
                                    class="form-check-input form-check-input-light fs-14"
                                    type="checkbox"
                                    name="remember"
                                    id="rememberMe"
                                />
                                <label class="form-check-label" for="rememberMe">Keep me signed in</label>
                            </div>
                            <a href="<?= APP_URL ?>/forgot-password" class="text-decoration-underline link-offset-3 text-muted">
                                Forgot Password?
                            </a>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">
                                Sign In
                            </button>
                        </div>
                    </form>

                    <!--<p class="text-muted text-center mt-4 mb-0">-->
                    <!--    New here?-->
                    <!--    <a href="<?= APP_URL ?>/register" class="text-decoration-underline link-offset-3 fw-semibold">-->
                    <!--        Create an account-->
                    <!--    </a>-->
                    <!--</p>-->
                </div>

                <p class="text-center text-muted mt-4 mb-0">
                    © <?= date('Y') ?> <?= APP_NAME ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
// Clear old session data
unset($_SESSION['errors']);
unset($_SESSION['old']);
?>
