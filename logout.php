<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['user_role'])) {
    // If admin, redirect to admin login page after logout
    if ($_SESSION['user_role'] === 'admin') {
        session_unset();
        session_destroy();
        header('Location: login.php'); // Redirect to main login page
        exit;
    } else {
        // For customers or other roles, redirect to the main index page
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} else {
    // If no session or role, simply destroy session and redirect to index
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

?>

