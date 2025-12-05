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
    <section class="hero fade-in">
        <div class="container">
            <h1>Collect Moments, Not Things.</h1>
            <p>Discover handcrafted souvenirs, artisan gifts, and travel keepsakes curated from around the world.</p>
            <div class="hero-actions">
                <a href="products.php" class="btn gradient-btn">Shop the Collection</a>
                <a href="categories.php" class="btn ghost-btn">Browse Categories</a>
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

