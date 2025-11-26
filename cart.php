<?php
session_start();
require_once 'includes/config.php';
$page_title = 'Cart';
$additional_css = ['cart.css'];
$additional_js = ['cart.js'];
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'cart/cart_session.php';

$cart = getCart();
$cart_items = [];
$total = 0;

if (!empty($cart)) {
    $placeholders = str_repeat('?,', count($cart) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $cart[$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<main>
    <section class="cart-section">
        <div class="container">
            <h1>Shopping Cart</h1>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <p>Your cart is empty.</p>
                    <a href="products.php" class="btn gradient-btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-items">
                    <table class="cart-table" data-animate>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="assets/images/products/<?php echo htmlspecialchars($item['product']['image']); ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                                        <span><?php echo htmlspecialchars($item['product']['name']); ?></span>
                                    </td>
                                    <td>$<?php echo number_format($item['product']['price'], 2); ?></td>
                                    <td>
                                        <input type="number" class="quantity-input" data-product-id="<?php echo $item['product']['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['product']['stock']; ?>">
                                    </td>
                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    <td>
                                        <button class="btn ghost-btn remove-from-cart" data-product-id="<?php echo $item['product']['id']; ?>">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn ghost-btn">Continue Shopping</a>
                        <a href="checkout.php" class="btn gradient-btn">Proceed to Checkout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

