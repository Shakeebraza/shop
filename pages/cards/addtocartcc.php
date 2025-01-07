<?php
session_start();
require '../../global.php'; 


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


$input = json_decode(file_get_contents('php://input'), true);
$cardId = $input['cardId'] ?? null;

if ($cardId) {

    try {
        $stmt = $pdo->prepare("SELECT id,card_number, name_on_card, price, card_type FROM credit_cards WHERE id = :cardId");
        $stmt->bindParam(':cardId', $cardId, PDO::PARAM_INT);
        $stmt->execute();

        $card = $stmt->fetch(PDO::FETCH_ASSOC);

 
        if ($card) {
           
            $cardData = [
                'id' => $card['id'],
                'bin' => substr($card['card_number'], 0, 6),
                'price' => $card['price'],
                'image' => '/shop/images/cards/'.strtolower($card['card_type']).'.png', 
            ];

 
            $_SESSION['cart'][$cardId] = $cardData;

    
            $total = array_sum(array_column($_SESSION['cart'], 'price'));

      
            echo json_encode([
                'success' => true,
                'cartItems' => array_values($_SESSION['cart']),
                'total' => $total,
            ]);
        } else {
      
            echo json_encode(['success' => false, 'message' => 'Card not found.']);
        }
    } catch (PDOException $e) {

        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Card ID is missing.']);
}
?>
