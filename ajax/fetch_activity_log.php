<?php
require '../global.php';

$draw = $_GET['draw'];
$start = $_GET['start'];
$length = $_GET['length'];
$searchValue = $_GET['search']['value']; 
$orderColumnIndex = $_GET['order'][0]['column'];
$orderDir = $_GET['order'][0]['dir'];
$itemType = $_GET['item_type']; 


$columns = ['user_name', 'buy_itm', 'item_price', 'item_type', 'created_at'];
$orderColumn = $columns[$orderColumnIndex];


$query = "SELECT * FROM activity_log WHERE 1";


if ($searchValue) {
    $query .= " AND (user_name LIKE :search OR buy_itm LIKE :search OR item_price LIKE :search OR item_type LIKE :search)";
}


if ($itemType) {
    $query .= " AND item_type = :item_type";
}

$query .= " ORDER BY $orderColumn $orderDir LIMIT :start, :length";


$stmt = $pdo->prepare($query);


if ($searchValue) {
    $stmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
}

if ($itemType) {
    $stmt->bindValue(':item_type', $itemType, PDO::PARAM_STR);
}

$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);


$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


$countQuery = "SELECT COUNT(*) FROM activity_log WHERE 1";

if ($itemType) {
    $countQuery .= " AND item_type = :item_type";
}

$countStmt = $pdo->prepare($countQuery);


if ($itemType) {
    $countStmt->bindValue(':item_type', $itemType, PDO::PARAM_STR);
}

$countStmt->execute();
$totalRecords = $countStmt->fetchColumn();

$response = [
    'draw' => intval($draw),
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalRecords,
    'data' => $data
];

echo json_encode($response);
?>