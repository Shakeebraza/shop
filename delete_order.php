<?php
require 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$tool_id = $_GET['tool_id'];
$section = isset($_GET['section']) ? $_GET['section'] : 'my-orders'; // Default to 'my-orders'

// Delete the order
$stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ? AND tool_id = ?");
$stmt->execute([$user_id, $tool_id]);

// Redirect back to My Orders section after deletion
echo "<script>alert('The tool has been successfully removed from your orders.'); window.location.href = 'dashboard.php#{$section}';</script>";
