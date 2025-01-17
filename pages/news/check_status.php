<?php
session_start();

header('Content-Type: application/json');

// Check if session variable 'active' is set
if (isset($_SESSION['active']) && $_SESSION['active'] == 0) {
    $response = [
        'status' => 'inactive',
        'message' => '<p>Your account is inactive. To activate your account, please top up your balance with at least $20.</p>' .
                     '<p>Attention: Accounts that remain inactive for more than 15 days will be automatically deleted.</p>',
    ];
} else {
    $response = ['status' => 'active'];
}

echo json_encode($response);