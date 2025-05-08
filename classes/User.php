<?php
class User {
    private $db;
    private $table = "kullanicilar";

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE kullanici_adi = ? AND aktif = 1";
        $stmt = $this->db->query($sql, [$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['sifre'])) {
            // Son giriş tarihini güncelle
            $this->updateLastLogin($user['id']);
            
            // Güvenlik logunu kaydet
            $this->logSecurityAction($user['id'], 'Giriş', 'Başarılı giriş yapıldı', true);
            
            return $user;
        }

        // Başarısız giriş denemesini logla
        $this->logSecurityAction(null, 'Giriş', 'Başarısız giriş denemesi', false);
        
        return false;
    }

    public function register($data) {
        // Şifreyi hashle
        $data['sifre'] = password_hash($data['sifre'], PASSWORD_DEFAULT);
        
        // Varsayılan değerleri ekle
        $data['rol'] = $data['rol'] ?? 'user';
        $data['aktif'] = 1;
        
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        // Şifre güncelleniyorsa hashle
        if (isset($data['sifre'])) {
            $data['sifre'] = password_hash($data['sifre'], PASSWORD_DEFAULT);
        }
        
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }

    public function delete($id) {
        return $this->db->delete($this->table, "id = ?", [$id]);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $stmt = $this->db->query($sql, [$limit, $offset]);
            } else {
                $stmt = $this->db->query($sql, [$limit]);
            }
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll();
    }

    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET son_giris = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function changePassword($id, $currentPassword, $newPassword) {
        $user = $this->getById($id);
        
        if (!$user || !password_verify($currentPassword, $user['sifre'])) {
            return false;
        }
        
        $data = [
            'sifre' => password_hash($newPassword, PASSWORD_DEFAULT)
        ];
        
        $result = $this->update($id, $data);
        
        if ($result) {
            $this->logSecurityAction($id, 'Şifre Değiştirme', 'Şifre başarıyla değiştirildi', true);
        }
        
        return $result;
    }

    public function enableTwoFactor($id) {
        $code = rand(100000, 999999);
        $data = [
            'iki_faktorlu' => 1,
            'iki_faktorlu_kod' => $code
        ];
        
        return $this->update($id, $data);
    }

    public function disableTwoFactor($id) {
        $data = [
            'iki_faktorlu' => 0,
            'iki_faktorlu_kod' => null
        ];
        
        return $this->update($id, $data);
    }

    public function verifyTwoFactor($id, $code) {
        $user = $this->getById($id);
        
        if (!$user || !$user['iki_faktorlu'] || $user['iki_faktorlu_kod'] != $code) {
            return false;
        }
        
        return true;
    }

    private function logSecurityAction($userId, $action, $detail, $success) {
        $data = [
            'kullanici_id' => $userId,
            'ip_adresi' => $_SERVER['REMOTE_ADDR'],
            'islem' => $action,
            'detay' => $detail,
            'basarili' => $success ? 1 : 0
        ];
        
        return $this->db->insert('guvenlik_loglari', $data);
    }

    public function getSecurityLogs($userId = null, $limit = 10) {
        $sql = "SELECT * FROM guvenlik_loglari";
        $params = [];
        
        if ($userId !== null) {
            $sql .= " WHERE kullanici_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getUserGroups($userId) {
        $sql = "SELECT g.* FROM kullanici_gruplari g 
                INNER JOIN kullanici_grup_iliskisi i ON g.id = i.grup_id 
                WHERE i.kullanici_id = ?";
        
        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetchAll();
    }

    public function addToGroup($userId, $groupId) {
        $data = [
            'kullanici_id' => $userId,
            'grup_id' => $groupId
        ];
        
        return $this->db->insert('kullanici_grup_iliskisi', $data);
    }

    public function removeFromGroup($userId, $groupId) {
        return $this->db->delete('kullanici_grup_iliskisi', 
            "kullanici_id = ? AND grup_id = ?", 
            [$userId, $groupId]
        );
    }

    public function hasPermission($userId, $permission) {
        $sql = "SELECT g.yetkiler FROM kullanici_gruplari g 
                INNER JOIN kullanici_grup_iliskisi i ON g.id = i.grup_id 
                WHERE i.kullanici_id = ?";
        
        $stmt = $this->db->query($sql, [$userId]);
        $groups = $stmt->fetchAll();
        
        foreach ($groups as $group) {
            $permissions = json_decode($group['yetkiler'], true);
            if (isset($permissions[$permission]) && $permissions[$permission]) {
                return true;
            }
        }
        
        return false;
    }
} 