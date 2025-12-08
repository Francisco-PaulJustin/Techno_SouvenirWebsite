<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$page_title = 'Manage Orders';
$order_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? '';
    
    if ($order_id && $status) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            $message = 'Order status updated successfully!';
        } else {
            $error = 'Error updating order status.';
        }
    }
}

if ($order_id) {
    // View single order
    $stmt = $pdo->prepare("
        SELECT o.*, u.first_name, u.last_name, u.email as user_email, u.phone as user_phone
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $page_title = 'Order #' . $order_id;
    }
} else {
    // List all orders
    $stmt = $pdo->query("
        SELECT o.*, u.first_name, u.last_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once 'includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php require_once 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <?php if ($order_id && isset($order)): ?>
            <!-- Single Order View -->
            <div class="page-header">
                <div>
                    <nav class="breadcrumb">
                        <a href="index.php">Dashboard</a>
                        <span class="separator">/</span>
                        <a href="manage_orders.php">Orders</a>
                        <span class="separator">/</span>
                        <span>Order #<?php echo $order['id']; ?></span>
                    </nav>
                    <h1 class="page-title">
                        <span class="material-icons-round">receipt_long</span>
                        Order #<?php echo $order['id']; ?>
                    </h1>
                </div>
                <div class="page-actions">
                    <a href="manage_orders.php" class="btn btn-secondary">
                        <span class="material-icons-round">arrow_back</span>
                        Back to Orders
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <span class="material-icons-round">check_circle</span>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="material-icons-round">error</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="order-details">
                <!-- Order Summary Card -->
                <div class="order-info-card">
                    <h3>
                        <span class="material-icons-round">info</span>
                        Order Summary
                    </h3>
                    <div class="order-info-grid">
                        <div class="order-info-item">
                            <span class="label">Order ID</span>
                            <span class="value">#<?php echo $order['id']; ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Date Placed</span>
                            <span class="value"><?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Status</span>
                            <span class="value">
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Total Amount</span>
                            <span class="value large">₱<?php echo number_format($order['total'], 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Status Update Form -->
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--clr-border);">
                        <form method="POST" action="manage_orders.php?id=<?php echo $order_id; ?>" class="status-form">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                            <label for="status" style="font-weight: 600; margin-right: 0.5rem;">Update Status:</label>
                            <select id="status" name="status">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo strtolower($order['status']) == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="completed" <?php echo strtolower($order['status']) == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo strtolower($order['status']) == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                <span class="material-icons-round">save</span>
                                Update
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="order-info-card">
                    <h3>
                        <span class="material-icons-round">person</span>
                        Customer Information
                    </h3>
                    <div class="order-info-grid">
                        <div class="order-info-item">
                            <span class="label">Name</span>
                            <span class="value"><?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Email</span>
                            <span class="value"><?php echo htmlspecialchars($order['user_email'] ?? $order['shipping_email'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Phone</span>
                            <span class="value"><?php echo htmlspecialchars($order['user_phone'] ?? $order['shipping_phone'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Payment Method</span>
                            <span class="value"><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Information -->
                <div class="order-info-card">
                    <h3>
                        <span class="material-icons-round">local_shipping</span>
                        Shipping Information
                    </h3>
                    <div class="order-info-grid">
                        <div class="order-info-item">
                            <span class="label">Recipient Name</span>
                            <span class="value"><?php echo htmlspecialchars($order['shipping_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">Address</span>
                            <span class="value"><?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">City</span>
                            <span class="value"><?php echo htmlspecialchars($order['shipping_city'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="order-info-item">
                            <span class="label">ZIP Code</span>
                            <span class="value"><?php echo htmlspecialchars($order['shipping_zip'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>
                            <span class="material-icons-round">shopping_bag</span>
                            Order Items
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                <?php endif; ?>
                                                <span style="font-weight: 500;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td style="font-weight: 600;">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-right" style="font-weight: 600;">Total:</td>
                                    <td style="font-weight: 700; font-size: 1.1rem; color: var(--clr-primary);">
                                        ₱<?php echo number_format($order['total'], 2); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Orders List View -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <span class="material-icons-round">receipt_long</span>
                        Manage Orders
                    </h1>
                    <p class="page-subtitle">View and manage all customer orders</p>
                </div>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h2>All Orders (<?php echo count($orders); ?>)</h2>
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
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <span class="material-icons-round">receipt_long</span>
                                            <h3>No orders yet</h3>
                                            <p>Orders will appear here when customers make purchases</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $ord): ?>
                                    <tr>
                                        <td class="cell-id">#<?php echo $ord['id']; ?></td>
                                        <td>
                                            <div style="display: flex; flex-direction: column;">
                                                <span style="font-weight: 500;">
                                                    <?php echo htmlspecialchars(($ord['first_name'] ?? '') . ' ' . ($ord['last_name'] ?? '')); ?>
                                                </span>
                                                <span class="text-muted" style="font-size: 0.8rem;">
                                                    <?php echo htmlspecialchars($ord['email'] ?? 'N/A'); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td style="font-weight: 600;">₱<?php echo number_format($ord['total'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($ord['status']); ?>">
                                                <?php echo ucfirst($ord['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                        <td class="cell-actions">
                                            <a href="manage_orders.php?id=<?php echo $ord['id']; ?>" class="btn btn-ghost btn-sm">
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
        <?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
