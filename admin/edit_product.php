<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$page_title = 'Edit Product';
$product_id = $_GET['id'] ?? null;
$message = '';
$error = '';

if (!$product_id) {
    header('Location: product_list.php');
    exit;
}

// Get product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: product_list.php');
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
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_image = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_image;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
                    unlink($upload_dir . $product['image']);
                }
                $image = $new_image;
            } else {
                $error = 'Error uploading image.';
            }
        } else {
            $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WebP';
        }
    }
    
    if (empty($error) && !empty($name) && !empty($price) && !empty($category_id)) {
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
        if (empty($error)) {
            $error = 'Please fill in all required fields.';
        }
    }
}

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php require_once 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <nav class="breadcrumb">
                    <a href="index.php">Dashboard</a>
                    <span class="separator">/</span>
                    <a href="product_list.php">Products</a>
                    <span class="separator">/</span>
                    <span>Edit Product</span>
                </nav>
                <h1 class="page-title">
                    <span class="material-icons-round">edit</span>
                    Edit Product
                </h1>
            </div>
            <div class="page-actions">
                <a href="product_list.php" class="btn btn-secondary">
                    <span class="material-icons-round">arrow_back</span>
                    Back to Products
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
        
        <!-- Edit Product Form -->
        <form method="POST" action="edit_product.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data" class="admin-form">
            <div class="form-header">
                <h2>Product Information</h2>
                <p>Update the product details below.</p>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <span class="material-icons-round">info</span>
                    Basic Details
                </div>
                
                <div class="form-group">
                    <label for="name">Product Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($product['name']); ?>"
                           placeholder="Enter product name">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" 
                              placeholder="Enter product description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    <span class="form-hint">Provide a detailed description of your product</span>
                </div>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <span class="material-icons-round">attach_money</span>
                    Pricing & Inventory
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (₱) <span class="required">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                               value="<?php echo $product['price']; ?>"
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity <span class="required">*</span></label>
                        <input type="number" id="stock" name="stock" min="0" required
                               value="<?php echo $product['stock']; ?>"
                               placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <span class="material-icons-round">image</span>
                    Product Image
                </div>
                
                <?php if (!empty($product['image'])): ?>
                    <div class="form-group">
                        <label>Current Image</label>
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--clr-secondary); border-radius: var(--radius-md);">
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="Current image" 
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: var(--radius-sm); box-shadow: var(--shadow-sm);">
                            <span class="text-muted">Current product image</span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="image">Upload New Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <span class="form-hint">Leave empty to keep the current image. Recommended size: 800x800px</span>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                        <span class="material-icons-round" style="color: var(--clr-accent); font-size: 1.1rem;">star</span>
                        Mark as Featured Product
                    </label>
                    <span class="form-hint">Featured products appear on the homepage</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <span class="material-icons-round">save</span>
                    Update Product
                </button>
                <a href="product_list.php" class="btn btn-secondary btn-lg">
                    Cancel
                </a>
            </div>
        </form>

<?php require_once 'includes/admin_footer.php'; ?>
