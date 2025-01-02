<?php
include_once('../global.php');
header('Content-Type: application/json');
$response = [
    'currentTime' =>$currentDateTime
];

echo json_encode($response);

?>