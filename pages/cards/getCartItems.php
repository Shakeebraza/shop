<?php
session_start();
if (isset($_SESSION['cart'])) {
    $cartItems = array_values($_SESSION['cart']);
    $total = array_sum(array_column($cartItems, 'price'));
    
    echo json_encode([
        'success' => true,
        'cartItems' => $cartItems,
        'total' => $total,
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
}
?>
