<?php
session_start(); // Start the session to access existing session data

// 1. Clear all session variables
$_SESSION = array();

// 2. Delete the session cookie if it exists (Extra security measure)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Fully destroy the session
session_destroy();

// 4. Redirect the user back to the main login page
// Ensure the path to login.php is correct based on your folder structure
header("location:login.php"); 
exit();
?>