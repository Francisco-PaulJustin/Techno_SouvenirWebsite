<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

require_once '../includes/product_functions.php';
$products = getProducts($search, $category, $sort, $limit, $offset);

echo json_encode([
    'success' => true,
    'products' => $products
]);
?>

