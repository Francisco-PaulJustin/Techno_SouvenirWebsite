<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../cart/cart_session.php';

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? $_POST['product_id'] ?? null;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : (isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1);

if (!$product_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

if ($quantity < 1) {
    removeFromCart($product_id);
    echo json_encode([
        'success' => true,
        'message' => 'Product removed from cart'
    ]);
    exit;
}

// Verify product exists and has stock
$stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit;
}

if ($quantity > $product['stock']) {
    echo json_encode([
        'success' => false,
        'message' => 'Insufficient stock available'
    ]);
    exit;
}

updateCartQuantity($product_id, $quantity);

echo json_encode([
    'success' => true,
    'message' => 'Cart quantity updated'
]);
?>

