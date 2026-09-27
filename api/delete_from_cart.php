<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../cart/cart_session.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$product_id = filter_var($data['product_id'] ?? $_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$product_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

removeFromCart($product_id);

echo json_encode([
    'success' => true,
    'message' => 'Product removed from cart'
]);
?>
