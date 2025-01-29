<?php
include_once('../../global.php');


if (isset($_POST['ticket_id'])) {
    $ticketId = $_POST['ticket_id'];


    $sql = "UPDATE credit_cards 
            SET buyer_id = NULL, status = 'unsold', is_view = 0 
            WHERE id = :ticket_id";


    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);

    if ($stmt->execute()) {
 
        echo json_encode(['success' => true]);
    } else {

        echo json_encode(['success' => false, 'message' => 'Failed to update ticket']);
    }
} else {

    echo json_encode(['success' => false, 'message' => 'Ticket ID not provided']);
}

?>