<?php
session_start();

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Also clear any remember me cookie
if (isset($_COOKIE['user_token'])) {
    setcookie('user_token', '', time()-3600, '/');
}

// Redirect to home page
header('Location: index.php');
exit();
?>