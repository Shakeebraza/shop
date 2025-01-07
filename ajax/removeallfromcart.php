<?php
session_start();

$inputData = json_decode(file_get_contents('php://input'), true);


if (isset($inputData['action']) && $inputData['action'] == 'removeAll') {

    unset($_SESSION['cart']);


    echo json_encode([
        'success' => true,
        'cartItems' => [],
        'total' => 0
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No action specified or invalid request.'
    ]);
}
?>
