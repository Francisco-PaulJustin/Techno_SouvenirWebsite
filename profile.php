<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Prevent admins from accessing user profile page
requireCustomer();

$page_title = 'Profile';
$body_class = 'profile-page';
$additional_css = ['profile.css'];
$additional_js = ['profile.js'];

$message = '';
$error = '';
$user_id = $_SESSION['user_id'];
$order_id_to_show = null;

// Check if redirected from checkout with order success
if (isset($_GET['order_success']) && $_GET['order_success'] === 'true') {
    if (isset($_SESSION['order_success'])) {
        $message = $_SESSION['order_success'];
        unset($_SESSION['order_success']);
    } else {
        $message = "Order placed successfully!";
    }
    
    // Get order ID from URL if present
    $order_id_to_show = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, address, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch Orders
$orders_stmt = $pdo->prepare("
    SELECT
        o.*,
        GROUP_CONCAT(
            COALESCE(oi.product_id, '') , ':',
            COALESCE(oi.quantity, '') , ':',
            COALESCE(oi.price, '') , ':',
            COALESCE(p.name, 'Unknown Product') , ':',
            COALESCE(p.image, '')
            SEPARATOR ';'
        ) AS items_full_data
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders_stmt->execute([$user_id]);
$user_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Parse order item data
foreach ($user_orders as &$order) {
    $order['items_data'] = [];
    if (!empty($order['items_full_data'])) {
        $products_raw = explode(';', $order['items_full_data']);
        foreach ($products_raw as $item_str) {
            $parts = explode(':', $item_str, 5); // Limit explode to 5 parts
            
            $pid = $parts[0] ?? null;
            $qty = $parts[1] ?? null;
            $price = $parts[2] ?? null;
            $pname = $parts[3] ?? null;
            $pimage = $parts[4] ?? null; // Can be null if p.image was empty
            
            $image_url = !empty($pimage) ? 'uploads/' . $pimage : 'assets/images/placeholder.png'; // Provide a default placeholder

            $order['items_data'][] = [
                'product_id' => $pid,
                'quantity' => $qty,
                'price' => $price,
                'name' => $pname,
                'image_url' => $image_url,
                'variant' => 'N/A' // Assuming no variants for now
            ];
        }
    }
}

function refreshUserSession($user)
{
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_profile_image'] = $user['profile_image'] ?? null;
}

// FORM PROCESSING
// Handle avatar upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
        $target_dir = "uploads/profiles/";
        // Ensure the directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_file_type = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $unique_file_name = uniqid() . '.' . $image_file_type;
        $target_file = $target_dir . $unique_file_name;
        $upload_ok = 1;
    
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check !== false) {
            $upload_ok = 1;
        } else {
            $error = "File is not an image.";
            $upload_ok = 0;
        }
    
        // Check file size (e.g., 5MB limit)
        if ($_FILES['profile_image']['size'] > 5000000) {
            $error = "Sorry, your file is too large. Max 5MB.";
            $upload_ok = 0;
        }
    
        // Allow certain file formats
        if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif") {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $upload_ok = 0;
        }
    
        // Check if $upload_ok is set to 0 by an error
        if ($upload_ok == 0) {
            $error = "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                // Delete old profile image if it exists and is not a default image
                if ($user['profile_image'] && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
    
                $update_image_stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                if ($update_image_stmt->execute([$target_file, $user_id])) {
                    $user['profile_image'] = $target_file;
                    refreshUserSession($user);
                    $message = "Profile image updated successfully!";
                    header('Location: profile.php');
                    exit();
                } else {
                    $error = "Error updating database with new image path.";
                }
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
    
    // FORM PROCESSING
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form_type = $_POST['form_type'] ?? '';
    
        // Update Profile
        if ($form_type === 'profile_update') {
            $first_name = trim($_POST['first_name']);
            $last_name  = trim($_POST['last_name']);
            $email      = trim($_POST['email']);
            $phone      = trim($_POST['phone']);
            $address    = trim($_POST['address']);
    
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $checkStmt->execute([$email, $user_id]);
    
                if ($checkStmt->fetch()) {
                    $error = "Email already in use.";
                } else {
                    $updateStmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=? WHERE id=?");
                    if ($updateStmt->execute([$first_name, $last_name, $email, $phone, $address, $user_id])) {
                        $user['first_name'] = $first_name;
                        $user['last_name'] = $last_name;
                        $user['email'] = $email;
                        $user['phone'] = $phone;
                        $user['address'] = $address;
                        refreshUserSession($user);
                        $message = "Profile updated successfully!";
                        header('Location: profile.php?form_submitted=true&active_section=profile-details-section');
                        exit();
                    }
                }
            }
        }
    
        // Update Password
        elseif ($form_type === 'password_update') {
            $current_password = $_POST['current_password'];
            $new_password     = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
    
            if ($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
            } elseif (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
                $stmt->execute([$user_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!password_verify($current_password, $row['password'])) {
                    $error = "Incorrect current password.";
                } else {
                    $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                    if ($update->execute([$new_hashed, $user_id])) {
                        $message = "Password updated successfully!";
                        header('Location: profile.php?form_submitted=true&active_section=password-section');
                        exit();
                    }
                }
            }
        }
    }

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="profile-main fade-in">
    <div class="container">

        <!-- PROFILE HEADER -->
        <div class="profile-header glass-card">
            <div class="profile-avatar">
                <form id="avatar-form" action="profile.php" method="POST" enctype="multipart/form-data">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image">
                    <?php else: ?>
                        <span class="avatar-placeholder"><?= strtoupper($user['first_name'][0]) ?></span>
                    <?php endif; ?>
                    <label for="profile_image_upload" class="btn ghost-btn upload-btn">Upload Image</label>
                    <input type="file" name="profile_image" id="profile_image_upload" accept="image/*" style="display: none;">
                </form>
            </div>

            <div class="profile-meta">
                <p class="eyebrow-text">Your Account</p>
                <h1><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h1>
                <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                <?php if (!empty($user['phone'])): ?>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['address'])): ?>
                    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($user['address'])) ?></p>
                <?php endif; ?>

                <button class="btn ghost-btn" id="btnShowProfile" data-target-section="profile-details-section">Update Profile</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
    
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- PROFILE GRID (Hidden on load) -->
        <div class="profile-grid" id="profileGrid" style="display: none;">

            <!-- UPDATE PROFILE -->
            <section class="profile-card glass-card" id="profile-details-section">
                <header><h2>Update Profile</h2></header>

                <form method="POST">
                    <input type="hidden" name="form_type" value="profile_update">

                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>

                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>

                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

                    <label>Address</label>
                    <textarea name="address" required><?= htmlspecialchars($user['address']) ?></textarea>

                    <button class="btn gradient-btn">Save Changes</button>
                </form>
            </section>

            <!-- UPDATE PASSWORD -->
            <section class="profile-card glass-card" id="password-section">
                <header><h2>Change Password</h2></header>

                <form method="POST">
                    <input type="hidden" name="form_type" value="password_update">

                    <label>Current Password</label>
                    <input type="password" name="current_password" required>

                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">

                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6">

                    <button class="btn gradient-btn">Update Password</button>
                </form>
            </section>
        </div>

        <!-- ORDER HISTORY (Default view) -->
        <section class="profile-card glass-card order-history" id="orderHistory">
            <header><h2>Your Orders</h2></header>

            <?php if ($user_orders): ?>
                <div class="orders-list">
                    <?php foreach ($user_orders as $order): ?>
                        <?php
                        $order_id_slug = 'order-' . $order['id'];
                        $status_class = strtolower(str_replace(' ', '-', $order['status']));
                        // Mark the order to auto-expand if it's the newly placed order
                        $is_new_order = ($order_id_to_show && $order['id'] == $order_id_to_show);
                        ?>
                        <div class="order-card" <?php if ($is_new_order): ?>id="new-order-<?= $order['id'] ?>" data-new-order="true"<?php endif; ?>>
                            <button class="order-summary-header" aria-expanded="<?= $is_new_order ? 'true' : 'false' ?>" aria-controls="<?= $order_id_slug ?>-details">
                                <div class="order-info">
                                    <h3>
                                        <?php
                                        if (!empty($order['items_data'])) {
                                            $product_names = array_column($order['items_data'], 'name');
                                            echo htmlspecialchars(implode(', ', $product_names));
                                        } else {
                                            echo "No products in this order";
                                        }
                                        ?>
                                    </h3>
                                    <p class="order-date"><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                                </div>
                                <div class="order-status-total">
                                    <span class="status-pill status-<?= $status_class ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span>
                                    <p class="order-total">₱<?= htmlspecialchars(number_format($order['total'], 2)) ?></p>
                                </div>
                            </button>

                            <div id="<?= $order_id_slug ?>-details" class="order-details-panel" <?php if (!$is_new_order): ?>hidden<?php endif; ?>>
                                <div class="order-products-summary">
                                    <h4>Products:</h4>
                                    <div class="product-list-items">
                                        <?php foreach ($order['items_data'] as $item): ?>
                                            <div class="product-item-row">
                                                <div class="product-thumbnail">
                                                    <?php if (!empty($item['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?> thumbnail">
                                                    <?php else: ?>
                                                        <div class="placeholder-thumbnail"></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product-details-info">
                                                    <p class="product-name"><?= htmlspecialchars($item['name']) ?></p>
                                                    <p class="product-variant">Variant: <?= htmlspecialchars($item['variant']) ?></p>
                                                    <p class="product-qty-price">Qty: <?= htmlspecialchars($item['quantity']) ?> × ₱<?= htmlspecialchars(number_format($item['price'], 2)) ?></p>
                                                    <p class="product-line-total">Line Total: ₱<?= htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)) ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="order-shipping-payment">
                                    <h4>Shipping & Payment:</h4>
                                    <p><strong>Shipping Method:</strong> <?= htmlspecialchars($order['shipping_method'] ?? 'Standard') ?></p>
                                    <?php if (!empty($order['tracking_number'])): ?>
                                        <p><strong>Tracking:</strong> 
                                            <a href="<?= htmlspecialchars($order['tracking_url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($order['tracking_number']) ?> <span class="material-icons icon-link">launch</span>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    <p><strong>Delivery Address:</strong> <?php 
                                        $addressParts = array_filter([
                                            $order['shipping_address'],
                                            $order['shipping_city'],
                                            $order['shipping_state'],
                                            $order['shipping_zip']
                                        ]);
                                        echo nl2br(htmlspecialchars(implode(', ', $addressParts)));
                                    ?></p>
                                    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                                </div>

                               
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No orders yet.</p>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
