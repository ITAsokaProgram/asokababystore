<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;


class AppLogger {
    private $logger;
    private $logDir;
    private $canWrite;
    
    public function __construct($logFile = 'app.log') {
        $this->logDir = __DIR__ . '/../../logs';
        $this->canWrite = $this->checkWritePermissions();
        
        if ($this->canWrite) {
            // Create logs directory if it doesn't exist
            if (!is_dir($this->logDir)) {
                @mkdir($this->logDir, 0755, true);
            }
            
            $logPath = $this->logDir . '/' . $logFile;
            
            try {
                // Create Monolog logger with proper configuration
                $this->logger = new MonologLogger('AppLogger', [], [], new DateTimeZone('Asia/Jakarta'));
                
                // Add rotating file handler (keeps logs for 30 days, max 10 files)
                $handler = new RotatingFileHandler($logPath, 30, MonologLogger::DEBUG, true, 0664);
                
                // Custom formatter with timestamp, level, and message
                $formatter = new LineFormatter(
                    "[%datetime%] [%level_name%] %message%\n",
                    'Y-m-d H:i:s'
                );
                $handler->setFormatter($formatter);
                
                $this->logger->pushHandler($handler);
                
            } catch (Exception $e) {
                // If Monolog fails, fall back to simple logging
                $this->canWrite = false;
            }
        }
    }
    
    /**
     * Check if we can write to logs directory
     */
    private function checkWritePermissions() {
        // Try to create directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            try {
                if (!@mkdir($this->logDir, 0755, true)) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }
        
        // Check if directory is writable
        if (!is_writable($this->logDir)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Safe log method that doesn't throw exceptions
     */
    private function safeLog($level, $message) {
        if (!$this->canWrite || !isset($this->logger)) {
            return;
        }
        
        try {
            switch ($level) {
                case 'info':
                    $this->logger->info($message);
                    break;
                case 'warning':
                    $this->logger->warning($message);
                    break;
                case 'error':
                    $this->logger->error($message);
                    break;
                case 'debug':
                    $this->logger->debug($message);
                    break;
                case 'critical':
                    $this->logger->critical($message);
                    break;
            }
        } catch (Exception $e) {
            // Silently fail - don't throw exceptions for logging
        }
    }
    
    /**
     * Log info message
     */
    public function info($message) {
        $this->safeLog('info', $message);
    }
    
    /**
     * Log success message
     */
    public function success($message) {
        $this->safeLog('info', "✅ " . $message);
    }
    
    /**
     * Log warning message
     */
    public function warning($message) {
        $this->safeLog('warning', "⚠️ " . $message);
    }
    
    /**
     * Log error message
     */
    public function error($message) {
        $this->safeLog('error', "❌ " . $message);
    }
    
    /**
     * Log debug message
     */
    public function debug($message) {
        $this->safeLog('debug', "🔍 " . $message);
    }
    
    /**
     * Log critical error
     */
    public function critical($message) {
        $this->safeLog('critical', "💥 " . $message);
    }
    
    /**
     * Log with context data
     */
    public function logWithContext($level, $message, array $context = []) {
        if (!$this->canWrite || !isset($this->logger)) {
            return;
        }
        
        try {
            $this->logger->log($level, $message, $context);
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Get log file path
     */
    public function getLogPath() {
        return $this->logDir;
    }
    
    /**
     * Clear old logs (older than specified days)
     */
    public function clearOldLogs($days = 30) {
        if (!$this->canWrite) return;
        
        try {
            $files = glob($this->logDir . '/*.log');
            $cutoff = time() - ($days * 24 * 60 * 60);
            
            foreach ($files as $file) {
                if (filemtime($file) < $cutoff) {
                    @unlink($file);
                }
            }
        } catch (Exception $e) {
            // Silently fail
        }
    }
}
?> 