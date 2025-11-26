<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../cart/cart_session.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to checkout'
    ]);
    exit;
}

$cart = getCart();
if (empty($cart)) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$zip = $_POST['zip'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

if (empty($name) || empty($email) || empty($address) || empty($payment_method)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

// Calculate total
$total = 0;
$placeholders = str_repeat('?,', count($cart) - 1) . '?';
$stmt = $pdo->prepare("SELECT id, price, stock FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($cart));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    $quantity = $cart[$product['id']];
    if ($quantity > $product['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock for ' . $product['name']
        ]);
        exit;
    }
    $total += $product['price'] * $quantity;
}

// Create order
try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total, $name, $email, $phone, $address, $city, $state, $zip, $payment_method]);
    $order_id = $pdo->lastInsertId();
    
    // Create order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $product) {
        $quantity = $cart[$product['id']];
        $stmt->execute([$order_id, $product['id'], $quantity, $product['price']]);
        
        // Update stock
        $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update_stmt->execute([$quantity, $product['id']]);
    }
    
    // Clear cart
    clearCart();
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}
?>

