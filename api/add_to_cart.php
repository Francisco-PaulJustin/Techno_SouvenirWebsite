<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../cart/cart_session.php';

$isLoggedIn = isset($_SESSION['user_id']);

$data = json_decode(file_get_contents('php://input'), true);

if (!$isLoggedIn) {
    echo json_encode([
        'success' => false,
        'requiresLogin' => true,
        'message' => 'You need to log in or create an account to add items to the cart.'
    ]);
    exit;
}

$product_id = $data['product_id'] ?? $_POST['product_id'] ?? null;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : (isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1);

if (!$product_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
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

$cart = getCart();
$current_quantity = $cart[$product_id] ?? 0;
$new_quantity = $current_quantity + $quantity;

if ($new_quantity > $product['stock']) {
    echo json_encode([
        'success' => false,
        'message' => 'Insufficient stock available'
    ]);
    exit;
}

addToCart($product_id, $new_quantity);

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart'
]);
?>

