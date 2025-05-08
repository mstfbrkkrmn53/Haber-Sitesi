<?php
class Comment {
    private $db;
    private $table = "yorumlar";

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        // Varsayılan değerleri ekle
        $data['durum'] = $data['durum'] ?? 'beklemede';
        $data['begeni_sayisi'] = 0;
        $data['ip_adresi'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Yorumu ekle
        $commentId = $this->db->insert($this->table, $data);
        
        if ($commentId) {
            // Haberin yorum sayısını artır
            $this->incrementCommentCount($data['haber_id']);
        }
        
        return $commentId;
    }

    public function update($id, $data) {
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }

    public function delete($id) {
        $comment = $this->getById($id);
        
        if ($comment) {
            // Haberin yorum sayısını azalt
            $this->decrementCommentCount($comment['haber_id']);
            
            // Alt yorumları da sil
            $this->deleteReplies($id);
            
            // Yorumu sil
            return $this->db->delete($this->table, "id = ?", [$id]);
        }
        
        return false;
    }

    public function getById($id) {
        $sql = "SELECT y.*, h.baslik as haber_baslik, u.ad_soyad as kullanici_adi 
                FROM {$this->table} y 
                LEFT JOIN haberler h ON y.haber_id = h.id 
                LEFT JOIN kullanicilar u ON y.kullanici_id = u.id 
                WHERE y.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getAll($limit = null, $offset = null, $filters = []) {
        $sql = "SELECT y.*, h.baslik as haber_baslik, u.ad_soyad as kullanici_adi 
                FROM {$this->table} y 
                LEFT JOIN haberler h ON y.haber_id = h.id 
                LEFT JOIN kullanicilar u ON y.kullanici_id = u.id";
        
        $params = [];
        $where = [];
        
        if (!empty($filters)) {
            if (isset($filters['haber_id'])) {
                $where[] = "y.haber_id = ?";
                $params[] = $filters['haber_id'];
            }
            
            if (isset($filters['kullanici_id'])) {
                $where[] = "y.kullanici_id = ?";
                $params[] = $filters['kullanici_id'];
            }
            
            if (isset($filters['durum'])) {
                $where[] = "y.durum = ?";
                $params[] = $filters['durum'];
            }
            
            if (isset($filters['yorum'])) {
                $where[] = "y.yorum LIKE ?";
                $params[] = "%{$filters['yorum']}%";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
        }
        
        $sql .= " ORDER BY y.created_at DESC";
        
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

    public function getByNews($newsId, $limit = null, $offset = null) {
        return $this->getAll($limit, $offset, ['haber_id' => $newsId]);
    }

    public function getByUser($userId, $limit = null, $offset = null) {
        return $this->getAll($limit, $offset, ['kullanici_id' => $userId]);
    }

    public function getReplies($commentId) {
        $sql = "SELECT y.*, u.ad_soyad as kullanici_adi 
                FROM {$this->table} y 
                LEFT JOIN kullanicilar u ON y.kullanici_id = u.id 
                WHERE y.ust_yorum_id = ? 
                ORDER BY y.created_at ASC";
        
        $stmt = $this->db->query($sql, [$commentId]);
        return $stmt->fetchAll();
    }

    public function approve($id) {
        $data = ['durum' => 'onaylandi'];
        return $this->update($id, $data);
    }

    public function reject($id) {
        $data = ['durum' => 'reddedildi'];
        return $this->update($id, $data);
    }

    public function markAsSpam($id) {
        $data = ['durum' => 'spam'];
        return $this->update($id, $data);
    }

    public function incrementLikeCount($id) {
        $sql = "UPDATE {$this->table} SET begeni_sayisi = begeni_sayisi + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function decrementLikeCount($id) {
        $sql = "UPDATE {$this->table} SET begeni_sayisi = begeni_sayisi - 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    private function incrementCommentCount($newsId) {
        $sql = "UPDATE haberler SET yorum_sayisi = yorum_sayisi + 1 WHERE id = ?";
        return $this->db->query($sql, [$newsId]);
    }

    private function decrementCommentCount($newsId) {
        $sql = "UPDATE haberler SET yorum_sayisi = yorum_sayisi - 1 WHERE id = ?";
        return $this->db->query($sql, [$newsId]);
    }

    private function deleteReplies($commentId) {
        $replies = $this->getReplies($commentId);
        
        foreach ($replies as $reply) {
            $this->delete($reply['id']);
        }
    }

    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE durum = 'beklemede'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getSpamCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE durum = 'spam'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as toplam,
                    SUM(CASE WHEN durum = 'beklemede' THEN 1 ELSE 0 END) as beklemede,
                    SUM(CASE WHEN durum = 'onaylandi' THEN 1 ELSE 0 END) as onaylandi,
                    SUM(CASE WHEN durum = 'reddedildi' THEN 1 ELSE 0 END) as reddedildi,
                    SUM(CASE WHEN durum = 'spam' THEN 1 ELSE 0 END) as spam,
                    SUM(begeni_sayisi) as toplam_begeni
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    public function getRecentComments($limit = 10) {
        $sql = "SELECT y.*, h.baslik as haber_baslik, u.ad_soyad as kullanici_adi 
                FROM {$this->table} y 
                LEFT JOIN haberler h ON y.haber_id = h.id 
                LEFT JOIN kullanicilar u ON y.kullanici_id = u.id 
                ORDER BY y.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    public function getMostLikedComments($limit = 10) {
        $sql = "SELECT y.*, h.baslik as haber_baslik, u.ad_soyad as kullanici_adi 
                FROM {$this->table} y 
                LEFT JOIN haberler h ON y.haber_id = h.id 
                LEFT JOIN kullanicilar u ON y.kullanici_id = u.id 
                WHERE y.durum = 'onaylandi' 
                ORDER BY y.begeni_sayisi DESC 
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
} 