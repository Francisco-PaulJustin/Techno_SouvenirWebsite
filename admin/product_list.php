<?php
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$page_title = 'Product List';

// Handle delete result messages
$message = isset($_GET['deleted']) ? 'Product deleted successfully!' : '';
$error = isset($_GET['delete_blocked'])
    ? 'This product has been ordered, so it can\'t be deleted without removing it from customers\' order history. Set its stock to 0 to stop selling it.'
    : '';

// Fetch all products
$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php require_once 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span class="material-icons-round">inventory_2</span>
                    Product List
                </h1>
                <p class="page-subtitle">Manage your store's product inventory</p>
            </div>
            <div class="page-actions">
                <a href="add_product.php" class="btn btn-primary">
                    <span class="material-icons-round">add</span>
                    Add Product
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <span class="material-icons-round">check_circle</span>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <span class="material-icons-round">error</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Products Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Products (<?php echo count($products); ?>)</h2>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <span class="material-icons-round">inventory_2</span>
                                        <h3>No products yet</h3>
                                        <p>Start by adding your first product</p>
                                        <a href="add_product.php" class="btn btn-primary mt-2">
                                            <span class="material-icons-round">add</span>
                                            Add Product
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="cell-id">#<?php echo $product['id']; ?></td>
                                    <td class="cell-image">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: var(--clr-secondary); border-radius: 8px; display: grid; place-items: center;">
                                                <span class="material-icons-round text-muted">image</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></span>
                                    </td>
                                    <td style="font-weight: 600; color: var(--clr-primary);">
                                        ₱<?php echo number_format($product['price'], 2); ?>
                                    </td>
                                    <td>
                                        <?php if ($product['stock'] < 5): ?>
                                            <span class="status-badge status-cancelled"><?php echo $product['stock']; ?></span>
                                        <?php elseif ($product['stock'] < 10): ?>
                                            <span class="status-badge status-pending"><?php echo $product['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="status-badge status-completed"><?php echo $product['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($product['featured']): ?>
                                            <span class="status-badge status-completed">
                                                <span class="material-icons-round" style="font-size: 0.85rem;">star</span>
                                                Yes
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="cell-actions">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-ghost btn-sm" title="Edit">
                                            <span class="material-icons-round">edit</span>
                                        </a>
                                        <form method="POST" action="delete_product.php" style="display: inline;" id="delete-product-form-<?php echo $product['id']; ?>">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="button" class="btn btn-danger btn-sm" title="Delete"
                                                    onclick="handleDeleteProduct(<?php echo $product['id']; ?>, 'Are you sure you want to delete this product?');">
                                                <span class="material-icons-round">delete</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<script>
// Handle delete product with custom confirm
async function handleDeleteProduct(productId, message) {
    const confirmed = await customConfirm(message);
    if (confirmed) {
        document.getElementById('delete-product-form-' + productId).submit();
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
