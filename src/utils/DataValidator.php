<?php
class DataValidator {
    
    /**
     * Validate required fields in an array
     */
    public static function validateRequiredFields($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Sanitize string data
     */
    public static function sanitizeString($string) {
        return trim($string);
    }
    
    /**
     * Sanitize array data
     */
    public static function sanitizeArray($data, $fields) {
        $sanitized = [];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = self::sanitizeString($data[$field]);
            }
        }
        return $sanitized;
    }
    
    /**
     * Validate JSON data
     */
    public static function validateJson($jsonData) {
        $decoded = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Error: " . json_last_error_msg());
        }
        return $decoded;
    }
    
    /**
     * Validate file exists and is readable
     */
    public static function validateFile($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: $filePath");
        }
        
        if (!is_readable($filePath)) {
            throw new Exception("File tidak dapat dibaca: $filePath");
        }
        
        return true;
    }
    
    /**
     * Validate database connection
     */
    public static function validateDatabaseConnection($connection) {
        if (!isset($connection) || !$connection) {
            throw new Exception("Koneksi database tidak tersedia");
        }
        return true;
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate numeric value
     */
    public static function validateNumeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Validate array is not empty
     */
    public static function validateArrayNotEmpty($array) {
        return is_array($array) && !empty($array);
    }
}
?> 