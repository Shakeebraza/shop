<?php
require '../../global.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : ''; // Capture search query

$files = $settings->getFilesBySection2('leads', 12, $page, $search);

header('Content-Type: application/json');
echo json_encode([
    'files' => $files['files'],
    'currentPage' => $files['currentPage'],
    'totalPages' => $files['totalPages']
]);
exit;
?>