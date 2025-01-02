<?php
include_once('../global.php'); 


$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';


$ccBin = isset($_POST['cc_bin']) ? trim($_POST['cc_bin']) : '';
$ccCountry = isset($_POST['cc_country']) ? trim($_POST['cc_country']) : '';
$ccState = isset($_POST['cc_state']) ? trim($_POST['cc_state']) : '';
$ccCity = isset($_POST['cc_city']) ? trim($_POST['cc_city']) : '';
$ccZip = isset($_POST['cc_zip']) ? trim($_POST['cc_zip']) : '';
$ccType = isset($_POST['cc_type']) ? trim($_POST['cc_type']) : 'all';
$basename = isset($_POST['basename']) ? trim($_POST['basename']) : 'all';

$sql = "SELECT SQL_CALC_FOUND_ROWS id, card_type, card_number, mm_exp, yyyy_exp, country, state, city, zip, price 
        FROM credit_cards 
        WHERE buyer_id IS NULL AND status = 'unsold'";

$params = [];


if (!empty($ccBin)) {
    $bins = array_map('trim', explode(',', $ccBin));
    $sql .= " AND (" . implode(" OR ", array_fill(0, count($bins), "card_number LIKE ?")) . ")";
    foreach ($bins as $bin) {
        $params[] = $bin . '%';
    }
}
if (!empty($ccCountry)) {
    $sql .= " AND UPPER(TRIM(country)) = ?";
    $params[] = strtoupper(trim($ccCountry));
}
if (!empty($ccState)) {
    $sql .= " AND state = ?";
    $params[] = $ccState;
}
if (!empty($ccCity)) {
    $sql .= " AND city = ?";
    $params[] = $ccCity;
}
if (!empty($ccZip)) {
    $sql .= " AND zip = ?";
    $params[] = $ccZip;
}
if ($ccType !== 'all') {
    $sql .= " AND card_type = ?";
    $params[] = $ccType;
}
if ($basename !== 'all') {
    $sql .= " AND base_name = ?";
    $params[] = $basename;
}


if (!empty($searchValue)) {
    $sql .= " AND (card_number LIKE ? OR country LIKE ? OR state LIKE ?)";
    $params[] = "%$searchValue%";
    $params[] = "%$searchValue%";
    $params[] = "%$searchValue%";
}


$sql .= " ORDER BY id DESC LIMIT " . intval($start) . ", " . intval($length);

// var_dump($pdo);
// exit();
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creditCards = $stmt->fetchAll(PDO::FETCH_ASSOC);;


$totalStmt = $pdo->query("SELECT FOUND_ROWS()");
$totalRecords = $totalStmt->fetchColumn();


$data = [];
foreach ($creditCards as $card) {
    $data[] = [
        


   

        'card_logo' => '<img src="/shop/images/cards/' . strtolower($card['card_type']) . '.png" alt="Card Logo" class="card-logo">',
        'card_number' => htmlspecialchars($card['card_number']),
        'expiry' => htmlspecialchars($card['mm_exp']) . '/' . htmlspecialchars($card['yyyy_exp']),
        'country' => htmlspecialchars($card['country']),
        'state' => htmlspecialchars($card['state']),
        'city' => htmlspecialchars($card['city']),
        'zip' => substr($card['zip'], 0, 3) . '****',
        'price' => '$' . htmlspecialchars($card['price']),
        'actions' => '<a href="#" class="buy-button" style="background-color:#0c182f;" onclick="showConfirm(\'' . $card['id'] . '\', \'' . $card['price'] . '\')">
        <span class="price">$' . htmlspecialchars($card['price']) . '</span>
        <span class="buy-now">Buy Now</span>
      </a>'
    ];
}


echo json_encode([
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalRecords,
    'data' => $data
]);
?>
