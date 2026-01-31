<?php
/**
 * logout.php
 * Safely ends the user session and redirects to the sign-in page.
 */

// 1. Initialize the session to access it
session_start();

// 2. Clear session data from memory
$_SESSION = array();

// 3. Destroy the session cookie on the client side
// This ensures the browser forgets the session ID entirely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Destroy the session data on the server
session_destroy();

// 5. Security: Prevent browser back-button from accessing cached authenticated pages
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// 6. Redirect to login page
// Note: Ensure login.php exists in the same root directory
header("Location: index.php?status=logged_out");
exit();
?>