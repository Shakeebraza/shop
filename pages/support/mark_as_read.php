<?php
session_start();
include_once('../../global.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the reply ID from the AJAX request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['reply_id'])) {
        $reply_id = $data['reply_id'];

        // Update the 'is_read' field for the admin reply
        $stmt = $pdo->prepare("UPDATE support_replies SET is_read = 1 WHERE ticket_id  = ? AND sender = 'admin'");
        $stmt->execute([$reply_id]);

        // Respond with a success message
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Reply ID not provided']);
    }
}
?>