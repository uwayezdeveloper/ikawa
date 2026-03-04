<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?= $pageTitle ?? 'Dashboard' ?> | <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= APP_NAME ?> - Coffee Management System" />
    <meta name="author" content="<?= APP_NAME ?>" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= \App\Core\View::asset('images/favicon.ico') ?>" />

    <!-- Theme Config Js -->
    <script src="<?= \App\Core\View::asset('js/config.js') ?>"></script>

    <!-- Vendor css -->
    <link href="<?= \App\Core\View::asset('css/vendors.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 CSS -->
    <link href="<?= \App\Core\View::asset('plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" type="text/css" />
   <!-- Boxicons CSS from CDN (fallback for missing font files) -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- App css -->
    <link href="<?= \App\Core\View::asset('css/app.min.css') ?>" rel="stylesheet" type="text/css" />
    
    <!-- Prevent Lucide from affecting SweetAlert2 icons -->
    <style>
        .swal2-icon svg { display: none !important; }
        .swal2-popup svg:not(.swal2-icon-content) { pointer-events: none; }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <!-- ========== Topbar Start ========== -->
        <?php \App\Core\View::include('header', ['user' => $user ?? null]); ?>
        <!-- ========== Topbar End ========== -->

        <!-- ========== Sidenav Start ========== -->
        <?php \App\Core\View::include('sidebar', ['user' => $user ?? null]); ?>
        <!-- ========== Sidenav End ========== -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="content-page">
            <div class="container-fluid">
                <?= $content ?>
            </div>

            <!-- ========== Footer Start ========== -->
            <?php \App\Core\View::include('footer'); ?>
            <!-- ========== Footer End ========== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="<?= \App\Core\View::asset('js/vendors.min.js') ?>"></script>

    <!-- SweetAlert2 -->
    <script src="<?= \App\Core\View::asset('plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

    <!-- App js -->
    <script src="<?= \App\Core\View::asset('js/app.js') ?>"></script>

    <script>
        window.APP_URL = '<?= APP_URL ?>';
    </script>

    <!-- Page specific scripts -->
    <?php if (isset($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
    <script src="<?= \App\Core\View::asset($script) ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>