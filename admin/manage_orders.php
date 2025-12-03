<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';
require_once 'includes/admin_header.php';
require_once 'includes/admin_sidebar.php';

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
    $stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    // List all orders
    $stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../assets/css/admin.css">
<div class="admin-content">
    <h1><?php echo $order_id ? 'Order Details' : 'Manage Orders'; ?></h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($order_id && isset($order)): ?>
        <div class="order-details">
            <div class="order-info">
                <h2>Order #<?php echo $order['id']; ?></h2>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name']); ?> (<?php echo htmlspecialchars($order['user_email']); ?>)</p>
                <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                <p><strong>Total:</strong> ₱<?php echo number_format($order['total'], 2); ?></p>
            </div>
            
            <form method="POST" action="manage_orders.php?id=<?php echo $order_id; ?>" class="status-form">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <div class="form-group">
                    <label for="status">Update Status:</label>
                    <select id="status" name="status">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                </div>
            </form>
            
            <h3>Order Items</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <a href="manage_orders.php" class="btn btn-secondary">Back to Orders</a>
    <?php else: ?>
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
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td>#<?php echo $ord['id']; ?></td>
                        <td><?php echo htmlspecialchars($ord['user_name']); ?></td>
                        <td>₱<?php echo number_format($ord['total'], 2); ?></td>
                        <td><span class="status-badge status-<?php echo $ord['status']; ?>"><?php echo ucfirst($ord['status']); ?></span></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($ord['created_at'])); ?></td>
                        <td><a href="manage_orders.php?id=<?php echo $ord['id']; ?>" class="btn btn-sm">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

</html>