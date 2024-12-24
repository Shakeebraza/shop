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

$columns = ['id', 'created_at', 'amount_usd', 'amount_btc', 'btc_address', 'tx_hash', 'status', 'username'];
$orderBy = $columns[$orderColumn];

$query = "SELECT pr.id, pr.created_at,amount_usd_margin, pr.amount_usd, pr.amount_btc, pr.btc_address, pr.tx_hash, pr.status,pr.created_at,pr.qr_uri, u.username 
          FROM payment_requests pr
          JOIN users u ON pr.user_id = u.id
          WHERE pr.user_id = :user_id";

if ($searchValue) {
    $query .= " AND (pr.id LIKE :search OR pr.created_at LIKE :search OR pr.amount_usd LIKE :search 
                    OR pr.amount_btc LIKE :search OR pr.btc_address LIKE :search 
                    OR pr.tx_hash LIKE :search OR pr.status LIKE :search OR u.username LIKE :search)";
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

$totalRecordsQuery = "SELECT COUNT(*) 
                      FROM payment_requests pr
                      JOIN users u ON pr.user_id = u.id
                      WHERE pr.user_id = :user_id";
$totalRecordsStmt = $pdo->prepare($totalRecordsQuery);
$totalRecordsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$totalRecordsStmt->execute();
$totalRecords = $totalRecordsStmt->fetchColumn();

$filteredRecordsQuery = "SELECT COUNT(*) 
                         FROM payment_requests pr
                         JOIN users u ON pr.user_id = u.id
                         WHERE pr.user_id = :user_id";
if ($searchValue) {
    $filteredRecordsQuery .= " AND (pr.id LIKE :search OR pr.created_at LIKE :search OR pr.amount_usd LIKE :search 
                                    OR pr.amount_btc LIKE :search OR pr.btc_address LIKE :search 
                                    OR pr.tx_hash LIKE :search OR pr.status LIKE :search OR u.username LIKE :search)";
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
