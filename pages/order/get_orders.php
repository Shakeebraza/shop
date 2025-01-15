<?php
require '../../global.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401); 
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$page = $_GET['page'] ?? 1;
$perPage = 10; 
$response = $settings->fetchOrders($user_id, $page, $perPage);

if (isset($response['error'])) {
    http_response_code($response['code']);
    echo json_encode(['error' => $response['error']]);
    exit;
}

header('Content-Type: application/json');
echo json_encode($response);