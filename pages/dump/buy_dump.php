<?php
session_start();
require '../../global.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a purchase.']);
    exit();
}

$buyer_id = $_SESSION['user_id'];
$dump_id = filter_input(INPUT_POST, 'dump_id', FILTER_VALIDATE_INT);

if (!$dump_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Dump ID.']);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT seller_id, price FROM dumps WHERE id = ? AND buyer_id IS NULL FOR UPDATE");
    $stmt->execute([$dump_id]);
    $dump = $stmt->fetch();

    if ($dump) {
        $seller_id = $dump['seller_id'];
        $price = $dump['price'];

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

            $updateDumpStmt = $pdo->prepare("UPDATE dumps SET buyer_id = ?, status = 'sold' WHERE id = ?");
            $updateDumpStmt->execute([$buyer_id, $dump_id]);

            $updateSellerStmt = $pdo->prepare("
                UPDATE users 
                SET dumps_balance = dumps_balance + ?, dumps_total_earned = dumps_total_earned + ? 
                WHERE id = ?
            ");
            $updateSellerStmt->execute([$seller_earnings, $seller_earnings, $seller_id]);

            $insertOrderStmt = $pdo->prepare("
                INSERT INTO dump_orders (user_id, dump_id, price, seller_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertOrderStmt->execute([$buyer_id, $dump_id, $price, $seller_id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Purchase successful. Please visit the My Dumps section view your purchased dumps.']);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Not enough balance to complete the purchase.']);
        }
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Dump not found or already sold.']);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Transaction error in buy_dumps.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Transaction failed. Please try again.']);
}
exit();