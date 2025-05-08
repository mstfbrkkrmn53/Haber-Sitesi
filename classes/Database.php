<?php
class Database {
    private static $instance = null;
    private $connection;
    private $statement;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            $this->logError('Veritabanı bağlantı hatası: ' . $e->getMessage());
            throw new Exception('Veritabanına bağlanılamadı.');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql) {
        try {
            $this->statement = $this->connection->prepare($sql);
            return $this;
        } catch (PDOException $e) {
            $this->logError('Sorgu hazırlama hatası: ' . $e->getMessage());
            throw new Exception('Sorgu hazırlanamadı.');
        }
    }
    
    public function bind($param, $value, $type = null) {
        try {
            if (is_null($type)) {
                switch (true) {
                    case is_int($value):
                        $type = PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                }
            }
            $this->statement->bindValue($param, $value, $type);
            return $this;
        } catch (PDOException $e) {
            $this->logError('Parametre bağlama hatası: ' . $e->getMessage());
            throw new Exception('Parametre bağlanamadı.');
        }
    }
    
    public function execute() {
        try {
            return $this->statement->execute();
        } catch (PDOException $e) {
            $this->logError('Sorgu çalıştırma hatası: ' . $e->getMessage());
            throw new Exception('Sorgu çalıştırılamadı.');
        }
    }
    
    public function resultSet() {
        $this->execute();
        return $this->statement->fetchAll();
    }
    
    public function single() {
        $this->execute();
        return $this->statement->fetch();
    }
    
    public function rowCount() {
        return $this->statement->rowCount();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    private function logError($message) {
        $logFile = __DIR__ . '/../logs/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    public function __destruct() {
        $this->statement = null;
        $this->connection = null;
    }
} 