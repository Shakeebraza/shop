<?php
ob_start();

session_start();
require 'config.php';

$ccBin = isset($_POST['cc_bin']) ? trim($_POST['cc_bin']) : '';
$ccCountry = isset($_POST['cc_country']) ? trim($_POST['cc_country']) : '';
$ccState = isset($_POST['cc_state']) ? trim($_POST['cc_state']) : '';
$ccCity = isset($_POST['cc_city']) ? trim($_POST['cc_city']) : '';
$ccZip = isset($_POST['cc_zip']) ? trim($_POST['cc_zip']) : '';
$ccType = isset($_POST['cc_type']) ? trim($_POST['cc_type']) : 'all';
$cardsPerPage = isset($_POST['cards_per_page']) ? (int)$_POST['cards_per_page'] : 10;

$sql = "SELECT id, card_type, card_number, mm_exp, yyyy_exp, country, state, city, zip, price 
        FROM credit_cards 
        WHERE buyer_id IS NULL AND status = 'unsold' ";
$params = [];

// Apply filters if provided
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

$sql .= " ORDER BY id DESC LIMIT " . intval($cardsPerPage);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creditCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add image path for each card type
foreach ($creditCards as &$card) {
$cardType = strtolower($card['card_type']); // Normalize to lowercase
$card['image_path'] = "images/cards/{$cardType}.png"; // Path to the logo image
}

header('Content-Type: application/json');
echo json_encode($creditCards);

ob_end_flush();
