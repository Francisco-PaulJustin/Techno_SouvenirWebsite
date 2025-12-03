<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';
require_once 'includes/admin_header.php';  // Loads header & top bar

// Fetch all products
$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product List</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>


    <!-- SIDEBAR -->
    <?php require_once 'includes/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="admin-content">
        <h1>Product List</h1>

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
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td>
                        <?php if (!empty($product['image'])): ?>
                            <img src="uploads/<?php echo $product['image']; ?>" 
                                 style="width:60px; border-radius:10px;">
                        <?php else: ?>
                            <span class="text-muted">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['stock']; ?></td>
                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                    <td><?php echo $product['featured'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-sm btn-primary">Edit</a>

                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
        
    </div>


<?php require_once 'includes/admin_footer.php'; ?>
</body>
</html>
