<?php
include_once('../global.php'); 
session_start();
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;

$ccBin = isset($_POST['cc_bin']) ? trim($_POST['cc_bin']) : '';
$ccCountry = isset($_POST['cc_country']) ? trim($_POST['cc_country']) : '';
$ccState = isset($_POST['cc_state']) ? trim($_POST['cc_state']) : '';
$ccCity = isset($_POST['cc_city']) ? trim($_POST['cc_city']) : '';
$ccZip = isset($_POST['cc_zip']) ? trim($_POST['cc_zip']) : '';
$ccType = isset($_POST['cc_type']) ? trim($_POST['cc_type']) : 'all';
$basename = isset($_POST['basename']) ? trim($_POST['basename']) : 'all';

$sql = "SELECT *
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
    $sql .= " AND state LIKE ?";
    $params[] = "%$ccState%";
}
if (!empty($ccCity)) {
    $sql .= " AND city LIKE ?";
    $params[] = "%$ccCity%";
}
if (!empty($ccZip)) {
    $sql .= " AND zip LIKE ?";
    $params[] = "%$ccZip%";
}
if ($ccType !== 'all') {
    $sql .= " AND card_type = ?";
    $params[] = $ccType;
}
if ($basename !== 'all') {
    $sql .= " AND base_name = ?";
    $params[] = $basename;
}


$sql .= " ORDER BY id DESC LIMIT " . intval($start) . ", " . intval($length);

// var_dump($pdo);
// exit();
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creditCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStmt = $pdo->query("SELECT FOUND_ROWS()");
$totalRecords = $totalStmt->fetchColumn();
$ative=$_SESSION['active'] === 0 ?'disabled':'';
$data = [];
foreach ($creditCards as $card) {
    if(!empty($card['otherinfo']) AND $card['otherinfo'] != 'NA'){
        $otherinfo =  'Yes';
    }else{
        $otherinfo =  'No';
        
    }

    $data[] = [
        'card_logo' => '<img src="/shop/images/cards/' . strtolower($card['card_type']) . '.png" alt="Card Logo" class="card-logo">',
        'card_number' => htmlspecialchars(substr($card['card_number'], 0, 6)),
        'expiry' => htmlspecialchars($card['mm_exp']) . '/' . htmlspecialchars($card['yyyy_exp']),
        'country' => htmlspecialchars($card['country']),
        'state' => htmlspecialchars($card['state']),
        'city' => htmlspecialchars($card['city']),
        // 'mmn' => htmlspecialchars($card['mmn']),
        // 'account_number' => htmlspecialchars($card['account_number']),
        // 'sort_code' => htmlspecialchars($card['sort_code']),
        // 'cardholder_name' => htmlspecialchars($card['cardholder_name']),
        'zip' => substr($card['zip'], 0, 3) . '****',
        'price' => '$' . htmlspecialchars($card['price']),
        'otherinfo' => $otherinfo,
        'actions' => '
    <div class="action-buttons">
        <a href="#" class="buy-button '.$ative.'" style="background-color:#0c182f;" onclick="showConfirm(\'' . $card['id'] . '\', \'' . $card['price'] . '\')">
            <span class="price">$' . htmlspecialchars($card['price']) . '</span>
            <span class="buy-now">Buy Now</span>
        </a>
        <button class="add-to-cart-button '.$ative.'" style="background-color:#6c5ce7; color:#fff; margin-left:10px; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;" 
            onclick="addToCart(\'' . $card['id'] . '\')">
            <span class="button-text">Add to Cart</span>
            <i class="card-icon fas fa-shopping-cart"></i> <!-- Font Awesome shopping cart icon -->
        </button>
    </div>'

    ];
}

echo json_encode([
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalRecords,
    'data' => $data
]);
?>