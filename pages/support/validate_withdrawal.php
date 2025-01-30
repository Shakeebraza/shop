<?php
session_start();
include_once('../../global.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User is not logged in."]);
    exit;
}
function validateBTCAddress($address) {
    $api_url = "https://blockchain.info/rawaddr/$address";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code === 200) {
        return true;
    } else {
        return false; 
    }
}

$user_id = $_SESSION['user_id'];


$btc_address = isset($_POST['btcAddress']) ? $_POST['btcAddress'] : '';
$secret_code = isset($_POST['secretCode']) ? $_POST['secretCode'] : '';

if (validateBTCAddress($btc_address)) {
    // echo "BTC Address is valid!";
    
} else {
    echo json_encode(["status" => "error", "message" => "Invalid BTC Address!"]);
    exit;

}
if (empty($btc_address) || empty($secret_code)) {
    echo json_encode(["status" => "error", "message" => "BTC Address or Secret Code is missing."]);
    exit;
}


try {

    $sql = "SELECT secret_code FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);  
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($secret_code != $_SESSION['secret_code']) {
        echo json_encode(["status" => "error", "message" => "Invalid secret code."]);
        exit;
    }

    $balance_saller = 100.00;
    $subject = 'Withdrawal Request';
    $message = "BTC Address: $btc_address\nAmount: $$balance_saller";
    $status = 'open';
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    
  
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
    

    $ticket_id = $pdo->lastInsertId();
    

    $sender = 'user'; 
    $reply_message = "BTC Address: $btc_address\nWithdrawal Amount: $$balance_saller";
    $created_at_reply = date('Y-m-d H:i:s');
    

    $sql_reply = "INSERT INTO support_replies (ticket_id, sender, message, created_at) 
                  VALUES (?, ?, ?, ?)";
    $stmt_reply = $pdo->prepare($sql_reply);
    $stmt_reply->bindValue(1, $ticket_id, PDO::PARAM_INT);
    $stmt_reply->bindValue(2, $sender, PDO::PARAM_STR);
    $stmt_reply->bindValue(3, $reply_message, PDO::PARAM_STR);
    $stmt_reply->bindValue(4, $created_at_reply, PDO::PARAM_STR);
    $stmt_reply->execute();


    echo json_encode(["status" => "success", "message" => "Your withdrawal request has been submitted. A support ticket has been created."]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}


?>