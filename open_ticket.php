<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, message, status, created_at) VALUES (?, ?, 'open', NOW())");
if ($stmt->execute([$user_id, $message])) {
    echo json_encode(['status' => 'success', 'message' => 'Ticket opened successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to open ticket']);
}
?>
