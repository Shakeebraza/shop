<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['message'])) {
    $ticket_id = $_POST['ticket_id'];
    $message = $_POST['message'];

    // Insert admin reply
    $stmt = $pdo->prepare("INSERT INTO support_replies (ticket_id, sender, message) VALUES (?, 'admin', ?)");
    $stmt->execute([$ticket_id, $message]);

    // Mark ticket as unread for the user
    $updateStmt = $pdo->prepare("UPDATE support_tickets SET user_unread = 1 WHERE id = ?");
    $updateStmt->execute([$ticket_id]);

    header("Location: support_chat.php");
    exit();
}
?>
