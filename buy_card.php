<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php?message=" . urlencode("You must be logged in to make a purchase."));
    exit();
}

$buyer_id = $_SESSION['user_id'];
$card_id = $_GET['id'] ?? null;

if (!$card_id) {
    header("Location: dashboard.php?message=" . urlencode("Card ID is missing."));
    exit();
}

try {
    // Begin the transaction
    $pdo->beginTransaction();

    // Lock the card row to prevent other transactions from selecting it
    $stmt = $pdo->prepare("SELECT seller_id, price FROM credit_cards WHERE id = ? AND status = 'unsold' FOR UPDATE");
    $stmt->execute([$card_id]);
    $card = $stmt->fetch();

    if ($card) {
        $seller_id = $card['seller_id'];
        $price = $card['price'];

        // Fetch seller's percentage
        $stmt = $pdo->prepare("SELECT seller_percentage FROM users WHERE id = ?");
        $stmt->execute([$seller_id]);
        $seller = $stmt->fetch();
        $seller_percentage = $seller['seller_percentage'] ?? 100;
        $seller_earnings = ($price * $seller_percentage) / 100;

        // Check buyer's balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$buyer_id]);
        $buyer = $stmt->fetch();

        if ($buyer && $buyer['balance'] >= $price) {
            // Deduct the balance from the buyer
            $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $updateBuyerStmt->execute([$price, $buyer_id]);

            // Update the card's status to 'sold' and set buyer_id
            $updateCardStmt = $pdo->prepare("UPDATE credit_cards SET buyer_id = ?, status = 'sold' WHERE id = ?");
            $updateCardStmt->execute([$buyer_id, $card_id]);

            // Update seller's balance and total earnings
            $updateSellerStmt = $pdo->prepare("
                UPDATE users 
                SET credit_cards_balance = credit_cards_balance + ?, credit_cards_total_earned = credit_cards_total_earned + ? 
                WHERE id = ?
            ");
            $updateSellerStmt->execute([$seller_earnings, $seller_earnings, $seller_id]);

            // Insert the transaction into the `card_orders` table
            $insertOrderStmt = $pdo->prepare("
                INSERT INTO card_orders (user_id, card_id, price, seller_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertOrderStmt->execute([$buyer_id, $card_id, $price, $seller_id]);

            // Commit the transaction
            $pdo->commit();

            // Redirect with a success message
            header("Location: dashboard.php?message=" . urlencode("Purchase successful!"));
            exit();
        } else {
            // Roll back transaction if the balance is insufficient
            $pdo->rollBack();
            header("Location: dashboard.php?message=" . urlencode("Not enough balance to complete the purchase."));
            exit();
        }
    } else {
        // Roll back transaction if the card is unavailable
        $pdo->rollBack();
        header("Location: dashboard.php?message=" . urlencode("Card not found or already sold."));
        exit();
    }
} catch (Exception $e) {
    // Roll back transaction and log the error
    $pdo->rollBack();
    error_log("Transaction failed in buy_card.php: " . $e->getMessage());
    header("Location: dashboard.php?message=" . urlencode("Transaction failed. Please try again."));
    exit();
}
?>
