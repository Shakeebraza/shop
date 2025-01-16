<?php
include_once('../global.php'); 

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
        exit;
    }

    $userId = intval($_SESSION['user_id']);
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $balance = floatval($user['balance']);

        if ($balance > 20) {
            $updateStmt = $pdo->prepare("UPDATE users SET active = 1 WHERE id = ?");
            $updateStmt->execute([$userId]);

            echo json_encode(['status' => 'success', 'message' => 'User activated successfully.']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'Balance is less than 20.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}