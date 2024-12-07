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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount_btc']) || !isset($data['memo']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit;
}

$btcAmount = $data['amount_btc'];
$usdAmount = $btcAmount * $rateData['bitcoin']['usd'] = getBitcoinPrice(); 
$memo = $data['memo'];
$userId = $_SESSION['user_id'];  
function getBitcoinPrice() {
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd';
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    return $data['bitcoin']['usd'] ?? 0; // Return the USD price of Bitcoin, or 0 if not available
}

$response = sendElectrumRpcRequest("getunusedaddress");

if (isset($response['error']) && $response['error']) {
    echo json_encode(['success' => false, 'message' => 'Error fetching BTC address.']);
} else {
    $btcAddress = $response['result'] ?? null;

    if ($btcAddress) {
       
        // $transactionResponse = sendElectrumRpcRequest("tx_hash");
        $randomNumber = rand(10, 100);

     
        $transactionResponse = password_hash($randomNumber, PASSWORD_BCRYPT);
        
       
        
            $tx_hash = $transactionResponse ?? null;

            if ($tx_hash) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO payment_requests (user_id, btc_address, amount_usd, amount_btc, memo, tx_hash, status, created_at) 
                                           VALUES (:user_id, :btc_address, :amount_usd, :amount_btc, :memo, :tx_hash, :status, NOW())");

                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':btc_address', $btcAddress);
                    $stmt->bindParam(':amount_usd', $usdAmount);
                    $stmt->bindParam(':amount_btc', $btcAmount);
                    $stmt->bindParam(':memo', $memo);
                    $stmt->bindParam(':tx_hash', $tx_hash);
                    $stmt->bindParam(':status', $status);

                    $status = 'PENDING';

                    $stmt->execute();

                    echo json_encode(['success' => true, 'btcAddress' => $btcAddress, 'tx_hash' => $tx_hash]);
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Transaction hash not generated.']);
            }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'BTC Address not found in response.']);
    }
}
?>
