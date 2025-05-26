<?php
// File: config/database.php
// Database configuration for Judge Scoreboard Application

class Database {
    private $host = 'localhost';
    private $db_name = 'scoreboard_db';
    private $username = 'scoreboard_user';
    private $password = 'root'; // Change this to your actual password
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
        
        return $this->conn;
    }

    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return $conn !== null;
        } catch(Exception $e) {
            return false;
        }
    }
}

// Create a global database instance
$database = new Database();
$pdo = $database->getConnection();

// Function to get database connection (for backward compatibility)
function getDBConnection() {
    global $pdo;
    return $pdo;
}
?>