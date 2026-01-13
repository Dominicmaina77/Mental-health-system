<?php
// Test registration endpoint
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Include necessary files
    require_once 'backend/config/config.php';
    require_once 'backend/includes/functions.php';
    require_once 'backend/includes/auth.php';
    require_once 'backend/models/User.php';
    
    // Simulate registration data
    $testData = [
        'name' => 'Test User',
        'email' => 'test' . time() . '@example.com',  // Unique email using timestamp
        'password' => 'password123',
        'age_group' => '18-24'
    ];
    
    // Validate input
    $errors = validateUserInput($testData, ['name', 'email', 'password']);
    
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }
    
    if (!validateEmail($testData['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }
    
    if (strlen($testData['password']) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
        exit;
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo json_encode(['status' => 'error', 'message' => 'Could not connect to database']);
        exit;
    }
    
    // Check if user already exists
    $user = new User($db);
    
    if ($user->findByEmail($testData['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit;
    }
    
    // Create new user
    $user->name = sanitizeInput($testData['name']);
    $user->email = sanitizeInput($testData['email']);
    $user->password_hash = hashPassword($testData['password']);
    $user->age_group = isset($testData['age_group']) ? sanitizeInput($testData['age_group']) : null;
    
    $userId = $user->create();
    
    if ($userId) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'User created successfully',
            'user_id' => $userId,
            'email' => $user->email,
            'name' => $user->name
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception occurred: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>