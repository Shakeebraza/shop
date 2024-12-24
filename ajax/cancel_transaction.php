<?php
include_once('../global.php');
if (isset($_POST['transaction_id'])) {
    $transactionId = $_POST['transaction_id'];
    $sql = "UPDATE payment_requests SET status = 'CANCELLED' WHERE id = :transaction_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':transaction_id', $transactionId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Transaction ID not provided']);
}

?>