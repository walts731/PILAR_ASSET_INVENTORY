<?php
/**
 * Database Class
 * 
 * Secure database connection and query handling
 */

class Database {
    private static $instance = null;
    private $connection = null;
    private $queryCount = 0;
    private $queries = [];
    
    /**
     * Get database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - initialize database connection
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Connect to the database
     */
    private function connect() {
        global $conn; // Use the existing connection from connect.php
        
        if (!isset($conn) || !($conn instanceof mysqli)) {
            throw new Exception("Database connection not available. Please check your configuration in connect.php");
        }
        
        $this->connection = $conn;
        
        // Set charset to ensure proper encoding
        $this->connection->set_charset('utf8mb4');
    }
    
    /**
     * Get the database connection status
     */
    public function isConnected() {
        return ($this->connection && $this->connection->ping());
    }
    
    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = [], $types = '') {
        // Log the query for debugging
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'time' => microtime(true)
        ];
        
        // Prepare the statement
        $stmt = $this->connection->prepare($sql);
        
        if ($stmt === false) {
            $error = "Prepare failed: " . $this->connection->error . "\nSQL: $sql";
            error_log($error);
            throw new Exception("Database error. Please try again later.");
        }
        
        // Bind parameters if any
        if (!empty($params)) {
            // If types is not provided, try to determine it from params
            if (empty($types)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b'; // blob
                    }
                }
            }
            
            $bindParams = [$types];
            foreach ($params as $key => $value) {
                $bindParams[] = &$params[$key];
            }
            
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
        }
        
        // Execute the statement
        $result = $stmt->execute();
        
        if ($result === false) {
            $error = "Execute failed: " . $stmt->error . "\nSQL: $sql";
            error_log($error);
            $stmt->close();
            throw new Exception("Database error. Please try again later.");
        }
        
        // Get the result set for SELECT queries
        if ($stmt->result_metadata()) {
            $result = $stmt->get_result();
            $rows = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $result->free();
            }
            
            $stmt->close();
            
            // Log query execution time
            $this->queries[count($this->queries) - 1]['time'] = 
                microtime(true) - $this->queries[count($this->queries) - 1]['time'];
                
            $this->queryCount++;
            
            return $rows;
        } else {
            // For INSERT, UPDATE, DELETE, etc.
            $affectedRows = $stmt->affected_rows;
            $insertId = $stmt->insert_id;
            $stmt->close();
            
            // Log query execution time
            $this->queries[count($this->queries) - 1]['time'] = 
                microtime(true) - $this->queries[count($this->queries) - 1]['time'];
                
            $this->queryCount++;
            
            return [
                'affected_rows' => $affectedRows,
                'insert_id' => $insertId
            ];
        }
    }
    
    /**
     * Get a single row
     */
    public function getRow($sql, $params = [], $types = '') {
        $result = $this->query($sql, $params, $types);
        return $result[0] ?? null;
    }
    
    /**
     * Get a single value
     */
    public function getValue($sql, $params = [], $types = '') {
        $row = $this->getRow($sql, $params, $types);
        return $row ? reset($row) : null;
    }
    
    /**
     * Insert a record
     */
    public function insert($table, $data) {
        if (empty($data) || !is_array($data)) {
            throw new InvalidArgumentException("Invalid data for insert");
        }
        
        $columns = [];
        $placeholders = [];
        $values = [];
        $types = '';
        
        foreach ($data as $column => $value) {
            $columns[] = $this->escapeIdentifier($column);
            $placeholders[] = '?';
            $values[] = $value;
            
            // Determine parameter type
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        $sql = "INSERT INTO " . $this->escapeIdentifier($table) . " 
                (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $result = $this->query($sql, $values, $types);
        
        return [
            'insert_id' => $result['insert_id'],
            'affected_rows' => $result['affected_rows']
        ];
    }
    
    /**
     * Update records
     */
    public function update($table, $data, $where, $whereParams = []) {
        if (empty($data) || !is_array($data)) {
            throw new InvalidArgumentException("Invalid data for update");
        }
        
        $setParts = [];
        $values = [];
        $types = '';
        
        // Process SET clause
        foreach ($data as $column => $value) {
            $setParts[] = $this->escapeIdentifier($column) . " = ?";
            $values[] = $value;
            
            // Determine parameter type
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_string($value)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        // Process WHERE clause
        if (!empty($whereParams)) {
            $values = array_merge($values, $whereParams);
            
            // Determine parameter types for WHERE clause
            foreach ($whereParams as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }
        }
        
        $sql = "UPDATE " . $this->escapeIdentifier($table) . " 
                SET " . implode(', ', $setParts) . " 
                WHERE $where";
        
        $result = $this->query($sql, $values, $types);
        
        return [
            'affected_rows' => $result['affected_rows']
        ];
    }
    
    /**
     * Delete records
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM " . $this->escapeIdentifier($table) . " WHERE $where";
        $result = $this->query($sql, $params);
        return $result['affected_rows'];
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        $this->connection->begin_transaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit() {
        $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback() {
        $this->connection->rollback();
    }
    
    /**
     * Escape an identifier (table or column name)
     */
    public function escapeIdentifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Get the last insert ID
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Get the number of queries executed
     */
    public function getQueryCount() {
        return $this->queryCount;
    }
    
    /**
     * Get all executed queries (for debugging)
     */
    public function getQueries() {
        return $this->queries;
    }
    
    /**
     * Get the database connection (use with caution)
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Close the database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Destructor - close the connection
     */
    public function __destruct() {
        $this->close();
    }
}

// Create a shortcut function for easy access
function db() {
    return Database::getInstance();
}
