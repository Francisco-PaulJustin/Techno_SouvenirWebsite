<?php
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

// Deleting changes data, so it must be a POST from the product list (not a plain link)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product_list.php');
    exit;
}

$product_id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($product_id) {
    // order_items cascade on product delete, which would erase lines from customers'
    // past orders, so products that were ever ordered can't be deleted
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: product_list.php?delete_blocked=1');
        exit;
    }

    // Get product to delete image
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    // Delete image file if exists
    if ($product && !empty($product['image']) && file_exists('uploads/' . basename($product['image']))) {
        unlink('uploads/' . basename($product['image']));
    }
}

header('Location: product_list.php?deleted=1');
exit;
?>
