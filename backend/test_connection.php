<?php
// Simple database test
require_once 'config/database.php';

echo "Testing database connection...\n";

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "Database connection successful!\n";
    
    // Test if tables exist by querying the users table
    try {
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "Users table exists\n";
        } else {
            echo "Users table does not exist\n";
        }
    } catch (Exception $e) {
        echo "Error checking tables: " . $e->getMessage() . "\n";
    }
} else {
    echo "Database connection failed!\n";
}
?>