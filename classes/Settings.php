<?php
class Settings {
    private $db;
    private $table = "ayarlar";
    private $cache = [];
    private $cacheFile = "cache/settings.php";

    public function __construct($db) {
        $this->db = $db;
        $this->loadCache();
    }

    public function get($key, $default = null) {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $sql = "SELECT deger FROM {$this->table} WHERE anahtar = ?";
        $stmt = $this->db->query($sql, [$key]);
        $result = $stmt->fetch();

        if ($result) {
            $this->cache[$key] = $result['deger'];
            return $result['deger'];
        }

        return $default;
    }

    public function set($key, $value) {
        $sql = "INSERT INTO {$this->table} (anahtar, deger) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE deger = ?";
        
        $result = $this->db->query($sql, [$key, $value, $value]);
        
        if ($result) {
            $this->cache[$key] = $value;
            $this->saveCache();
            return true;
        }
        
        return false;
    }

    public function delete($key) {
        $sql = "DELETE FROM {$this->table} WHERE anahtar = ?";
        $result = $this->db->query($sql, [$key]);
        
        if ($result) {
            unset($this->cache[$key]);
            $this->saveCache();
            return true;
        }
        
        return false;
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY anahtar ASC";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['anahtar']] = $row['deger'];
            $this->cache[$row['anahtar']] = $row['deger'];
        }

        return $settings;
    }

    public function getGroup($prefix) {
        $sql = "SELECT * FROM {$this->table} WHERE anahtar LIKE ? ORDER BY anahtar ASC";
        $stmt = $this->db->query($sql, ["{$prefix}%"]);
        $results = $stmt->fetchAll();

        $settings = [];
        foreach ($results as $row) {
            $key = str_replace($prefix . '_', '', $row['anahtar']);
            $settings[$key] = $row['deger'];
        }

        return $settings;
    }

    public function setGroup($prefix, $settings) {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $fullKey = $prefix . '_' . $key;
                $this->set($fullKey, $value);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getSiteSettings() {
        return [
            'site' => $this->getGroup('site'),
            'contact' => $this->getGroup('contact'),
            'social' => $this->getGroup('social'),
            'mail' => $this->getGroup('mail'),
            'seo' => $this->getGroup('seo')
        ];
    }

    public function updateSiteSettings($settings) {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $group => $values) {
                $this->setGroup($group, $values);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    private function loadCache() {
        if (file_exists($this->cacheFile)) {
            $this->cache = include $this->cacheFile;
        }
    }

    private function saveCache() {
        $content = "<?php\nreturn " . var_export($this->cache, true) . ";\n";
        file_put_contents($this->cacheFile, $content);
    }

    public function clearCache() {
        $this->cache = [];
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function getDefaultSettings() {
        return [
            'site' => [
                'title' => 'Haber Sitesi',
                'description' => 'Güncel haberler ve son dakika gelişmeleri',
                'keywords' => 'haber, gündem, son dakika',
                'logo' => 'assets/img/logo.png',
                'favicon' => 'assets/img/favicon.ico',
                'theme' => 'default',
                'language' => 'tr',
                'timezone' => 'Europe/Istanbul',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
                'items_per_page' => '10',
                'maintenance_mode' => '0'
            ],
            'contact' => [
                'email' => 'info@habersitesi.com',
                'phone' => '+90 555 123 4567',
                'address' => 'İstanbul, Türkiye',
                'working_hours' => 'Pazartesi - Cuma: 09:00 - 18:00'
            ],
            'social' => [
                'facebook' => 'https://facebook.com/habersitesi',
                'twitter' => 'https://twitter.com/habersitesi',
                'instagram' => 'https://instagram.com/habersitesi',
                'youtube' => 'https://youtube.com/habersitesi'
            ],
            'mail' => [
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => '587',
                'smtp_user' => 'your-email@gmail.com',
                'smtp_pass' => 'your-password',
                'smtp_secure' => 'tls',
                'mail_from' => 'noreply@habersitesi.com',
                'mail_name' => 'Haber Sitesi'
            ],
            'seo' => [
                'meta_title' => 'Haber Sitesi - Güncel Haberler',
                'meta_description' => 'Güncel haberler, son dakika gelişmeleri ve daha fazlası',
                'meta_keywords' => 'haber, gündem, son dakika',
                'google_analytics' => '',
                'google_verification' => '',
                'robots_txt' => 'User-agent: *\nAllow: /',
                'sitemap_enabled' => '1',
                'sitemap_frequency' => 'daily',
                'sitemap_priority' => '0.8'
            ]
        ];
    }

    public function installDefaultSettings() {
        $defaultSettings = $this->getDefaultSettings();
        return $this->updateSiteSettings($defaultSettings);
    }
} 