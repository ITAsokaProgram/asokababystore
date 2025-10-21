<?php
/**
 * Reusable Menu Handler Component
 * Handles authentication, permission checks, and error handling for menu pages
 */

require_once __DIR__ . '/../../aa_kon_sett.php';
require_once __DIR__ . '/../auth/middleware_login.php';
require_once __DIR__ . '/../api/middleware/permission_access.php';

class MenuHandler {
    private $conn;
    private $logger;
    private $menu_code;
    private $user_id;
    private $token;
    private $message;
    
    public function __construct($menu_code) {
        global $conn;
        $this->conn = $conn;
        $this->menu_code = $menu_code;
        $this->token = $_COOKIE['admin_token'] ?? "";
        $this->message = "";
        
        // Create logger instance for this menu
        $this->logger = new AppLogger('menu_logging.log');
    }
    
    /**
     * Initialize and check access for the menu
     * Returns true if access is granted, false otherwise
     */
    public function initialize() {
        if (!$this->token) {
            $this->message = "Token tidak ada";
            $this->logger->error("❌ Token tidak ada");
            $this->showErrorPage("Token tidak ada");
            return false;
        }
        
        try {
            // Verify token
            $checkingTokens = verify_token($this->token);
            $this->user_id = $checkingTokens->kode ?? 'Unknown';
            $this->message = "Tidak ada error";
            
            // Check permission for this menu
            $permissionAccess = new PermissionAccess($this->conn);
            $permissionAccess->checkAccess($this->menu_code, $this->token);
            
            // If we reach here, user has access
            // $this->logger->success("✅ Permission check berhasil untuk " . $this->menu_code);
            return true;
            
        } catch (Exception $e) {
            // Log error for debugging
            $this->logger->error("❌ Error in " . $this->menu_code . ": " . $e->getMessage());
            
            // Show error page
            $this->showErrorPage($e->getMessage());
            return false;
        }
    }
    
    /**
     * Show error page with proper styling
     */
    private function showErrorPage($error_details) {
        $menu_code = $this->menu_code;
        $user_id = $this->user_id ?? 'Unknown';
        
        include __DIR__ . '/error_page.php';
        exit();
    }
    
    /**
     * Get user ID from token
     */
    public function getUserId() {
        return $this->user_id;
    }
    
    /**
     * Get current message
     */
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * Get logger instance for additional logging
     */
    public function getLogger() {
        return $this->logger;
    }
    
    /**
     * Get token
     */
    public function getToken() {
        return $this->token;
    }
} 