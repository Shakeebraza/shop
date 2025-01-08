<?php
session_start();
require '../global.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to make a purchase.';
    echo json_encode($response);
    exit();
}

$buyer_id = $_SESSION['user_id'];
$sessionCards = $_SESSION['cards'] ?? [];
$sessionDumps = $_SESSION['dumps'] ?? [];

$items = array_merge(
    array_map(fn($id) => ['id' => $id, 'type' => 'card'], $sessionCards),
    array_map(fn($id) => ['id' => $id, 'type' => 'dump'], $sessionDumps)
);

if (empty($items)) {
    $response['message'] = 'No items in the cart.';
    echo json_encode($response);
    exit();
}

try {
    $pdo->beginTransaction();

    $totalPrice = 0;
    $validCards = [];
    $validDumps = [];

    // Process cards

    foreach ($sessionCards as $cardId) {
        $stmt = $pdo->prepare("SELECT seller_id, price FROM credit_cards WHERE id = ? AND status = 'unsold' FOR UPDATE");
        $stmt->execute([$cardId['id']]);  // Correct usage of $cardId
        $cardData = $stmt->fetch();
        
        if ($cardData) {
            $totalPrice += $cardData['price'];
            $validCards[] = [
                'id' => $cardId['id'],
                'seller_id' => $cardData['seller_id'],
                'price' => $cardData['price'],
            ];
        } else {
            $pdo->rollBack();
            $response['message'] = "Some card items are no longer available or already sold.";
            echo json_encode($response);
            exit();
        }
    }

    // Process dumps

    foreach ($sessionDumps as $dumpId) {
        $stmt = $pdo->prepare("SELECT seller_id, price FROM dumps WHERE id = ? AND buyer_id IS NULL FOR UPDATE");
        $stmt->execute([$dumpId['id']]);  // Correct usage of $dumpId
        $dumpData = $stmt->fetch();
    
        if ($dumpData) {
            $totalPrice += $dumpData['price'];
            $validDumps[] = [
                'id' => $dumpId['id'],
                'seller_id' => $dumpData['seller_id'],
                'price' => $dumpData['price'],
            ];
        } else {
            $pdo->rollBack();
            $response['message'] = "Some dump items are no longer available or already sold.";
            echo json_encode($response);
            exit();
        }
    }


    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$buyer_id]);
    $buyer = $stmt->fetch();

    if ($buyer && $buyer['balance'] >= $totalPrice) {
 
        foreach ($validCards as $card) {
            $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $updateBuyerStmt->execute([$card['price'], $buyer_id]);

            $updateCardStmt = $pdo->prepare("UPDATE credit_cards SET buyer_id = ?, status = 'sold', created_at = NOW() WHERE id = ?");
            $updateCardStmt->execute([$buyer_id, $card['id']]);

            $sellerPercentageStmt = $pdo->prepare("SELECT seller_percentage FROM users WHERE id = ?");
            $sellerPercentageStmt->execute([$card['seller_id']]);
            $seller = $sellerPercentageStmt->fetch();
            $seller_percentage = $seller['seller_percentage'] ?? 100;
            $seller_earnings = ($card['price'] * $seller_percentage) / 100;

            $updateSellerStmt = $pdo->prepare("
                UPDATE users 
                SET balance = balance + ?, total_earned = total_earned + ? 
                WHERE id = ?
            ");
            $updateSellerStmt->execute([$seller_earnings, $seller_earnings, $card['seller_id']]);
        }

  
        foreach ($validDumps as $dump) {
            $updateBuyerStmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $updateBuyerStmt->execute([$dump['price'], $buyer_id]);

            $updateDumpStmt = $pdo->prepare("UPDATE dumps SET buyer_id = ?, status = 'sold' WHERE id = ?");
            $updateDumpStmt->execute([$buyer_id, $dump['id']]);

            $sellerPercentageStmt = $pdo->prepare("SELECT seller_percentage FROM users WHERE id = ?");
            $sellerPercentageStmt->execute([$dump['seller_id']]);
            $seller = $sellerPercentageStmt->fetch();
            $seller_percentage = $seller['seller_percentage'] ?? 100;
            $seller_earnings = ($dump['price'] * $seller_percentage) / 100;

            $updateSellerStmt = $pdo->prepare("
                UPDATE users 
                SET balance = balance + ?, total_earned = total_earned + ? 
                WHERE id = ?
            ");
            $updateSellerStmt->execute([$seller_earnings, $seller_earnings, $dump['seller_id']]);
        }

        $pdo->commit();

        unset($_SESSION['cards'], $_SESSION['dumps']);

        $response['success'] = true;
        $response['message'] = 'Purchase successful!';
    } else {
        $pdo->rollBack();
        $response['message'] = 'Not enough balance to complete the purchase.';
    }
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Transaction failed in checkout.php: " . $e->getMessage());
    $response['message'] = 'Transaction failed. Please try again.'. $e->getMessage();
}

echo json_encode($response);
exit();
