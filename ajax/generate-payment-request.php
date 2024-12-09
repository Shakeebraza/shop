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
        "jsonrpc" => "2.0", // Ensure jsonrpc field is added
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
    error_log("Electrum Response: " . print_r($responseArray, true));

    return $responseArray;
}

function getBitcoinPrice() {
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd';
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    return $data['bitcoin']['usd'] ?? 0; 
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount_btc']) || !isset($data['memo']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit;
}

$btcAmount = $data['amount_btc'];
$usdAmount = $btcAmount * getBitcoinPrice(); 
$memo = $data['memo'] ?? 'test';
$userId = $_SESSION['user_id'];

$btcAddress = "your_btc_address_here"; 


if ($btcAddress) {
   
    $transactionResponse = sendElectrumRpcRequest("add_request", [
        "amount" => $btcAmount, 
        "memo" => $memo         
    ]);


    if (isset($transactionResponse['result']['rhash'])) {
        $tx_hash = "";
        $status = $transactionResponse['result']['status'] == 0 ? 'PENDING' : 'CONFIRMED';

        try {
            $stmt = $pdo->prepare("INSERT INTO payment_requests (user_id, btc_address, amount_usd, amount_btc, memo, tx_hash, status, created_at) 
                                   VALUES (:user_id, :btc_address, :amount_usd, :amount_btc, :memo, :tx_hash, :status, NOW())");

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':btc_address', $transactionResponse['result']['address']); 
            $stmt->bindParam(':amount_usd', $usdAmount);
            $stmt->bindParam(':amount_btc', $btcAmount);
            $stmt->bindParam(':memo', $memo);
            $stmt->bindParam(':tx_hash', $tx_hash);
            $stmt->bindParam(':status', $status);


            $stmt->execute();

            echo json_encode(['success' => true, 'btcAddress' => $transactionResponse['result']['address'], 'tx_hash' => $tx_hash]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction hash not generated.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'BTC Address not found in response.']);
}
?>
