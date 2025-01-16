<?php
include_once('../global.php'); 
session_start();
$dumpBin = isset($_POST['dump_bin']) ? trim($_POST['dump_bin']) : '';
$dumpCountry = isset($_POST['dump_country']) ? trim($_POST['dump_country']) : '';
$dumpType = isset($_POST['dump_type']) ? trim($_POST['dump_type']) : 'all';
$dumpPin = isset($_POST['dump_pin']) ? trim($_POST['dump_pin']) : 'all';
$base_name = isset($_POST['base_name']) ? trim($_POST['base_name']) : 'all';
$track_pin = isset($_POST['track_pin']) ? trim($_POST['track_pin']) : 'all';


$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;

$sql = "SELECT id, track1, track2, monthexp, yearexp, pin, card_type, price, country 
        FROM dumps 
        WHERE buyer_id IS NULL AND status = 'unsold'";

$params = [];

if (!empty($dumpBin)) {
    $bins = array_map('trim', explode(',', $dumpBin));
    $sql .= " AND (" . implode(" OR ", array_fill(0, count($bins), "track2 LIKE ?")) . ")";
    foreach ($bins as $bin) {
        $params[] = $bin . '%';
    }
}
if (!empty($dumpCountry)) {
    $sql .= " AND UPPER(TRIM(country)) = ?";
    $params[] = strtoupper(trim($dumpCountry));
}
if ($dumpType !== 'all') {
    $sql .= " AND card_type = ?";
    $params[] = $dumpType;
}
if ($dumpPin === 'yes') {
    $sql .= " AND pin IS NOT NULL";
} elseif ($dumpPin === 'no') {
    $sql .= " AND pin IS NULL";
}
if ($base_name !== 'all') {
    $sql .= " AND base_name = ?";
    $params[] = $base_name;
}
if ($track_pin !== 'all') {
    if ($track_pin === 'no') {
        $sql .= " AND track1 IS NULL"; 
    } elseif ($track_pin === 'yes') {
        $sql .= " AND (track1 IS NOT NULL AND track1 != '')";
    }
}


$sql .= " ORDER BY id DESC LIMIT $start, $length";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dumps = $stmt->fetchAll();


$totalStmt = $pdo->query("SELECT COUNT(*) FROM dumps WHERE buyer_id IS NULL AND status = 'unsold'");
$totalRecords = $totalStmt->fetchColumn();

$filteredSql = "SELECT COUNT(*) FROM dumps WHERE buyer_id IS NULL AND status = 'unsold'";
$filteredParams = [];
if (!empty($dumpBin)) {
    $filteredSql .= " AND (" . implode(" OR ", array_fill(0, count($bins), "track2 LIKE ?")) . ")";
    foreach ($bins as $bin) {
        $filteredParams[] = $bin . '%';
    }
}
if (!empty($dumpCountry)) {
    $filteredSql .= " AND UPPER(TRIM(country)) = ?";
    $filteredParams[] = strtoupper(trim($dumpCountry));
}
if ($dumpType !== 'all') {
    $filteredSql .= " AND card_type = ?";
    $filteredParams[] = $dumpType;
}
if ($dumpPin === 'yes') {
    $filteredSql .= " AND pin IS NOT NULL";
} elseif ($dumpPin === 'no') {
    $filteredSql .= " AND pin IS NULL";
}
if ($base_name !== 'all') {
    $filteredSql .= " AND base_name = ?";
    $filteredParams[] = $base_name;
}

$filteredStmt = $pdo->prepare($filteredSql);
$filteredStmt->execute($filteredParams);
$totalFiltered = $filteredStmt->fetchColumn();
$ative=$_SESSION['active'] === 0 ?'disabled':'';
$data = [];
foreach ($dumps as $dump) {
    $data[] = [
        'card_logo' => '<img src="/shop/images/cards/' . strtolower($dump['card_type']) . '.png" alt="' . htmlspecialchars($dump['card_type']) . '" class="card-logo">',
        'track2' => htmlspecialchars(substr($dump['track2'], 0, 6)),
        'expiry' => htmlspecialchars( $dump['yearexp']. '/' . $dump['monthexp']),
        'pin' => !empty($dump['pin']) ? 'Yes' : 'No',
        'track' => !empty($dump['track1']) ? 'Yes' : 'No',
        'country' => htmlspecialchars($dump['country']),
        'price' => '$' . htmlspecialchars($dump['price']),
        // 'actions' => '<a href="buy_dump.php?dump_id=' . htmlspecialchars($dump['id']) . '" class="buy-button-dump" style="background-color:#0c182f;" onclick="return confirm(\'Are you sure you want to buy this dump?\');">
        //                 <span class="price">$' . htmlspecialchars($dump['price']) . '</span>
        //                 <span class="buy-now">Buy Now</span>
        //               </a>',
     'actions' => '  <div class="action-buttons">
        <a href="#" class="buy-button '.$ative.'" style="background-color:#0c182f;" onclick="showConfirm(\'' . $dump['id'] . '\', \'' . $dump['price'] . '\')">
            <span class="price">$' . htmlspecialchars($dump['price']) . '</span>
            <span class="buy-now">Buy Now</span>
        </a>
        <button class="add-to-cart-button '.$ative.'" style="background-color:#6c5ce7; color:#fff; margin-left:10px; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;" 
            onclick="addToDump(\'' . $dump['id'] . '\')">
            <span class="button-text">Add to Cart</span>
            <i class="card-icon fas fa-shopping-cart"></i> <!-- Font Awesome shopping cart icon -->
        </button>
    </div>'

    ];
}

echo json_encode([
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $totalFiltered,
    'data' => $data
]);
?>