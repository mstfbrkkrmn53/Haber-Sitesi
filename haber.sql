-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS haber_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE haber_db;

-- Kategoriler tablosu
CREATE TABLE IF NOT EXISTS kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    aciklama TEXT,
    resim VARCHAR(255),
    sira INT DEFAULT 0,
    aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'editor', 'user') NOT NULL DEFAULT 'user',
    profil_resmi VARCHAR(255),
    telefon VARCHAR(20),
    adres TEXT,
    son_giris TIMESTAMP NULL,
    iki_faktorlu BOOLEAN DEFAULT FALSE,
    iki_faktorlu_kod VARCHAR(6),
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Kullanıcı grupları tablosu
CREATE TABLE IF NOT EXISTS kullanici_gruplari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    aciklama TEXT,
    yetkiler JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Kullanıcı-grup ilişki tablosu
CREATE TABLE IF NOT EXISTS kullanici_grup_iliskisi (
    kullanici_id INT NOT NULL,
    grup_id INT NOT NULL,
    PRIMARY KEY (kullanici_id, grup_id),
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (grup_id) REFERENCES kullanici_gruplari(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Haberler tablosu
CREATE TABLE IF NOT EXISTS haberler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    ozet TEXT NOT NULL,
    icerik TEXT NOT NULL,
    resim VARCHAR(255),
    kategori_id INT NOT NULL,
    yazar_id INT NOT NULL,
    durum ENUM('taslak', 'beklemede', 'yayinda', 'arsiv') DEFAULT 'taslak',
    yayin_tarihi TIMESTAMP NULL,
    son_guncelleme TIMESTAMP NULL,
    goruntulenme INT DEFAULT 0,
    yorum_sayisi INT DEFAULT 0,
    begeni_sayisi INT DEFAULT 0,
    paylasim_sayisi INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    onay BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id) ON DELETE CASCADE,
    FOREIGN KEY (yazar_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Haber versiyonları tablosu
CREATE TABLE IF NOT EXISTS haber_versiyonlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    haber_id INT NOT NULL,
    baslik VARCHAR(255) NOT NULL,
    ozet TEXT NOT NULL,
    icerik TEXT NOT NULL,
    yazar_id INT NOT NULL,
    versiyon INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (haber_id) REFERENCES haberler(id) ON DELETE CASCADE,
    FOREIGN KEY (yazar_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Yorumlar tablosu
CREATE TABLE IF NOT EXISTS yorumlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    haber_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    ust_yorum_id INT NULL,
    yorum TEXT NOT NULL,
    begeni_sayisi INT DEFAULT 0,
    durum ENUM('beklemede', 'onaylandi', 'reddedildi', 'spam') DEFAULT 'beklemede',
    ip_adresi VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (haber_id) REFERENCES haberler(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (ust_yorum_id) REFERENCES yorumlar(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Etiketler tablosu
CREATE TABLE IF NOT EXISTS etiketler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Haber-Etiket ilişki tablosu
CREATE TABLE IF NOT EXISTS haber_etiket (
    haber_id INT NOT NULL,
    etiket_id INT NOT NULL,
    PRIMARY KEY (haber_id, etiket_id),
    FOREIGN KEY (haber_id) REFERENCES haberler(id) ON DELETE CASCADE,
    FOREIGN KEY (etiket_id) REFERENCES etiketler(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Medya tablosu
CREATE TABLE IF NOT EXISTS medya (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dosya_adi VARCHAR(255) NOT NULL,
    dosya_yolu VARCHAR(255) NOT NULL,
    dosya_tipi VARCHAR(50) NOT NULL,
    boyut INT NOT NULL,
    genislik INT,
    yukseklik INT,
    kategori VARCHAR(50),
    etiketler VARCHAR(255),
    yukleyen_id INT NOT NULL,
    yukleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (yukleyen_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Ayarlar tablosu
CREATE TABLE IF NOT EXISTS ayarlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anahtar VARCHAR(50) NOT NULL UNIQUE,
    deger TEXT NOT NULL,
    aciklama TEXT,
    grup VARCHAR(50) DEFAULT 'genel',
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- İstatistikler tablosu
CREATE TABLE IF NOT EXISTS istatistikler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    sayfa VARCHAR(255) NOT NULL,
    ziyaretci_sayisi INT DEFAULT 0,
    tekil_ziyaretci INT DEFAULT 0,
    goruntulenme INT DEFAULT 0,
    ortalama_sure INT DEFAULT 0,
    cikis_orani FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Ziyaretçi logları tablosu
CREATE TABLE IF NOT EXISTS ziyaretci_loglari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_adresi VARCHAR(45) NOT NULL,
    user_agent TEXT,
    sayfa VARCHAR(255) NOT NULL,
    referrer VARCHAR(255),
    ulke VARCHAR(100),
    sehir VARCHAR(100),
    tarayici VARCHAR(100),
    isletim_sistemi VARCHAR(100),
    cihaz VARCHAR(100),
    ziyaret_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_tarih (ip_adresi, ziyaret_tarihi),
    INDEX idx_tarih (ziyaret_tarihi),
    INDEX idx_sayfa (sayfa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Bildirimler tablosu
CREATE TABLE IF NOT EXISTS bildirimler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    baslik VARCHAR(255) NOT NULL,
    mesaj TEXT NOT NULL,
    tip ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    okundu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- E-posta şablonları tablosu
CREATE TABLE IF NOT EXISTS email_sablonlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    konu VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    degiskenler TEXT,
    aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- E-posta kuyruğu tablosu
CREATE TABLE IF NOT EXISTS email_kuyrugu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alici VARCHAR(255) NOT NULL,
    konu VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    durum ENUM('beklemede', 'gonderildi', 'hata') DEFAULT 'beklemede',
    hata_mesaji TEXT,
    deneme_sayisi INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    gonderim_tarihi TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Güvenlik logları tablosu
CREATE TABLE IF NOT EXISTS guvenlik_loglari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT,
    ip_adresi VARCHAR(45) NOT NULL,
    islem VARCHAR(255) NOT NULL,
    detay TEXT,
    basarili BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Yedekleme logları tablosu
CREATE TABLE IF NOT EXISTS yedekleme_loglari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dosya_adi VARCHAR(255) NOT NULL,
    boyut INT NOT NULL,
    tur ENUM('tam', 'kismi') NOT NULL,
    durum ENUM('basarili', 'basarisiz') NOT NULL,
    hata_mesaji TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Rate limiting tablosu
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `ip_address` varchar(45) NOT NULL,
  `requests` int(11) NOT NULL DEFAULT 1,
  `last_request` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- İndeksler
ALTER TABLE `haberler` ADD INDEX `idx_kategori_id` (`kategori_id`);
ALTER TABLE `haberler` ADD INDEX `idx_yazar_id` (`yazar_id`);
ALTER TABLE `haberler` ADD INDEX `idx_created_at` (`created_at`);
ALTER TABLE `haberler` ADD INDEX `idx_durum` (`durum`);
ALTER TABLE `haberler` ADD INDEX `idx_slug` (`slug`);

ALTER TABLE `yorumlar` ADD INDEX `idx_haber_id` (`haber_id`);
ALTER TABLE `yorumlar` ADD INDEX `idx_kullanici_id` (`kullanici_id`);
ALTER TABLE `yorumlar` ADD INDEX `idx_created_at` (`created_at`);
ALTER TABLE `yorumlar` ADD INDEX `idx_durum` (`durum`);

ALTER TABLE `kullanicilar` ADD INDEX `idx_kullanici_adi` (`kullanici_adi`);
ALTER TABLE `kullanicilar` ADD INDEX `idx_email` (`email`);
ALTER TABLE `kullanicilar` ADD INDEX `idx_rol` (`rol`);

ALTER TABLE `kategoriler` ADD INDEX `idx_slug` (`slug`);
ALTER TABLE `kategoriler` ADD INDEX `idx_sira` (`sira`);

ALTER TABLE `etiketler` ADD INDEX `idx_slug` (`slug`);

ALTER TABLE `medya` ADD INDEX `idx_yukleyen_id` (`yukleyen_id`);
ALTER TABLE `medya` ADD INDEX `idx_dosya_tipi` (`dosya_tipi`);

ALTER TABLE `bildirimler` ADD INDEX `idx_kullanici_id` (`kullanici_id`);
ALTER TABLE `bildirimler` ADD INDEX `idx_okundu` (`okundu`);

ALTER TABLE `ziyaretci_loglari` ADD INDEX `idx_ip_adresi` (`ip_adresi`);
ALTER TABLE `ziyaretci_loglari` ADD INDEX `idx_ziyaret_tarihi` (`ziyaret_tarihi`);

ALTER TABLE `guvenlik_loglari` ADD INDEX `idx_kullanici_id` (`kullanici_id`);
ALTER TABLE `guvenlik_loglari` ADD INDEX `idx_ip_adresi` (`ip_adresi`);
ALTER TABLE `guvenlik_loglari` ADD INDEX `idx_created_at` (`created_at`);

-- Örnek kategoriler
INSERT INTO kategoriler (ad, slug, aciklama, sira) VALUES
('Gündem', 'gundem', 'Güncel haberler ve son dakika gelişmeleri', 1),
('Ekonomi', 'ekonomi', 'Ekonomi ve finans dünyasından haberler', 2),
('Spor', 'spor', 'Spor dünyasından en son haberler', 3),
('Teknoloji', 'teknoloji', 'Teknoloji dünyasından yenilikler', 4),
('Sağlık', 'saglik', 'Sağlık ve yaşam haberleri', 5),
('Eğitim', 'egitim', 'Eğitim dünyasından gelişmeler', 6);

-- Örnek kullanıcı grupları
INSERT INTO kullanici_gruplari (ad, aciklama, yetkiler) VALUES
('Yöneticiler', 'Tam yetkili yönetici grubu', '{"haber_yonetimi": true, "kullanici_yonetimi": true, "yorum_yonetimi": true, "ayar_yonetimi": true}'),
('Editörler', 'İçerik yönetimi yapan editör grubu', '{"haber_yonetimi": true, "yorum_yonetimi": true}'),
('Yazarlar', 'Haber yazan yazar grubu', '{"haber_yonetimi": true}');

-- Örnek kullanıcılar (şifre: 123456)
INSERT INTO kullanicilar (kullanici_adi, email, sifre, ad_soyad, rol, profil_resmi, telefon, adres) VALUES
('admin', 'admin@haber.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin', 'admin.jpg', '+90 555 123 4567', 'İstanbul, Türkiye'),
('editor', 'editor@haber.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Editör User', 'editor', 'editor.jpg', '+90 555 234 5678', 'Ankara, Türkiye'),
('user', 'user@haber.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Normal User', 'user', 'user.jpg', '+90 555 345 6789', 'İzmir, Türkiye');

-- Kullanıcı-grup ilişkileri
INSERT INTO kullanici_grup_iliskisi (kullanici_id, grup_id) VALUES
(1, 1), -- Admin -> Yöneticiler
(2, 2), -- Editor -> Editörler
(3, 3); -- User -> Yazarlar

-- Örnek haberler
INSERT INTO haberler (baslik, slug, ozet, icerik, kategori_id, yazar_id, durum, meta_title, meta_description, meta_keywords) VALUES
('Yeni Teknoloji Trendleri', 'yeni-teknoloji-trendleri', '2024 yılının öne çıkan teknoloji trendleri', 'Yapay zeka, blockchain ve metaverse gibi teknolojiler 2024 yılında da gelişmeye devam ediyor...', 4, 1, 'yayinda', '2024 Teknoloji Trendleri', '2024 yılının en önemli teknoloji trendleri ve yenilikleri', 'teknoloji, yapay zeka, blockchain, metaverse'),
('Ekonomide Son Durum', 'ekonomide-son-durum', 'Merkez Bankası faiz kararını açıkladı', 'Merkez Bankası, enflasyonla mücadele kapsamında faiz oranlarını değiştirmedi...', 2, 2, 'yayinda', 'Ekonomi Haberleri', 'Güncel ekonomi haberleri ve finans dünyasından gelişmeler', 'ekonomi, finans, merkez bankası, faiz'),
('Spor Dünyasından Haberler', 'spor-dunyasindan-haberler', 'Transfer sezonu başladı', 'Futbol dünyasında transfer sezonu başladı. Büyük kulüpler yeni transferler için harekete geçti...', 3, 2, 'yayinda', 'Spor Haberleri', 'Güncel spor haberleri ve transfer gelişmeleri', 'spor, futbol, transfer, lig'),
('Sağlıklı Yaşam İpuçları', 'saglikli-yasam-ipuclari', 'Uzmanlardan sağlıklı yaşam önerileri', 'Uzmanlar, sağlıklı bir yaşam için düzenli egzersiz ve dengeli beslenmenin önemine dikkat çekiyor...', 5, 1, 'yayinda', 'Sağlık Haberleri', 'Sağlıklı yaşam için öneriler ve uzman tavsiyeleri', 'sağlık, yaşam, beslenme, egzersiz');

-- Örnek etiketler
INSERT INTO etiketler (ad, slug) VALUES
('Yapay Zeka', 'yapay-zeka'),
('Blockchain', 'blockchain'),
('Metaverse', 'metaverse'),
('Sağlık', 'saglik'),
('Spor', 'spor'),
('Ekonomi', 'ekonomi');

-- Örnek haber-etiket ilişkileri
INSERT INTO haber_etiket (haber_id, etiket_id) VALUES
(1, 1), -- Yeni Teknoloji Trendleri - Yapay Zeka
(1, 2), -- Yeni Teknoloji Trendleri - Blockchain
(1, 3), -- Yeni Teknoloji Trendleri - Metaverse
(4, 4); -- Sağlıklı Yaşam İpuçları - Sağlık

-- Örnek yorumlar
INSERT INTO yorumlar (haber_id, kullanici_id, yorum, durum, ip_adresi) VALUES
(1, 3, 'Çok faydalı bir yazı olmuş, teşekkürler.', 'onaylandi', '192.168.1.1'),
(2, 3, 'Ekonomi hakkında daha detaylı bilgi verilebilirdi.', 'onaylandi', '192.168.1.1'),
(3, 3, 'Transfer haberlerini takip etmeye devam edeceğim.', 'onaylandi', '192.168.1.1'),
(4, 3, 'Sağlıklı yaşam için öneriler çok değerli.', 'onaylandi', '192.168.1.1');

-- Örnek medya dosyaları
INSERT INTO medya (dosya_adi, dosya_yolu, dosya_tipi, boyut, genislik, yukseklik, kategori, etiketler, yukleyen_id) VALUES
('haber1.jpg', 'uploads/haber1.jpg', 'image/jpeg', 1024000, 800, 600, 'haber', 'teknoloji, yapay zeka', 1),
('haber2.jpg', 'uploads/haber2.jpg', 'image/jpeg', 2048000, 1200, 800, 'haber', 'ekonomi, finans', 2),
('haber3.jpg', 'uploads/haber3.jpg', 'image/jpeg', 1536000, 1000, 700, 'haber', 'spor, futbol', 2),
('haber4.jpg', 'uploads/haber4.jpg', 'image/jpeg', 768000, 600, 400, 'haber', 'sağlık, yaşam', 1);

-- Örnek site ayarları
INSERT INTO ayarlar (anahtar, deger, aciklama, grup) VALUES
('site_baslik', 'Haber Sitesi', 'Sitenin başlığı', 'genel'),
('site_aciklama', 'Güncel haberler ve son dakika gelişmeleri', 'Sitenin açıklaması', 'genel'),
('iletisim_email', 'info@haber.com', 'İletişim e-posta adresi', 'iletisim'),
('iletisim_telefon', '+90 555 123 4567', 'İletişim telefon numarası', 'iletisim'),
('adres', 'İstanbul, Türkiye', 'Sitenin adresi', 'iletisim'),
('facebook', 'https://facebook.com/habersitesi', 'Facebook sayfası', 'sosyal_medya'),
('twitter', 'https://twitter.com/habersitesi', 'Twitter sayfası', 'sosyal_medya'),
('instagram', 'https://instagram.com/habersitesi', 'Instagram sayfası', 'sosyal_medya');

-- Örnek e-posta şablonları
INSERT INTO email_sablonlari (baslik, konu, icerik, degiskenler) VALUES
('Hoş Geldiniz', 'Haber Sitesine Hoş Geldiniz', 'Merhaba {ad_soyad},\n\nHaber sitesine hoş geldiniz. Hesabınız başarıyla oluşturuldu.\n\nSaygılarımızla,\nHaber Sitesi Ekibi', '["ad_soyad", "kullanici_adi"]'),
('Şifre Sıfırlama', 'Şifre Sıfırlama Talebi', 'Merhaba {ad_soyad},\n\nŞifre sıfırlama talebiniz alındı. Yeni şifreniz: {yeni_sifre}\n\nSaygılarımızla,\nHaber Sitesi Ekibi', '["ad_soyad", "yeni_sifre"]');

-- Örnek bildirimler
INSERT INTO bildirimler (kullanici_id, baslik, mesaj, tip) VALUES
(1, 'Yeni Yorum', 'Haberinize yeni bir yorum yapıldı.', 'info'),
(2, 'Haber Onayı', 'Haberiniz onaylandı ve yayınlandı.', 'success'),
(3, 'Hesap Aktivasyonu', 'Hesabınız başarıyla aktifleştirildi.', 'success');

-- Örnek istatistikler
INSERT INTO istatistikler (tarih, sayfa, ziyaretci_sayisi, tekil_ziyaretci, goruntulenme, ortalama_sure, cikis_orani) VALUES
('2024-03-20', '/', 1000, 800, 1500, 180, 0.3),
('2024-03-20', '/haber/yeni-teknoloji-trendleri', 500, 400, 600, 240, 0.2),
('2024-03-20', '/haber/ekonomide-son-durum', 300, 250, 350, 200, 0.25);

-- Örnek ziyaretçi logları
INSERT INTO ziyaretci_loglari (ip_adresi, user_agent, sayfa, referrer, ulke, sehir, tarayici, isletim_sistemi, cihaz) VALUES
('192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '/', 'https://google.com', 'Türkiye', 'İstanbul', 'Chrome', 'Windows', 'Desktop'),
('192.168.1.2', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '/haber/yeni-teknoloji-trendleri', 'https://facebook.com', 'Türkiye', 'Ankara', 'Safari', 'iOS', 'Mobile');

-- Örnek güvenlik logları
INSERT INTO guvenlik_loglari (kullanici_id, ip_adresi, islem, detay, basarili) VALUES
(1, '192.168.1.1', 'Giriş', 'Başarılı giriş yapıldı', TRUE),
(2, '192.168.1.2', 'Şifre Değiştirme', 'Şifre başarıyla değiştirildi', TRUE),
(NULL, '192.168.1.3', 'Başarısız Giriş', 'Yanlış şifre denemesi', FALSE);

-- Örnek yedekleme logları
INSERT INTO yedekleme_loglari (dosya_adi, boyut, tur, durum) VALUES
('backup_20240320_001.sql', 1048576, 'tam', 'basarili'),
('backup_20240320_002.sql', 524288, 'kismi', 'basarili'); 