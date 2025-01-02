<?php
include_once('../global.php'); 
$dumpBin = isset($_POST['dump_bin']) ? trim($_POST['dump_bin']) : '';
$dumpCountry = isset($_POST['dump_country']) ? trim($_POST['dump_country']) : '';
$dumpType = isset($_POST['dump_type']) ? trim($_POST['dump_type']) : 'all';
$dumpPin = isset($_POST['dump_pin']) ? trim($_POST['dump_pin']) : 'all';
$dumpsPerPage = isset($_POST['dumps_per_page']) ? (int)$_POST['dumps_per_page'] : 10;


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


$sql .= " ORDER BY id DESC LIMIT " . intval($dumpsPerPage);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dumps = $stmt->fetchAll();


$totalStmt = $pdo->query("SELECT COUNT(*) FROM dumps WHERE buyer_id IS NULL AND status = 'unsold'");
$totalRecords = $totalStmt->fetchColumn();

$data = [];
foreach ($dumps as $dump) {
    $data[] = [
        'card_logo' => '<img src="/shop/images/cards/' . strtolower($dump['card_type']) . '.png" alt="' . htmlspecialchars($dump['card_type']) . '" class="card-logo">',
        'track2' => htmlspecialchars(substr($dump['track2'], 0, 6)),
        'expiry' => htmlspecialchars($dump['monthexp'] . '/' . $dump['yearexp']),
        'pin' => !empty($dump['pin']) ? 'Yes' : 'No',
        'country' => htmlspecialchars($dump['country']),
        'price' => '$' . htmlspecialchars($dump['price']),
        'actions' => '<a href="buy_dump.php?dump_id=' . htmlspecialchars($dump['id']) . '" class="buy-button-dump" style="background-color:#0c182f;" onclick="return confirm(\'Are you sure you want to buy this dump?\');">
                        <span class="price">$' . htmlspecialchars($dump['price']) . '</span>
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