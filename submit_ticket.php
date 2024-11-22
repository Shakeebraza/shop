<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'], $_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $message = trim($_POST['message']);
    $subject = "Support Ticket";

    if (strlen($message) > 500) {
        echo json_encode(['success' => false, 'message' => 'Message exceeds 500 characters.']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM support_tickets WHERE user_id = ? AND status = 'open'");
    $stmt->execute([$user_id]);
    $ticket = $stmt->fetch();

    if ($ticket) {
        $ticket_id = $ticket['id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_replies WHERE ticket_id = ? AND sender = 'user'");
        $stmt->execute([$ticket_id]);
        $userReplyCount = $stmt->fetchColumn();

        if ($userReplyCount >= 3) {
            echo json_encode(['success' => false, 'message' => 'Limit of 3 consecutive messages reached. Wait for admin reply.']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO support_replies (ticket_id, sender, message, created_at) VALUES (?, 'user', ?, NOW())");
        $stmt->execute([$ticket_id, $message]);
        $pdo->prepare("UPDATE support_tickets SET admin_unread = 1 WHERE id = ?")->execute([$ticket_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, status, admin_unread) VALUES (?, ?, 'open', 1)");
        $stmt->execute([$user_id, $subject]);
        $ticket_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO support_replies (ticket_id, sender, message, created_at) VALUES (?, 'user', ?, NOW())");
        $stmt->execute([$ticket_id, $message]);
    }

    header("Location: dashboard.php#support");
    exit();
} else {
    echo "Error: Missing required fields.";
    exit();
}
?>
