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
// But allow admins to view customer pages when admin_viewing_site flag is set
function requireCustomer() {
    requireLogin();
    if (isAdmin()) {
        // Allow admin to access if they're viewing the site (admin_viewing_site flag)
        if (isset($_SESSION['admin_viewing_site'])) {
            // Admin is viewing the site, allow access to customer pages
            return;
        } else {
            // Admin is trying to access user interface without viewing flag, redirect to admin panel
            header('Location: admin/index.php');
            exit;
        }
    }
    if (!isCustomer()) {
        // Not a customer, redirect to login
        header('Location: login.php');
        exit;
    }
}

// Redirect admins from public pages to admin interface
// Use this on public pages (index.php, products.php, etc.) to ensure admins only see admin interface
// Skip redirect if admin_view parameter is present (for admin preview)
function redirectAdminIfLoggedIn() {
    if (isAdmin()) {
        // Set session flag if admin_view parameter is present
        if (isset($_GET['admin_view']) && $_GET['admin_view'] == '1') {
            $_SESSION['admin_viewing_site'] = true;
        }
        
        // Allow admins to view the site if admin_view parameter is set or session flag exists
        if (!isset($_GET['admin_view']) && !isset($_SESSION['admin_viewing_site'])) {
            header('Location: admin/index.php');
            exit;
        }
    }
}
?>
