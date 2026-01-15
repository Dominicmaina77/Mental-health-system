<?php
// Use absolute path to ensure proper inclusion
$rootDir = dirname(dirname(__FILE__)); // Go up two levels to get to backend/
$functionsPath = $rootDir . '/includes/functions.php';

if (file_exists($functionsPath)) {
    require_once $functionsPath;
} else {
    // Fallback: try to include from parent directory
    require_once 'functions.php';
}

/**
 * Get all headers (fallback for servers where getallheaders() is not available)
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Extract token from Authorization header
 */
function getTokenFromHeader() {
    // First try standard Authorization header
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }

    // Some servers put the Authorization header in HTTP_AUTHORIZATION
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }

    // Apache specific: Authorization header might be in REDIRECT_HTTP_AUTHORIZATION
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        if (preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }

    return null;
}

/**
 * Validate token from header against session
 */
function validateTokenFromHeader() {
    $headerToken = getTokenFromHeader();
    if (!$headerToken) {
        return false;
    }

    // Check if the token matches the one stored in session
    if (isset($_SESSION['user_token']) && $_SESSION['user_token'] === $headerToken) {
        return true;
    }

    return false;
}

/**
 * Require authentication for protected routes
 * Returns user ID if authenticated, otherwise sends error response
 */
function requireAuth() {
    // First try to validate using token from header
    if (validateTokenFromHeader()) {
        // Token is valid, ensure user_id is in session
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
    }

    // Fallback to traditional session check
    if (!isAuthenticated()) {
        jsonResponse(['error' => 'Authentication required'], 401);
    }
    return $_SESSION['user_id'];
}

/**
 * Get current user ID
 */
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

/**
 * Login user
 */
function login($userId, $email, $name = '') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    return generateToken($userId);
}

/**
 * Logout user
 */
function logout() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_token']);
    session_destroy();
    session_start(); // Start a new session
    return true;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>