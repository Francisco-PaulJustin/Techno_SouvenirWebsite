<?php
// Include auth.php to ensure isAdmin() function is available
if (!function_exists('isAdmin')) {
    require_once __DIR__ . '/auth.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#5A2E98">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="assets/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<?php
$body_class = $body_class ?? '';
$is_logged_in = isset($_SESSION['user_id']);
?>
<body class="app-body <?php echo htmlspecialchars($body_class); ?>" data-logged-in="<?php echo $is_logged_in ? '1' : '0'; ?>">
    <?php if (isset($_SESSION['admin_viewing_site']) && function_exists('isAdmin') && isAdmin()): ?>
        <div class="admin-preview-banner">
            <div class="container">
                <div class="admin-preview-content">
                    <span class="admin-preview-icon">👁️</span>
                    <span class="admin-preview-text">You are viewing the website as an admin. <a href="admin/index.php">Return to Admin Panel</a></span>
                    <button class="admin-preview-close" onclick="this.closest('.admin-preview-banner').style.display='none'" aria-label="Close banner">×</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

