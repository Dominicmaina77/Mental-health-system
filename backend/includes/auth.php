<?php
require_once 'functions.php';

/**
 * Require authentication for protected routes
 * Returns user ID if authenticated, otherwise sends error response
 */
function requireAuth() {
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