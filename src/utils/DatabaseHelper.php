<?php
class DatabaseHelper {
    private $connection;
    private $logger;
    
    public function __construct($connection, $logger = null) {
        $this->connection = $connection;
        $this->logger = $logger;
    }
    
    /**
     * Execute prepared statement with parameters
     */
    public function executePreparedStatement($sql, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Gagal prepare statement: " . $this->connection->error);
            }
            
            if (!empty($params)) {
                if (empty($types)) {
                    // Auto-detect types
                    $types = str_repeat('s', count($params));
                }
                
                $stmt->bind_param($types, ...$params);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Gagal execute statement: " . $stmt->error);
            }
            
            $stmt->close();
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("Database Error: " . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Insert or update data with ON DUPLICATE KEY UPDATE
     */
    public function insertOrUpdate($table, $data, $updateFields = []) {
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $sql = "INSERT INTO $table (" . implode(',', $columns) . ") 
                VALUES ($placeholders)";
        
        if (!empty($updateFields)) {
            $updateClause = [];
            foreach ($updateFields as $field) {
                $updateClause[] = "$field = VALUES($field)";
            }
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', $updateClause);
        }
        
        return $this->executePreparedStatement($sql, array_values($data));
    }
    
    /**
     * Select data from database
     */
    public function select($table, $columns = ['*'], $where = '', $params = []) {
        $columnsStr = implode(',', $columns);
        $sql = "SELECT $columnsStr FROM $table";
        
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Gagal prepare select statement: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        return $data;
    }
    
    /**
     * Delete data from database
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->executePreparedStatement($sql, $params);
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->executePreparedStatement($sql, [$tableName]);
        return $result !== false;
    }
    
    /**
     * Get last insert ID
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Get affected rows
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
}
?> 