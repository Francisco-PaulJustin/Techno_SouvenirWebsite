<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

// Always clear admin viewing session flag when accessing admin panel
// This ensures admin panel works normally
if (isset($_SESSION['admin_viewing_site'])) {
    unset($_SESSION['admin_viewing_site']);
}

$page_title = 'Dashboard';

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending' OR status = 'processing' OR status = 'Processing'");
$stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total revenue
$stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE status = 'completed' OR status = 'Completed'");
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Low stock products
$stmt = $pdo->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
$low_stock_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php require_once 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span class="material-icons-round">dashboard</span>
                    Dashboard
                </h1>
                <p class="page-subtitle">Welcome back! Here's what's happening with your store.</p>
            </div>
            <div class="page-actions">
                <a href="add_product.php" class="btn btn-primary">
                    <span class="material-icons-round">add</span>
                    Add Product
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Products</h3>
                    <div class="stat-icon purple">
                        <span class="material-icons-round">inventory_2</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo number_format($stats['products']); ?></p>
                <a href="product_list.php" class="text-muted" style="font-size: 0.85rem; text-decoration: none;">
                    View all products →
                </a>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Orders</h3>
                    <div class="stat-icon blue">
                        <span class="material-icons-round">receipt_long</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo number_format($stats['orders']); ?></p>
                <?php if ($stats['pending_orders'] > 0): ?>
                    <span class="stat-trend" style="background: var(--clr-warning-bg); color: #a16900;">
                        <?php echo $stats['pending_orders']; ?> pending
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Customers</h3>
                    <div class="stat-icon green">
                        <span class="material-icons-round">people</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo number_format($stats['users']); ?></p>
                <a href="manage_users.php" class="text-muted" style="font-size: 0.85rem; text-decoration: none;">
                    Manage users →
                </a>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Revenue</h3>
                    <div class="stat-icon yellow">
                        <span class="material-icons-round">payments</span>
                    </div>
                </div>
                <p class="stat-number">₱<?php echo number_format($stats['revenue'], 2); ?></p>
                <span class="text-muted" style="font-size: 0.85rem;">From completed orders</span>
            </div>
        </div>
        
        <!-- Recent Orders Section -->
        <div class="section">
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <span class="material-icons-round">receipt_long</span>
                        Recent Orders
                    </h2>
                    <a href="manage_orders.php" class="btn btn-ghost btn-sm">
                        View All
                        <span class="material-icons-round">arrow_forward</span>
                    </a>
                </div>
                <div class="table-responsive">
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
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding: 3rem;">
                                        No orders yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td class="cell-id">#<?php echo $order['id']; ?></td>
                                        <td>
                                            <div style="display: flex; flex-direction: column;">
                                                <span style="font-weight: 500;">
                                                    <?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?>
                                                </span>
                                                <span class="text-muted" style="font-size: 0.8rem;">
                                                    <?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td style="font-weight: 600;">₱<?php echo number_format($order['total'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td class="cell-actions">
                                            <a href="manage_orders.php?id=<?php echo $order['id']; ?>" class="btn btn-ghost btn-sm">
                                                <span class="material-icons-round">visibility</span>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if (!empty($low_stock_products)): ?>
        <!-- Low Stock Alert -->
        <div class="section">
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <span class="material-icons-round" style="color: var(--clr-danger);">warning</span>
                        Low Stock Alert
                    </h2>
                    <a href="product_list.php" class="btn btn-ghost btn-sm">
                        View All Products
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_products as $product): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="uploads/<?php echo $product['image']; ?>" 
                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                            <?php endif; ?>
                                            <span style="font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $product['stock'] < 5 ? 'status-cancelled' : 'status-pending'; ?>">
                                            <?php echo $product['stock']; ?> left
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline btn-sm">
                                            <span class="material-icons-round">edit</span>
                                            Update
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
