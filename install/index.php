<?php
/**
 * RCVXTR — Installer
 * Access via /install
 */

if (!defined('RCVXTR_BOOT')) {
    define('RCVXTR_BOOT', true);
    require_once dirname(__DIR__) . '/app/bootstrap.php';
}

use App\Core\Database;
use App\Core\Schema;

$configPath = dirname(__DIR__) . '/config.php';
$installed = is_file($configPath) && Database::isInstalled();

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed) {
    $siteName = trim($_POST['site_name'] ?? 'RCVXTR');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $driver = $_POST['db_driver'] ?? 'sqlite';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Lütfen tüm alanları doğru doldurun (şifre en az 6 karakter).';
    } else {
        try {
            if ($driver === 'mysql') {
                $dbHost = $_POST['db_host'] ?? '127.0.0.1';
                $dbPort = $_POST['db_port'] ?? '3306';
                $dbName = $_POST['db_name'] ?? '';
                $dbUser = $_POST['db_user'] ?? '';
                $dbPass = $_POST['db_pass'] ?? '';
                $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");
                $config = [
                    'db_driver' => 'mysql', 'db_host' => $dbHost, 'db_port' => $dbPort,
                    'db_name' => $dbName, 'db_user' => $dbUser, 'db_pass' => $dbPass, 'debug' => true,
                ];
            } else {
                $dbPath = dirname(__DIR__) . '/storage/database.sqlite';
                $config = ['db_driver' => 'sqlite', 'db_path' => $dbPath, 'debug' => true];
                $pdo = new PDO('sqlite:' . $dbPath);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            Schema::install($pdo);
            Schema::seed($pdo, ['site_name' => $siteName, 'name' => $name, 'email' => $email, 'password' => $password]);

            $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/install/index.php')), '/');
            $config['base_url'] = preg_replace('#/install/?$#', '', $base);

            file_put_contents($configPath, "<?php\nreturn " . var_export($config, true) . ";\n");
            $success = true;
        } catch (Throwable $t) {
            $error = 'Kurulum hatası: ' . $t->getMessage();
        }
    }
}

$theme = 'rcvxtrwhite';
$baseUrl = $GLOBALS['__base_url'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurulum — RCVXTR</title>
    <link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/css/app.css">
</head>
<body>
<div class="auth-wrap">
    <div class="card" style="max-width:640px;width:100%">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <span style="width:36px;height:36px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:800;background:linear-gradient(135deg,#3b82f6,#6366f1)">R</span>
                <h2>RCVXTR Kurulum</h2>
            </div>
        </div>
        <div class="card-body">
<?php if ($installed && !$success): ?>
    <div class="alert alert-info">Sistem zaten kurulu. <a href="<?= e($baseUrl) ?>/">Ana sayfaya gidin</a>.</div>
<?php elseif ($success): ?>
    <div class="alert alert-success">Kurulum başarıyla tamamlandı!</div>
    <p>RCVXTR kuruldu. Şimdi giriş yapabilirsiniz.</p>
    <div class="flex gap-2 mt-3">
        <a class="btn btn-primary" href="<?= e($baseUrl) ?>/admin">Yönetim Paneli</a>
        <a class="btn btn-outline" href="<?= e($baseUrl) ?>/">Ana Sayfa</a>
    </div>
<?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="">
        <h3 class="mb-2">Site Bilgileri</h3>
        <div class="form-group">
            <label>Site Adı</label>
            <input class="form-control" name="site_name" value="RCVXTR" required>
        </div>
        <h3 class="mb-2 mt-3">Yönetici Hesabı</h3>
        <div class="form-group">
            <label>Ad Soyad</label>
            <input class="form-control" name="name" required placeholder="Yönetici">
        </div>
        <div class="form-group">
            <label>E-posta</label>
            <input class="form-control" type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Şifre</label>
            <input class="form-control" type="password" name="password" required minlength="6">
        </div>
        <h3 class="mb-2 mt-3">Veritabanı</h3>
        <div class="form-group">
            <label>Sürücü</label>
            <select class="form-control" name="db_driver" id="db_driver" onchange="document.getElementById('mysql-fields').style.display = this.value === 'mysql' ? 'block' : 'none'">
                <option value="sqlite">SQLite (dosya tabanlı, önerilen)</option>
                <option value="mysql">MySQL</option>
            </select>
        </div>
        <div id="mysql-fields" style="display:none">
            <div class="form-row">
                <div class="form-group"><label>Host</label><input class="form-control" name="db_host" value="127.0.0.1"></div>
                <div class="form-group"><label>Port</label><input class="form-control" name="db_port" value="3306"></div>
            </div>
            <div class="form-group"><label>Veritabanı</label><input class="form-control" name="db_name"></div>
            <div class="form-row">
                <div class="form-group"><label>Kullanıcı</label><input class="form-control" name="db_user"></div>
                <div class="form-group"><label>Şifre</label><input class="form-control" type="password" name="db_pass"></div>
            </div>
        </div>
        <button class="btn btn-primary btn-lg btn-block mt-2" type="submit">Kurulumu Başlat</button>
    </form>
<?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

