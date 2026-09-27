<?php
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$page_title = 'Manage Categories';
$message = '';
$error = '';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            // Check if category name already exists
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                $error = 'Category name already exists.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                if ($stmt->execute([$name, $description])) {
                    $message = 'Category added successfully!';
                } else {
                    $error = 'Error adding category.';
                }
            }
        }
    } elseif (isset($_POST['update_category'])) {
        $category_id = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } elseif (!$category_id) {
            $error = 'Invalid category ID.';
        } else {
            // Check if category name already exists (excluding current category)
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $category_id]);
            if ($stmt->fetch()) {
                $error = 'Category name already exists.';
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $category_id])) {
                    $message = 'Category updated successfully!';
                } else {
                    $error = 'Error updating category.';
                }
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        $category_id = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        
        if ($category_id) {
            // Check if any products are using this category
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $product_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($product_count > 0) {
                $error = "Cannot delete category. There are {$product_count} product(s) using this category. Please reassign or delete those products first.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                if ($stmt->execute([$category_id])) {
                    $message = 'Category deleted successfully!';
                } else {
                    $error = 'Error deleting category.';
                }
            }
        } else {
            $error = 'Invalid category ID.';
        }
    }
}

// Get all categories with product counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total category count
$total_categories = count($categories);

// Get categories with products
$categories_with_products = 0;
foreach ($categories as $cat) {
    if ($cat['product_count'] > 0) {
        $categories_with_products++;
    }
}

require_once 'includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php require_once 'includes/admin_sidebar.php'; ?>
    
    <main class="admin-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span class="material-icons-round">category</span>
                    Manage Categories
                </h1>
                <p class="page-subtitle">Organize your products by categories</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" onclick="openAddModal()">
                    <span class="material-icons-round">add</span>
                    Add Category
                </button>
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
        
        <!-- Category Stats -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Categories</h3>
                    <div class="stat-icon purple">
                        <span class="material-icons-round">category</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo $total_categories; ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Active Categories</h3>
                    <div class="stat-icon blue">
                        <span class="material-icons-round">inventory_2</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo $categories_with_products; ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Empty Categories</h3>
                    <div class="stat-icon yellow">
                        <span class="material-icons-round">folder_open</span>
                    </div>
                </div>
                <p class="stat-number"><?php echo $total_categories - $categories_with_products; ?></p>
            </div>
        </div>
        
        <!-- Categories Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Categories (<?php echo $total_categories; ?>)</h2>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <span class="material-icons-round">category</span>
                                        <h3>No categories yet</h3>
                                        <p>Start by adding your first category</p>
                                        <button type="button" class="btn btn-primary mt-2" onclick="openAddModal()">
                                            <span class="material-icons-round">add</span>
                                            Add Category
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="cell-id">#<?php echo $category['id']; ?></td>
                                    <td>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($category['name']); ?></span>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo !empty($category['description']) ? htmlspecialchars($category['description']) : '<span style="color: var(--clr-text-muted); font-style: italic;">No description</span>'; ?>
                                    </td>
                                    <td>
                                        <?php if ($category['product_count'] > 0): ?>
                                            <span class="status-badge status-completed"><?php echo $category['product_count']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                    <td class="cell-actions">
                                        <button type="button" 
                                                class="btn btn-ghost btn-sm" 
                                                title="Edit"
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                            <span class="material-icons-round">edit</span>
                                        </button>
                                        <form method="POST" action="manage_categories.php" style="display: inline;" id="delete-category-form-<?php echo $category['id']; ?>">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <input type="hidden" name="delete_category" value="1">
                                            <button type="button" class="btn btn-danger btn-sm" title="Delete"
                                                    onclick="handleDeleteCategory(<?php echo $category['id']; ?>, 'Are you sure you want to delete this category? This action cannot be undone.');">
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
    </main>
</div>

<!-- Add Category Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="material-icons-round">add_box</span>
                Add New Category
            </h2>
            <button type="button" class="modal-close" onclick="closeAddModal()">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form method="POST" action="manage_categories.php">
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_name">Category Name <span class="required">*</span></label>
                    <input type="text" id="add_name" name="name" required placeholder="e.g., Keychains, Magnets, Postcards">
                </div>
                <div class="form-group">
                    <label for="add_description">Description</label>
                    <textarea id="add_description" name="description" rows="3" placeholder="Optional description for this category"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeAddModal()">Cancel</button>
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="material-icons-round">edit</span>
                Edit Category
            </h2>
            <button type="button" class="modal-close" onclick="closeEditModal()">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form method="POST" action="manage_categories.php">
            <input type="hidden" id="edit_category_id" name="category_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">Category Name <span class="required">*</span></label>
                    <input type="text" id="edit_name" name="name" required placeholder="e.g., Keychains, Magnets, Postcards">
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3" placeholder="Optional description for this category"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    const modal = document.getElementById('addModal');
    modal.style.display = 'flex';
    document.getElementById('add_name').focus();
}

function closeAddModal() {
    const modal = document.getElementById('addModal');
    modal.style.display = 'none';
    document.querySelector('#addModal form').reset();
}

function openEditModal(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    document.getElementById('edit_name').focus();
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
    document.querySelector('#editModal form').reset();
}

// Handle delete category with custom confirm
async function handleDeleteCategory(categoryId, message) {
    const confirmed = await customConfirm(message);
    if (confirmed) {
        document.getElementById('delete-category-form-' + categoryId).submit();
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target == addModal) {
        closeAddModal();
    }
    if (event.target == editModal) {
        closeEditModal();
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>

