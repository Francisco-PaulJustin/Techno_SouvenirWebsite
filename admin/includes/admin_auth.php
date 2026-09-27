<?php
// Clear admin viewing session flag when accessing admin panel
// This ensures admin panel works normally without interference
if (isset($_SESSION['admin_viewing_site'])) {
    unset($_SESSION['admin_viewing_site']);
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
