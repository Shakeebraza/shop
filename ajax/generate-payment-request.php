<?php
include_once('../global.php');
session_start();



$key_mar = 'margin';
$value_margin = $settings->getValueByKey($key_mar);

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
    return json_decode($response, true);
}

function getBitcoinPrice() {
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd';
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    return $data['bitcoin']['usd'] ?? 0; 
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount_usd']) || !isset($data['memo']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    exit;
}
$userId = $_SESSION['user_id'];
// $sqlCheck = "SELECT * FROM payment_requests WHERE user_id = :user_id AND (status = 'PENDING' OR status = 'RECEIVING') LIMIT 1";
// $stmtCheck = $pdo->prepare($sqlCheck);
// $stmtCheck->bindParam(':user_id', $userId);
// $stmtCheck->execute();

// if ($stmtCheck->rowCount() > 0) {
//     echo json_encode(['success' => false, 'message' => 'You already have an active payment request. Please wait until it is processed or expired, or cancel the transaction to initialize a new one.']);
//     exit;
// }

$usdAmount = $data['amount_usd'];

$username = $_SESSION['username'];
$memo = $data['memo'] ?? 'Recharge';

$marginPercent = $value_margin;
$usdAmountMargin = $usdAmount * (1 + $marginPercent);


$btcRate = getBitcoinPrice();
$amountBtc = $usdAmountMargin / $btcRate;

$transactionResponse = sendElectrumRpcRequest("add_request", [
    "amount" => $amountBtc, 
    "memo" => $memo
]);


if (isset($transactionResponse['result']['rhash'])) {
    $btcAddress = $transactionResponse['result']['address'];
    $uri = $transactionResponse['result']['URI'];
    $request_id = $transactionResponse['result']['request_id'];
    $txHash = "";
    $status = $transactionResponse['result']['status'] == 0 ? 'PENDING' : 'CONFIRMED';

    try {
        $stmt = $pdo->prepare("INSERT INTO payment_requests (user_id, username, btc_address, amount_usd, amount_usd_margin, amount_btc, memo, qr_uri, tx_hash, request_id, status, created_at) 
        VALUES (:user_id, :username, :btc_address, :amount_usd, :amount_usd_margin, :amount_btc, :memo, :qr_uri, :tx_hash, :request_id, :status, :created_at)");

        // Bind the parameters
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':btc_address', $btcAddress);
        $stmt->bindParam(':amount_usd', $usdAmount);
        $stmt->bindParam(':amount_usd_margin', $usdAmountMargin);
        $stmt->bindParam(':amount_btc', $amountBtc);
        $stmt->bindParam(':memo', $memo);
        $stmt->bindParam(':qr_uri', $uri);
        $stmt->bindParam(':tx_hash', $txHash);
        $stmt->bindParam(':request_id', $request_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_at', $currentDateTime);

        $stmt->execute();

        echo json_encode([
            'success' => true,
            'data' => [
                'btcAddress' => $btcAddress,
                'amount_usd_margin' => number_format($usdAmountMargin, 2),
                'amountBtc' => number_format($amountBtc, 8),
                'margin' => $marginPercent
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to generate BTC address.']);
}
?>
