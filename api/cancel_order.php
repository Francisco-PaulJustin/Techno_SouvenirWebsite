<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Only customers can cancel their own orders
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? $_POST['order_id'] ?? null;

if (!$order_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get order details and verify ownership
    $stmt = $pdo->prepare("
        SELECT o.*, oi.product_id, oi.quantity 
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($order_data)) {
        throw new Exception('Order not found or you do not have permission to cancel this order');
    }
    
    // Get order status from first row
    $order_status = $order_data[0]['status'];
    
    // Check if order can be cancelled
    $cancellable_statuses = ['pending', 'processing'];
    if (!in_array(strtolower($order_status), $cancellable_statuses)) {
        throw new Exception('This order cannot be cancelled. Only pending or processing orders can be cancelled.');
    }
    
    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ?");
    if (!$stmt->execute([$order_id, $user_id])) {
        throw new Exception('Failed to update order status');
    }
    
    // Restore product stock
    foreach ($order_data as $item) {
        if (!empty($item['product_id']) && !empty($item['quantity'])) {
            $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
