<?php
class Category {
    private $db;
    private $table = "kategoriler";

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        // Slug oluştur
        $data['slug'] = $this->createSlug($data['ad']);
        
        // Varsayılan değerleri ekle
        $data['aktif'] = $data['aktif'] ?? 1;
        $data['sira'] = $data['sira'] ?? 0;
        
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        // Slug güncelleniyorsa yeni slug oluştur
        if (isset($data['ad'])) {
            $data['slug'] = $this->createSlug($data['ad']);
        }
        
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }

    public function delete($id) {
        // Kategoriye ait haberleri kontrol et
        $sql = "SELECT COUNT(*) as count FROM haberler WHERE kategori_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // Kategoriye ait haber varsa silme
        }
        
        return $this->db->delete($this->table, "id = ?", [$id]);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
        $stmt = $this->db->query($sql, [$slug]);
        return $stmt->fetch();
    }

    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($activeOnly) {
            $sql .= " WHERE aktif = 1";
        }
        
        $sql .= " ORDER BY sira ASC, ad ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getNewsCount($id) {
        $sql = "SELECT COUNT(*) as count FROM haberler WHERE kategori_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function updateOrder($orders) {
        $this->db->beginTransaction();
        
        try {
            foreach ($orders as $id => $order) {
                $this->update($id, ['sira' => $order]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function toggleStatus($id) {
        $category = $this->getById($id);
        
        if ($category) {
            $data = ['aktif' => $category['aktif'] ? 0 : 1];
            return $this->update($id, $data);
        }
        
        return false;
    }

    private function createSlug($name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        // Aynı slug'dan var mı kontrol et
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = ?";
        $stmt = $this->db->query($sql, [$slug]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }

    public function getStats() {
        $sql = "SELECT 
                    c.*,
                    COUNT(h.id) as haber_sayisi,
                    SUM(h.goruntulenme) as toplam_goruntulenme,
                    SUM(h.yorum_sayisi) as toplam_yorum
                FROM {$this->table} c
                LEFT JOIN haberler h ON c.id = h.kategori_id
                GROUP BY c.id
                ORDER BY c.sira ASC, c.ad ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getPopularCategories($limit = 5) {
        $sql = "SELECT 
                    c.*,
                    COUNT(h.id) as haber_sayisi,
                    SUM(h.goruntulenme) as toplam_goruntulenme
                FROM {$this->table} c
                LEFT JOIN haberler h ON c.id = h.kategori_id
                WHERE c.aktif = 1
                GROUP BY c.id
                ORDER BY toplam_goruntulenme DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    public function search($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ad LIKE ? OR aciklama LIKE ? 
                ORDER BY sira ASC, ad ASC";
        
        $params = ["%{$query}%", "%{$query}%"];
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getCategoryTree() {
        $categories = $this->getAll();
        $tree = [];
        
        foreach ($categories as $category) {
            $tree[] = [
                'id' => $category['id'],
                'text' => $category['ad'],
                'data' => $category
            ];
        }
        
        return $tree;
    }

    public function getBreadcrumb($id) {
        $category = $this->getById($id);
        
        if (!$category) {
            return [];
        }
        
        return [
            [
                'id' => $category['id'],
                'ad' => $category['ad'],
                'slug' => $category['slug']
            ]
        ];
    }

    public function getRelatedCategories($id, $limit = 5) {
        $sql = "SELECT DISTINCT c.* 
                FROM {$this->table} c
                INNER JOIN haberler h1 ON c.id = h1.kategori_id
                INNER JOIN haberler h2 ON h1.kategori_id != h2.kategori_id
                WHERE h2.kategori_id = ? AND c.id != ?
                GROUP BY c.id
                ORDER BY COUNT(h1.id) DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$id, $id, $limit]);
        return $stmt->fetchAll();
    }
} 