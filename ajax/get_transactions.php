<?php
include_once('../config.php');

session_start();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
 
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}


$start = $_GET['start'];
$length = $_GET['length'];
$orderColumn = $_GET['order'][0]['column'];
$orderDir = $_GET['order'][0]['dir']; 
$searchValue = $_GET['search']['value']; 

$columns = ['id', 'created_at', 'amount_usd', 'amount_btc', 'btc_address', 'tx_hash', 'status'];
$orderBy = $columns[$orderColumn];


$query = "SELECT id,created_at, amount_usd, amount_btc, btc_address, tx_hash, status 
          FROM payment_requests 
          WHERE user_id = :user_id";


if ($searchValue) {
    $query .= " AND (id LIKE :search OR date LIKE :search OR amount_usd LIKE :search 
                    OR amount_btc LIKE :search OR btc_address LIKE :search 
                    OR tx_hash LIKE :search OR status LIKE :search)";
}

$query .= " ORDER BY $orderBy $orderDir LIMIT :start, :length";


$stmt = $pdo->prepare($query);


$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
if ($searchValue) {
    $stmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
}
$stmt->bindValue(':start', (int) $start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int) $length, PDO::PARAM_INT);


$stmt->execute();

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);


$totalRecordsQuery = "SELECT COUNT(*) FROM payment_requests WHERE user_id = :user_id";
$totalRecordsStmt = $pdo->prepare($totalRecordsQuery);
$totalRecordsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$totalRecordsStmt->execute();
$totalRecords = $totalRecordsStmt->fetchColumn();


$filteredRecordsQuery = "SELECT COUNT(*) FROM payment_requests WHERE user_id = :user_id";
if ($searchValue) {
    $filteredRecordsQuery .= " AND (id LIKE :search OR date LIKE :search OR amount_usd LIKE :search 
                                 OR amount_btc LIKE :search OR btc_address LIKE :search 
                                 OR tx_hash LIKE :search OR status LIKE :search)";
}
$filteredRecordsStmt = $pdo->prepare($filteredRecordsQuery);
$filteredRecordsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
if ($searchValue) {
    $filteredRecordsStmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
}
$filteredRecordsStmt->execute();
$filteredRecords = $filteredRecordsStmt->fetchColumn();

echo json_encode([
    "draw" => $_GET['draw'],
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords, 
    "data" => $transactions
]);
?>
