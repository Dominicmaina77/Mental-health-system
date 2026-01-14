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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logging Out...</title>
</head>
<body>
    <p>Logging out...</p>

    <script>
        // Clear browser storage tokens
        localStorage.removeItem('token');
        sessionStorage.removeItem('token');

        // Redirect to home page
        window.location.href = 'index.php';
    </script>
</body>
</html>