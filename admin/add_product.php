<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$page_title = 'Add Product';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $image = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error = 'Error uploading image.';
            }
        } else {
            $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WebP';
        }
    }
    
    if (empty($error) && !empty($name) && !empty($price) && !empty($category_id)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image, $featured])) {
            $message = 'Product added successfully!';
            // Clear form
            $name = $description = $price = $stock = $category_id = '';
        } else {
            $error = 'Error adding product.';
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
                    <span>Add Product</span>
                </nav>
                <h1 class="page-title">
                    <span class="material-icons-round">add_box</span>
                    Add New Product
                </h1>
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
        
        <!-- Add Product Form -->
        <form method="POST" action="add_product.php" enctype="multipart/form-data" class="admin-form">
            <div class="form-header">
                <h2>Product Information</h2>
                <p>Fill in the details below to add a new product to your store.</p>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <span class="material-icons-round">info</span>
                    Basic Details
                </div>
                
                <div class="form-group">
                    <label for="name">Product Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                           placeholder="Enter product name">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" 
                              placeholder="Enter product description"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
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
                               value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>"
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity <span class="required">*</span></label>
                        <input type="number" id="stock" name="stock" min="0" required
                               value="<?php echo isset($stock) ? htmlspecialchars($stock) : ''; ?>"
                               placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
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
                
                <div class="form-group">
                    <label for="image">Upload Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <span class="form-hint">Recommended size: 800x800px. Max file size: 5MB</span>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="featured" value="1">
                        <span class="material-icons-round" style="color: var(--clr-accent); font-size: 1.1rem;">star</span>
                        Mark as Featured Product
                    </label>
                    <span class="form-hint">Featured products appear on the homepage</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <span class="material-icons-round">add</span>
                    Add Product
                </button>
                <a href="product_list.php" class="btn btn-secondary btn-lg">
                    Cancel
                </a>
            </div>
        </form>

<?php require_once 'includes/admin_footer.php'; ?>
