<?php
session_start();
require '../global.php'; 

if (!isset($_SESSION['dumps'])) {
    $_SESSION['dumps'] = [];
}

$input = json_decode(file_get_contents('php://input'), true);
$cardId = $input['cardId'] ?? null;

if ($cardId) {
  
    if (isset($_SESSION['dumps'][$cardId])) {
     
        unset($_SESSION['dumps'][$cardId]);

      
        $totalcard = array_sum(array_column($_SESSION['cards'], 'price'));
        $totaldumps = array_sum(array_column($_SESSION['dumps'], 'price'));
        $total =$totalcard+$totaldumps;

       
        echo json_encode([
            'success' => true,
            'cartItems' => array_values($_SESSION['cards']),
            'dumpsItems' => array_values($_SESSION['dumps']),
            'total' => $total,
        ]);
    } else {

        echo json_encode(['success' => false, 'message' => 'Card not found in the cart.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Card ID is missing.']);
}
?>