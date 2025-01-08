<?php

session_start();

if (!isset($_SESSION['cards'])) {
    $_SESSION['cards'] = [];
}
if (!isset($_SESSION['dumps'])) {
    $_SESSION['dumps'] = [];
}


$cartTotal = array_sum(array_column($_SESSION['cards'], 'price'));


$dumpsTotal = array_sum(array_column($_SESSION['dumps'], 'price'));


$total = $cartTotal + $dumpsTotal;

echo json_encode([
    'success' => true,
    'cartItems' => array_values($_SESSION['cards']),
    'dumpsItems' => array_values($_SESSION['dumps']),
    'total' => $total,
    'cartTotal' => $cartTotal,
    'dumpsTotal' => $dumpsTotal
]);


?>