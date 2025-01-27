<?php
include_once('../global.php'); 
session_start();



function formatCardData($creditCards) {
    $formattedData = [];
    $ative = $_SESSION['active'] === 0 ? 'disabled' : '';

    foreach ($creditCards as $card) {
        $otherinfo = (!empty($card['otherinfo']) && $card['otherinfo'] != 'NA') ? 'Yes' : 'No';
        $cardimg=$card['card_type'] ?? 'visa';
        $formattedData[] = [
            'card_logo' => '<img src="/shop/images/cards/' . strtolower($cardimg) . '.png" alt="Card Logo" class="card-logo">',
            'email' => htmlspecialchars($card['email'] ?? 'NA'),
            'card_number' => htmlspecialchars(substr($card['card_number'] ?? '', 0, 6)),
            'expiry-m' => htmlspecialchars($card['mm_exp'] ?? '') ,
            'expiry-y' =>  htmlspecialchars($card['yyyy_exp'] ?? ''),
            'country' => htmlspecialchars($card['country'] ?? ''),
            'state' => htmlspecialchars($card['state'] ?? ''),
            'city' => htmlspecialchars($card['city'] ?? ''),
            'zip' => substr($card['zip'] ?? '', 0, 3) . '****',
            'price' => '$' . htmlspecialchars($card['price'] ?? ''),

            'otherinfo' => $otherinfo,
            'actions' => '
                <div class="action-buttons">
                    <a href="#" class="buy-button ' . $ative . '" style="background-color:#0c182f;" onclick="showConfirm(\'' . $card['id'] . '\', \'' . $card['price'] . '\')">
                        <span class="price">$' . htmlspecialchars($card['price']) . '</span>
                        <span class="buy-now">Buy Now</span>
                    </a>
                    <button class="add-to-cart-button ' . $ative . '" style="background-color:#6c5ce7; color:#fff; margin-left:10px; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;" 
                            onclick="addToCart(\'' . $card['id'] . '\')">
                        <span class="button-text">Add to Cart</span>
                        <i class="card-icon fas fa-shopping-cart"></i>
                    </button>
                </div>'
        ];
    }

    return $formattedData;
}

// Fetch the data with filters and pagination
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$filters = [
    'cc_bin' => isset($_POST['cc_bin']) ? $_POST['cc_bin'] : '',
    'cc_country' => isset($_POST['cc_country']) ? $_POST['cc_country'] : '',
    'cc_state' => isset($_POST['cc_state']) ? $_POST['cc_state'] : '',
    'cc_city' => isset($_POST['cc_city']) ? $_POST['cc_city'] : '',
    'cc_zip' => isset($_POST['cc_zip']) ? $_POST['cc_zip'] : '',
    'cc_type' => isset($_POST['cc_type']) ? $_POST['cc_type'] : 'all',
    'basename' => isset($_POST['basename']) ? $_POST['basename'] : 'all'
];

$response = $settings->getCreditCardData($start, $length, $filters);
$formattedData = formatCardData($response['data']);

echo json_encode([
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => $response['totalRecords'],
    'recordsFiltered' => $response['totalRecords'],
    'data' => $formattedData
]);
?>