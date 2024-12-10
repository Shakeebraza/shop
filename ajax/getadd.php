<?php
include_once('../config.php');
session_start();

$rpcUser = "user";
$rpcPassword = "6w4geWw7LyTVDJFunXSoVQ==";
$rpcHost = "188.119.149.183";
$rpcPort = "7777";

function sendElectrumRpcRequestBatch($requests) {
    global $rpcUser, $rpcPassword, $rpcHost, $rpcPort;

    $url = "http://$rpcHost:$rpcPort/";
    $payload = [];

    // Create batch payload
    foreach ($requests as $id => $request) {
        $payload[] = [
            "jsonrpc" => "2.0",
            "method" => $request['method'],
            "params" => $request['params'],
            "id" => $id
        ];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_USERPWD, "$rpcUser:$rpcPassword");

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

try {
 
    $pdo->exec("UPDATE payment_requests 
                SET status = 'EXPIRED' 
                WHERE status = 'PENDING' AND TIMESTAMPDIFF(HOUR, created_at, NOW()) > 1");

  
    $limit = 10; 
    $stmt = $pdo->prepare("SELECT id, user_id, amount_btc, btc_address, amount_usd 
                           FROM payment_requests 
                           WHERE status = 'PENDING' 
                           LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pendingRequests)) {
        echo "No pending payment requests.";
        exit;
    }


    $batchRequests = [];
    foreach ($pendingRequests as $key => $request) {
        $batchRequests[$key * 2] = ['method' => 'getaddresshistory', 'params' => [$request['btc_address']]];
        $batchRequests[$key * 2 + 1] = ['method' => 'getaddressbalance', 'params' => [$request['btc_address']]];
    }

    $responses = sendElectrumRpcRequestBatch($batchRequests);

  
    foreach ($pendingRequests as $key => $request) {
        $address = $request['btc_address'];
        $id = $request['id'];
        $userId = $request['user_id'];
        $amountBtc = (float)$request['amount_btc'];
        $amountUsd = (float)$request['amount_usd'];

        $historyResponse = $responses[$key * 2]['result'] ?? [];
        $balanceResponse = $responses[$key * 2 + 1]['result']['confirmed'] ?? 0;

        $totalReceived = number_format($balanceResponse, 8, '.', '');
        $amountBtcFormatted = number_format($amountBtc, 8, '.', '');

        if (!empty($historyResponse) && $totalReceived >= $amountBtcFormatted) {
          
            $txHash = $historyResponse[0]['tx_hash']; 
            $updateStmt = $pdo->prepare("UPDATE payment_requests 
                                        SET status = 'CONFIRMED', tx_hash = ? 
                                        WHERE id = ?");
            $updateStmt->execute([$txHash, $id]);

      
            $balanceUpdateStmt = $pdo->prepare("UPDATE users 
                                               SET balance = balance + ? 
                                               WHERE id = ?");
            $balanceUpdateStmt->execute([$amountUsd, $userId]);

            echo "Payment confirmed for address $address.\n";
        } else {
       
            $status = empty($historyResponse) ? 'NO_TRANSACTION' : 'INSUFFICIENT';
            $updateStmt = $pdo->prepare("UPDATE payment_requests 
                                        SET status = ?, received_payment = ? 
                                        WHERE id = ?");
            $updateStmt->execute([$status, $totalReceived, $id]);

            echo "Payment status updated to '$status' for address $address.\n";
        }
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
