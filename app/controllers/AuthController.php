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

        $code = \App\Core\Otp::generate($email, 'login');
        \App\Core\Otp::deliver($email, $user['phone'] ?? '', $code);
        $_SESSION['pending_login_email'] = $email;
        flash('info', 'Giriş için doğrulama kodu e-posta adresinize gönderildi.');
        redirect(url('verify-login'));
    }

    public function showVerifyLogin(): void
    {
        if (empty($_SESSION['pending_login_email'])) redirect(url('login'));
        echo $this->render('auth/verify', ['title' => 'Giriş Doğrulama', 'mode' => 'login', 'email' => $_SESSION['pending_login_email']], 'auth');
    }

    public function verifyLogin(): void
    {
        $this->validateCsrf();
        $email = $_SESSION['pending_login_email'] ?? '';
        $code = trim($this->input('code', ''));
        if ($email === '' || !\App\Core\Otp::verify($email, $code, 'login')) {
            flash('error', 'Doğrulama kodu hatalı veya süresi dolmuş.');
            redirect(url('verify-login'));
        }
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        unset($_SESSION['pending_login_email']);
        if (!$user) redirect(url('login'));

        auth()->login($user);
        $this->log('login', 'Müşteri girişi (OTP doğrulandı)', (int)$user['id']);
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
        $accountType = $this->input('account_type', 'individual');
        $tcNo = preg_replace('/\D/', '', $this->input('tc_no', ''));
        $taxNo = preg_replace('/\D/', '', $this->input('tax_no', ''));
        $company = trim($this->input('company', ''));

        if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Lütfen tüm alanları doğru doldurun.');
            redirect(url('register'));
        }
        if (strlen($password) < 6 || $password !== $password2) {
            flash('error', 'Şifre en az 6 karakter olmalı ve eşleşmelidir.');
            redirect(url('register'));
        }

        if ($accountType === 'corporate') {
            if ($company === '' || !validate_tax_no($taxNo)) {
                flash('error', 'Kurumsal hesap için şirket adı ve geçerli 10 haneli vergi numarası gereklidir.');
                redirect(url('register'));
            }
        } else {
            if (!validate_tc_no($tcNo)) {
                flash('error', 'Geçerli bir T.C. kimlik numarası (11 hane) giriniz.');
                redirect(url('register'));
            }
        }

        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            flash('error', 'Bu e-posta adresi zaten kayıtlı.');
            redirect(url('register'));
        }

        $refCode = strtoupper(trim($_GET['ref'] ?? ''));
        $referredBy = null;
        if ($refCode !== '') {
            $stmt = db()->prepare('SELECT id FROM users WHERE referral_code = ?');
            $stmt->execute([$refCode]);
            $referredBy = $stmt->fetchColumn() ?: null;
        }

        $stmt = db()->prepare('INSERT INTO users (first_name, last_name, email, password, account_type, tc_no, tax_no, company, email_verified, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)');
        $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT), $accountType, $tcNo, $taxNo, $company, generate_referral_code(), $referredBy]);
        $id = (int)db()->lastInsertId();

        $code = \App\Core\Otp::generate($email, 'register');
        \App\Core\Otp::sendEmail($email, $code);
        $_SESSION['pending_verify_email'] = $email;

        $this->log('register', 'Yeni kayıt (email doğrulama bekleniyor)', $id);
        flash('info', 'Kayıt oluşturuldu. E-posta adresinize gönderilen doğrulama kodunu girin.');
        redirect(url('verify-email'));
    }

    public function showVerifyEmail(): void
    {
        if (empty($_SESSION['pending_verify_email'])) redirect(url('register'));
        echo $this->render('auth/verify', ['title' => 'E-posta Doğrulama', 'mode' => 'email', 'email' => $_SESSION['pending_verify_email']], 'auth');
    }

    public function verifyEmail(): void
    {
        $this->validateCsrf();
        $email = $_SESSION['pending_verify_email'] ?? '';
        $code = trim($this->input('code', ''));
        if ($email === '' || !\App\Core\Otp::verify($email, $code, 'register')) {
            flash('error', 'Doğrulama kodu hatalı veya süresi dolmuş.');
            redirect(url('verify-email'));
        }
        db()->prepare('UPDATE users SET email_verified = 1 WHERE email = ?')->execute([$email]);
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        unset($_SESSION['pending_verify_email']);
        auth()->login($user);
        $this->log('register_verified', 'E-posta doğrulandı', (int)$user['id']);
        flash('success', 'E-posta adresiniz doğrulandı. Hoş geldiniz!');
        redirect(url('client'));
    }

    public function resendOtp(): void
    {
        $this->validateCsrf();
        $email = $_SESSION['pending_verify_email'] ?? ($_SESSION['pending_login_email'] ?? '');
        if ($email === '') redirect(url('login'));
        $type = isset($_SESSION['pending_verify_email']) ? 'register' : 'login';
        $code = \App\Core\Otp::generate($email, $type);
        \App\Core\Otp::sendEmail($email, $code);
        flash('info', 'Yeni doğrulama kodu gönderildi.');
        redirect(url($type === 'register' ? 'verify-email' : 'verify-login'));
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
