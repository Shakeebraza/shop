<?php
session_start();
require '../../global.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must be logged in to make a purchase.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$tool_id = filter_input(INPUT_POST, 'tool_id', FILTER_VALIDATE_INT);

if (!$tool_id) {
    echo json_encode(['error' => 'Invalid Tool ID.']);
    exit();
}


$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['error' => 'User not found.']);
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id = ?");
$stmt->execute([$tool_id]);
$tool = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tool) {
    echo json_encode(['error' => 'Tool not found.']);
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND tool_id = ?");
$stmt->execute([$user_id, $tool_id]);
$existingOrder = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingOrder) {
    echo json_encode(['error' => 'You have already purchased this product. Check your My Orders section.']);
    exit();
}


if ($user['balance'] < $tool['price']) {
    echo json_encode(['error' => 'Insufficient funds. Please add money to your account.']);
    exit();
}


$newBalance = $user['balance'] - $tool['price'];
$stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
$stmt->execute([$newBalance, $user_id]);


if ($stmt->rowCount() === 0) {
    echo json_encode(['error' => 'Failed to update balance. Please try again.']);
    exit();
}


$stmt = $pdo->prepare("INSERT INTO orders (user_id, tool_id, created_at) VALUES (?, ?, NOW())");
$stmt->execute([$user_id, $tool_id]);


if ($stmt->rowCount() === 0) {
    echo json_encode(['error' => 'Failed to add order. Please try again.']);
    exit();
}else{
    $logData = [
        "user_id"    => $_SESSION['user_id'], 
        "user_name"  => $_SESSION['username'], 
        "buy_itm"    => $tool['name'], 
        "item_price" => $tool['price'], 
        "item_type"  => $tool['section'] 
    ];

 
   $settings->insertActivityLog($logData);
}


echo json_encode(['success' => 'Purchase successful! To view your purchased items, please visit the My Orders section.']);
exit(); 