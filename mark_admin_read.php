<?php
session_start();
require 'config.php';

if (isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];

    // Mark as read for the admin
    $stmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = 0 WHERE id = ?");
    $stmt->execute([$ticket_id]);

    echo json_encode(['status' => 'success']);
}
?>
