<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
$page_title = 'Checkout';
$additional_js = ['cart.js'];
require_once 'cart/cart_session.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit;
}

$cart = getCart();
if (empty($cart)) {
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
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($address) && !empty($payment_method)) {
        // Calculate total
        $total = 0;
        $placeholders = str_repeat('?,', count($cart) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, price, stock, name FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_keys($cart));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $product) {
            $quantity = $cart[$product['id']];
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
                
                $message = 'Order placed successfully! Order ID: #' . $order_id;
                $cart = []; // Clear cart for display
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Error processing order: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
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
                            <input type="text" id="address" name="address" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State *</label>
                                <input type="text" id="state" name="state" required>
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
                </form>
                
                <div class="order-summary" data-animate>
                    <h2>Order Summary</h2>
                    <?php
                    if (!empty($cart)) {
                        $total = 0;
                        $placeholders = str_repeat('?,', count($cart) - 1) . '?';
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
                        $stmt->execute(array_keys($cart));
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($products as $product) {
                            $quantity = $cart[$product['id']];
                            $subtotal = $product['price'] * $quantity;
                            $total += $subtotal;
                            echo '<div class="order-item">';
                            echo '<span>' . htmlspecialchars($product['name']) . ' x ' . $quantity . '</span>';
                            echo '<span>₱' . number_format($subtotal, 2) . '</span>';
                            echo '</div>';
                        }
                        echo '<div class="order-total"><strong>Total: ₱' . number_format($total, 2) . '</strong></div>';
                    } else {
                        echo '<p>Your cart is empty. Add items to see a summary.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

