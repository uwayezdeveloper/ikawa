<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>404 - Page Not Found | <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?= \App\Core\View::asset('images/favicon.ico') ?>" />
    <script src="<?= \App\Core\View::asset('js/config.js') ?>"></script>
    <link href="<?= \App\Core\View::asset('css/vendors.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= \App\Core\View::asset('css/app.min.css') ?>" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="auth-box overflow-hidden align-items-center d-flex">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card text-center p-4">
                        <div class="card-body">
                            <h1 class="display-1 fw-bold text-primary">404</h1>
                            <h4 class="mb-3">Page Not Found</h4>
                            <p class="text-muted mb-4">
                                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                            </p>
                            <a href="<?= APP_URL ?>" class="btn btn-primary">
                                <i class="ti ti-home me-1"></i> Go to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= \App\Core\View::asset('js/vendors.min.js') ?>"></script>
    <script src="<?= \App\Core\View::asset('js/app.js') ?>"></script>
</body>
</html>
