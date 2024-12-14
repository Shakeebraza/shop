<?php
require 'config.php';

// Get JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Check if ticket_id is provided
if (isset($data['ticket_id'])) {
    $ticket_id = $data['ticket_id'];

    // Update the ticket status to "closed" and mark it as read for both admin and user
    $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'closed', admin_unread = 0, user_unread = 0 WHERE id = ?");
    if ($stmt->execute([$ticket_id])) {
        echo json_encode(['success' => true, 'message' => 'The ticket has been successfully closed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to close the ticket.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
