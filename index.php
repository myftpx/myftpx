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

if (str_starts_with($uri, '/install') || str_starts_with($uri, '/assets') || str_starts_with($uri, '/favicon')) {
    // Let install / static assets pass through to their own files
    if (str_starts_with($uri, '/install')) {
        require __DIR__ . '/install/index.php';
        exit;
    }
    return false; // serve static via built-in server / web server
}

if (!Database::isInstalled()) {
    redirect(dirname($_SERVER['SCRIPT_NAME'] ?? '/') . '/install');
}

// Build routes
$router = new Router();
require __DIR__ . '/app/routes.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $uri);
