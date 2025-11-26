<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';
require_once 'includes/admin_header.php';
require_once 'includes/admin_sidebar.php';

$product_id = $_GET['id'] ?? null;
$message = '';
$error = '';

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Get product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = $product['image']; // Keep existing image by default
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_image = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_image;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Delete old image if exists
            if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                unlink($upload_dir . $product['image']);
            }
            $image = $new_image;
        }
    }
    
    if (!empty($name) && !empty($price) && !empty($category_id)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, featured = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image, $featured, $product_id])) {
            $message = 'Product updated successfully!';
            // Refresh product data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Error updating product.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-content">
    <h1>Edit Product</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="edit_product.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image">Product Image</label>
            <?php if (!empty($product['image'])): ?>
                <p>Current: <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Current image" style="max-width: 100px;"></p>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                Featured Product
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

