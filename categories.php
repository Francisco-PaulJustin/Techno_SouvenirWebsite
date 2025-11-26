<?php
session_start();
require_once 'includes/config.php';
$page_title = 'Categories';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Get category from URL
$category_id = $_GET['id'] ?? null;
$category_name = 'All Categories';

if ($category_id) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($category) {
        $category_name = $category['name'];
    }
}
?>

<main>
    <section class="categories-section">
        <div class="container">
            <div class="section-heading" data-animate>
                <p class="eyebrow-text">Shop by Mood</p>
                <h1>Discover souvenirs by curated categories.</h1>
            </div>
            
            <div class="categories-list">
                <?php
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($categories as $cat) {
                    echo '<div class="category-card" data-animate>';
                    echo '<a href="categories.php?id=' . $cat['id'] . '">';
                    echo '<h3>' . htmlspecialchars($cat['name']) . '</h3>';
                    if (!empty($cat['description'])) {
                        echo '<p>' . htmlspecialchars($cat['description']) . '</p>';
                    } else {
                        echo '<p>Hand picked items inspired by ' . htmlspecialchars($cat['name']) . '.</p>';
                    }
                    echo '<span class="btn ghost-btn">Explore</span>';
                    echo '</a>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <?php if ($category_id): ?>
                <div class="category-products" data-animate>
                    <div class="section-heading">
                        <p class="eyebrow-text">Now viewing</p>
                        <h2><?php echo htmlspecialchars($category_name); ?></h2>
                    </div>
                    <?php
                    require_once 'includes/product_functions.php';
                    $products = getProductsByCategory($category_id);
                    if ($products) {
                        echo '<div class="products-grid">';
                        foreach ($products as $product) {
                            echo '<div class="product-card" data-animate>';
                            echo '<img src="assets/images/products/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                            echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                            echo '<p class="price">$' . number_format($product['price'], 2) . '</p>';
                            echo '<a href="product_view.php?id=' . $product['id'] . '" class="btn ghost-btn">View Details</a>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No products available in this category yet.</p>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

