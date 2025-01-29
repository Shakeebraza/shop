<?php
include_once('../../global.php');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['ticket_id'])) {
    $ticketId = $data['ticket_id'];

    $sql = "UPDATE dumps 
            SET buyer_id = NULL, status = 'unsold', is_view = 0 
            WHERE id = :ticket_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);

    if ($stmt->execute()) {

        echo json_encode(['success' => true, 'message' => 'Remove the dumps']);
    } else {

        echo json_encode(['success' => false, 'message' => 'Failed to update ticket']);
    }
} else {

    echo json_encode(['success' => false, 'message' => 'Ticket ID not provided']);
}

?>