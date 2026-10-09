<?php
/**
 * RCVXTR — Bootstrap
 */

declare(strict_types=1);

// Simple PSR-4 autoloader for App\ namespace (directories are lowercase)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $parts = explode('\\', $relative);
        $file = array_pop($parts); // class name (PascalCase)
        $dirs = array_map('strtolower', $parts);
        $path = __DIR__ . '/' . implode('/', $dirs) . '/' . $file . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

require __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/views/partials/status_badge.php';

date_default_timezone_set('Europe/Istanbul');
mb_internal_encoding('UTF-8');

\App\Core\Session::start();

// Determine base URL
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
$baseDir = str_replace('\\', '/', dirname($scriptName));
if ($baseDir === '/' || $baseDir === '.') {
    $baseDir = '';
}
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $baseDir;

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $baseDir;
}

// Allow config.php to override base_url
$cfg = is_file(dirname(__DIR__) . '/config.php') ? include dirname(__DIR__) . '/config.php' : [];
if (!empty($cfg['base_url'])) {
    $baseUrl = rtrim($cfg['base_url'], '/');
}
$GLOBALS['__base_url'] = $baseUrl;
