<?php
class MenuSync {
    private $logger;
    private $dbHelper;
    private $fileHelper;
    private $validator;
    
    public function __construct($connection) {
        $this->logger = new AppLogger('menu_sync.log');
        $this->dbHelper = new DatabaseHelper($connection, $this->logger);
        $this->fileHelper = new FileHelper($this->logger);
        $this->validator = new DataValidator();
    }
    
    /**
     * Sync menu from JSON file to database
     */
    public function syncFromJson($jsonFilePath) {
        try {
            $this->logger->info("🚀 Memulai proses sync menu ke database...");
            
            // Read and validate JSON file
            $menuList = $this->fileHelper->readJsonFile($jsonFilePath);
            
            // Validate array structure
            if (!DataValidator::validateArrayNotEmpty($menuList)) {
                throw new Exception("Array menu kosong");
            }
            
            $this->logger->info("✅ JSON berhasil di-parse. Total menu: " . count($menuList));
            
            // Process each menu
            $stats = $this->processMenuList($menuList);
            
            // Display final results
            $this->displayResults($stats);
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logger->critical("💥 FATAL ERROR: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process menu list
     */
    private function processMenuList($menuList) {
        $stats = [
            'success' => 0,
            'error' => 0,
            'skipped' => 0,
            'total' => count($menuList),
            'errors' => []
        ];
        
        foreach ($menuList as $index => $menu) {
            try {
                // Validate menu data
                if (!$this->validateMenuData($menu)) {
                    $stats['skipped']++;
                    $this->logger->warning("⚠️ Menu #$index dilewati - data tidak lengkap");
                    continue;
                }
                
                // Sanitize menu data
                $sanitizedMenu = $this->sanitizeMenuData($menu);
                
                // Insert or update menu
                $result = $this->insertMenu($sanitizedMenu);
                
                if ($result) {
                    $stats['success']++;
                    $this->logger->success("✅ Menu berhasil: {$sanitizedMenu['menu_code']} - {$sanitizedMenu['menu_nama']}");
                } else {
                    $stats['error']++;
                    $errorMsg = "❌ Gagal insert menu: {$sanitizedMenu['menu_code']}";
                    $stats['errors'][] = $errorMsg;
                    $this->logger->error($errorMsg);
                }
                
            } catch (Exception $e) {
                $stats['error']++;
                $errorMsg = "❌ Error pada menu #$index: " . $e->getMessage();
                $stats['errors'][] = $errorMsg;
                $this->logger->error($errorMsg);
            }
        }
        
        return $stats;
    }
    
    /**
     * Validate menu data
     */
    private function validateMenuData($menu) {
        $requiredFields = ['menu_code', 'menu_nama', 'endpoint_url'];
        return DataValidator::validateRequiredFields($menu, $requiredFields);
    }
    
    /**
     * Sanitize menu data
     */
    private function sanitizeMenuData($menu) {
        $fields = ['menu_code', 'menu_nama', 'endpoint_url'];
        return DataValidator::sanitizeArray($menu, $fields);
    }
    
    /**
     * Insert menu to database
     */
    private function insertMenu($menuData) {
        $table = 'menu_website';
        $updateFields = ['menu_nama', 'endpoint_url'];
        
        return $this->dbHelper->insertOrUpdate($table, $menuData, $updateFields);
    }
    
    /**
     * Display final results
     */
    private function displayResults($stats) {
        $this->logger->info("🎯 PROSES SELESAI");
        $this->logger->info("📊 Statistik:");
        $this->logger->info("   ✅ Berhasil: {$stats['success']}");
        $this->logger->info("   ❌ Error: {$stats['error']}");
        $this->logger->info("   ⚠️ Dilewati: {$stats['skipped']}");
        $this->logger->info("   📝 Total diproses: {$stats['total']}");
        
        if (!empty($stats['errors'])) {
            $this->logger->info("📋 Detail Error:");
            foreach ($stats['errors'] as $error) {
                $this->logger->error("   $error");
            }
        }
        
        // Display final message
        if ($stats['error'] === 0 && $stats['success'] > 0) {
            echo "\n🎉 SUKSES: Berhasil sync {$stats['success']} menu ke database!\n";
        } elseif ($stats['success'] > 0) {
            echo "\n⚠️ SEBAGIAN SUKSES: {$stats['success']} berhasil, {$stats['error']} error\n";
        } else {
            echo "\n❌ GAGAL: Tidak ada menu yang berhasil disync\n";
        }
    }
    
    /**
     * Get menu statistics
     */
    public function getMenuStats() {
        try {
            $data = $this->dbHelper->select('menu_website', ['COUNT(*) as total']);
            return $data[0]['total'] ?? 0;
        } catch (Exception $e) {
            $this->logger->error("Error getting menu stats: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all menus
     */
    public function getAllMenus() {
        try {
            return $this->dbHelper->select('menu_website', ['*'], '', []);
        } catch (Exception $e) {
            $this->logger->error("Error getting all menus: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete menu by code
     */
    public function deleteMenu($menuCode) {
        try {
            return $this->dbHelper->delete('menu_website', 'menu_code = ?', [$menuCode]);
        } catch (Exception $e) {
            $this->logger->error("Error deleting menu: " . $e->getMessage());
            return false;
        }
    }
}
?> 