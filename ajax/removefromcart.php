<?php
session_start();
require '../global.php'; 

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$input = json_decode(file_get_contents('php://input'), true);
$cardId = $input['cardId'] ?? null;

if ($cardId) {
  
    if (isset($_SESSION['cart'][$cardId])) {
     
        unset($_SESSION['cart'][$cardId]);

      
        $total = array_sum(array_column($_SESSION['cart'], 'price'));

       
        echo json_encode([
            'success' => true,
            'cartItems' => array_values($_SESSION['cart']),
            'total' => $total,
        ]);
    } else {

        echo json_encode(['success' => false, 'message' => 'Card not found in the cart.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Card ID is missing.']);
}
?>
