<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Prevent admins from accessing checkout page
requireCustomer();

$page_title = 'Checkout';
$additional_js = ['cart.js'];
require_once 'cart/cart_session.php';

// Handle direct checkout from product_view.php
$direct_product_id = $_POST['product_id'] ?? ($_GET['product_id'] ?? null);
$direct_quantity = $_POST['quantity'] ?? ($_GET['quantity'] ?? null);
$is_direct_checkout = false;

// Determine if this request is a direct checkout (Buy Now)
if ($direct_product_id && $direct_quantity) {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=checkout.php?product_id=' . $direct_product_id . '&quantity=' . $direct_quantity);
        exit;
    }

    // Create a temporary cart for direct checkout
    $selected_cart = [$direct_product_id => (int)$direct_quantity];
    $selected_products_ids = [$direct_product_id];
    $is_direct_checkout = true;
} else {
    $cart = getCart();
}

if (!$is_direct_checkout && empty($cart)) {
    header('Location: cart.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = ''; // State field removed - using empty string
    $zip = $_POST['zip'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    // If not a direct checkout POST, then get selected products from form
    if (!$is_direct_checkout) {
        $selected_products_ids = $_POST['selected_products'] ?? [];
        $selected_cart = [];
        foreach ($selected_products_ids as $p_id) {
            if (isset($cart[$p_id])) {
                $selected_cart[$p_id] = $cart[$p_id];
            }
        }
    } else {
        // Ensure direct checkout cart is populated based on posted values
        $direct_product_id = $_POST['product_id'] ?? $direct_product_id;
        $direct_quantity = $_POST['quantity'] ?? $direct_quantity;
        if ($direct_product_id && $direct_quantity) {
            $selected_cart = [$direct_product_id => (int)$direct_quantity];
            $selected_products_ids = [$direct_product_id];
        }
    }

    if (empty($selected_cart)) {
        $error = 'No items selected for checkout.';
    } elseif (!empty($name) && !empty($email) && !empty($address) && !empty($payment_method)) {
        // Calculate total
        $total = 0;
        $placeholders = str_repeat('?,', count($selected_cart) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, price, stock, name FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_keys($selected_cart));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $product) {
            $quantity = $selected_cart[$product['id']];
            if ($quantity > $product['stock']) {
                $error = 'Insufficient stock for ' . $product['name'];
                break;
            }
            $total += $product['price'] * $quantity;
        }
        
        if (empty($error)) {
            // Create order
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $total, $name, $email, $phone, $address, $city, $state, $zip, $payment_method, 'pending']);
                $order_id = $pdo->lastInsertId();
                
                // Create order items
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                foreach ($products as $product) {
                    $quantity = $selected_cart[$product['id']];
                    $stmt->execute([$order_id, $product['id'], $quantity, $product['price']]);
                    
                    // Update stock
                    $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $update_stmt->execute([$quantity, $product['id']]);
                }
                
                // Clear only selected items from cart if not direct checkout
                if (!$is_direct_checkout) {
                    foreach ($selected_products_ids as $p_id) {
                        unset($_SESSION['cart'][$p_id]);
                    }
                }
                
                $pdo->commit();
                
                // Redirect to profile page with success message and order ID
                $_SESSION['order_success'] = 'Order placed successfully!';
                header('Location: profile.php?order_success=true&order_id=' . $order_id);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Error processing order: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
} else {
    // For initial GET request, ensure cart items are loaded for display
    // If it's a direct checkout, selected_cart is already populated
    if (!$is_direct_checkout) {
        $selected_products_ids = array_keys($cart);
        $selected_cart = $cart;
    }
}

// Fetch products for display on checkout page based on selected_cart
$display_cart_items = [];
$display_total = 0;
if (!empty($selected_cart)) {
    $placeholders = str_repeat('?,', count($selected_cart) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, price, stock, name FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($selected_cart));
    $display_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($display_products as $product) {
        $quantity = $selected_cart[$product['id']];
        $subtotal = $product['price'] * $quantity;
        $display_total += $subtotal;
        $display_cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <section class="checkout-section">
        <div class="container">
            <h1>Checkout</h1>
            
            <?php 
            // Display session error messages from API redirects
            if (isset($_SESSION['checkout_error'])) {
                $error = $_SESSION['checkout_error'];
                unset($_SESSION['checkout_error']);
            }
            ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="checkout-content">
                <form method="POST" action="checkout.php" class="checkout-form" data-animate>
                    <div class="form-section">
                        <h2>Shipping Information</h2>
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address *</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="zip">ZIP Code *</label>
                                <input type="text" id="zip" name="zip" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Payment Method</h2>
                        <div class="form-group">
                            <label>
                                <input type="radio" name="payment_method" value="credit_card" required>
                                Credit Card
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="paypal" required>
                                PayPal
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="cash_on_delivery" required>
                                Cash on Delivery
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn gradient-btn">Place Order</button>

                    <?php
                    // Persist selection for direct checkout
                    if ($is_direct_checkout && !empty($selected_products_ids)) {
                        echo '<input type="hidden" name="is_direct_checkout" value="1">';
                        echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($direct_product_id) . '">';
                        echo '<input type="hidden" name="quantity" value="' . htmlspecialchars($direct_quantity) . '">';
                    }
                    // Pass selected product IDs as hidden inputs if not a direct checkout
                    if (!$is_direct_checkout && !empty($selected_products_ids)) {
                        foreach ($selected_products_ids as $p_id) {
                            echo '<input type="hidden" name="selected_products[]" value="' . htmlspecialchars($p_id) . '">';
                        }
                    }
                    ?>

                </form>
                
                <div class="order-summary" data-animate>
                    <h2>Order Summary</h2>
                    <?php
                    if (!empty($display_cart_items)) {
                        foreach ($display_cart_items as $item) {
                            echo '<div class="order-item">';
                            echo '<span>' . htmlspecialchars($item['product']['name']) . ' x ' . $item['quantity'] . '</span>';
                            echo '<span>₱' . number_format($item['subtotal'], 2) . '</span>';
                            echo '</div>';
                        }
                        echo '<div class="order-total"><strong>Total: ₱' . number_format($display_total, 2) . '</strong></div>';
                    } else {
                        echo '<p>No items selected for checkout.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

