<?php
// Simple API test to diagnose the issue
header('Content-Type: application/json');

try {
    // Include the config to set up the environment
    require_once 'backend/config/config.php';
    
    // Try to create a database connection
    require_once 'backend/config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Database connection successful',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception occurred: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>