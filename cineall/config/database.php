<?php
/**
 * ============================================================================
 * CineAll — SHARED DATABASE LAYER
 * ============================================================================
 * One PDO singleton for the whole platform. Exposes BOTH styles the original
 * apps used:
 *   - $db = Database::getInstance();  $db->query(...) / queryOne / execute
 *   - $pdo = getDB();                 raw PDO (used by auth/admin/curator code)
 * ============================================================================
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->handleError($e);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Raw PDO connection */
    public function getConnection() {
        return $this->conn;
    }

    /** Run a query, return all rows */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /** Run a query, return a single row */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /** Run INSERT/UPDATE/DELETE. Returns lastInsertId for INSERT, rowCount otherwise */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            if (stripos(trim($sql), 'INSERT') === 0) {
                return $this->conn->lastInsertId();
            }
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    public function beginTransaction() { return $this->conn->beginTransaction(); }
    public function commit()           { return $this->conn->commit(); }
    public function rollback()         { return $this->conn->rollBack(); }
    public function lastInsertId()     { return $this->conn->lastInsertId(); }

    private function handleError(PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        if (DEBUG_MODE) {
            throw $e;
        }
        die("A database error occurred. Please try again later.");
    }

    private function __clone() {}
    public function __wakeup() { throw new Exception("Cannot unserialize singleton"); }
}

/**
 * Raw PDO accessor — kept for compatibility with the auth/admin/curator code.
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
