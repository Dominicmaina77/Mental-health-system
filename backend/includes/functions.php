<?php
require_once '../config/config.php';

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password using PHP's password_hash function
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate a secure token for authentication
 */
function generateToken($userId) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['user_token'] = $token;
    $_SESSION['user_id'] = $userId;
    return $token;
}

/**
 * Verify if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Validate required fields in input data
 */
function validateUserInput($data, $requiredFields) {
    $errors = [];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[] = "$field is required";
        }
    }
    return $errors;
}

/**
 * Check if current request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Validate mood value (1-10)
 */
function validateMoodValue($value) {
    return is_numeric($value) && $value >= 1 && $value <= 10;
}

/**
 * Get current date in YYYY-MM-DD format
 */
function getCurrentDate() {
    return date('Y-m-d');
}

/**
 * Get current timestamp
 */
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

/**
 * Log error message
 */
function logError($message) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . $message . "\n", 3, "error.log");
}
?>