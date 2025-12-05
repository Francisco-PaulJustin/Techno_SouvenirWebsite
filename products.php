<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect admins to admin interface if they try to access public pages
redirectAdminIfLoggedIn();

$page_title = 'Products';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

require_once 'includes/product_functions.php';
$products = getProducts($search, $category, $sort, $per_page, $offset);
$total_products = getProductCount($search, $category);
$total_pages = ceil($total_products / $per_page);
?>

<main>
    <section class="products-section">
        <div class="container">
            <div class="section-heading" data-animate>
                <p class="eyebrow-text">All Products</p>
                <h1>Browse souvenirs from every corner of the globe.</h1>
            </div>
            
            <div class="products-filters" data-animate>
                <form method="GET" action="products.php" class="filter-form">
                    <input type="text" name="search" placeholder="Search artisan goods..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                        while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($category == $cat['id']) ? 'selected' : '';
                            echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                        }
                        ?>
                    </select>
                    <select name="sort">
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                    <button type="submit" class="btn gradient-btn">Filter</button>
                </form>
            </div>
            
            <div class="products-grid">
                <?php
                if ($products) {
                    foreach ($products as $product) {
                        echo '<div class="product-card" data-animate>';
                        echo '<img src="admin/uploads/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                        echo '<p class="price">₱' . number_format($product['price'], 2) . '</p>';
                        echo '<a href="product_view.php?id=' . $product['id'] . '" class="btn ghost-btn">View Details</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No products found.</p>';
                }
                ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $query_params = $_GET;
                        $query_params['page'] = $i;
                        $query_string = http_build_query($query_params);
                        $active = ($i == $page) ? 'active' : '';
                        echo '<a href="products.php?' . $query_string . '" class="' . $active . '">' . $i . '</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

