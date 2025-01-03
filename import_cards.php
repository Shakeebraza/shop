<?php
session_start();
require 'config.php';

// Check if admin is logged in, if not redirect to admin login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php?redirect=panel.php");
    exit();
}

// Initialize message variables
$successMessage = '';
$duplicateMessage = '';
$errorMessage = '';
$importedCount = 0;
$duplicateCount = 0;

// Fetch sellers (users where seller = 1)
try {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE seller = 1");
    $stmt->execute();
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Error fetching sellers: ' . $e->getMessage();
}

// Function to determine card type based on card number
function getCardType($card_number) {
    $card_number = preg_replace('/\D/', '', $card_number);
    if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $card_number)) return 'Visa';
    if (preg_match('/^5[1-5][0-9]{14}$/', $card_number) || preg_match('/^2(2[2-9]|[3-6][0-9]|7[01])[0-9]{12}$/', $card_number)) return 'Mastercard';
    if (preg_match('/^3[47][0-9]{13}$/', $card_number)) return 'Amex';
    if (preg_match('/^6(?:011|5[0-9]{2}|4[4-9][0-9]|22[1-9][0-9]|622[1-9][0-9]{1,2})[0-9]{12}$/', $card_number)) return 'Discover';
    return 'N/A';
}

// Handle file upload and form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
        $file = $_FILES['import_file']['tmp_name'];
        $data = file_get_contents($file);
    } else {
        $data = $_POST['data'];
    }

    try {
        $section = 'credit_cards';
        $seller_id = $_POST['seller_id'];
        $price = $_POST['price'];
        $pos_card_number = $_POST['pos_card_number'];
        $pos_exp_month = $_POST['pos_exp_month'];
        $pos_exp_year = $_POST['pos_exp_year'];
        $pos_cvv = $_POST['pos_cvv'];
        $pos_name_on_card = $_POST['pos_name_on_card'];
        $pos_address = $_POST['pos_address'];
        $pos_city = $_POST['pos_city'];
        $pos_state = $_POST['pos_state'];
        $pos_zip = $_POST['pos_zip'];
        $pos_country = $_POST['pos_country'];
        $pos_phone_number = $_POST['pos_phone_number'];
        $pos_dob = $_POST['pos_dob'];
        $pos_full_name = $_POST['pos_full_name'];
        $pos_mmn = $_POST['pos_mmn'];
        $pos_account_number = $_POST['pos_account_number'];
        $pos_sort_code = $_POST['pos_sort_code'];
        $pos_cardholder_name = $_POST['pos_cardholder_name'];

        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$seller_id]);
        $seller = $stmt->fetch(PDO::FETCH_ASSOC);
        $seller_name = $seller['username'];

        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            $details = explode('|', $line);
            if (count($details) >= max($pos_card_number, $pos_exp_month, $pos_exp_year, $pos_cvv, $pos_name_on_card, $pos_address, $pos_city, $pos_state, $pos_zip, $pos_country, $pos_phone_number, $pos_dob, $pos_full_name)) {
                $card_number = $pos_card_number ? $details[$pos_card_number - 1] : 'N/A';
                $mm_exp = $pos_exp_month ? $details[$pos_exp_month - 1] : 'N/A';
                $yyyy_exp = $pos_exp_year ? $details[$pos_exp_year - 1] : 'N/A';
                $cvv = $pos_cvv ? $details[$pos_cvv - 1] : 'N/A';
                $name_on_card = $pos_name_on_card ? $details[$pos_name_on_card - 1] : 'N/A';
                $address = $pos_address ? $details[$pos_address - 1] : 'N/A';
                $city = $pos_city ? $details[$pos_city - 1] : 'N/A';
                $state = $pos_state ? $details[$pos_state - 1] : 'N/A';
                $zip = $pos_zip ? $details[$pos_zip - 1] : 'N/A';
                $mmn = $pos_mmn ? $details[$pos_mmn - 1] : 'N/A';
                $account_number = $pos_account_number ? $details[$pos_account_number - 1] : 'N/A';
                $sort_code = $pos_sort_code ? $details[$pos_sort_code - 1] : 'N/A';
                $cardholder_name = $pos_cardholder_name ? $details[$pos_cardholder_name - 1] : 'N/A';
                $country = $pos_country ? strtoupper(trim(preg_replace('/\s+/', ' ', $details[$pos_country - 1]))) : 'N/A';
                $phone_number = $pos_phone_number ? $details[$pos_phone_number - 1] : 'N/A';
                $otherinfo=$_POST['otherinfo'] !== 'No' ?$_POST['otherinfo'] :'NA';
                $card_type = getCardType($card_number);

                if (strlen($phone_number) > 20) $phone_number = substr($phone_number, 0, 20);
                $dob_raw = trim($pos_dob ? $details[$pos_dob - 1] : 'N/A');
                $dob_obj = DateTime::createFromFormat('d/m/Y', $dob_raw) ?: DateTime::createFromFormat('Y-m-d', $dob_raw);
                $dob = $dob_obj ? $dob_obj->format('Y-m-d') : null;
                $full_name = $pos_full_name ? $details[$pos_full_name - 1] : 'N/A';

                $checkQuery = "SELECT card_number FROM credit_cards WHERE card_number = ?";
                $checkStmt = $pdo->prepare($checkQuery);
                $checkStmt->execute([$card_number]);

                if ($checkStmt->rowCount() == 0) {
                    $query = "INSERT INTO $section (mmn,account_number,sort_code,cardholder_name,card_number, mm_exp, yyyy_exp, cvv, name_on_card, address, city, state, zip, country, phone_number, date_of_birth, full_name, seller_id, seller_name, price, section, card_type,otherinfo)
                              VALUES (?,?,?,?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$mmn,$account_number,$sort_code,$cardholder_name,$card_number, $mm_exp, $yyyy_exp, $cvv, $name_on_card, $address, $city, $state, $zip, $country, $phone_number, $dob, $full_name, $seller_id, $seller_name, $price, $section, $card_type,$otherinfo]);
                    $importedCount++;
                } else {
                    $duplicateCount++;
                    $duplicateMessage .= "<p class='duplicate-message'>Card with number $card_number already exists and was ignored.</p>";
                }
            } else {
                $errorMessage = 'Data format incorrect, please check the format and try again.';
            }
        }
        header("Location: import_cards.php?imported=$importedCount&duplicates=$duplicateCount");
        exit;
    } catch (Exception $e) {
        $errorMessage = 'Error importing data: ' . $e->getMessage();
    }
}

