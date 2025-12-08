<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect admins to admin interface if they try to access public pages
redirectAdminIfLoggedIn();

$page_title = 'Home';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <?php
    // Get a random featured product image for hero background, or use first available product
    $hero_image = null;
    $hero_stmt = $pdo->query("SELECT image FROM products WHERE image IS NOT NULL AND image != '' ORDER BY RAND() LIMIT 1");
    $hero_product = $hero_stmt->fetch(PDO::FETCH_ASSOC);
    if ($hero_product && !empty($hero_product['image'])) {
        $hero_image = 'admin/uploads/' . $hero_product['image'];
    }
    ?>
    <section class="hero fade-in" <?php if ($hero_image): ?>style="background-image: linear-gradient(135deg, rgba(90, 46, 152, 0.88) 0%, rgba(123, 75, 191, 0.8) 40%, rgba(255, 184, 0, 0.75) 100%), url('<?php echo htmlspecialchars($hero_image); ?>');"<?php endif; ?>>
        <div class="container hero-content">
            <span class="hero-badge">✨ Handcrafted with Love</span>
            <h1>Collect Moments, Not Things.</h1>
            <p>Discover handcrafted souvenirs, artisan gifts, and travel keepsakes curated from around the world.</p>
            <div class="hero-actions">
                <a href="products.php" class="btn hero-btn-primary">
                    <span>Shop the Collection</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="categories.php" class="btn hero-btn-secondary">Browse Categories</a>
            </div>
        </div>
    </section>

    <section class="featured-products" data-animate>
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow-text">Staff Picks</p>
                <h2>Featured Souvenirs</h2>
            </div>
            <div class="products-grid">
                <?php
                // Featured products will be displayed here
                require_once 'includes/product_functions.php';
                $featured = getFeaturedProducts();
                if ($featured) {
                    foreach ($featured as $product) {
                        echo '<div class="product-card" data-animate>';
                        echo '<img src="admin/uploads/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                        echo '<p class="price">₱' . number_format($product['price'], 2) . '</p>';
                        echo '<a href="product_view.php?id=' . $product['id'] . '" class="btn ghost-btn">View Details</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No featured products available right now.</p>';
                }
                ?>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

