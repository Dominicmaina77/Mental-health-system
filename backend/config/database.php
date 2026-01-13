<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'soothespace_db';
    private $username = 'root';  // Default XAMPP username
    private $password = '';      // Default XAMPP password is empty
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host,
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");

            // Check if database exists, if not create it
            $this->conn->exec("USE " . $this->db_name);
        } catch(PDOException $exception) {
            // If database doesn't exist, try to create it
            if (strpos($exception->getMessage(), 'Unknown database') !== false) {
                try {
                    // Connect without specifying database
                    $tempConn = new PDO("mysql:host=" . $this->host,
                                        $this->username, $this->password);
                    $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $tempConn->exec("set names utf8");

                    // Create the database
                    $tempConn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $tempConn->exec("USE " . $this->db_name);

                    // Now connect with the database specified
                    $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                                          $this->username, $this->password);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->conn->exec("set names utf8");

                    // Import the schema
                    $this->createTables();
                } catch(PDOException $e) {
                    error_log("Database creation error: " . $e->getMessage());
                    return null;
                }
            } else {
                error_log("Connection error: " . $exception->getMessage());
                return null;
            }
        }
        return $this->conn;
    }

    private function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            age_group VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        );

        CREATE TABLE IF NOT EXISTS mood_entries (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            mood_value INT CHECK (mood_value >= 1 AND mood_value <= 10),
            mood_note TEXT,
            date_recorded DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS mood_tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) UNIQUE NOT NULL
        );

        CREATE TABLE IF NOT EXISTS mood_entry_tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            mood_entry_id INT NOT NULL,
            tag_id INT NOT NULL,
            FOREIGN KEY (mood_entry_id) REFERENCES mood_entries(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES mood_tags(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS journal_entries (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(200),
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS reminders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            reminder_type ENUM('daily', 'weekly', 'custom') DEFAULT 'daily',
            reminder_time TIME,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        INSERT IGNORE INTO mood_tags (name) VALUES
        ('school'),('stress'),('family'),('friends'),('relationship'),
        ('health'),('work'),('weather'),('gratitude'),('achievement');
        ";

        try {
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $this->conn->exec($query);
                }
            }
        } catch(PDOException $e) {
            error_log("Table creation error: " . $e->getMessage());
        }
    }
}
?>