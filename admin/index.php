<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total revenue
$stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE status = 'completed'");
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Recent orders
$stmt = $pdo->query(
    "SELECT o.*, u.name as user_name 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC LIMIT 10"
);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<?php 
require_once 'includes/admin_header.php';
require_once 'includes/admin_sidebar.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        
        <h1 class="page-title">Dashboard</h1>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p class="stat-number"><?= $stats['products']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p class="stat-number"><?= $stats['orders']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="stat-number"><?= $stats['users']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p class="stat-number">₱<?= number_format($stats['revenue'], 2); ?></p>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id']; ?></td>
                        <td><?= htmlspecialchars($order['user_name']); ?></td>
                        <td>₱<?= number_format($order['total'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?= $order['status']; ?>">
                                <?= ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="manage_orders.php?id=<?= $order['id']; ?>" class="btn btn-sm">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
</body>
</html>
