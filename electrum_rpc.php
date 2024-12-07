<?php

$rpc_user = 'user';
$rpc_password = 'C5u1s9a8';
$rpc_host = '188.119.149.183';
$rpc_port = '7777';

function electrumRequest($method, $params = []) {
    global $rpc_user, $rpc_password, $rpc_host, $rpc_port;

    $url = "http://$rpc_host:$rpc_port/";
    $auth = "$rpc_user:$rpc_password";

    $payload = json_encode([
        'jsonrpc' => '2.0',
        'method' => $method,
        'params' => $params,
        'id' => 1
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, $auth);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    // Log raw response for debugging
    echo "<h3>Raw Response:</h3><pre>" . htmlentities($response) . "</pre>";

    $decoded_response = json_decode($response, true);

    if ($decoded_response === null) {
        echo 'JSON Decode Error: ' . json_last_error_msg();
        return null;
    }

    // Check for any Electrum error in the response
    if (isset($decoded_response['error'])) {
        echo "Electrum Error: " . json_encode($decoded_response['error']);
        return null;
    }

    return $decoded_response;
}



// $load_wallet = electrumRequest('load_wallet', ["wallet_path" => "/root/electrum/shop_wallet"]);

// $balance = electrumRequest('getbalance');


// $unused_address = electrumRequest('getunusedaddress');


// echo "<h2>Electrum Wallet Information</h2>";
// echo "<p>Wallet Load Status: " . json_encode($load_wallet) . "</p>";
// echo "<p>Balance: " . json_encode($balance) . "</p>";
// echo "<h3>Unused Address Response</h3>";
// echo "<pre>";
// print_r($unused_address);
// echo "</pre>";

// if (isset($unused_address['result'])) {
//     echo "<p>Unused Address: " . $unused_address['result'] . "</p>";
// } else {
//     echo "<p>Error: Unable to fetch unused address. Response: " . json_encode($unused_address) . "</p>";
// }

?>
