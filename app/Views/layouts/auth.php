<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= $pageTitle ?? 'Sign In' ?> | <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= APP_NAME ?> - Coffee Management System" />
    <meta name="author" content="<?= APP_NAME ?>" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= \App\Core\View::asset('images/favicon.ico') ?>" />
    
    <!-- Theme Config Js -->
    <script src="<?= \App\Core\View::asset('js/config.js') ?>"></script>

    <!-- Vendor css -->
    <link href="<?= \App\Core\View::asset('css/vendors.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="<?= \App\Core\View::asset('css/app.min.css') ?>" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="position-absolute top-0 end-0">
        <img src="<?= \App\Core\View::asset('images/auth-card-bg.svg') ?>" class="auth-card-bg-img" alt="auth-card-bg" />
    </div>
    <div class="position-absolute bottom-0 start-0" style="transform: rotate(180deg)">
        <img src="<?= \App\Core\View::asset('images/auth-card-bg.svg') ?>" class="auth-card-bg-img" alt="auth-card-bg" />
    </div>
    
    <?= $content ?>
    
    <!-- Vendor js -->
    <script src="<?= \App\Core\View::asset('js/vendors.min.js') ?>"></script>
    
    <!-- App js -->
    <script src="<?= \App\Core\View::asset('js/app.js') ?>"></script>
</body>
</html>
