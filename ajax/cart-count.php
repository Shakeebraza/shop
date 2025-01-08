<?php
session_start();


if (!isset($_SESSION['cards'])) {
    $_SESSION['cards'] = [];
}
if (!isset($_SESSION['dumps'])) {
    $_SESSION['dumps'] = [];
}
$count=count($_SESSION['cards']) +count($_SESSION['dumps']);

echo json_encode(['count' => $count]);
?>
