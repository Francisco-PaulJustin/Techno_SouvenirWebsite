<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../cart/cart_session.php';

// Prevent admins from processing checkout
if (!isLoggedIn()) {
    $_SESSION['checkout_error'] = 'Please login to complete your order.';
    header('Location: ../login.php?redirect=' . urlencode('checkout.php'));
    exit;
}

// Ensure only customers can process checkout
if (isAdmin()) {
    $_SESSION['checkout_error'] = 'Admin accounts cannot place orders.';
    header('Location: ../admin/index.php');
    exit;
}

// Initialize variables for processing
$items_to_process = [];
$is_direct_checkout = ($_POST['is_direct_checkout'] ?? 'false') === 'true';
$redirect_on_error = '../checkout.php'; // Default redirect for errors

if ($is_direct_checkout) {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id || $quantity < 1) {
        $_SESSION['checkout_error'] = 'Invalid product or quantity for direct checkout.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../products.php'));
        exit;
    }

    // Fetch product details for direct checkout
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['checkout_error'] = 'Product not found.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../products.php'));
        exit;
    }

    if ($quantity > $product['stock']) {
        $_SESSION['checkout_error'] = 'Insufficient stock for ' . $product['name'] . '.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../product_view.php?id=' . $product_id));
        exit;
    }

    $items_to_process = [$product_id => $quantity];
    $redirect_on_error = '../product_view.php?id=' . $product_id; // Redirect to product page on direct checkout error

} else {
    // Cart checkout
    $selected_products_ids = $_POST['selected_products'] ?? [];
    if (empty($selected_products_ids)) {
        $_SESSION['checkout_error'] = 'No items selected for checkout.';
        header('Location: ../checkout.php');
        exit;
    }

    $cart = getCart();
    foreach ($selected_products_ids as $p_id) {
        if (isset($cart[$p_id])) {
            $items_to_process[$p_id] = $cart[$p_id];
        }
    }

    if (empty($items_to_process)) {
        $_SESSION['checkout_error'] = 'Selected cart items are no longer available.';
        header('Location: ../checkout.php');
        exit;
    }
    $redirect_on_error = '../checkout.php'; // Redirect to checkout page on cart checkout error
}

// Gather shipping and payment info
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$state = ''; // State field removed - using empty string
$zip = $_POST['zip'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

if (empty($name) || empty($email) || empty($address) || empty($payment_method)) {
    $_SESSION['checkout_error'] = 'Please fill in all required shipping and payment fields.';
    header('Location: ' . $redirect_on_error);
    exit;
}

// Calculate total and validate stock for items_to_process
$total = 0;
$product_details = [];

if (!empty($items_to_process)) {
    $placeholders = str_repeat('?,', count($items_to_process) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($items_to_process));
    $fetched_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fetched_products as $product) {
        $quantity = $items_to_process[$product['id']];
        if ($quantity > $product['stock']) {
            $_SESSION['checkout_error'] = 'Insufficient stock for ' . htmlspecialchars($product['name']) . '.';
            header('Location: ' . $redirect_on_error);
            exit;
        }
        $total += $product['price'] * $quantity;
        $product_details[$product['id']] = $product; // Store details for order items
    }
}

// Prevent duplicate order submission - check if same order was just created
$check_duplicate = $pdo->prepare("
    SELECT id FROM orders 
    WHERE user_id = ? 
    AND total = ? 
    AND shipping_name = ? 
    AND shipping_email = ? 
    AND created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
    ORDER BY created_at DESC 
    LIMIT 1
");
$check_duplicate->execute([$_SESSION['user_id'], $total, $name, $email]);
$recent_order = $check_duplicate->fetch(PDO::FETCH_ASSOC);

if ($recent_order) {
    // Duplicate submission detected - redirect to existing order
    $_SESSION['checkout_error'] = 'Order is already being processed.';
    header('Location: ../orders.php?order_success=true&order_id=' . $recent_order['id']);
    exit;
}

// Create order
try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'], $total, $name, $email, $phone,
        $address, $city, $state, $zip, $payment_method, 'Processing'
    ]);
    $order_id = $pdo->lastInsertId();
    
    // Create order items and update stock
    $stmt_order_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_update_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($items_to_process as $p_id => $quantity) {
        // Get product details for this product ID
        if (!isset($product_details[$p_id])) {
            throw new Exception('Product details not found for product ID: ' . $p_id);
        }
        $product = $product_details[$p_id];
        $stmt_order_item->execute([$order_id, $p_id, $quantity, $product['price']]);
        $stmt_update_stock->execute([$quantity, $p_id]);
    }
    
    // Clear selected items from cart only if it's a cart checkout
    if (!$is_direct_checkout) {
        foreach (array_keys($items_to_process) as $p_id) {
            unset($_SESSION['cart'][$p_id]);
        }
    }
    
    $pdo->commit();
    
    // Set success message in session and redirect to orders page with order ID
    $_SESSION['order_success'] = 'Order placed successfully!';
    header('Location: ../orders.php?order_success=true&order_id=' . $order_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['checkout_error'] = 'Error processing order: ' . $e->getMessage();
    header('Location: ' . $redirect_on_error);
    exit;
}
?>