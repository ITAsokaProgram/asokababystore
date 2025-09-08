<?php
class FileHelper {
    private $logger;
    
    public function __construct($logger = null) {
        $this->logger = $logger;
    }
    
    /**
     * Read JSON file and validate
     */
    public function readJsonFile($filePath) {
        try {
            // Validate file
            DataValidator::validateFile($filePath);
            
            // Read file content
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new Exception("Gagal membaca file: $filePath");
            }
            
            // Parse JSON
            $data = DataValidator::validateJson($content);
            
            if ($this->logger) {
                $this->logger->info("✅ File JSON berhasil dibaca: $filePath");
            }
            
            return $data;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("❌ Error membaca file JSON: " . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Write JSON file
     */
    public function writeJsonFile($filePath, $data) {
        try {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if ($jsonData === false) {
                throw new Exception("Gagal encode JSON data");
            }
            
            $result = file_put_contents($filePath, $jsonData, LOCK_EX);
            
            if ($result === false) {
                throw new Exception("Gagal menulis file: $filePath");
            }
            
            if ($this->logger) {
                $this->logger->success("✅ File JSON berhasil ditulis: $filePath");
            }
            
            return true;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("❌ Error menulis file JSON: " . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Create directory if not exists
     */
    public function createDirectory($path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new Exception("Gagal membuat direktori: $path");
            }
        }
        return true;
    }
    
    /**
     * Check if file exists and is readable
     */
    public function fileExists($filePath) {
        return file_exists($filePath) && is_readable($filePath);
    }
    
    /**
     * Get file size
     */
    public function getFileSize($filePath) {
        if (!$this->fileExists($filePath)) {
            return false;
        }
        return filesize($filePath);
    }
    
    /**
     * Get file modification time
     */
    public function getFileModTime($filePath) {
        if (!$this->fileExists($filePath)) {
            return false;
        }
        return filemtime($filePath);
    }
    
    /**
     * Delete file
     */
    public function deleteFile($filePath) {
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                if ($this->logger) {
                    $this->logger->info("✅ File berhasil dihapus: $filePath");
                }
                return true;
            } else {
                throw new Exception("Gagal menghapus file: $filePath");
            }
        }
        return false;
    }
    
    /**
     * Copy file
     */
    public function copyFile($source, $destination) {
        if (!file_exists($source)) {
            throw new Exception("File sumber tidak ditemukan: $source");
        }
        
        if (!copy($source, $destination)) {
            throw new Exception("Gagal copy file dari $source ke $destination");
        }
        
        if ($this->logger) {
            $this->logger->info("✅ File berhasil di-copy: $source -> $destination");
        }
        
        return true;
    }
    
    /**
     * Move file
     */
    public function moveFile($source, $destination) {
        if (!file_exists($source)) {
            throw new Exception("File sumber tidak ditemukan: $source");
        }
        
        if (!rename($source, $destination)) {
            throw new Exception("Gagal move file dari $source ke $destination");
        }
        
        if ($this->logger) {
            $this->logger->info("✅ File berhasil di-move: $source -> $destination");
        }
        
        return true;
    }
}
?> 