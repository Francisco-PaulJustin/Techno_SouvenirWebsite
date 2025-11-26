<?php
session_start();
require_once '../includes/config.php';
require_once 'includes/admin_auth.php';

$product_id = $_GET['id'] ?? null;

if ($product_id) {
    // Get product to delete image
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    // Delete image file if exists
    if ($product && !empty($product['image']) && file_exists('uploads/' . $product['image'])) {
        unlink('uploads/' . $product['image']);
    }
}

header('Location: index.php');
exit;
?>

