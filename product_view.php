<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect admins to admin interface if they try to access public pages
redirectAdminIfLoggedIn();

$product_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$product_id) {
    header('Location: products.php');
    exit;
}

require_once 'includes/product_functions.php';
$product = getProductById($product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}

$page_title = $product['name'] ?? 'Product';
$additional_css = ['product.css'];
$additional_js = ['product.js'];
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <section class="product-detail-section">
        <div class="container">
            <div class="product-detail" data-animate>
                <div class="product-image">
                    <img src="admin/uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                    <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    
                    <form class="add-to-cart-form" method="POST" action="api/add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        </div>
                        <button type="submit" class="btn gradient-btn">Add to Cart</button>
                        <button type="button" class="btn ghost-btn" id="buy-now-button">Buy Now</button>
                        <div class="cart-inline-message" id="cart-inline-message"></div>
                    </form>
                    
                    <?php
                    // Display success/error messages from session (fallback for non-JS users or direct form submission)
                    if (isset($_SESSION['cart_success'])) {
                        echo '<div class="alert alert-success" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['cart_success']) . '</div>';
                        unset($_SESSION['cart_success']);
                    }
                    if (isset($_SESSION['cart_error'])) {
                        echo '<div class="alert alert-error" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['cart_error']) . '</div>';
                        unset($_SESSION['cart_error']);
                    }
                    ?>
                    
                    <div class="product-meta">
                        <p><strong>Stock:</strong> <?php echo $product['stock']; ?> available</p>
                        <?php if (!empty($product['category_name'])): ?>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

