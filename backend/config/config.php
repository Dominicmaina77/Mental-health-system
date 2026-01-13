<?php
// Prevent multiple session starts
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
require_once 'database.php';

// Application settings
define('APP_NAME', 'SootheSpace');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/Mental-health-system/backend');

// Error reporting - enable for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Request-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>