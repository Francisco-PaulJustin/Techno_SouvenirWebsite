<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    // Redirect to login page, then back to product view with an error or message
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? '../products.php') . '&message=Please login to buy this product');
    exit;
}

// Ensure only customers can process direct checkout
if (isAdmin()) {
    header('Location: ../admin/index.php?error=Admin accounts cannot place orders.');
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;
$user_id = $_SESSION['user_id'];

if (!$product_id || $quantity < 1) {
    // Redirect back to product page with error
    header('Location: ../products.php?error=Invalid product or quantity');
    exit;
}

// Fetch product details
$stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // Redirect back to product page with error
    header('Location: ../products.php?error=Product not found');
    exit;
}

if ($quantity > $product['stock']) {
    // Redirect back to product page with stock error
    header('Location: ../product_view.php?id=' . $product_id . '&error=Insufficient stock for ' . urlencode($product['name']));
    exit;
}

// Assuming default shipping and payment details for direct checkout
// In a real application, these would be collected from the user or pre-filled from profile
$shipping_name = $_SESSION['user_name'] ?? '';
$shipping_email = $_SESSION['user_email'] ?? '';
$shipping_phone = $_SESSION['user_phone'] ?? ''; // Assuming phone is in session or fetched from DB
$shipping_address = $_SESSION['user_address'] ?? ''; // Assuming address is in session or fetched from DB
$shipping_city = ''; // Placeholder
$shipping_state = ''; // Placeholder
$shipping_zip = ''; // Placeholder
$payment_method = 'Cash on Delivery'; // Default for direct checkout

$total = $product['price'] * $quantity;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id, $total, $shipping_name, $shipping_email, $shipping_phone,
        $shipping_address, $shipping_city, $shipping_state, $shipping_zip,
        $payment_method, 'Processing'
    ]);
    $order_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $product['id'], $quantity, $product['price']]);

    // Update stock
    $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $update_stmt->execute([$quantity, $product['id']]);

    $pdo->commit();

    // Redirect to orders page with success message
    header('Location: ../orders.php?order_success=true&order_id=' . $order_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    // Redirect back to product page with error
    header('Location: ../product_view.php?id=' . $product_id . '&error=Error processing order: ' . urlencode($e->getMessage()));
    exit;
}
?>