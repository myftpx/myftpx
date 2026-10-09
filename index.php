<?php
/**
 * RCVXTR — Front Controller
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Router;

// Install redirect
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = rtrim($uri, '/') ?: '/';

// Serve the installer
if (str_starts_with($uri, '/install')) {
    require __DIR__ . '/install/index.php';
    exit;
}

// Serve real static files directly (assets, images, downloads, zip, etc.)
$staticFile = __DIR__ . $uri;
if ($uri !== '/' && is_file($staticFile) && !str_ends_with($uri, '.php')) {
    return false; // hand off to the built-in / web server
}

if (!Database::isInstalled()) {
    redirect(dirname($_SERVER['SCRIPT_NAME'] ?? '/') . '/install');
}

// Build routes
$router = new Router();
require __DIR__ . '/app/routes.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $uri);
