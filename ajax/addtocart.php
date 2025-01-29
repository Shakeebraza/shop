<?php
session_start();
require '../global.php';

if (!isset($_SESSION['cards'])) {
    $_SESSION['cards'] = [];
}
if (!isset($_SESSION['dumps'])) {
    $_SESSION['dumps'] = [];
}

$input = json_decode(file_get_contents('php://input'), true);
$cardId = $input['cardId'] ?? null;
$type = $input['type'] ?? null;

if ($cardId && $type) {
    try {

        if ($type == 'card') {
            $stmt = $pdo->prepare("SELECT id, card_number, name_on_card, price, card_type FROM credit_cards WHERE id = :cardId");
        } elseif ($type == 'dump') {
            $stmt = $pdo->prepare("SELECT id, track1, track2, monthexp, yearexp, pin, price, country, base_name, seller_name, status, card_type FROM dumps WHERE id = :cardId");
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid type.']);
            exit;
        }

        $stmt->bindParam(':cardId', $cardId, PDO::PARAM_INT);
        $stmt->execute();

        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {

            if ($type == 'dump') {
                $cardtpe=$item['card_type']??'visa';
                $itemData = [
                    'id' => $item['id'],
                    'bin' => substr($item['track2'], 0, 6), 
                    'price' => $item['price'],
                    'image' => '/shop/images/cards/' . strtolower($cardtpe) . '.png',
                    'type' => 'dump'
                ];
          
                if (isset($_SESSION['dumps'][$cardId])) {
                    $_SESSION['dumps'][$cardId] = $itemData; 
                } else {
                    $_SESSION['dumps'][$cardId] = $itemData;
                }
            } else {
                $cardtpe=$item['card_type']??'visa';
                $itemData = [
                    'id' => $item['id'],
                    'bin' => substr($item['card_number'], 0, 6), 
                    'price' => $item['price'],
                    'image' => '/shop/images/cards/' . strtolower($cardtpe) . '.png',
                    'type' => 'card'
                ];
           
                if (isset($_SESSION['cards'][$cardId])) {
                    $_SESSION['cards'][$cardId] = $itemData; 
                } else {
                    $_SESSION['cards'][$cardId] = $itemData; 
                }
            }

            $cardTotal = array_sum(array_column($_SESSION['cards'], 'price')) ?? 0;
            $dumpTotal = array_sum(array_column($_SESSION['dumps'], 'price')) ?? 0;
            $total = $cardTotal + $dumpTotal;
            echo json_encode([
                'success' => true,
                'cards' => array_values($_SESSION['cards']??[]),
                'dumps' => array_values($_SESSION['dumps']??[]),
                'total' =>  $total, 
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Item ID or type is missing.']);
}
?>