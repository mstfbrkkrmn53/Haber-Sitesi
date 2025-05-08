<?php
class Statistics {
    private $db;
    private $table = "istatistikler";
    private $visitorTable = "ziyaretci_loglari";

    public function __construct($db) {
        $this->db = $db;
    }

    public function logVisit($data) {
        $data['ip_adresi'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['tarih'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->visitorTable, $data);
    }

    public function getDailyStats($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT 
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret,
                    COUNT(DISTINCT haber_id) as farkli_haber,
                    COUNT(DISTINCT kullanici_id) as aktif_kullanici
                FROM {$this->visitorTable}
                WHERE DATE(tarih) = ?";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetch();
    }

    public function getMonthlyStats($year = null, $month = null) {
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }

        $sql = "SELECT 
                    DATE(tarih) as tarih,
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret,
                    COUNT(DISTINCT haber_id) as farkli_haber,
                    COUNT(DISTINCT kullanici_id) as aktif_kullanici
                FROM {$this->visitorTable}
                WHERE YEAR(tarih) = ? AND MONTH(tarih) = ?
                GROUP BY DATE(tarih)
                ORDER BY tarih ASC";
        
        $stmt = $this->db->query($sql, [$year, $month]);
        return $stmt->fetchAll();
    }

    public function getYearlyStats($year = null) {
        if (!$year) {
            $year = date('Y');
        }

        $sql = "SELECT 
                    MONTH(tarih) as ay,
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret,
                    COUNT(DISTINCT haber_id) as farkli_haber,
                    COUNT(DISTINCT kullanici_id) as aktif_kullanici
                FROM {$this->visitorTable}
                WHERE YEAR(tarih) = ?
                GROUP BY MONTH(tarih)
                ORDER BY ay ASC";
        
        $stmt = $this->db->query($sql, [$year]);
        return $stmt->fetchAll();
    }

    public function getPopularNews($limit = 10, $period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    h.id,
                    h.baslik,
                    h.slug,
                    h.resim,
                    COUNT(v.id) as goruntulenme,
                    COUNT(DISTINCT v.ip_adresi) as tekil_goruntulenme
                FROM haberler h
                LEFT JOIN {$this->visitorTable} v ON h.id = v.haber_id
                WHERE v.tarih >= ?
                GROUP BY h.id
                ORDER BY goruntulenme DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$date, $limit]);
        return $stmt->fetchAll();
    }

    public function getPopularCategories($limit = 5, $period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    k.id,
                    k.ad,
                    k.slug,
                    COUNT(v.id) as goruntulenme,
                    COUNT(DISTINCT v.ip_adresi) as tekil_goruntulenme
                FROM kategoriler k
                LEFT JOIN haberler h ON k.id = h.kategori_id
                LEFT JOIN {$this->visitorTable} v ON h.id = v.haber_id
                WHERE v.tarih >= ?
                GROUP BY k.id
                ORDER BY goruntulenme DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$date, $limit]);
        return $stmt->fetchAll();
    }

    public function getVisitorStats($period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret,
                    COUNT(DISTINCT haber_id) as farkli_haber,
                    COUNT(DISTINCT kullanici_id) as aktif_kullanici,
                    AVG(TIMESTAMPDIFF(SECOND, tarih, 
                        (SELECT MIN(tarih) FROM {$this->visitorTable} v2 
                         WHERE v2.ip_adresi = v1.ip_adresi 
                         AND v2.tarih > v1.tarih))) as ortalama_sure
                FROM {$this->visitorTable} v1
                WHERE tarih >= ?";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetch();
    }

    public function getDeviceStats($period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    CASE 
                        WHEN user_agent LIKE '%Mobile%' THEN 'Mobile'
                        WHEN user_agent LIKE '%Tablet%' THEN 'Tablet'
                        ELSE 'Desktop'
                    END as cihaz,
                    COUNT(*) as sayi
                FROM {$this->visitorTable}
                WHERE tarih >= ?
                GROUP BY cihaz
                ORDER BY sayi DESC";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getBrowserStats($period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    CASE 
                        WHEN user_agent LIKE '%Chrome%' THEN 'Chrome'
                        WHEN user_agent LIKE '%Firefox%' THEN 'Firefox'
                        WHEN user_agent LIKE '%Safari%' THEN 'Safari'
                        WHEN user_agent LIKE '%Edge%' THEN 'Edge'
                        WHEN user_agent LIKE '%MSIE%' OR user_agent LIKE '%Trident%' THEN 'Internet Explorer'
                        ELSE 'Other'
                    END as tarayici,
                    COUNT(*) as sayi
                FROM {$this->visitorTable}
                WHERE tarih >= ?
                GROUP BY tarayici
                ORDER BY sayi DESC";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getOSStats($period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    CASE 
                        WHEN user_agent LIKE '%Windows%' THEN 'Windows'
                        WHEN user_agent LIKE '%Mac%' THEN 'MacOS'
                        WHEN user_agent LIKE '%Linux%' THEN 'Linux'
                        WHEN user_agent LIKE '%Android%' THEN 'Android'
                        WHEN user_agent LIKE '%iOS%' THEN 'iOS'
                        ELSE 'Other'
                    END as isletim_sistemi,
                    COUNT(*) as sayi
                FROM {$this->visitorTable}
                WHERE tarih >= ?
                GROUP BY isletim_sistemi
                ORDER BY sayi DESC";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getReferrerStats($period = 'daily') {
        $date = date('Y-m-d');
        
        if ($period == 'weekly') {
            $date = date('Y-m-d', strtotime('-7 days'));
        } elseif ($period == 'monthly') {
            $date = date('Y-m-d', strtotime('-30 days'));
        }

        $sql = "SELECT 
                    referrer,
                    COUNT(*) as sayi
                FROM {$this->visitorTable}
                WHERE tarih >= ? AND referrer IS NOT NULL
                GROUP BY referrer
                ORDER BY sayi DESC
                LIMIT 10";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getHourlyStats($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT 
                    HOUR(tarih) as saat,
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret
                FROM {$this->visitorTable}
                WHERE DATE(tarih) = ?
                GROUP BY HOUR(tarih)
                ORDER BY saat ASC";
        
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getWeeklyStats($year = null, $week = null) {
        if (!$year) {
            $year = date('Y');
        }
        if (!$week) {
            $week = date('W');
        }

        $sql = "SELECT 
                    DAYNAME(tarih) as gun,
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret
                FROM {$this->visitorTable}
                WHERE YEAR(tarih) = ? AND WEEK(tarih) = ?
                GROUP BY DAYNAME(tarih)
                ORDER BY FIELD(DAYNAME(tarih), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        
        $stmt = $this->db->query($sql, [$year, $week]);
        return $stmt->fetchAll();
    }

    public function getYearlyComparison($year1 = null, $year2 = null) {
        if (!$year1) {
            $year1 = date('Y');
        }
        if (!$year2) {
            $year2 = $year1 - 1;
        }

        $sql = "SELECT 
                    MONTH(tarih) as ay,
                    YEAR(tarih) as yil,
                    COUNT(*) as toplam_ziyaret,
                    COUNT(DISTINCT ip_adresi) as tekil_ziyaret
                FROM {$this->visitorTable}
                WHERE YEAR(tarih) IN (?, ?)
                GROUP BY YEAR(tarih), MONTH(tarih)
                ORDER BY yil ASC, ay ASC";
        
        $stmt = $this->db->query($sql, [$year1, $year2]);
        return $stmt->fetchAll();
    }
} 