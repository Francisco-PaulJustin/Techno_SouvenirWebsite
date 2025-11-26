<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';
require_once 'includes/admin_header.php';
require_once 'includes/admin_sidebar.php';

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
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $image;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Image uploaded successfully
        } else {
            $error = 'Error uploading image.';
        }
    }
    
    if (empty($error) && !empty($name) && !empty($price) && !empty($category_id)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image, $featured])) {
            $message = 'Product added successfully!';
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
?>

<div class="admin-content">
    <h1>Add Product</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="add_product.php" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" id="stock" name="stock" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" value="1">
                Featured Product
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

