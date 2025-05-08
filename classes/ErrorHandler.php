<?php
class ErrorHandler {
    private static $instance = null;
    private $db;
    private $logFile;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->logFile = __DIR__ . '/../logs/error.log';
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $error = [
            'type' => $this->getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        
        $this->logError($error);
        
        if (ini_get('display_errors')) {
            $this->displayError($error);
        }
        
        return true;
    }
    
    public function handleException($exception) {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        $this->logError($error);
        
        if (ini_get('display_errors')) {
            $this->displayError($error);
        }
    }
    
    public function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }
    
    private function getErrorType($type) {
        switch($type) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }
    
    private function logError($error) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] {$error['type']}: {$error['message']} in {$error['file']} on line {$error['line']}\n";
        
        if (isset($error['trace'])) {
            $logMessage .= "Stack trace:\n{$error['trace']}\n";
        }
        
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // Veritabanına da kaydet
        $query = "INSERT INTO guvenlik_loglari (ip_adresi, islem, detay, basarili) 
                 VALUES (:ip_adresi, :islem, :detay, :basarili)";
        
        $this->db->query($query)
            ->bind(':ip_adresi', $_SERVER['REMOTE_ADDR'])
            ->bind(':islem', 'error')
            ->bind(':detay', json_encode($error))
            ->bind(':basarili', false)
            ->execute();
    }
    
    private function displayError($error) {
        if (php_sapi_name() === 'cli') {
            echo "Error: {$error['message']} in {$error['file']} on line {$error['line']}\n";
            if (isset($error['trace'])) {
                echo "Stack trace:\n{$error['trace']}\n";
            }
        } else {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: text/html; charset=utf-8');
            }
            
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Error</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; }
                    .error h1 { color: #721c24; margin-top: 0; }
                    .error pre { background: #fff; padding: 10px; border-radius: 3px; overflow: auto; }
                </style>
            </head>
            <body>
                <div class="error">
                    <h1>Error</h1>
                    <p><strong>Type:</strong> ' . htmlspecialchars($error['type']) . '</p>
                    <p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>
                    <p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>
                    <p><strong>Line:</strong> ' . htmlspecialchars($error['line']) . '</p>';
            
            if (isset($error['trace'])) {
                echo '<h2>Stack Trace:</h2>
                      <pre>' . htmlspecialchars($error['trace']) . '</pre>';
            }
            
            echo '</div></body></html>';
        }
    }
} 