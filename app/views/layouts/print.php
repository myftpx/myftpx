<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= e($title ?? 'Fatura') ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 30px; }
        .inv-head { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 16px; margin-bottom: 20px; }
        .inv-head h1 { margin: 0; font-size: 22px; }
        .brand { font-size: 20px; font-weight: bold; color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 9px 12px; border-bottom: 1px solid #ddd; font-size: 13px; }
        th { background: #f5f5f5; }
        .totals { width: 320px; margin-left: auto; }
        .totals td { border: none; }
        .totals .grand { font-size: 16px; font-weight: bold; border-top: 2px solid #333; }
        .meta { font-size: 12px; color: #555; }
        @media print { body { margin: 10px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="padding:10px 20px;margin-bottom:16px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer">🖨 Yazdır / PDF Kaydet</button>
    <?= $content ?>
    <script>window.onload = function(){ /* optional auto-print */ };</script>
</body>
</html>
