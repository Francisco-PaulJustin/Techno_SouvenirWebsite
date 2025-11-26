<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

require_once '../includes/product_functions.php';
$product = getProductById($product_id);

if ($product) {
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
}
?>

