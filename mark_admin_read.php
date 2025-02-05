<?php
session_start();
require 'config.php';

if (isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];

    // Mark the ticket as read for the admin
    $stmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = 1 WHERE id = ?");
    $stmt->execute([$ticket_id]);

    // Mark all replies under this ticket as read
    $stmt = $pdo->prepare("UPDATE support_replies SET is_read = 1 WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);

    echo json_encode(['status' => 'success']);
}
?>