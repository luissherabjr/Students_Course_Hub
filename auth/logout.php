<?php
/**
 * Logout - Destroy session and redirect to login
 */

session_start();

// Log logout
if (isset($_SESSION['user_id'])) {
    require_once '../includes/config.php';
    logSecurityEvent("Logout - Username: " . ($_SESSION['user_username'] ?? 'unknown'));
}

// Clear all session variables
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start new session for message
session_start();
$_SESSION['success'] = 'You have been successfully logged out.';

// Add no-cache headers to prevent back button from showing logged-in page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login
header('Location: login.php');
exit();
?>