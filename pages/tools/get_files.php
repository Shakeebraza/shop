<?php
// File: get_files.php
require '../../global.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$files = $settings->getFilesBySection('tools', 12, $page);

header('Content-Type: application/json');
echo json_encode([
    'files' => $files['files'],
    'currentPage' => $files['currentPage'],
    'totalPages' => $files['totalPages']
]);
exit;

?>
