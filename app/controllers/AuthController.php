<?php
namespace App\Controllers;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (auth()->check()) redirect(url('client'));
        echo $this->render('auth/login', ['title' => 'Giriş Yap'], 'auth');
    }

    public function login(): void
    {
        $this->validateCsrf();
        $email = trim($this->input('email', ''));
        $password = (string)$this->input('password', '');

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'E-posta veya şifre hatalı.');
            redirect(url('login'));
        }
        if (($user['status'] ?? 'active') === 'suspended') {
            flash('error', 'Hesabınız askıya alınmış durumda. Lütfen destek ile iletişime geçin.');
            redirect(url('login'));
        }
        auth()->login($user);
        $this->log('login', 'Müşteri girişi', (int)$user['id']);
        flash('success', 'Hoş geldiniz, ' . $user['first_name'] . '!');
        redirect(url('client'));
    }

    public function showRegister(): void
    {
        if (auth()->check()) redirect(url('client'));
        if (!(int)setting('allow_registration', 1)) {
            flash('error', 'Yeni kayıtlar şu anda kapalıdır.');
            redirect(url('login'));
        }
        echo $this->render('auth/register', ['title' => 'Kayıt Ol'], 'auth');
    }

    public function register(): void
    {
        $this->validateCsrf();
        $firstName = trim($this->input('first_name', ''));
        $lastName = trim($this->input('last_name', ''));
        $email = trim($this->input('email', ''));
        $password = (string)$this->input('password', '');
        $password2 = (string)$this->input('password_confirm', '');

        if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Lütfen tüm alanları doğru doldurun.');
            redirect(url('register'));
        }
        if (strlen($password) < 6) {
            flash('error', 'Şifre en az 6 karakter olmalıdır.');
            redirect(url('register'));
        }
        if ($password !== $password2) {
            flash('error', 'Şifreler eşleşmiyor.');
            redirect(url('register'));
        }
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            flash('error', 'Bu e-posta adresi zaten kayıtlı.');
            redirect(url('register'));
        }

        $stmt = db()->prepare('INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)');
        $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $id = db()->lastInsertId();

        auth()->login(['id' => $id]);
        $this->log('register', 'Yeni kayıt', (int)$id);
        flash('success', 'Kayıt başarılı! Hoş geldiniz.');
        redirect(url('client'));
    }

    public function logout(): void
    {
        auth()->logout();
        flash('success', 'Çıkış yaptınız.');
        redirect(url('/'));
    }

    public function showForgot(): void
    {
        echo $this->render('auth/forgot', ['title' => 'Şifremi Unuttum'], 'auth');
    }

    public function forgot(): void
    {
        $this->validateCsrf();
        $email = trim($this->input('email', ''));
        flash('info', 'Eğer bu e-posta sistemde kayıtlıysa, şifre sıfırlama bağlantısı gönderilecektir.');
        redirect(url('login'));
    }
}
