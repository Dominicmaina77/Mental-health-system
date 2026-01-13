<!DOCTYPE html>
<html>
<head>
    <title>Database Setup Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .result { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Database Setup Test</h1>
    <p>This script will test if the database and tables are created automatically.</p>
    
    <?php
    require_once 'backend/config/database.php';
    
    echo "<div class='result'>";
    echo "<h3>Testing Database Connection...</h3>";
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<div class='result success'>";
        echo "<p><strong>✓ Database connection successful!</strong></p>";
        
        // Check if tables exist
        try {
            $tables = ['users', 'mood_entries', 'journal_entries', 'reminders'];
            $missing_tables = [];
            
            foreach ($tables as $table) {
                $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() == 0) {
                    $missing_tables[] = $table;
                }
            }
            
            if (empty($missing_tables)) {
                echo "<p><strong>✓ All required tables exist!</strong></p>";
            } else {
                echo "<p><strong>⚠ Some tables are missing:</strong> " . implode(', ', $missing_tables) . "</p>";
                echo "<p>The system should have created them automatically.</p>";
            }
        } catch (Exception $e) {
            echo "<p>Error checking tables: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    } else {
        echo "<div class='result error'>";
        echo "<p><strong>✗ Database connection failed!</strong></p>";
        echo "<p>Please check your database configuration.</p>";
        echo "</div>";
    }
    echo "</div>";
    ?>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>If the database connection was successful, try accessing the API again</li>
        <li>Make sure your MySQL service is running in XAMPP</li>
        <li>If you still have issues, check the XAMPP error logs</li>
    </ol>
</body>
</html>