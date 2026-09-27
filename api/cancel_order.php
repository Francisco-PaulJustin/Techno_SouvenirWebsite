<?php
header('Content-Type: application/json');
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

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$order_id = filter_var($data['order_id'] ?? $_POST['order_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$order_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Cancel in one statement that only matches the user's own cancellable order,
    // so two cancel requests at once can't both restore stock
    $stmt = $pdo->prepare("
        UPDATE orders SET status = 'cancelled', updated_at = NOW()
        WHERE id = ? AND user_id = ? AND status IN ('pending', 'processing')
    ");
    $stmt->execute([$order_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'This order cannot be cancelled. Only your pending or processing orders can be cancelled.'
        ]);
        exit;
    }

    // Restore product stock
    $stmt = $pdo->prepare("
        UPDATE products p SET stock = p.stock + oi.quantity
        FROM order_items oi
        WHERE oi.order_id = ? AND oi.product_id = p.id
    ");
    $stmt->execute([$order_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully'
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Cancel order failed: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Could not cancel the order. Please try again.'
    ]);
}
?>
