<?php
session_start();
require_once 'includes/config.php';

$product_id = $_GET['id'] ?? null;

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
                    <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                    <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    
                    <form class="add-to-cart-form" method="POST" action="api/add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        </div>
                        <button type="submit" class="btn gradient-btn">Add to Cart</button>
                    </form>
                    
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

