<?php
require_once 'config.php';

// Log the logout event before destroying session
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'logout', 'User logged out');
    log_security_event('logout', "User {$_SESSION['email']} logged out", 'info');
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?> 