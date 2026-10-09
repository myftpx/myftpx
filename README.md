# RCVXTR — Kurumsal Hosting & Müşteri Yönetim Sistemi

WHMCS ve WiseCP tarzında, saf PHP ile yazılmış (framework'siz, düz `.php` dosyaları) kurumsal barındırma, fatura ve müşteri yönetim paneli. İki kurumsal tema (RCVXTRWHITE / RCVXTRDARK), kapsamlı admin paneli, müşteri paneli ve güçlü REST API içerir.

## Özellikler

### Kurulum
- `/install` üzerinden web tabanlı kurulum
- SQLite (varsayılan) ve MySQL desteği
- Yönetici hesabı + site bilgileri tek adımda oluşturulur

### Temalar
- **RCVXTRWHITE** — açık (beyaz) kurumsal tema
- **RCVXTRDARK** — koyu kurumsal tema
- Admin panelinden tek tıkla tema değiştirme (tüm arayüzlere anında yansır)

### Yönetim Paneli (Admin)
- Dashboard (istatistikler, son biletler/faturalar/siparişler)
- Müşteri yönetimi (oluştur/düzenle/sil, bakiye yönetimi, "müşteri olarak giriş")
- Ürün/hizmet kataloğu (fiyat, dönem, kurulum ücreti, yapılandırma seçenekleri, modül atama, öne çıkarma)
- Siparişler ve hizmetler (aktifleştirme, durdurma, sonlandırma)
- Faturalar (manuel fatura, ödendi işaretleme, silme)
- Destek biletleri (yanıtlama, durum yönetimi, departman/öncelik)
- Alan adları (kayıt firması, DNS, süre yönetimi)
- Ödeme yöntemleri (Stripe, PayPal, Havale/EFT — genişletilebilir)
- Modüller & entegrasyonlar (cPanel, Plesk, Domain Registrar, SMTP)
- Genel ayarlar (site adı, tema, para birimi, KDV, fatura öneki, API aç/kapa, kayıt izni, bakım modu)
- Raporlar (aylık gelir, ödeme yöntemine göre dağılım)

### Müşteri Paneli (Client)
- Dashboard, hizmetler, alan adları, faturalar, biletler, bakiye, profil
- Bakiye ile ürün/hizmet satın alma
- DNS yönetimi (alan adları için)
- **API Erişimi**: API key + auth key oluşturma, IP izin listesi, izin (permission) seçimi

### REST API (`/api/v1/...`)
- Kimlik doğrulama: `X-Api-Key` + `X-Auth-Key` başlıkları (veya `Authorization: Bearer`)
- IP izin listesi (boş = tüm IP'ler)
- İzin (permission) tabanlı erişim
- Kaynaklar: `me`, `services`, `domains`, `invoices`, `tickets`, `balance`, `orders`
- Bakiye ile sipariş ve fatura ödeme

## Gereksinimler
- PHP 8.0+ (PDO, SQLite3 veya MySQL)
- Yazılabilir `storage/` ve kök dizin (config.php oluşturulur)

## Kurulum

1. Dosyaları web sunucunuza yükleyin.
2. Tarayıcıdan `/install` adresine gidin.
3. Site bilgilerini, yönetici hesabını ve veritabanı sürücüsünü girin.
4. "Kurulumu Başlat" deyin — bitti!

Varsayılan yönetici erişimi kurulumda belirlediğiniz e-posta/şifredir.

> **Apache**: `.htaccess` otomatik olarak yönlendirmeyi yapar. **Nginx** için tüm istekleri `index.php`'ye yönlendirin.
> Yerel geliştirme: `php -S 0.0.0.0:8000 index.php`

## Dizin Yapısı
```
app/
  bootstrap.php        Başlangıç & autoload
  core/                Database, Router, View, Auth, Session, Csrf, Schema, helpers
  controllers/         Store, Auth, Client, Admin, Api
  views/               Tüm şablonlar (layouts + sayfalar)
  routes.php           Rota tanımları
assets/css|js          Tema stilleri ve scriptler
install/index.php      Kurulum sihirbazı
config.php             (kurulumda oluşturulur)
storage/database.sqlite
```

## API Örneği
```bash
curl -H "X-Api-Key: rcvx_..." -H "X-Auth-Key: ..." \
     https://site.com/api/v1/me

# Sipariş oluştur (bakiye ile)
curl -X POST -H "X-Api-Key: rcvx_..." -H "Content-Type: application/json" \
     -d '{"product_id":1,"billing_cycle":"monthly","domain":"example.com"}' \
     https://site.com/api/v1/orders
```

## Demo Verisi (opsiyonel)
`php storage/seed-demo.php` çalıştırarak örnek ürünler ve bir demo müşteri ekleyebilirsiniz.
