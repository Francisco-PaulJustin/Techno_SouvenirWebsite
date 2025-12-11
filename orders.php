<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Prevent admins from accessing user orders page
requireCustomer();

$page_title = 'My Orders';
$body_class = 'orders-page';
$additional_css = ['profile.css'];
$additional_js = ['orders.js'];

$message = '';
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
            $parts = explode(':', $item_str, 5);
            
            $pid = $parts[0] ?? null;
            $qty = $parts[1] ?? null;
            $price = $parts[2] ?? null;
            $pname = $parts[3] ?? null;
            $pimage = $parts[4] ?? null;
            
            $image_url = !empty($pimage) ? 'admin/uploads/' . $pimage : 'assets/images/placeholder.png';

            $order['items_data'][] = [
                'product_id' => $pid,
                'quantity' => $qty,
                'price' => $price,
                'name' => $pname,
                'image_url' => $image_url,
                'variant' => 'N/A'
            ];
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="profile-main fade-in">
    <div class="container">
        
        <!-- Page Header -->
        <div class="orders-page-header glass-card">
            <div class="orders-header-content">
                <p class="eyebrow-text">Your Account</p>
                <h1>My Orders</h1>
                <p class="orders-subtitle">Track and manage your order history</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- ORDER HISTORY -->
        <section class="profile-card glass-card order-history" id="orderHistory">
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
                                
                                <?php 
                                // Show cancel button only for cancellable orders
                                $can_cancel = in_array(strtolower($order['status']), ['pending', 'processing']);
                                ?>
                                <?php if ($can_cancel): ?>
                                    <div class="order-actions">
                                        <button type="button" class="btn btn-danger cancel-order-btn" 
                                                data-order-id="<?= $order['id'] ?>" 
                                                data-order-total="₱<?= number_format($order['total'], 2) ?>">
                                            <span class="material-icons-round">cancel</span>
                                            Cancel Order
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <p>You haven't placed any orders yet.</p>
                    <a href="products.php" class="btn gradient-btn">Start Shopping</a>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

