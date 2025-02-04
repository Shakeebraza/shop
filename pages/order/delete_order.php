<?php
require '../../global.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$tool_id = $_POST['tool_id']; // Use $_POST instead of $_GET
$section = isset($_POST['section']) ? $_POST['section'] : 'my-orders'; 

// Delete the order
$stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ? AND tool_id = ?");
$stmt->execute([$user_id, $tool_id]);

echo json_encode(["status" => "success", "message" => "successfully removed from your orders."]);
exit();