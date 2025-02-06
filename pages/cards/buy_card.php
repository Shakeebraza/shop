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
$card_id = $_POST['card_id'] ?? null;

if (!$card_id) {
    $response['message'] = 'Card ID is missing.';
    echo json_encode($response);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id,seller_id, price, card_number, card_type FROM credit_cards WHERE id = ? AND status = 'unsold' FOR UPDATE");
    $stmt->execute([$card_id]);
    $card = $stmt->fetch();

    if ($card) {
        $seller_id = $card['seller_id'];
        $price = $card['price'];
        $card_number = $card['card_number'];
        $card_id = $card['id'];
        $card_type = 'Cards';

        // Buyer balance check
        $stmt = $pdo->prepare("SELECT balance, username FROM users WHERE id = ?");
        $stmt->execute([$buyer_id]);
        $buyer = $stmt->fetch();

        if ($buyer && $buyer['balance'] >= $price) {
            // Update balances and statuses
            $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $updateBuyerStmt->execute([$price, $buyer_id]);

            $updateCardStmt = $pdo->prepare("
                UPDATE credit_cards 
                SET buyer_id = ?, status = 'sold', created_at = NOW() 
                WHERE id = ?
            ");
            $updateCardStmt->execute([$buyer_id, $card_id]);

            $pdo->commit();

            $logData = [
                'user_id' => $buyer_id,
                'user_name' => $buyer['username'],
                'item_id' => $card_id,
                'buy_itm' => "Card_id :$card_id",
                'item_price' => $price,
                'item_type' => $card_type
            ];

            $settings->insertActivityLog($logData);

            $response['success'] = true;
            $response['message'] = 'Purchase successful. Please visit the My Cards section to view your purchased cards.';
        } else {
            $pdo->rollBack();
            $response['message'] = 'Not enough balance to complete the purchase.';
        }
    } else {
        $pdo->rollBack();
        $response['message'] = 'Card not found or already sold.';
    }
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Transaction failed in buy_card.php: " . $e->getMessage());
    $response['message'] = 'Transaction failed. Please try again.';
}

echo json_encode($response);
exit();