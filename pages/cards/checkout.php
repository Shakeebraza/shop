<?php
session_start();
require '../../global.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to make a purchase.';
    echo json_encode($response);
    exit();
}

$buyer_id = $_SESSION['user_id'];
$cartItems = json_decode(file_get_contents('php://input'), true)['cartItems'];

if (empty($cartItems)) {
    $response['message'] = 'No items in the cart.';
    echo json_encode($response);
    exit();
}

try {
    $pdo->beginTransaction();

    
    $totalPrice = 0;
    $cardIds = [];

    foreach ($cartItems as $item) {
        $cardIds[] = $item['cardId'];
        $totalPrice +=  getCardPrice($item['cardId']); 
    }
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$buyer_id]);
    $buyer = $stmt->fetch();


    if ($buyer && $buyer['balance'] >= $totalPrice) {
       
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("SELECT seller_id, price FROM credit_cards WHERE id = ? AND status = 'unsold' FOR UPDATE");
            $stmt->execute([$item['cardId']]);
            $card = $stmt->fetch();

            if ($card) {
      
                $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                $updateBuyerStmt->execute([ $card['price'], $buyer_id]);

                $updateCardStmt = $pdo->prepare("
                    UPDATE credit_cards 
                    SET buyer_id = ?, status = 'sold', created_at = NOW() 
                    WHERE id = ?
                ");
                $updateCardStmt->execute([$buyer_id, $item['cardId']]);
            } else {
                $pdo->rollBack();
                $response['message'] = 'Some items are no longer available or already sold.';
                echo json_encode($response);
                exit();
            }
        }

        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Purchase successful!';
    } else {
        $pdo->rollBack();
        $response['message'] = 'Not enough balance to complete the purchase.';
    }

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Transaction failed in checkout.php: " . $e->getMessage());
    $response['message'] = 'Transaction failed. Please try again.';
}

echo json_encode($response);
exit();


function getCardPrice($cardId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT price FROM credit_cards WHERE id = ?");
    $stmt->execute([$cardId]);
    $card = $stmt->fetch();
    return $card['price'] ?? 0;
}
