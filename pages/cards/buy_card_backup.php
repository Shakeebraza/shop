<?php
session_start();
require '../../global.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?message=" . urlencode("You must be logged in to make a purchase."));
    exit();
}

$buyer_id = $_SESSION['user_id'];
$card_id = $_GET['id'] ?? null;

if (!$card_id) {
    header("Location: index.php?message=" . urlencode("Card ID is missing."));
    exit();
}

try {

    $pdo->beginTransaction();

   
    $stmt = $pdo->prepare("SELECT seller_id, price FROM credit_cards WHERE id = ? AND status = 'unsold' FOR UPDATE");
    $stmt->execute([$card_id]);
    $card = $stmt->fetch();

    if ($card) {
        $seller_id = $card['seller_id'];
        $price = $card['price'];


        $stmt = $pdo->prepare("SELECT seller_percentage FROM users WHERE id = ?");
        $stmt->execute([$seller_id]);
        $seller = $stmt->fetch();
        $seller_percentage = $seller['seller_percentage'] ?? 100;
        $seller_earnings = ($price * $seller_percentage) / 100;

       
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$buyer_id]);
        $buyer = $stmt->fetch();

        if ($buyer && $buyer['balance'] >= $price) {
   
            $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $updateBuyerStmt->execute([$price, $buyer_id]);

         
            $updateCardStmt = $pdo->prepare("UPDATE credit_cards SET buyer_id = ?, status = 'sold' WHERE id = ?");
            $updateCardStmt->execute([$buyer_id, $card_id]);

           
            $updateSellerStmt = $pdo->prepare("
                UPDATE users 
                SET credit_cards_balance = credit_cards_balance + ?, credit_cards_total_earned = credit_cards_total_earned + ? 
                WHERE id = ?
            ");
            $updateSellerStmt->execute([$seller_earnings, $seller_earnings, $seller_id]);

     
            $insertOrderStmt = $pdo->prepare("
                INSERT INTO card_orders (user_id, card_id, price, seller_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertOrderStmt->execute([$buyer_id, $card_id, $price, $seller_id]);

       
            $pdo->commit();

      
            header("Location: index.php?message=" . urlencode("Purchase successful!"));
            exit();
        } else {
     
            $pdo->rollBack();
            header("Location: index.php?message=" . urlencode("Not enough balance to complete the purchase."));
            exit();
        }
    } else {

        $pdo->rollBack();
        header("Location: index.php?message=" . urlencode("Card not found or already sold."));
        exit();
    }
} catch (Exception $e) {
    
    $pdo->rollBack();
    error_log("Transaction failed in buy_card.php: " . $e->getMessage());
    header("Location: index.php?message=" . urlencode("Transaction failed. Please try again."));
    exit();
}
?>
