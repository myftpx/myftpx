<?php
namespace App\Core;

class Auth
{
    protected static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function user(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) return null;
        static $cached = null;
        if ($cached !== null) return $cached;
        try {
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $cached = $stmt->fetch() ?: null;
        } catch (\Throwable $t) {
            $cached = null;
        }
        return $cached;
    }

    public function admin(): ?array
    {
        $id = $_SESSION['admin_id'] ?? null;
        if (!$id) return null;
        static $cached = null;
        if ($cached !== null) return $cached;
        try {
            $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
            $stmt->execute([$id]);
            $cached = $stmt->fetch() ?: null;
        } catch (\Throwable $t) {
            $cached = null;
        }
        return $cached;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function checkAdmin(): bool
    {
        return $this->admin() !== null;
    }

    public function id(): ?int
    {
        $u = $this->user();
        return $u ? (int)$u['id'] : null;
    }

    public function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['user_id'] = $user['id'];
    }

    public function loginAdmin(array $admin): void
    {
        Session::regenerate();
        $_SESSION['admin_id'] = $admin['id'];
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    public function logoutAdmin(): void
    {
        unset($_SESSION['admin_id']);
    }

    public function require(): void
    {
        if (!$this->check()) {
            redirect(url('login'));
        }
    }

    public function requireAdmin(): void
    {
        if (!$this->checkAdmin()) {
            redirect(url('admin/login'));
        }
    }
}
