<!DOCTYPE html>
<html lang="tr" data-theme="<?= e(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? setting('site_name', 'RCVXTR')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<?php include __DIR__ . '/../partials/flash.php'; ?>
<?= $content ?>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
