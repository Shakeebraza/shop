<?php
session_start();
require '../global.php'; 

if (!isset($_SESSION['cards'])) {
    $_SESSION['cards'] = [];
}

$input = json_decode(file_get_contents('php://input'), true);
$cardId = $input['cardId'] ?? null;

if ($cardId) {
  
    if (isset($_SESSION['cards'][$cardId])) {
     
        unset($_SESSION['cards'][$cardId]);

      
        $totalcard = array_sum(array_column($_SESSION['cards'], 'price'));
        $totaldumps = array_sum(array_column($_SESSION['dumps'], 'price'));
        $total =$totalcard+$totaldumps;
       
        echo json_encode([
            'success' => true,
            'dumpsItems' => array_values($_SESSION['dumps']),
            'cardsItems' => array_values($_SESSION['cards']),
            'total' => $total,
        ]);
    } else {

        echo json_encode(['success' => false, 'message' => 'Card not found in the cart.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Card ID is missing.']);
}
?>