<?php
// c:\xampp\htdocs\project\config\db_connect.php
// Secure PDO Database Connection Class

// Load constants
require_once __DIR__ . '/constants.php';

/**
 * Database Connection Class
 * Implements Singleton Pattern for single database connection
 * Uses PDO with prepared statements for security
 */
class Database {
    
    private static $instance = null;
    private $connection = null;
    
    /**
     * Private constructor to prevent direct instantiation
     * Establishes PDO connection with error handling
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                die("Database connection error. Please contact administrator.");
            }
        }
    }
    
    /**
     * Prevent cloning of instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Get singleton instance
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection object
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a SELECT query with prepared statements
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array Result set
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e, $query);
            return false;
        }
    }
    
    /**
     * Execute a SELECT query and return single row
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Single row or false
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e, $query);
            return false;
        }
    }
    
    /**
     * Execute INSERT query
     * @param string $table Table name OR full query
     * @param array $data Associative array of column=>value OR params array for query
     * @return int|false Last insert ID or false
     */
    public function insert($table, $data = []) {
        try {
            // Check if $table is actually a full query (for backward compatibility)
            if (stripos($table, 'INSERT') === 0 || stripos($table, 'SELECT') === 0) {
                $stmt = $this->connection->prepare($table);
                $stmt->execute($data);
                return $this->connection->lastInsertId();
            }
            
            // Build INSERT query from table and data array
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            
            $query = "INSERT INTO `{$table}` (" . implode(', ', array_map(function($col) { return "`{$col}`"; }, $columns)) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute(array_values($data));
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->logError($e, $query ?? $table);
            return false;
        }
    }
    
    /**
     * Execute UPDATE query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return int|false Number of affected rows or false
     */
    public function update($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e, $query);
            return false;
        }
    }
    
    /**
     * Execute DELETE query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return int|false Number of affected rows or false
     */
    public function delete($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e, $query);
            return false;
        }
    }
    
    /**
     * Execute any custom query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return bool Success status
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e, $query);
            return false;
        }
    }
    
    /**
     * Begin transaction
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Get row count from last query
     * @param string $table Table name
     * @param string $where WHERE clause (optional)
     * @param array $params Parameters for WHERE clause
     * @return int Row count
     */
    public function count($table, $where = '', $params = []) {
        try {
            $query = "SELECT COUNT(*) as total FROM {$table}";
            if (!empty($where)) {
                $query .= " WHERE {$where}";
            }
            $result = $this->selectOne($query, $params);
            return $result ? (int)$result['total'] : 0;
        } catch (Exception $e) {
            $this->logError($e, $query);
            return 0;
        }
    }
    
    /**
     * Check if record exists
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return bool
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    /**
     * Log database errors
     * @param Exception $e Exception object
     * @param string $query SQL query that caused error
     */
    private function logError($e, $query = '') {
        if (ENABLE_ERROR_LOG) {
            $errorMessage = date('Y-m-d H:i:s') . " - Database Error: " . $e->getMessage();
            if (!empty($query)) {
                $errorMessage .= " | Query: " . $query;
            }
            $errorMessage .= " | File: " . $e->getFile() . " | Line: " . $e->getLine() . "\n";
            
            // Create logs directory if not exists
            $logDir = dirname(ERROR_LOG_PATH);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            error_log($errorMessage, 3, ERROR_LOG_PATH);
        }
        
        if (DEBUG_MODE) {
            echo "<pre>Database Error: " . $e->getMessage() . "</pre>";
            if (!empty($query)) {
                echo "<pre>Query: " . $query . "</pre>";
            }
        }
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->connection = null;
    }
}

/**
 * Helper function to get database instance
 * @return Database
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Helper function to get PDO connection
 * @return PDO
 */
function getConnection() {
    return Database::getInstance()->getConnection();
}
