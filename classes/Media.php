<?php
class Media {
    private $db;
    private $table = "medya";
    private $uploadDir = "uploads/";
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'application/pdf'];
    private $maxFileSize = 10485760; // 10MB

    public function __construct($db) {
        $this->db = $db;
    }

    public function upload($file, $data = []) {
        // Dosya kontrolü
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Geçersiz dosya yükleme.');
        }

        // Dosya tipi kontrolü
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('Desteklenmeyen dosya tipi.');
        }

        // Dosya boyutu kontrolü
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('Dosya boyutu çok büyük.');
        }

        // Dosya adı oluştur
        $fileName = $this->generateFileName($file['name']);
        $filePath = $this->uploadDir . $fileName;

        // Dosyayı taşı
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Dosya yüklenirken bir hata oluştu.');
        }

        // Veritabanına kaydet
        $data['dosya_adi'] = $fileName;
        $data['dosya_yolu'] = $filePath;
        $data['dosya_tipi'] = $file['type'];
        $data['dosya_boyutu'] = $file['size'];
        $data['yukleyen_id'] = $data['yukleyen_id'] ?? null;
        $data['yukleme_tarihi'] = date('Y-m-d H:i:s');

        // Resim ise boyutları al
        if (strpos($file['type'], 'image/') === 0) {
            $dimensions = getimagesize($filePath);
            $data['genislik'] = $dimensions[0];
            $data['yukseklik'] = $dimensions[1];
        }

        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }

    public function delete($id) {
        $media = $this->getById($id);
        
        if ($media) {
            // Dosyayı sil
            if (file_exists($media['dosya_yolu'])) {
                unlink($media['dosya_yolu']);
            }
            
            // Veritabanından sil
            return $this->db->delete($this->table, "id = ?", [$id]);
        }
        
        return false;
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (isset($filters['dosya_tipi'])) {
            $sql .= " AND dosya_tipi LIKE ?";
            $params[] = "%{$filters['dosya_tipi']}%";
        }

        if (isset($filters['kategori'])) {
            $sql .= " AND kategori = ?";
            $params[] = $filters['kategori'];
        }

        if (isset($filters['yukleyen_id'])) {
            $sql .= " AND yukleyen_id = ?";
            $params[] = $filters['yukleyen_id'];
        }

        $sql .= " ORDER BY yukleme_tarihi DESC";

        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getByType($type) {
        $sql = "SELECT * FROM {$this->table} WHERE dosya_tipi LIKE ? ORDER BY yukleme_tarihi DESC";
        $stmt = $this->db->query($sql, ["%{$type}%"]);
        return $stmt->fetchAll();
    }

    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE kategori = ? ORDER BY yukleme_tarihi DESC";
        $stmt = $this->db->query($sql, [$category]);
        return $stmt->fetchAll();
    }

    public function getByUploader($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE yukleyen_id = ? ORDER BY yukleme_tarihi DESC";
        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetchAll();
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM {$this->table} ORDER BY yukleme_tarihi DESC LIMIT ?";
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    public function search($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE dosya_adi LIKE ? OR aciklama LIKE ? 
                ORDER BY yukleme_tarihi DESC";
        
        $params = ["%{$query}%", "%{$query}%"];
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as toplam_dosya,
                    SUM(dosya_boyutu) as toplam_boyut,
                    COUNT(CASE WHEN dosya_tipi LIKE 'image/%' THEN 1 END) as resim_sayisi,
                    COUNT(CASE WHEN dosya_tipi LIKE 'video/%' THEN 1 END) as video_sayisi,
                    COUNT(CASE WHEN dosya_tipi LIKE 'application/pdf' THEN 1 END) as pdf_sayisi
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    private function generateFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    public function resizeImage($id, $width, $height) {
        $media = $this->getById($id);
        
        if (!$media || strpos($media['dosya_tipi'], 'image/') !== 0) {
            return false;
        }

        $sourcePath = $media['dosya_yolu'];
        $info = getimagesize($sourcePath);

        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        $newImage = imagecreatetruecolor($width, $height);
        
        // PNG için şeffaflığı koru
        if ($info[2] == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

        // Yeni dosya adı oluştur
        $newFileName = $this->generateFileName($media['dosya_adi']);
        $newFilePath = $this->uploadDir . $newFileName;

        // Yeni dosyayı kaydet
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $newFilePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $newFilePath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $newFilePath);
                break;
        }

        imagedestroy($source);
        imagedestroy($newImage);

        // Yeni medya kaydı oluştur
        $data = [
            'dosya_adi' => $newFileName,
            'dosya_yolu' => $newFilePath,
            'dosya_tipi' => $media['dosya_tipi'],
            'dosya_boyutu' => filesize($newFilePath),
            'genislik' => $width,
            'yukseklik' => $height,
            'kategori' => $media['kategori'],
            'yukleyen_id' => $media['yukleyen_id'],
            'aciklama' => "Resized version of {$media['dosya_adi']}"
        ];

        return $this->db->insert($this->table, $data);
    }

    public function getImageThumbnail($id, $width = 150, $height = 150) {
        return $this->resizeImage($id, $width, $height);
    }
} 