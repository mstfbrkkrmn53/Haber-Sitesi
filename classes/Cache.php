<?php
class Cache {
    private $db;
    private $cacheDir = 'cache/';
    private $defaultExpiration = 3600; // 1 saat
    private $prefix = 'cache_';

    public function __construct($db) {
        $this->db = $db;
        $this->initCacheDir();
    }

    private function initCacheDir() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function set($key, $value, $expiration = null) {
        $expiration = $expiration ?? $this->defaultExpiration;
        $data = [
            'value' => $value,
            'expiration' => time() + $expiration
        ];

        $cacheFile = $this->getCacheFile($key);
        return file_put_contents($cacheFile, serialize($data));
    }

    public function get($key, $default = null) {
        $cacheFile = $this->getCacheFile($key);

        if (!file_exists($cacheFile)) {
            return $default;
        }

        $data = unserialize(file_get_contents($cacheFile));

        if ($data['expiration'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function delete($key) {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        return true;
    }

    public function clear() {
        $files = glob($this->cacheDir . $this->prefix . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    public function clearExpired() {
        $files = glob($this->cacheDir . $this->prefix . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                if ($data['expiration'] < time()) {
                    unlink($file);
                }
            }
        }
        return true;
    }

    public function has($key) {
        return $this->get($key) !== null;
    }

    public function remember($key, $callback, $expiration = null) {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $expiration);

        return $value;
    }

    public function increment($key, $value = 1) {
        $current = $this->get($key, 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }

    public function decrement($key, $value = 1) {
        return $this->increment($key, -$value);
    }

    public function getMultiple($keys, $default = null) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple($values, $expiration = null) {
        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $expiration)) {
                $result = false;
            }
        }
        return $result;
    }

    public function deleteMultiple($keys) {
        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }
        return $result;
    }

    public function getStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'expired_files' => 0,
            'expired_size' => 0
        ];

        $files = glob($this->cacheDir . $this->prefix . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $stats['total_files']++;
                $stats['total_size'] += filesize($file);

                $data = unserialize(file_get_contents($file));
                if ($data['expiration'] < time()) {
                    $stats['expired_files']++;
                    $stats['expired_size'] += filesize($file);
                }
            }
        }

        return $stats;
    }

    public function cacheQuery($key, $sql, $params = [], $expiration = null) {
        return $this->remember($key, function() use ($sql, $params) {
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
        }, $expiration);
    }

    public function cacheNews($newsId, $expiration = null) {
        $key = "news_{$newsId}";
        return $this->remember($key, function() use ($newsId) {
            $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                    FROM haberler h 
                    LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                    LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                    WHERE h.id = ?";
            
            $stmt = $this->db->query($sql, [$newsId]);
            return $stmt->fetch();
        }, $expiration);
    }

    public function cacheCategory($categoryId, $expiration = null) {
        $key = "category_{$categoryId}";
        return $this->remember($key, function() use ($categoryId) {
            $sql = "SELECT * FROM kategoriler WHERE id = ?";
            $stmt = $this->db->query($sql, [$categoryId]);
            return $stmt->fetch();
        }, $expiration);
    }

    public function cacheSettings($expiration = null) {
        return $this->remember('settings', function() {
            $sql = "SELECT * FROM ayarlar";
            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll();

            $settings = [];
            foreach ($results as $row) {
                $settings[$row['anahtar']] = $row['deger'];
            }

            return $settings;
        }, $expiration);
    }

    public function cachePopularNews($limit = 10, $expiration = null) {
        $key = "popular_news_{$limit}";
        return $this->remember($key, function() use ($limit) {
            $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                    FROM haberler h 
                    LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                    LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                    WHERE h.durum = 'yayinda' 
                    ORDER BY h.goruntulenme DESC 
                    LIMIT ?";
            
            $stmt = $this->db->query($sql, [$limit]);
            return $stmt->fetchAll();
        }, $expiration);
    }

    public function cacheLatestNews($limit = 10, $expiration = null) {
        $key = "latest_news_{$limit}";
        return $this->remember($key, function() use ($limit) {
            $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                    FROM haberler h 
                    LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                    LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                    WHERE h.durum = 'yayinda' 
                    ORDER BY h.created_at DESC 
                    LIMIT ?";
            
            $stmt = $this->db->query($sql, [$limit]);
            return $stmt->fetchAll();
        }, $expiration);
    }

    private function getCacheFile($key) {
        return $this->cacheDir . $this->prefix . md5($key) . '.cache';
    }
} 