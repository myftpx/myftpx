<?php
namespace App\Controllers;

use App\Core\View;

abstract class Controller
{
    protected function render(string $template, array $data = [], ?string $layout = null): string
    {
        if ($layout !== null) {
            View::layout($layout);
        }
        return View::render($template, $data);
    }

    protected function json($data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!\App\Core\Csrf::verify($token)) {
            if ($this->wantsJson()) {
                $this->json(['error' => 'CSRF token geçersiz'], 419);
            }
            flash('error', 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
            redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
        }
    }

    protected function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    protected function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function log(string $action, string $description = '', ?int $userId = null, ?int $adminId = null): void
    {
        try {
            $stmt = db()->prepare('INSERT INTO activity_log (user_id, admin_id, action, description, ip) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $adminId, $action, $description, $_SERVER['REMOTE_ADDR'] ?? '']);
        } catch (\Throwable $t) {
            // ignore
        }
    }
}
