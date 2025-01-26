<?php
include_once('../global.php');

try {
    $query = "UPDATE support_tickets SET unread = 1 WHERE unread != 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>