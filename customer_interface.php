<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Prevent admins from accessing customer interface
requireCustomer();

$page_title = 'Customer Dashboard';
require_once 'includes/header.php';
?>

<main class="customer-dashboard fade-in">
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'Customer'); ?>!</h1>
        <p>This is your personalized customer interface. Here you can view your orders, update your profile, and more.</p>

        <div class="dashboard-actions">
            <a href="products.php" class="btn btn-primary">Browse Products</a>
            <a href="profile.php" class="btn btn-secondary">View Profile</a>
            <!-- Add more customer-specific links here -->
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
