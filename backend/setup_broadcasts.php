<?php
// Add the broadcasts table to the database if it doesn't exist
$basePath = dirname(__FILE__); // backend/
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Create broadcasts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS broadcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    sender_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $db->exec($sql);
    echo "Broadcasts table created successfully or already exists.\n";
} catch (Exception $e) {
    echo "Error creating broadcasts table: " . $e->getMessage() . "\n";
}

// Also make sure the users table has the necessary columns
$alterSql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'moderator', 'admin') DEFAULT 'user'";
try {
    $db->exec($alterSql);
    echo "Users table role column added or already exists.\n";
} catch (Exception $e) {
    echo "Role column may already exist: " . $e->getMessage() . "\n";
}

$alterSql2 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1";
try {
    $db->exec($alterSql2);
    echo "Users table is_active column added or already exists.\n";
} catch (Exception $e) {
    echo "is_active column may already exist: " . $e->getMessage() . "\n";
}

echo "Database setup completed.\n";
?>