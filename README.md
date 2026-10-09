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
- Destek biletleri (yanıtlama, durum yönetimi, departman/öncelik, hazır yanıtlar)
- Alan adları (kayıt firması, DNS, süre yönetimi) + **TLD fiyatlandırma**
- **Ürün Eklentileri** + **Promosyon/Kupon Kodları**
- Ödeme yöntemleri (PayTR, iyzico, Havale/EFT) + **Banka Hesapları** + **Ödeme Logları**
- Modüller & entegrasyonlar (cPanel, Plesk, Domain Registrar, SMTP)
- **Duyurular** + **Bilgi Bankası** (kategori + makale) + **E-posta Şablonları**
- Genel ayarlar (site adı, tema, para birimi, KDV, fatura öneki, API, bakım modu)
- Raporlar (aylık gelir, ödeme yöntemine göre dağılım)

### Müşteri Paneli (Client)
- Dashboard, hizmetler, alan adları, faturalar, biletler, bakiye, profil
- Bakiye ile ürün/hizmet satın alma
- DNS yönetimi (alan adları için)
- **API Erişimi**: API key + auth key oluşturma, IP izin listesi, izin (permission) seçimi
- **Kayıtlı Kartlar**: kart saklama (PayTR / iyzico token), varsayılan kart, silme
- **Ödeme Geçmişi**: tüm ödeme işlemlerinin şeffaf görünümü
- **Otomatik Ödeme (abonelik)**: hizmet bazında aç/kapat + kart seçimi
- **Alt Hesaplar / Kişiler** (sub-accounts)
- **Teklifler** (quotes)
- Bilet değerlendirme (rating)

### Genel Site (Store)
- Kurumsal ana sayfa (hero + domain arama + TLD fiyat + özellikler)
- **Bilgi Bankası** (kategori + makale + arama)
- **Duyurular**
- **Alan adı arama + kayıt**
- **Ağ Durumu** sayfası

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

## Ödeme Sistemi

### Ödeme Kuruluşları (POS)
- **PayTR** — `merchant_id`, `merchant_key`, `merchant_salt`, test/canlı mod
- **iyzico** — `api_key`, `secret_key`, sandbox/canlı mod
- **Havale / EFT** — banka hesapları (IBAN) + "ödeme yaptım" bildirimi + admin onayı

> Kart bilgileri asla sisteminizde saklanmaz — yalnızca ödeme kuruluşundan dönen **token** tutulur (PCI uyumlu).

### Kart Saklama
- Müşteri paneli → Kartlarım → kart ekle (PayTR/iyzico token oluşturur)
- Varsayılan kart belirleme, silme

### Otomatik Ödeme (Abonelik)
- Hizmet detayında müşteri "Otomatik Ödeme"yi açıp kart seçer
- Ödeme tarihi geldiğinde sistem otomatik fatura keser ve kayıtlı karttan çeker
- Cron: `GET /cron/billing?key=<cron_secret>` (gizli anahtar admin → Ayarlar'da değil, `settings` tablosunda `cron_secret`)
- Crontab örneği (günde bir):
  ```
  0 * * * * curl -s "https://site.com/cron/billing?key=GIZLI_ANAHTAR" > /dev/null
  ```

### Ödeme Logları
- Tüm işlemler loglanır: kart kaydetme/silme, ödeme denemesi, başarılı/başarısız ödeme, otomatik ödeme, havale onay/ret
- Admin → **Ödeme Logları** (tüm kullanıcılar) ve Müşteri → **Ödeme Geçmişi** (kendi kayıtları)
- Loglar: işlem tipi, tutar, kuruluş, referans, durum, IP, tarih

### Test / Canlı Mod
- Test/sandbox modunda gerçek para çekilmez; ödemeler simüle edilir (geliştirme için idealdir)
- Canlıya geçmek için gerçek API bilgilerini girip modu "Canlı" yapın
