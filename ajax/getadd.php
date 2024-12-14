<?php
include_once('../global.php');
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
        curl_close($ch);
        return ["error" => "Internal server error"];
    }

    curl_close($ch);

    $responseArray = json_decode($response, true);
    return $responseArray;
}
      function getRequestDataForAddress($address, $list_tra) {
            if (!is_array($list_tra)) {
                throw new Exception("list_requests data must be an array.");
            }
        
            foreach ($list_tra["result"] as $request) {
                if (isset($request['address']) && trim($request['address']) == trim($address)) {
              
                    if (!empty($request['tx_hashes'])) {
                        $request['status_str'] = "Paid";
                    }
        
                 
                    $currentTimestamp = time();
                    $expiryTime = $request['timestamp'] + $request['expiry'];
                    if ($currentTimestamp > $expiryTime && empty($request['tx_hashes'])) {
                        $request['status_str'] = "Expired";
                    }
        
                    return $request;
                }
            }
        
            return null; 
        }

try {


    $expiredStmt = $pdo->prepare("
        UPDATE payment_requests 
        SET status = 'EXPIRED' 
        WHERE status = 'PENDING' AND created_at <= DATE_SUB(:currentDateTime, INTERVAL 1 HOUR)
    ");


    $expiredStmt->bindParam(':currentDateTime', $currentDateTime);
    $expiredStmt->execute();

    $affectedRows = $expiredStmt->rowCount();
    echo $affectedRows.'row are effected';

    $stmt = $pdo->prepare("
        SELECT id, user_id, amount_btc, btc_address, tx_hash, amount_usd, received_payment 
        FROM payment_requests 
        WHERE status = 'PENDING'
    ");
    $stmt->execute();
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pendingRequests as $request) {
        $address = $request['btc_address'];
        $id = $request['id'];
        $userId = $request['user_id'];
        $amountUsd = $request['amount_usd'];
        $amount_btc = $request['amount_btc'];
        $receivedPayment = $request['received_payment'] ?? 0;

     
       
  
        $list_tra = sendElectrumRpcRequest("list_requests");
        $responseTableData=getRequestDataForAddress($address, $list_tra);
        
       
        if($responseTableData['status_str'] == 'Paid' && $responseTableData['confirmations'] > 2){
        $response = sendElectrumRpcRequest("getaddresshistory", [$address]);
        if (isset($response['result']) && !empty($response['result'])) {
            $transactions = $response['result'];
            $totalReceived = 0;
            $txHash = null;

          
            foreach ($transactions as $tx) {
                $txHash = $tx['tx_hash'];
                $height = $tx['height'];
                if ($height > 0) {
                    $balResponse = sendElectrumRpcRequest("getaddressbalance", [$address]);
                    if (isset($balResponse['result']['confirmed'])) {
                        $totalReceived = $balResponse['result']['confirmed'];
           
                    }
                    if (isset($balResponse['result']['unconfirmed']) && $balResponse['result']['unconfirmed'] > 0) {
                        $unconfirmedBalance = $balResponse['result']['unconfirmed'];
                        echo("Payment received or unconfirmed matches the required amount.");
                        exit();
                    }
                }
            }
  
          

   
            if ($totalReceived >= $amount_btc) {
           
                $updateStmt = $pdo->prepare("
                    UPDATE payment_requests 
                    SET status = 'CONFIRMED', tx_hash = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$txHash, $id]);

          
                $balanceUpdateStmt = $pdo->prepare("
                    UPDATE users 
                    SET balance = balance + ? 
                    WHERE id = ?
                ");
                $balanceUpdateStmt->execute([$amountUsd, $userId]);

                echo "Payment received for address $address (TX: $txHash), updated status to 'CONFIRMED', balance updated for user ID: $userId\n";
            } else {
           
                $updateStmt = $pdo->prepare("
                    UPDATE payment_requests 
                    SET status = 'INSUFFICIENT', received_payment = ?, tx_hash = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$totalReceivedFormatted, $txHash, $id]);

                echo "Insufficient payment for address: $address, updated status to 'INSUFFICIENT'\n";
            }
        } else {
            echo "No transactions found for address: $address\n";
        }
    }else{
        echo "Your transactions Expried found for address: $address\n";
    }
    }

} catch (PDOException $e) {

    error_log("Database error: " . $e->getMessage());
    echo "An error occurred while processing payments.\n";
}
?>
