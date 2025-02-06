<?php
require '../global.php';

// Get the data from the AJAX request
$itemId = $_GET['item_id'];
$itemType = $_GET['item_type'];

// Default response
$response = '';

// Query to fetch additional data based on item type and ID
if ($itemType === 'Leads' || $itemType === 'Pages' || $itemType === 'Tools') {
    // You can add more specific queries depending on your requirements
    $query = "SELECT * FROM uploads WHERE id = :item_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the data
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Format the response as needed
        $response = 'Name: ' . $data['name'] . ', Description: ' . $data['description'];
    } else {
        $response = 'No additional data found.';
    }
} elseif ($itemType === 'Cards') {
    // Handle the "BHE" cards query
    $query = "SELECT * FROM credit_cards WHERE id = :item_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the data
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Format the response with all card details
        $response = 'Card Number: ' . $data['card_number'] . 
                    ', Expiry: ' . $data['mm_exp'] . '/' . $data['yyyy_exp'] .
                    ', CVV: ' . $data['cvv'] . 
                    ', Name on Card: ' . $data['name_on_card'] ;
    } else {
        $response = 'No Cards data found.';
    }
}elseif ($itemType === 'Dumps') {
  
    $query = "SELECT * FROM dumps WHERE  id = :item_id";
    $stmt = $pdo->prepare($query);
    
    $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
    $stmt->execute();


    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
      
        $response = 'Track2: ' . $data['track2'] . ', Code: ' . $data['code'] . ', Track2: ' . $data['track2'] . ', Expiration: ' . $data['monthexp'] . '/' . $data['yearexp'];
    } else {
        $response = 'No other Dumps data found.';
    }
}



echo $response;
?>