<?php
require 'config.php';

// Get JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Check if ticket_id is provided
if (isset($data['ticket_id'])) {
    $ticket_id = $data['ticket_id'];

    // Delete all replies related to this ticket
    $stmt = $pdo->prepare("DELETE FROM support_replies WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);

    // Delete the ticket itself
    $stmt = $pdo->prepare("DELETE FROM support_tickets WHERE id = ?");
    if ($stmt->execute([$ticket_id])) {
        echo json_encode(['success' => true, 'message' => 'The ticket has been successfully deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete the ticket.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
