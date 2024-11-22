<?php

// Electrum RPC credentials
$rpc_user = 'user';
$rpc_password = 'C5u1s9a8';
$rpc_host = '188.119.149.183';
$rpc_port = '7777';

// Function to send a JSON-RPC request
function electrumRequest($method, $params = []) {
    global $rpc_user, $rpc_password, $rpc_host, $rpc_port;

    // Set up the URL and authentication
    $url = "http://$rpc_host:$rpc_port/";
    $auth = "$rpc_user:$rpc_password";

    // Set up the JSON-RPC payload
    $payload = json_encode([
        'jsonrpc' => '2.0',
        'method' => $method,
        'params' => $params,
        'id' => 1
    ]);

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, $auth); // Set user and password
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Execute the request and decode the response
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);

    return json_decode($response, true);
}

// Load the wallet
$load_wallet = electrumRequest('load_wallet', ["wallet_path" => "/root/electrum/shop_wallet"]);

// Get the wallet balance
$balance = electrumRequest('getbalance');

// Get an unused receiving address
$unused_address = electrumRequest('getunusedaddress');

// Display the results
echo "<h2>Electrum Wallet Information</h2>";
echo "<p>Wallet Load Status: " . json_encode($load_wallet) . "</p>";
echo "<p>Balance: " . json_encode($balance) . "</p>";
echo "<p>Unused Address: " . json_encode($unused_address) . "</p>";

?>