<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['message'])) {
    $ticket_id = $_POST['ticket_id'];
    $message = $_POST['message'];

    // Insert admin reply into the support_replies table with is_read set to 0
    $stmt = $pdo->prepare("INSERT INTO support_replies (ticket_id, sender, message, is_read) VALUES (?, 'admin', ?, 1)");
    $stmt->execute([$ticket_id, $message]);

    // Update the ticket to mark it as unread for the user
    $updateStmt = $pdo->prepare("UPDATE support_tickets SET user_unread = 1 WHERE id = ?");
    $updateStmt->execute([$ticket_id]);

    // Optionally: You can update other fields such as admin_unread, last_user_reply, etc.
    $updateTicketStmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = 0, last_user_reply = 1 WHERE id = ?");
    $updateTicketStmt->execute([$ticket_id]);

    // Redirect to the support chat page after inserting the message
    header("Location: support_chat.php");
    exit();
}
?>