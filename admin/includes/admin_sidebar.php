<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" aria-label="Admin navigation">
    <button class="sidebar-toggle sidebar-toggle-inline" aria-label="Toggle sidebar" aria-expanded="true" title="Collapse sidebar">
        <span class="material-icons-round">chevron_left</span>
    </button>
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon material-icons-round">store</span>
            <h2><?php echo SITE_NAME; ?></h2>
        </div>
        <span class="sidebar-badge">Admin</span>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">Main</span>
            <ul>
                <li>
                    <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                        <span class="nav-icon material-icons-round">dashboard</span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Products</span>
            <ul>
                <li>
                    <a href="add_product.php" class="<?php echo $current_page === 'add_product.php' ? 'active' : ''; ?>">
                        <span class="nav-icon material-icons-round">add_box</span>
                        <span class="nav-label">Add Product</span>
                    </a>
                </li>
                <li>
                    <a href="product_list.php" class="<?php echo $current_page === 'product_list.php' ? 'active' : ''; ?>">
                        <span class="nav-icon material-icons-round">inventory_2</span>
                        <span class="nav-label">Product List</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">Management</span>
            <ul>
                <li>
                    <a href="manage_orders.php" class="<?php echo $current_page === 'manage_orders.php' ? 'active' : ''; ?>">
                        <span class="nav-icon material-icons-round">receipt_long</span>
                        <span class="nav-label">Manage Orders</span>
                    </a>
                </li>
                <li>
                    <a href="manage_users.php" class="<?php echo $current_page === 'manage_users.php' ? 'active' : ''; ?>">
                        <span class="nav-icon material-icons-round">people</span>
                        <span class="nav-label">Manage Users</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section nav-section-bottom">
            <ul>
                <li>
                    <a href="../index.php" class="nav-link-external">
                        <span class="nav-icon material-icons-round">launch</span>
                        <span class="nav-label">View Website</span>
                    </a>
                </li>
                <li>
                    <a href="../logout.php" class="nav-link-logout">
                        <span class="nav-icon material-icons-round">logout</span>
                        <span class="nav-label">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <div class="admin-user-info">
            <div class="admin-avatar">
                <span class="material-icons-round">admin_panel_settings</span>
            </div>
            <div class="admin-user-details">
                <span class="admin-name"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></span>
                <span class="admin-role">Administrator</span>
            </div>
        </div>
    </div>
</aside>
