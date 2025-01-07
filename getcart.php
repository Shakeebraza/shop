<?php

session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$total = array_sum(array_column($_SESSION['cart'], 'price'));

echo json_encode([
    'success' => true,
    'cartItems' => array_values($_SESSION['cart']),
    'total' => $total,
]);

?>