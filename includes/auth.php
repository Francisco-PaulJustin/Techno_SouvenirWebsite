<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isCustomer() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Prevent admins from accessing user interface pages
function requireCustomer() {
    requireLogin();
    if (isAdmin()) {
        // Admin is trying to access user interface, redirect to admin panel
        header('Location: admin/index.php');
        exit;
    }
    if (!isCustomer()) {
        // Not a customer, redirect to login
        header('Location: login.php');
        exit;
    }
}

// Redirect admins from public pages to admin interface
// Use this on public pages (index.php, products.php, etc.) to ensure admins only see admin interface
function redirectAdminIfLoggedIn() {
    if (isAdmin()) {
        header('Location: admin/index.php');
        exit;
    }
}
?>

