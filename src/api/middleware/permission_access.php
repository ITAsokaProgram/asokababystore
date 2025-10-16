<?php
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/DataValidator.php';
require_once __DIR__ . '/../../utils/DatabaseHelper.php';

class PermissionAccess {
    private $logger;
    private $dbHelper;
    
    public function __construct($connection) {
        $this->logger = new AppLogger('permission_access.log');
        $this->dbHelper = new DatabaseHelper($connection, $this->logger);
    }
    
    /**
     * Check user access for specific menu
     */
    public function checkAccess($menu_code, $jwt) {
        try {
            $this->logger->info("🔐 Checking access for menu: $menu_code");
            
            // Validate input
            if (empty($menu_code) || empty($jwt)) {
                $this->logger->error("❌ Invalid input: menu_code or jwt is empty");
                $this->denyAccess(400, "Data tidak lengkap", $menu_code);
            }
            
            // Verify JWT token
            $user = $this->verifyToken($jwt);
            if (!$user) {
                $this->logger->error("❌ Authentication failed for menu: $menu_code");
                $this->denyAccess(401, "Tidak terautentikasi", $menu_code);
            }
            
            // Convert stdClass to array if needed
            $userId = $this->getUserId($user);
            
            if (!$userId) {
                $this->logger->error("❌ User ID not found in token");
                $this->denyAccess(401, "Token tidak valid - User ID tidak ditemukan", $menu_code);
            }
            
            $this->logger->info("✅ User authenticated: $userId");
            
            // Check database permission
            $hasAccess = $this->checkDatabasePermission($userId, $menu_code);
            
            if (!$hasAccess) {
                $this->logger->warning("⚠️ Access denied for user $userId to menu: $menu_code");
                $this->denyAccess(403, "Akses ditolak untuk menu '$menu_code'", $menu_code);
            }
            
            $this->logger->success("✅ Access granted for user $userId to menu: $menu_code");
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("💥 Error checking access: " . $e->getMessage());
            $this->denyAccess(500, "Error internal server", $menu_code);
        }
    }
    
    /**
     * Get user ID from token result (handles both array and object)
     */
    private function getUserId($user) {
        if (is_object($user)) {
            // Handle stdClass object - check multiple possible field names
            return $user->id_user ?? $user->id ?? $user->kode ?? null;
        } elseif (is_array($user)) {
            // Handle array - check multiple possible field names
            return $user['id_user'] ?? $user['id'] ?? $user['kode'] ?? null;
        }
        return null;
    }
    
    /**
     * Verify JWT token
     */
    private function verifyToken($jwt) {
        try {
            // Assuming verify_token function exists
            if (!function_exists('verify_token')) {
                throw new Exception("Function verify_token tidak ditemukan");
            }
            
            $user = verify_token($jwt);
            
            if (!$user) {
                throw new Exception("Token tidak valid");
            }
            
            return $user;
            
        } catch (Exception $e) {
            $this->logger->error("❌ Token verification failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check permission in database
     */
    private function checkDatabasePermission($userId, $menuCode) {
        try {
            $result = $this->dbHelper->select('user_internal_access', ['can_view'], 'id_user = ? AND menu_code = ?', [$userId, $menuCode]);
            
            if (empty($result)) {
                $this->logger->warning("⚠️ No permission record found for user $userId and menu $menuCode");
                return false;
            }
            
            $canView = $result[0]['can_view'] ?? 0;
            return (bool)$canView;
            
        } catch (Exception $e) {
            $this->logger->error("❌ Database error checking permission: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Deny access with proper HTTP response
     */
    private function denyAccess($httpCode, $message, $menu_code = null) {
        http_response_code($httpCode);
        
        // Check if it's an API request
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $message,
                'code' => $httpCode
            ]);
        } else {
            // For web requests, show beautiful error page
            $error_details = $message;
            if (!$menu_code) {
                $menu_code = 'dashboard';
            }
            
            // Try to get user_id from token if available
            $user_id = 'Unknown';
            if (isset($_COOKIE['admin_token'])) {
                try {
                    $token = $_COOKIE['admin_token'];
                    $user = verify_token($token);
                    if ($user && isset($user->kode)) {
                        $user_id = $user->kode;
                    }
                } catch (Exception $e) {
                    // Keep user_id as 'Unknown'
                }
            }
            
            // Include error page
            include __DIR__ . '/../../../src/component/error_page.php';
        }
        
        exit();
    }
    
    /**
     * Check if request is API call
     */
    private function isApiRequest() {
        return isset($_SERVER['HTTP_ACCEPT']) && 
               strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    }
    
    /**
     * Get user permissions for all menus
     */
    public function getUserPermissions($userId) {
        try {
            $this->logger->info("📋 Getting permissions for user: $userId");
            
            $permissions = $this->dbHelper->select(
                'user_internal_access', 
                ['menu_code', 'can_view', 'can_create', 'can_update', 'can_delete'], 
                'id_user = ?', 
                [$userId]
            );
            
            $this->logger->success("✅ Retrieved " . count($permissions) . " permissions for user $userId");
            return $permissions;
            
        } catch (Exception $e) {
            $this->logger->error("❌ Error getting user permissions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($userId, $menuCode, $permission = 'can_view') {
        try {
            $result = $this->dbHelper->select(
                'user_internal_access', 
                [$permission], 
                'id_user = ? AND menu_code = ?', 
                [$userId, $menuCode]
            );
            
            if (empty($result)) {
                return false;
            }
            
            return (bool)($result[0][$permission] ?? 0);
            
        } catch (Exception $e) {
            $this->logger->error("❌ Error checking specific permission: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Grant permission to user
     */
    public function grantPermission($userId, $menuCode, $permissions = []) {
        try {
            $this->logger->info("🔓 Granting permissions for user $userId to menu $menuCode");
            
            $data = [
                'id_user' => $userId,
                'menu_code' => $menuCode,
                'can_view' => $permissions['can_view'] ?? 0,
                'can_create' => $permissions['can_create'] ?? 0,
                'can_update' => $permissions['can_update'] ?? 0,
                'can_delete' => $permissions['can_delete'] ?? 0
            ];
            
            $updateFields = ['can_view', 'can_create', 'can_update', 'can_delete'];
            $result = $this->dbHelper->insertOrUpdate('user_internal_access', $data, $updateFields);
            
            if ($result) {
                $this->logger->success("✅ Permissions granted successfully");
                return true;
            } else {
                $this->logger->error("❌ Failed to grant permissions");
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error("❌ Error granting permissions: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Revoke permission from user
     */
    public function revokePermission($userId, $menuCode) {
        try {
            $this->logger->info("🔒 Revoking permissions for user $userId from menu $menuCode");
            
            $result = $this->dbHelper->delete(
                'user_internal_access', 
                'id_user = ? AND menu_code = ?', 
                [$userId, $menuCode]
            );
            
            if ($result) {
                $this->logger->success("✅ Permissions revoked successfully");
                return true;
            } else {
                $this->logger->error("❌ Failed to revoke permissions");
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error("❌ Error revoking permissions: " . $e->getMessage());
            return false;
        }
    }
}

// Legacy function for backward compatibility
function checkAccess($menu_code, $jwt) {
    global $conn;
    
    $permissionAccess = new PermissionAccess($conn);
    return $permissionAccess->checkAccess($menu_code, $jwt);
}
?>