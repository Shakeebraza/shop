<?php
require '../global.php';
session_start();

header('Content-Type: application/json'); 

$response = [
    'status' => 'error',
    'errors' => []
];


if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}


if ($_SESSION['login_attempts'] >= 3 && time() - $_SESSION['last_attempt_time'] < 60) {
    $remaining_time = 60 - (time() - $_SESSION['last_attempt_time']);
    $response['errors'][] = "Too many failed attempts. Please try again after {$remaining_time} seconds.";
    $response['remaining_time'] = $remaining_time;
    echo json_encode($response);
    exit();
}

if (!isset($_POST['csrf_token']) || !$settings->verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode([
        'status' => 'error',
        'errors' => ['Invalid CSRF token. Please refresh the page and try again.']
    ]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $captcha = $_POST['captcha'];

    // if ($captcha !== $_SESSION['captcha_code']) {
    //     $response['errors'][] = "Invalid CAPTCHA";
    // }

    if (empty($response['errors'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['banned'] == 1) {
                $response['errors'][] = "Your account has been banned. Please contact support.";
            } else {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                $response['status'] = 'success';
                $response['redirect'] = $urlval . "/pages/news/index.php";
                echo json_encode($response);
                exit();
            }
        } else {
            $response['errors'][] = "Invalid username or password";
        }
    }


    if (!empty($response['errors'])) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();

        if ($_SESSION['login_attempts'] >= 3) {
            $response['errors'][] = "Too many failed attempts. Please try again after 60 seconds.";
            $response['remaining_time'] = 60;
        }
    }
}

echo json_encode($response);
exit();