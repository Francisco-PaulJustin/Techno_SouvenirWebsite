<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../cart/cart_session.php';

$cart = getCart();
$count = array_sum($cart);

echo json_encode([
    'success' => true,
    'count' => $count
]);
?>