if (isset($_GET['imported']) && $_GET['imported'] > 0) {
    $successMessage = $_GET['imported'] . " items were imported successfully.";
}

if (isset($_GET['duplicates']) && $_GET['duplicates'] > 0) {
    $duplicateMessage = $_GET['duplicates'] . " duplicates were detected and ignored.";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data</title>
    <link rel="stylesheet" href="css/importer.css"> 
</head>
<body>
    <div class="container">
        <h2>Import Credit Cards</h2>

        <form action="import_cards.php" method="POST" enctype="multipart/form-data">

           
            <textarea name="data" id="data" placeholder="Enter data based on the format selected"></textarea>
            <div style="display: flex; gap: 30px; justify-content: center; align-items: center; padding: 20px; border: 2px dashed gold; border-radius: 12px; background-color: #fff9e6; box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);">
            <label style="display: flex; align-items: center; position: relative; cursor: pointer; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold;">
                <input type="radio" name="otherinfo" value="Yes" style="display: none;" <?= isset($otherinfo) && $otherinfo == 'Yes' ? 'checked' : '' ?>>
                <span style="padding: 12px 24px; border: 2px solid gold; border-radius: 8px; background: linear-gradient(145deg, #f5f5f5, #ffffff); color: gold; transition: all 0.3s ease; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2); text-transform: uppercase; display: inline-block;">
                    Yes
                </span>
            </label>
            <label style="display: flex; align-items: center; position: relative; cursor: pointer; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold;">
                <input type="radio" name="otherinfo" value="No" style="display: none;" <?= isset($otherinfo) && $otherinfo == 'No' ? 'checked' : '' ?>>
                <span style="padding: 12px 24px; border: 2px solid gold; border-radius: 8px; background: linear-gradient(145deg, #f5f5f5, #ffffff); color: gold; transition: all 0.3s ease; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2); text-transform: uppercase; display: inline-block;">
                    No
                </span>
            </label>
        </div>

            <input type="file" name="import_file" accept=".csv, .txt">

          
            <div class="grid-container">
                <input type="number" name="pos_card_number" placeholder="Card Number Pos" required>
                <input type="number" name="pos_exp_month" placeholder="Exp Month Pos" required>
                <input type="number" name="pos_exp_year" placeholder="Exp Year Pos" required>
                
                <input type="number" name="pos_cvv" placeholder="CVV Pos" required>
                <input type="number" name="pos_name_on_card" placeholder="Name on Card Pos" required>
                <input type="number" name="pos_address" placeholder="Address Pos" required>

                <input type="number" name="pos_city" placeholder="City Pos" required>
                <input type="number" name="pos_state" placeholder="State Pos" required>
                <input type="number" name="pos_zip" placeholder="ZIP Pos" required>
                <input type="number" name="pos_country" placeholder="Country Pos" required>

                <input type="number" name="pos_phone_number" placeholder="Phone Number Pos" required>
                <input type="number" name="pos_dob" placeholder="DOB Pos" required>
                <!-- <input type="number" name="pos_full_name" placeholder="Full Name Pos" required> -->
           
                <input type="number" name="pos_mmn" placeholder="MMN Pos">
                <input type="number" name="pos_account_number" placeholder="Account Number Pos">
                <input type="number" name="pos_sort_code" placeholder="Sort Code Pos">
                <!-- <input type="number" name="pos_cardholder_name" placeholder="Cardholder Name Pos"> -->
            </div>

            <select name="seller_id" id="seller_id" required>
                <option value="">Select Seller</option>
                <?php
       
                if ($sellers) {
                    foreach ($sellers as $seller) {
                        echo "<option value='{$seller['id']}'>{$seller['username']}</option>";
                    }
                } else {
                    echo "<option value=''>No sellers found</option>";
                }
                ?>
            </select>

            <!-- Set the price for the batch -->
            <input type="number" name="price" id="price" step="0.01" min="0" placeholder="Price (USD)" required>

            <button type="submit" class="import-button">Import Cards</button><br><br>
        <a href="panel.php" class="back-button">Back to Selection</a>
        </form>

        <!-- Display success and duplicate messages below the button -->
        <?php if ($successMessage): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if ($duplicateMessage): ?>
            <div class="duplicate-message"><?php echo $duplicateMessage; ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
    </div>
    <script>
    const radios = document.querySelectorAll('input[name="otherinfo"]');
    radios.forEach((radio) => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('span').forEach((span) => {
                span.style.background = 'linear-gradient(145deg, #f5f5f5, #ffffff)';
                span.style.color = 'gold';
                span.style.boxShadow = '0px 4px 6px rgba(0, 0, 0, 0.2)';
            });
            const label = this.nextElementSibling;
            label.style.background = 'gold';
            label.style.color = 'white';
            label.style.boxShadow = '0px 6px 10px rgba(0, 0, 0, 0.4)';
        });
    });
</script>

</body>
</html>
