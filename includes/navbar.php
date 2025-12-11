<nav class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="index.php"><img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo"></a>
            <span style="font-size: 1.5rem; font-weight: bold; color: #333; margin-left: 0.5rem;">MemoCraft</span>
        </div>
        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <div class="nav-actions">
            <?php 
            // Only show user interface elements for customers, not admins
            $is_customer = isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
            ?>
            <?php if ($is_customer): ?>
                <a href="cart.php" class="cart-link nav-btn" aria-label="Shopping cart">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-text">Cart (<span id="cart-count">0</span>)</span>
                </a>
                <a href="orders.php" class="nav-btn">Orders</a>
                <div class="nav-profile">
                    <?php
                        $first_name = $_SESSION['user_first_name'] ?? ($_SESSION['user_name'] ?? 'User');
                        $profile_image = $_SESSION['user_profile_image'] ?? null;
                    ?>
                    <button class="nav-profile-btn" aria-haspopup="true" aria-expanded="false">
                        <span class="nav-avatar">
                            <?php if ($profile_image): ?>
                                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="<?php echo htmlspecialchars($first_name); ?>">
                            <?php else: ?>
                                <span class="nav-avatar-initials"><?php echo strtoupper(substr($first_name, 0, 1)); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="nav-profile-name"><?php echo htmlspecialchars($first_name); ?></span>
                        <span class="nav-profile-caret">&#9662;</span>
                    </button>
                    <div class="nav-profile-menu">
                        <a href="profile.php">Profile</a>
                        <a href="logout.php" class="logout-link">Logout</a>
                    </div>
                </div>
            <?php elseif (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <!-- Admin is logged in - don't show user interface elements -->
                <!-- Admin should use admin interface, not user interface -->
            <?php else: ?>
                <a href="login.php" class="nav-btn ghost-btn">Login</a>
                <a href="signup.php" class="nav-btn primary-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

