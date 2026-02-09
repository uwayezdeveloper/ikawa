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
                        <h4 class="fw-bold text-dark mt-3">Create your account 🚀</h4>
                        <p class="text-muted w-lg-75 mx-auto">
                            Join <?= APP_NAME ?> today. Fill in your details to get started.
                        </p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form action="<?= APP_URL ?>/register" method="POST" id="registerForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label">
                                        First Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control <?= isset($_SESSION['errors']['first_name']) ? 'is-invalid' : '' ?>"
                                        id="firstName"
                                        name="first_name"
                                        placeholder="John"
                                        value="<?= $_SESSION['old']['first_name'] ?? '' ?>"
                                        required
                                    />
                                    <?php if (isset($_SESSION['errors']['first_name'])): ?>
                                        <div class="invalid-feedback">
                                            <?= implode('<br>', $_SESSION['errors']['first_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastName" class="form-label">
                                        Last Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control <?= isset($_SESSION['errors']['last_name']) ? 'is-invalid' : '' ?>"
                                        id="lastName"
                                        name="last_name"
                                        placeholder="Doe"
                                        value="<?= $_SESSION['old']['last_name'] ?? '' ?>"
                                        required
                                    />
                                    <?php if (isset($_SESSION['errors']['last_name'])): ?>
                                        <div class="invalid-feedback">
                                            <?= implode('<br>', $_SESSION['errors']['last_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="userEmail" class="form-label">
                                Email address
                                <span class="text-danger">*</span>
                            </label>
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

                        <div class="mb-3">
                            <label for="userPassword" class="form-label">
                                Password
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                type="password"
                                class="form-control <?= isset($_SESSION['errors']['password']) ? 'is-invalid' : '' ?>"
                                id="userPassword"
                                name="password"
                                placeholder="••••••••"
                                required
                                minlength="8"
                            />
                            <?php if (isset($_SESSION['errors']['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= implode('<br>', $_SESSION['errors']['password']) ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="userPasswordConfirm" class="form-label">
                                Confirm Password
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                type="password"
                                class="form-control"
                                id="userPasswordConfirm"
                                name="password_confirmation"
                                placeholder="••••••••"
                                required
                            />
                        </div>

                        <div class="form-check mb-3">
                            <input
                                class="form-check-input form-check-input-light fs-14"
                                type="checkbox"
                                id="termsCheck"
                                required
                            />
                            <label class="form-check-label" for="termsCheck">
                                I agree to the <a href="#" class="text-decoration-underline">Terms of Service</a> 
                                and <a href="#" class="text-decoration-underline">Privacy Policy</a>
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">
                                Create Account
                            </button>
                        </div>
                    </form>

                    <p class="text-muted text-center mt-4 mb-0">
                        Already have an account?
                        <a href="<?= APP_URL ?>/login" class="text-decoration-underline link-offset-3 fw-semibold">
                            Sign In
                        </a>
                    </p>
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
