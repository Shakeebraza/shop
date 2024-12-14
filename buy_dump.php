<?php
session_start();
require 'config.php';

// Check if the buyer is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php?dumps_message=" . urlencode("You must be logged in to make a purchase.") . "&section=dumps");
    exit();
}

$buyer_id = $_SESSION['user_id'];
$dump_id = filter_input(INPUT_GET, 'dump_id', FILTER_VALIDATE_INT);

if (!$dump_id) {
    header("Location: dashboard.php?dumps_message=" . urlencode("Invalid Dump ID.") . "&section=dumps");
    exit();
}

try {
    // Start the transaction
    $pdo->beginTransaction();

    // Fetch the dump details, ensuring it is unsold with a "FOR UPDATE" lock
    $stmt = $pdo->prepare("SELECT seller_id, price FROM dumps WHERE id = ? AND buyer_id IS NULL FOR UPDATE");
    $stmt->execute([$dump_id]);
    $dump = $stmt->fetch();

    if ($dump) {
        $seller_id = $dump['seller_id'];
        $price = $dump['price'];

        // Fetch the seller's percentage from the 'users' table
        $stmt = $pdo->prepare("SELECT seller_percentage FROM users WHERE id = ?");
        $stmt->execute([$seller_id]);
        $seller = $stmt->fetch();
        $seller_percentage = $seller['seller_percentage'] ?? 100;
        $seller_earnings = ($price * $seller_percentage) / 100;

        // Check if the buyer has enough balance
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$buyer_id]);
        $buyer = $stmt->fetch();

        if ($buyer && $buyer['balance'] >= $price) {
            // Deduct the price from the buyer's balance
            $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $updateBuyerStmt->execute([$price, $buyer_id]);

            // Mark the dump as sold and assign it to the buyer
            $updateDumpStmt = $pdo->prepare("UPDATE dumps SET buyer_id = ?, status = 'sold' WHERE id = ?");
            $updateDumpStmt->execute([$buyer_id, $dump_id]);

            // Update the seller's balance and total earned from dumps
            $updateSellerStmt = $pdo->prepare("
                UPDATE users 
                SET dumps_balance = dumps_balance + ?, dumps_total_earned = dumps_total_earned + ? 
                WHERE id = ?
            ");
            $updateSellerStmt->execute([$seller_earnings, $seller_earnings, $seller_id]);

            // Insert the transaction into the `dump_orders` table
            $insertOrderStmt = $pdo->prepare("
                INSERT INTO dump_orders (user_id, dump_id, price, seller_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertOrderStmt->execute([$buyer_id, $dump_id, $price, $seller_id]);

            // Commit the transaction
            $pdo->commit();

            // Redirect with dumps-specific message, show dumps section, and redirect to my-dumps
            header("Location: dashboard.php?dumps_message=" . urlencode("Purchase successful!") . "&section=dumps&redirect=my-dumps");
        } else {
            // Not enough balance, rollback the transaction
            $pdo->rollBack();
            header("Location: dashboard.php?dumps_message=" . urlencode("Not enough balance to complete the purchase.") . "&section=dumps");
        }
    } else {
        // Dump not found or already sold, rollback the transaction
        $pdo->rollBack();
        header("Location: dashboard.php?dumps_message=" . urlencode("Dump not found or already sold.") . "&section=dumps");
    }
} catch (Exception $e) {
    // Rollback the transaction and log the error for debugging
    $pdo->rollBack();
    error_log('Transaction error in buy_dumps.php: ' . $e->getMessage());
    header("Location: dashboard.php?dumps_message=" . urlencode("Transaction failed. Please try again.") . "&section=dumps");
}
exit();
