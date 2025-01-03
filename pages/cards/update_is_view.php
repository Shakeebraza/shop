<?php
session_start();
include_once('../../global.php');



if (isset($_SESSION['user_id'])) {
    $buyerId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("UPDATE credit_cards SET is_view = 1 WHERE buyer_id = :buyer_id AND is_view = 0");
    $stmt->bindParam(':buyer_id', $buyerId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'All new cards marked as viewed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cards.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
}
?>