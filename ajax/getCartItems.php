<?php
session_start();

if (!isset($_SESSION['cards'])) {
    $_SESSION['cards'] = [];
}
if (!isset($_SESSION['dumps'])) {
    $_SESSION['dumps'] = [];
}

$cartItems = array_merge($_SESSION['cards'], $_SESSION['dumps']);
$total = array_sum(array_column($cartItems, 'price'));

if (!empty($cartItems)) {
    echo json_encode([
        'success' => true,
        'cartItems' => $cartItems,
        'total' => $total,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty.',
    ]);
}
