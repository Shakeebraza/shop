<?php
session_start();
include_once('../../global.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User is not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the POST data
$btc_address = isset($_POST['btcAddress']) ? $_POST['btcAddress'] : '';
$secret_code = isset($_POST['secretCode']) ? $_POST['secretCode'] : '';

// Validate required fields
if (empty($btc_address) || empty($secret_code)) {
    echo json_encode(["status" => "error", "message" => "BTC Address or Secret Code is missing."]);
    exit;
}

try {
    // Fetch the user's secret code from the database to validate
    $sql = "SELECT secret_code FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);  // Binding the user_id parameter
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // if (!$user || !password_verify($secret_code, $user['secret_code'])) {
    //     echo json_encode(["status" => "error", "message" => "Invalid secret code."]);
    //     exit;
    // }

    // Step 1: Create a withdrawal ticket
    $balance_saller = 100.00; // Example, replace with actual balance logic
    $subject = 'Withdrawal Request';
    $message = "BTC Address: $btc_address\nSecret Code: $secret_code\nAmount: $$balance_saller";
    $status = 'open';
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    
    // Insert into support_tickets table
    $sql_ticket = "INSERT INTO support_tickets (user_id, message, status, created_at, updated_at, subject) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_ticket = $pdo->prepare($sql_ticket);
    $stmt_ticket->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt_ticket->bindValue(2, $message, PDO::PARAM_STR);
    $stmt_ticket->bindValue(3, $status, PDO::PARAM_STR);
    $stmt_ticket->bindValue(4, $created_at, PDO::PARAM_STR);
    $stmt_ticket->bindValue(5, $updated_at, PDO::PARAM_STR);
    $stmt_ticket->bindValue(6, $subject, PDO::PARAM_STR);
    $stmt_ticket->execute();
    
    // Get the last inserted ticket id
    $ticket_id = $pdo->lastInsertId();
    
    // Step 2: Insert a reply to the support_replies table
    $sender = 'user';  // Since this is a user submission
    $reply_message = "BTC Address: $btc_address\nSecret Code: $secret_code\nWithdrawal Amount: $$balance_saller";
    $created_at_reply = date('Y-m-d H:i:s');
    
    // Insert into support_replies table
    $sql_reply = "INSERT INTO support_replies (ticket_id, sender, message, created_at) 
                  VALUES (?, ?, ?, ?)";
    $stmt_reply = $pdo->prepare($sql_reply);
    $stmt_reply->bindValue(1, $ticket_id, PDO::PARAM_INT);
    $stmt_reply->bindValue(2, $sender, PDO::PARAM_STR);
    $stmt_reply->bindValue(3, $reply_message, PDO::PARAM_STR);
    $stmt_reply->bindValue(4, $created_at_reply, PDO::PARAM_STR);
    $stmt_reply->execute();

    // Return success message
    echo json_encode(["status" => "success", "message" => "Your withdrawal request has been submitted. A support ticket has been created."]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}


?>