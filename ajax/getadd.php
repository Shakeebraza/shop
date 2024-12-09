<?php
include_once('../config.php');
session_start();

$rpcUser = "user";
$rpcPassword = "6w4geWw7LyTVDJFunXSoVQ==";
$rpcHost = "188.119.149.183";
$rpcPort = "7777";

function sendElectrumRpcRequest($method, $params = []) {
    global $rpcUser, $rpcPassword, $rpcHost, $rpcPort;

    $url = "http://$rpcHost:$rpcPort/";
    $payload = [
        "jsonrpc" => "2.0",
        "method" => $method,
        "params" => $params,
        "id" => 1
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_USERPWD, "$rpcUser:$rpcPassword");

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
        return ["error" => "Internal server error"];
    }

    curl_close($ch);

    $responseArray = json_decode($response, true);
    return $responseArray;
}

try {

    $expiredStmt = $pdo->prepare("
        UPDATE payment_requests 
        SET status = 'EXPIRED' 
        WHERE status = 'PENDING' AND TIMESTAMPDIFF(HOUR, created_at, NOW()) > 1
    ");
    $expiredStmt->execute();
    echo "Expired status updated for requests older than 1 hour.\n";


    $stmt = $pdo->prepare("SELECT id, user_id, btc_address, tx_hash, amount_usd FROM payment_requests WHERE status = 'PENDING'");
    $stmt->execute();
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pendingRequests as $request) {
        $address = $request['btc_address'];
        $id = $request['id'];
        $userId = $request['user_id'];
        $amountUsd = $request['amount_usd'];

        $response = sendElectrumRpcRequest("getaddresshistory", [$address]);

        if (isset($response['result']) && !empty($response['result'])) {
            $transactions = $response['result'];

            foreach ($transactions as $tx) {
                $txHash = $tx['tx_hash'];
                $height = $tx['height'];

                if ($height > 0) {
        
                    $updateStmt = $pdo->prepare("UPDATE payment_requests SET status = 'CONFIRMED', tx_hash = ? WHERE id = ?");
                    $updateStmt->execute([$txHash, $id]);

     
                    $balanceUpdateStmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $balanceUpdateStmt->execute([$amountUsd, $userId]);

                    echo "Payment confirmed for address $address (TX: $txHash), balance updated for user ID: $userId\n";
                }
            }
        } else {
            echo "No transactions found for address: $address\n";
        }
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
