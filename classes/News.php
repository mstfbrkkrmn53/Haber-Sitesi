<?php
class News {
    private $db;
    private $table = "haberler";

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        // Slug oluştur
        $data['slug'] = $this->createSlug($data['baslik']);
        
        // Varsayılan değerleri ekle
        $data['durum'] = $data['durum'] ?? 'taslak';
        $data['goruntulenme'] = 0;
        $data['yorum_sayisi'] = 0;
        $data['begeni_sayisi'] = 0;
        $data['paylasim_sayisi'] = 0;
        
        // Haberi ekle
        $newsId = $this->db->insert($this->table, $data);
        
        if ($newsId && isset($data['etiketler'])) {
            $this->addTags($newsId, $data['etiketler']);
        }
        
        return $newsId;
    }

    public function update($id, $data) {
        // Slug güncelleniyorsa yeni slug oluştur
        if (isset($data['baslik'])) {
            $data['slug'] = $this->createSlug($data['baslik']);
        }
        
        $result = $this->db->update($this->table, $data, "id = ?", [$id]);
        
        if ($result && isset($data['etiketler'])) {
            // Mevcut etiketleri sil
            $this->db->delete('haber_etiket', "haber_id = ?", [$id]);
            // Yeni etiketleri ekle
            $this->addTags($id, $data['etiketler']);
        }
        
        return $result;
    }

    public function delete($id) {
        // Önce etiketleri sil
        $this->db->delete('haber_etiket', "haber_id = ?", [$id]);
        // Sonra yorumları sil
        $this->db->delete('yorumlar', "haber_id = ?", [$id]);
        // En son haberi sil
        return $this->db->delete($this->table, "id = ?", [$id]);
    }

    public function getById($id) {
        $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                FROM {$this->table} h 
                LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                WHERE h.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        $news = $stmt->fetch();
        
        if ($news) {
            $news['etiketler'] = $this->getTags($id);
        }
        
        return $news;
    }

    public function getAll($limit = null, $offset = null, $filters = []) {
        $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                FROM {$this->table} h 
                LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON h.yazar_id = u.id";
        
        $params = [];
        $where = [];
        
        if (!empty($filters)) {
            if (isset($filters['kategori_id'])) {
                $where[] = "h.kategori_id = ?";
                $params[] = $filters['kategori_id'];
            }
            
            if (isset($filters['yazar_id'])) {
                $where[] = "h.yazar_id = ?";
                $params[] = $filters['yazar_id'];
            }
            
            if (isset($filters['durum'])) {
                $where[] = "h.durum = ?";
                $params[] = $filters['durum'];
            }
            
            if (isset($filters['baslik'])) {
                $where[] = "h.baslik LIKE ?";
                $params[] = "%{$filters['baslik']}%";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
        }
        
        $sql .= " ORDER BY h.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getBySlug($slug) {
        $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                FROM {$this->table} h 
                LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                WHERE h.slug = ?";
        
        $stmt = $this->db->query($sql, [$slug]);
        $news = $stmt->fetch();
        
        if ($news) {
            $news['etiketler'] = $this->getTags($news['id']);
            // Görüntülenme sayısını artır
            $this->incrementViewCount($news['id']);
        }
        
        return $news;
    }

    public function getByCategory($categoryId, $limit = null, $offset = null) {
        return $this->getAll($limit, $offset, ['kategori_id' => $categoryId]);
    }

    public function getByAuthor($authorId, $limit = null, $offset = null) {
        return $this->getAll($limit, $offset, ['yazar_id' => $authorId]);
    }

    public function getByTag($tagId, $limit = null, $offset = null) {
        $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                FROM {$this->table} h 
                LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                INNER JOIN haber_etiket he ON h.id = he.haber_id 
                WHERE he.etiket_id = ? 
                ORDER BY h.created_at DESC";
        
        $params = [$tagId];
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getPopular($limit = 10) {
        $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                FROM {$this->table} h 
                LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                WHERE h.durum = 'yayinda' 
                ORDER BY h.goruntulenme DESC 
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    public function getLatest($limit = 10) {
        $sql = "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
                FROM {$this->table} h 
                LEFT JOIN kategoriler k ON h.kategori_id = k.id 
                LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
                WHERE h.durum = 'yayinda' 
                ORDER BY h.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    public function incrementViewCount($id) {
        $sql = "UPDATE {$this->table} SET goruntulenme = goruntulenme + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function incrementCommentCount($id) {
        $sql = "UPDATE {$this->table} SET yorum_sayisi = yorum_sayisi + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function decrementCommentCount($id) {
        $sql = "UPDATE {$this->table} SET yorum_sayisi = yorum_sayisi - 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function incrementLikeCount($id) {
        $sql = "UPDATE {$this->table} SET begeni_sayisi = begeni_sayisi + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function decrementLikeCount($id) {
        $sql = "UPDATE {$this->table} SET begeni_sayisi = begeni_sayisi - 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function incrementShareCount($id) {
        $sql = "UPDATE {$this->table} SET paylasim_sayisi = paylasim_sayisi + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    private function createSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Aynı slug'dan var mı kontrol et
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = ?";
        $stmt = $this->db->query($sql, [$slug]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }

    private function addTags($newsId, $tags) {
        foreach ($tags as $tag) {
            // Etiket varsa ID'sini al, yoksa yeni etiket oluştur
            $sql = "SELECT id FROM etiketler WHERE slug = ?";
            $stmt = $this->db->query($sql, [$this->createSlug($tag)]);
            $tagData = $stmt->fetch();
            
            if (!$tagData) {
                $tagId = $this->db->insert('etiketler', [
                    'ad' => $tag,
                    'slug' => $this->createSlug($tag)
                ]);
            } else {
                $tagId = $tagData['id'];
            }
            
            // Haber-etiket ilişkisini ekle
            $this->db->insert('haber_etiket', [
                'haber_id' => $newsId,
                'etiket_id' => $tagId
            ]);
        }
    }

    private function getTags($newsId) {
        $sql = "SELECT e.* FROM etiketler e 
                INNER JOIN haber_etiket he ON e.id = he.etiket_id 
                WHERE he.haber_id = ?";
        
        $stmt = $this->db->query($sql, [$newsId]);
        return $stmt->fetchAll();
    }

    public function saveVersion($newsId, $data) {
        $news = $this->getById($newsId);
        
        if (!$news) {
            return false;
        }
        
        // Son versiyon numarasını al
        $sql = "SELECT MAX(versiyon) as max_version FROM haber_versiyonlari WHERE haber_id = ?";
        $stmt = $this->db->query($sql, [$newsId]);
        $result = $stmt->fetch();
        $version = ($result['max_version'] ?? 0) + 1;
        
        // Versiyonu kaydet
        $versionData = [
            'haber_id' => $newsId,
            'baslik' => $data['baslik'],
            'ozet' => $data['ozet'],
            'icerik' => $data['icerik'],
            'yazar_id' => $data['yazar_id'],
            'versiyon' => $version
        ];
        
        return $this->db->insert('haber_versiyonlari', $versionData);
    }

    public function getVersions($newsId) {
        $sql = "SELECT hv.*, u.ad_soyad as yazar_adi 
                FROM haber_versiyonlari hv 
                LEFT JOIN kullanicilar u ON hv.yazar_id = u.id 
                WHERE hv.haber_id = ? 
                ORDER BY hv.versiyon DESC";
        
        $stmt = $this->db->query($sql, [$newsId]);
        return $stmt->fetchAll();
    }

    public function getVersion($newsId, $version) {
        $sql = "SELECT hv.*, u.ad_soyad as yazar_adi 
                FROM haber_versiyonlari hv 
                LEFT JOIN kullanicilar u ON hv.yazar_id = u.id 
                WHERE hv.haber_id = ? AND hv.versiyon = ?";
        
        $stmt = $this->db->query($sql, [$newsId, $version]);
        return $stmt->fetch();
    }

    public function restoreVersion($newsId, $version) {
        $versionData = $this->getVersion($newsId, $version);
        
        if (!$versionData) {
            return false;
        }
        
        // Mevcut versiyonu kaydet
        $currentNews = $this->getById($newsId);
        $this->saveVersion($newsId, $currentNews);
        
        // Versiyonu geri yükle
        $data = [
            'baslik' => $versionData['baslik'],
            'ozet' => $versionData['ozet'],
            'icerik' => $versionData['icerik']
        ];
        
        return $this->update($newsId, $data);
    }
} 