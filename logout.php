<?php
session_start();

// Store role before clearing session for redirect decision
$user_role = $_SESSION['user_role'] ?? null;

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect based on previous role
if ($user_role === 'admin') {
    // Admin logged out, redirect to login page
    header('Location: login.php');
} else {
    // Customer or other role logged out, redirect to index
    header('Location: index.php');
}
exit;
?>

