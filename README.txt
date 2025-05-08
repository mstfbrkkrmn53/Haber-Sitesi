PHP Haber Sitesi Kurulumu
========================

1. Sistem Gereksinimleri:
-------------------------
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- mod_rewrite modülü (Apache için)
- PDO PHP eklentisi
- GD/Imagick PHP eklentisi (görsel işleme için)

2. Kurulum Adımları:
--------------------
a) Veritabanı Kurulumu:
   - MySQL'de 'haber_db' adında bir veritabanı oluşturun:
     ```sql
     CREATE DATABASE haber_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
     ```
   - haber.sql dosyasını içe aktarın:
     ```bash
     mysql -u root -p haber_db < haber.sql
     ```
   - Veritabanı bağlantı bilgilerini proje.env dosyasında güncelleyin:
     ```env
     DB_HOST=localhost
     DB_NAME=haber_db
     DB_USER=root
     DB_PASS=your-password
     ```

b) Dosya Yapılandırması:
   - proje.env dosyasını .env olarak kopyalayın:
     ```bash
     cp proje.env .env
     ```
   - .env dosyasındaki ayarları kendi ortamınıza göre düzenleyin
   - Gerekli dizinlerin yazma izinlerini ayarlayın:
     ```bash
     chmod 755 uploads cache logs
     ```

c) Mail Yapılandırması:
   - Gmail hesabınızda "Uygulama Şifreleri" oluşturun
   - .env dosyasında mail ayarlarını güncelleyin:
     ```env
     MAIL_HOST=smtp.gmail.com
     MAIL_PORT=587
     MAIL_USERNAME=your-email@gmail.com
     MAIL_PASSWORD=your-app-password
     MAIL_ENCRYPTION=tls
     ```

d) Güvenlik Ayarları:
   - .htaccess dosyasını kontrol edin
   - SSL sertifikası kurulumu (önerilen)
   - Güvenlik başlıklarını yapılandırın
   - API anahtarlarını oluşturun ve .env dosyasına ekleyin

3. Dizin Yapısı:
---------------
- /admin/           : Yönetici paneli
- /assets/          : CSS, JS ve medya dosyaları
- /cache/           : Önbellek dosyaları
- /classes/         : PHP sınıf dosyaları
- /logs/            : Log dosyaları
- /uploads/         : Yüklenen medya dosyaları
- /vendor/          : Composer bağımlılıkları (varsa)

4. Önemli Dosyalar:
------------------
- config.php        : Ana yapılandırma dosyası
- functions.php     : Yardımcı fonksiyonlar
- .htaccess         : Apache yapılandırması
- .env              : Ortam değişkenleri

5. Sınıflar:
-----------
- Admin.php         : Yönetici işlemleri
- Cache.php         : Önbellek yönetimi
- Category.php      : Kategori işlemleri
- Comment.php       : Yorum işlemleri
- Database.php      : Veritabanı işlemleri
- ErrorHandler.php  : Hata yönetimi
- Media.php         : Medya işlemleri
- News.php          : Haber işlemleri
- Notification.php  : Bildirim sistemi
- SEO.php           : SEO yönetimi
- Security.php      : Güvenlik işlemleri
- Settings.php      : Ayarlar yönetimi
- Statistics.php    : İstatistik işlemleri
- User.php          : Kullanıcı işlemleri

6. Varsayılan Giriş Bilgileri:
------------------------------
- Admin Paneli:
  * URL: /admin
  * Kullanıcı adı: admin
  * Şifre: 123456

7. Özellikler:
-------------
- Çok dilli destek
- SEO optimizasyonu
- Responsive tasarım
- Gelişmiş arama
- Yorum sistemi
- Bildirim sistemi
- İstatistik takibi
- Güvenlik önlemleri
- Önbellek sistemi
- Medya yönetimi
- Kullanıcı yönetimi
- Rol tabanlı yetkilendirme

8. Güvenlik:
-----------
- XSS koruması
- CSRF koruması
- SQL injection koruması
- Rate limiting
- Güvenli şifreleme
- Oturum güvenliği
- Dosya yükleme güvenliği

9. Bakım:
--------
- Log dosyalarını düzenli kontrol edin
- Veritabanı yedeklerini alın
- Güvenlik güncellemelerini takip edin
- Önbelleği temizleyin
- Dosya izinlerini kontrol edin

10. Önemli Yapılandırmalar:
--------------------------
a) Veritabanı:
   - MySQL karakter seti: utf8mb4
   - Collation: utf8mb4_turkish_ci
   - InnoDB motoru kullanımı
   - İndeksler ve foreign key'ler aktif

b) PHP Ayarları:
   - memory_limit = 256M
   - upload_max_filesize = 10M
   - post_max_size = 10M
   - max_execution_time = 300
   - display_errors = Off (production)
   - error_reporting = E_ALL (development)

c) Apache/Nginx:
   - mod_rewrite aktif
   - SSL yapılandırması
   - Gzip sıkıştırma
   - Browser caching
   - Security headers

11. Sorun Giderme:
-----------------
a) Veritabanı Bağlantı Hatası:
   - Veritabanı bilgilerini kontrol edin
   - MySQL servisinin çalıştığından emin olun
   - Kullanıcı yetkilerini kontrol edin

b) Dosya Yükleme Hatası:
   - Dizin izinlerini kontrol edin
   - PHP upload limitlerini kontrol edin
   - Dosya boyutu limitlerini kontrol edin

c) Mail Gönderim Hatası:
   - SMTP ayarlarını kontrol edin
   - Gmail uygulama şifresini kontrol edin
   - Port numarasını kontrol edin
