<?php 
require 'config.php';
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php?tool_message=" . urlencode("You must be logged in to make a purchase.") . "&section=tools");
    exit();
}

$user_id = $_SESSION['user_id'];
$tool_id = filter_input(INPUT_GET, 'tool_id', FILTER_VALIDATE_INT);

// Check if the tool ID is valid
if (!$tool_id) {
    header("Location: dashboard.php?tool_message=" . urlencode("Invalid Tool ID.") . "&section=tools");
    exit();
}

// Fetch user balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: dashboard.php?tool_message=" . urlencode("User not found.") . "&section=tools");
    exit();
}

// Fetch tool information
$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id = ?");
$stmt->execute([$tool_id]);
$tool = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tool) {
    header("Location: dashboard.php?tool_message=" . urlencode("Tool not found.") . "&section=tools");
    exit();
}

// Check if the user already purchased this tool
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND tool_id = ?");
$stmt->execute([$user_id, $tool_id]);
$existingOrder = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingOrder) {
    header("Location: dashboard.php?tool_message=" . urlencode("You have already purchased this product. Check your My Orders section.") . "&section=my-orders");
    exit();
}

// Check if the user has enough balance
if ($user['balance'] < $tool['price']) {
    header("Location: dashboard.php?tool_message=" . urlencode("Insufficient funds. Please add money to your account.") . "&section=add-money");
    exit();
}

// Deduct the price from the user's balance
$newBalance = $user['balance'] - $tool['price'];
$stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
$stmt->execute([$newBalance, $user_id]);

// Verify balance update
if ($stmt->rowCount() === 0) {
    header("Location: dashboard.php?tool_message=" . urlencode("Failed to update balance. Please try again.") . "&section=tools");
    exit();
}

// Add purchase to the orders table with timestamp
$stmt = $pdo->prepare("INSERT INTO orders (user_id, tool_id, created_at) VALUES (?, ?, NOW())");
$stmt->execute([$user_id, $tool_id]);

// Verify order insertion
if ($stmt->rowCount() === 0) {
    header("Location: dashboard.php?tool_message=" . urlencode("Failed to add order. Please try again.") . "&section=tools");
    exit();
}

// Redirect with success message to My Orders section
header("Location: dashboard.php?tool_message=" . urlencode("Purchase successful!") . "&section=tools&redirect=my-orders");
exit();
