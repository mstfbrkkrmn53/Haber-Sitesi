<?php
/**
 * SEO sınıfı
 * 
 * Bu sınıf, SEO ile ilgili işlemleri yönetir.
 */
class SEO {
    private $db;
    private $siteSettings;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->siteSettings = $this->db->query("SELECT * FROM site_ayarlari")->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Meta etiketlerini oluşturur
     * 
     * @param string $title Sayfa başlığı
     * @param string $description Sayfa açıklaması
     * @param string $keywords Anahtar kelimeler
     * @param string $image Resim URL'i
     * @return string Meta etiketleri HTML'i
     */
    public function generateMetaTags($title, $description, $keywords = '', $image = '') {
        $siteName = $this->siteSettings['site_baslik'] ?? 'Haber Sitesi';
        $fullTitle = $title . ' - ' . $siteName;
        
        $meta = '<title>' . htmlspecialchars($fullTitle) . '</title>' . PHP_EOL;
        $meta .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
        
        if (!empty($keywords)) {
            $meta .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">' . PHP_EOL;
        }
        
        // Open Graph meta etiketleri
        $meta .= '<meta property="og:title" content="' . htmlspecialchars($fullTitle) . '">' . PHP_EOL;
        $meta .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
        $meta .= '<meta property="og:type" content="website">' . PHP_EOL;
        $meta .= '<meta property="og:url" content="' . htmlspecialchars(getCurrentUrl()) . '">' . PHP_EOL;
        
        if (!empty($image)) {
            $meta .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . PHP_EOL;
        }
        
        // Twitter Card meta etiketleri
        $meta .= '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
        $meta .= '<meta name="twitter:title" content="' . htmlspecialchars($fullTitle) . '">' . PHP_EOL;
        $meta .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
        
        if (!empty($image)) {
            $meta .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . PHP_EOL;
        }
        
        return $meta;
    }
    
    /**
     * Sitemap.xml dosyasını oluşturur
     * 
     * @return bool İşlem başarılı mı?
     */
    public function generateSitemap() {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
        
        // Ana sayfa
        $url = $xml->addChild('url');
        $url->addChild('loc', APP_URL);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '1.0');
        
        // Haberler
        $haberler = $this->db->query("SELECT slug, updated_at FROM haberler WHERE durum = 'yayinda'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($haberler as $haber) {
            $url = $xml->addChild('url');
            $url->addChild('loc', APP_URL . '/haber/' . $haber['slug']);
            $url->addChild('lastmod', date('Y-m-d', strtotime($haber['updated_at'])));
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');
        }
        
        // Kategoriler
        $kategoriler = $this->db->query("SELECT slug FROM kategoriler WHERE aktif = 1")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($kategoriler as $kategori) {
            $url = $xml->addChild('url');
            $url->addChild('loc', APP_URL . '/kategori/' . $kategori['slug']);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.7');
        }
        
        // Statik sayfalar
        $sayfalar = ['hakkimizda', 'iletisim', 'gizlilik', 'kullanim-kosullari'];
        foreach ($sayfalar as $sayfa) {
            $url = $xml->addChild('url');
            $url->addChild('loc', APP_URL . '/' . $sayfa);
            $url->addChild('changefreq', 'monthly');
            $url->addChild('priority', '0.5');
        }
        
        return file_put_contents(APP_ROOT . '/sitemap.xml', $xml->asXML());
    }
    
    /**
     * robots.txt dosyasını oluşturur
     * 
     * @return bool İşlem başarılı mı?
     */
    public function generateRobotsTxt() {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /cache/\n";
        $content .= "Disallow: /logs/\n";
        $content .= "Disallow: /uploads/\n\n";
        
        $content .= "Sitemap: " . APP_URL . "/sitemap.xml\n";
        
        return file_put_contents(APP_ROOT . '/robots.txt', $content);
    }
    
    /**
     * Canonical URL oluşturur
     * 
     * @param string $url Canonical URL
     * @return string Canonical link HTML'i
     */
    public function generateCanonical($url) {
        return '<link rel="canonical" href="' . htmlspecialchars($url) . '">' . PHP_EOL;
    }
    
    /**
     * JSON-LD yapısal veri oluşturur
     * 
     * @param array $data Yapısal veri
     * @return string JSON-LD HTML'i
     */
    public function generateJsonLd($data) {
        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . PHP_EOL;
    }
    
    /**
     * Haber için yapısal veri oluşturur
     * 
     * @param array $haber Haber verileri
     * @return string JSON-LD HTML'i
     */
    public function generateNewsJsonLd($haber) {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $haber['baslik'],
            'description' => $haber['ozet'],
            'image' => APP_URL . '/uploads/' . $haber['resim'],
            'datePublished' => $haber['yayin_tarihi'],
            'dateModified' => $haber['son_guncelleme'],
            'author' => [
                '@type' => 'Person',
                'name' => $haber['yazar_adi']
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteSettings['site_baslik'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => APP_URL . '/assets/img/logo.png'
                ]
            ]
        ];
        
        return $this->generateJsonLd($data);
    }
} 