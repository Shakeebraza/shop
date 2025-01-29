<?php
include_once('../../global.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to reply to a ticket.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate and sanitize input
if (isset($_POST['ticket_id'], $_POST['message']) && !empty(trim($_POST['message']))) {
    $ticket_id = (int)$_POST['ticket_id'];
    $message = trim($_POST['message']);

    // Validate ticket ID
    if (!filter_var($ticket_id, FILTER_VALIDATE_INT)) {
        echo json_encode(['success' => false, 'message' => 'Invalid ticket ID.']);
        exit();
    }

    // Enforce character limit
    if (strlen($message) > 500) {
        echo json_encode(['success' => false, 'message' => 'Message cannot exceed 500 characters.']);
        exit();
    }

    // Check if the ticket belongs to the user
    $ticketCheckStmt = $pdo->prepare("SELECT id FROM support_tickets WHERE id = ? AND user_id = ?");
    $ticketCheckStmt->execute([$ticket_id, $user_id]);
    $ticket = $ticketCheckStmt->fetch();

    if (!$ticket) {
        error_log("Ticket not found for User ID: $user_id, Ticket ID: $ticket_id");
        echo json_encode(['success' => false, 'message' => 'Ticket not found or does not belong to you.']);
        exit();
    }

    // Check consecutive replies
    $consecutiveReplyCountStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM (
            SELECT sender 
            FROM support_replies 
            WHERE ticket_id = ? 
            ORDER BY created_at DESC 
            LIMIT 3
        ) AS last_replies
        WHERE sender = 'user'
    ");
    $consecutiveReplyCountStmt->execute([$ticket_id]);
    $consecutiveUserReplies = $consecutiveReplyCountStmt->fetchColumn();

    if ($consecutiveUserReplies >= 3) {
        echo json_encode(['success' => false, 'message' => 'You cannot send more than 3 consecutive messages without an admin reply.']);
        exit();
    }

    // Insert the reply into the database
    $stmt = $pdo->prepare("INSERT INTO support_replies (ticket_id, sender, message, created_at) VALUES (?, 'user', ?, NOW())");
    if ($stmt->execute([$ticket_id, $message])) {
        // Update admin unread status
        $updateStmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = 1 WHERE id = ?");
        if ($updateStmt->execute([$ticket_id])) {
            echo json_encode([
                'success' => true,
                'message' => htmlspecialchars($message),
                'username' => htmlspecialchars($_SESSION['username'] ?? 'User'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            error_log("Failed to update ticket status: " . implode(", ", $updateStmt->errorInfo()));
            echo json_encode(['success' => false, 'message' => 'Failed to update ticket status.']);
        }
    } else {
        error_log("Failed to insert reply: " . implode(", ", $stmt->errorInfo()));
        echo json_encode(['success' => false, 'message' => 'Failed to submit reply.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ticket ID or empty message.']);
}