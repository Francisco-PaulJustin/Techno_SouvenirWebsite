<?php
require_once '../includes/config.php';
require_once '../cart/cart_session.php';

// Detect if this is an AJAX request (JSON) or a regular form POST
$isAjax = false;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

// Check if it's an AJAX request by checking Content-Type header
// AJAX requests with JSON will have 'application/json' in Content-Type
// Regular form POSTs will have 'application/x-www-form-urlencoded' or similar
if (strpos($contentType, 'application/json') !== false) {
    $isAjax = true;
    header('Content-Type: application/json');
}

$isLoggedIn = isset($_SESSION['user_id']);

// Get data from JSON or POST
$data = [];
if ($isAjax) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $data = json_decode($rawInput, true) ?? [];
    }
}

$product_id = filter_var($data['product_id'] ?? $_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$quantity = (int)($data['quantity'] ?? $_POST['quantity'] ?? 1);

// Non-AJAX requests go back to the product page (or the product list)
$returnPage = $product_id ? 'product_view.php?id=' . $product_id : 'products.php';
$redirectUrl = '../' . $returnPage;

// Handle login requirement
if (!$isLoggedIn) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'requiresLogin' => true,
            'message' => 'You need to log in or create an account to add items to the cart.'
        ]);
    } else {
        // Redirect to login with return URL
        $redirectUrl = '../login.php?redirect=' . urlencode($returnPage);
        header('Location: ' . $redirectUrl);
    }
    exit;
}

// Validate product ID
if (!$product_id) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
    } else {
        $_SESSION['cart_error'] = 'Product ID is required';
        header('Location: ' . $redirectUrl);
    }
    exit;
}

if ($quantity < 1) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Quantity must be at least 1'
        ]);
    } else {
        $_SESSION['cart_error'] = 'Quantity must be at least 1';
        header('Location: ' . $redirectUrl);
    }
    exit;
}

// Verify product exists and has stock
$stmt = $pdo->prepare("SELECT id, stock, name FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
    } else {
        $_SESSION['cart_error'] = 'Product not found';
        header('Location: ' . $redirectUrl);
    }
    exit;
}

$cart = getCart();
$current_quantity = $cart[$product_id] ?? 0;
$new_quantity = $current_quantity + $quantity;

if ($new_quantity > $product['stock']) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock available'
        ]);
    } else {
        $_SESSION['cart_error'] = 'Insufficient stock available for ' . $product['name'];
        header('Location: ' . $redirectUrl);
    }
    exit;
}

// Add to cart
addToCart($product_id, $new_quantity);

// Handle response based on request type
if ($isAjax) {
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart'
    ]);
} else {
    // For regular form POST, redirect back with success message
    $_SESSION['cart_success'] = 'Product added to cart successfully!';
    header('Location: ' . $redirectUrl);
}
exit;
?>
