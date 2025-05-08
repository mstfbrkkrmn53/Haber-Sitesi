<?php
/**
 * Bildirim sınıfı
 * 
 * Bu sınıf, kullanıcı bildirimlerini yönetir.
 */
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Yeni bildirim ekler
     * 
     * @param int $userId Kullanıcı ID
     * @param string $title Bildirim başlığı
     * @param string $message Bildirim mesajı
     * @param string $type Bildirim tipi (info, success, warning, error)
     * @param array $data Ek veriler
     * @return bool İşlem başarılı mı?
     */
    public function add($userId, $title, $message, $type = 'info', $data = []) {
        $query = "INSERT INTO bildirimler (kullanici_id, baslik, mesaj, tip, data) 
                 VALUES (:user_id, :title, :message, :type, :data)";
        
        return $this->db->query($query)
            ->bind(':user_id', $userId)
            ->bind(':title', $title)
            ->bind(':message', $message)
            ->bind(':type', $type)
            ->bind(':data', json_encode($data))
            ->execute();
    }
    
    /**
     * Bildirimi okundu olarak işaretler
     * 
     * @param int $notificationId Bildirim ID
     * @return bool İşlem başarılı mı?
     */
    public function markAsRead($notificationId) {
        $query = "UPDATE bildirimler SET okundu = 1 WHERE id = :id";
        
        return $this->db->query($query)
            ->bind(':id', $notificationId)
            ->execute();
    }
    
    /**
     * Tüm bildirimleri okundu olarak işaretler
     * 
     * @param int $userId Kullanıcı ID
     * @return bool İşlem başarılı mı?
     */
    public function markAllAsRead($userId) {
        $query = "UPDATE bildirimler SET okundu = 1 WHERE kullanici_id = :user_id AND okundu = 0";
        
        return $this->db->query($query)
            ->bind(':user_id', $userId)
            ->execute();
    }
    
    /**
     * Bildirimi siler
     * 
     * @param int $notificationId Bildirim ID
     * @return bool İşlem başarılı mı?
     */
    public function delete($notificationId) {
        $query = "DELETE FROM bildirimler WHERE id = :id";
        
        return $this->db->query($query)
            ->bind(':id', $notificationId)
            ->execute();
    }
    
    /**
     * Tüm bildirimleri siler
     * 
     * @param int $userId Kullanıcı ID
     * @return bool İşlem başarılı mı?
     */
    public function deleteAll($userId) {
        $query = "DELETE FROM bildirimler WHERE kullanici_id = :user_id";
        
        return $this->db->query($query)
            ->bind(':user_id', $userId)
            ->execute();
    }
    
    /**
     * Bildirimleri getirir
     * 
     * @param int $userId Kullanıcı ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @param bool $unreadOnly Sadece okunmamış bildirimler
     * @return array Bildirimler
     */
    public function get($userId, $limit = 10, $offset = 0, $unreadOnly = false) {
        $query = "SELECT * FROM bildirimler WHERE kullanici_id = :user_id";
        
        if ($unreadOnly) {
            $query .= " AND okundu = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        return $this->db->query($query)
            ->bind(':user_id', $userId)
            ->bind(':limit', $limit)
            ->bind(':offset', $offset)
            ->resultSet();
    }
    
    /**
     * Okunmamış bildirim sayısını getirir
     * 
     * @param int $userId Kullanıcı ID
     * @return int Okunmamış bildirim sayısı
     */
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM bildirimler WHERE kullanici_id = :user_id AND okundu = 0";
        
        $result = $this->db->query($query)
            ->bind(':user_id', $userId)
            ->single();
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Bildirim detaylarını getirir
     * 
     * @param int $notificationId Bildirim ID
     * @return array Bildirim detayları
     */
    public function getDetails($notificationId) {
        $query = "SELECT * FROM bildirimler WHERE id = :id";
        
        return $this->db->query($query)
            ->bind(':id', $notificationId)
            ->single();
    }
    
    /**
     * Bildirim şablonunu işler
     * 
     * @param string $template Şablon
     * @param array $data Veriler
     * @return string İşlenmiş şablon
     */
    private function processTemplate($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    /**
     * E-posta bildirimi gönderir
     * 
     * @param int $userId Kullanıcı ID
     * @param string $subject Konu
     * @param string $template Şablon
     * @param array $data Veriler
     * @return bool İşlem başarılı mı?
     */
    public function sendEmail($userId, $subject, $template, $data = []) {
        $user = $this->db->query("SELECT email, ad_soyad FROM kullanicilar WHERE id = :id")
            ->bind(':id', $userId)
            ->single();
        
        if (!$user) {
            return false;
        }
        
        $message = $this->processTemplate($template, $data);
        
        return sendMail($user['email'], $subject, $message);
    }
    
    /**
     * Push bildirimi gönderir
     * 
     * @param int $userId Kullanıcı ID
     * @param string $title Başlık
     * @param string $message Mesaj
     * @param array $data Ek veriler
     * @return bool İşlem başarılı mı?
     */
    public function sendPush($userId, $title, $message, $data = []) {
        $user = $this->db->query("SELECT push_token FROM kullanicilar WHERE id = :id")
            ->bind(':id', $userId)
            ->single();
        
        if (!$user || !$user['push_token']) {
            return false;
        }
        
        // Firebase Cloud Messaging veya başka bir push servisi entegrasyonu
        // Bu örnek için sadece veritabanına kaydediyoruz
        return $this->add($userId, $title, $message, 'push', $data);
    }
    
    /**
     * SMS bildirimi gönderir
     * 
     * @param int $userId Kullanıcı ID
     * @param string $message Mesaj
     * @return bool İşlem başarılı mı?
     */
    public function sendSMS($userId, $message) {
        $user = $this->db->query("SELECT telefon FROM kullanicilar WHERE id = :id")
            ->bind(':id', $userId)
            ->single();
        
        if (!$user || !$user['telefon']) {
            return false;
        }
        
        // SMS servisi entegrasyonu
        // Bu örnek için sadece veritabanına kaydediyoruz
        return $this->add($userId, 'SMS Bildirimi', $message, 'sms');
    }
} 