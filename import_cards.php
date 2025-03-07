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
    $card_number = preg_replace('/\D/', '', $card_number); // Remove non-numeric characters

    $patterns = [
        'Visa' => '/^4\d{12,18}$/',  // 13, 16, or 19 digits
        'MasterCard' => '/^(5[1-5]\d{14}|222[1-9]\d{12}|22[3-9]\d{13}|2[3-6]\d{14}|27[01]\d{13}|2720\d{12})$/',  // 16 digits
        'American Express' => '/^3[47]\d{13}$/',  // 15 digits
        'Discover' => '/^(6011\d{12}|65\d{14}|64[4-9]\d{13}|6221[2-9]\d{10}|622[2-8]\d{10}|6229[01]\d{10}|62292[0-5]\d{10})$/',  // 16 digits
        'JCB' => '/^35(2[89]|[3-8]\d)\d{12}$/',  // 16 digits (3528-3589)
        'Diners Club' => '/^(3[0689]\d{12}|30[0-5]\d{11})$/',  // 14 digits
        'UnionPay' => '/^62\d{14,18}$/',  // 16-19 digits
        'Maestro' => '/^(50|56|57|58|59|6[0-9])\d{10,16}$/'  // 12-19 digits
    ];

    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $card_number)) {
            return $type;
        }
    }

    return 'N/A'; // Not a recognized card type
}


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
        $base_name = $_POST['base_name'];
        $otherinfo = $_POST['otherinfo'];
        $pos_mmn = $_POST['pos_mmn'];
        $pos_account_number = $_POST['pos_account_number'];
        $email = $_POST['email_address'];
        $sinssn = $_POST['sinss'];
        $pin = $_POST['pin'];
        $driverslicense = $_POST['driverslicense'];
   
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
                $base_name_pos = $base_name ? $details[$base_name - 1] : 'N/A';
                $pos_mmn_pos = $pos_mmn ? $details[$pos_mmn - 1] : 'N/A';
                $pos_account_number_pos = $pos_account_number ? $details[$pos_account_number - 1] : 'N/A';
                $state = $pos_state ? $details[$pos_state - 1] : 'N/A';
                $zip = $pos_zip ? $details[$pos_zip - 1] : 'N/A';
                $email_pos = $email ? $details[$email - 1] : 'N/A';
                $sinssn_pos = $sinssn ? $details[$sinssn - 1] : 'N/A';
                $pin_pos = $pin ? $details[$pin - 1] : 'N/A';
                $driverslicense_pos = $driverslicense ? $details[$driverslicense - 1] : 'N/A';
                $country = $pos_country ? strtoupper(trim(preg_replace('/\s+/', ' ', $details[$pos_country - 1]))) : 'N/A';
                $phone_number = $pos_phone_number ? $details[$pos_phone_number - 1] : 'N/A';
                $card_type = getCardType($card_number);
              

                if (strlen($phone_number) > 20) $phone_number = substr($phone_number, 0, 20);
                $dob_raw = trim($pos_dob ? $details[$pos_dob - 1] : 'N/A');
                $dob_obj = DateTime::createFromFormat('d/m/Y', $dob_raw) ?: DateTime::createFromFormat('Y-m-d', $dob_raw);
                $dob = $dob_obj ? $dob_obj->format('Y-m-d') : null;
                $full_name = $pos_full_name ? $details[$pos_full_name - 1] : 'N/A';

                    $checkQuery = "SELECT card_number, mm_exp, yyyy_exp FROM credit_cards WHERE card_number = ? AND mm_exp = ? AND yyyy_exp = ?";
                    $checkStmt = $pdo->prepare($checkQuery);
                    $checkStmt->execute([$card_number, $mm_exp, $yyyy_exp]);

                    if ($checkStmt->rowCount() == 0) {
                        $query = "INSERT INTO $section (card_number, mm_exp, yyyy_exp, cvv, name_on_card, address, city, state, zip, country, phone_number, date_of_birth, full_name, seller_id, seller_name, price, section, card_type, base_name, otherinfo, mmn, account_number, email, sinssn, pin, drivers)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute([$card_number, $mm_exp, $yyyy_exp, $cvv, $name_on_card, $address, $city, $state, $zip, $country, $phone_number, $dob, $full_name, $seller_id, $seller_name, $price, $section, $card_type,$base_name_pos,$otherinfo,$pos_mmn_pos,$pos_account_number_pos,$email_pos,$sinssn_pos,$pin_pos,$driverslicense_pos]);
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
    <div class="container"
        style="position: relative; max-height: 80vh; overflow-y: auto; border: 1px solid #ccc; padding: 20px; border-radius: 10px;">
        <h2>Import Credit Cards</h2>

        <form action="import_cards.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">


            <textarea name="data" id="data" placeholder="Enter data based on the format selected"></textarea>
            <div
                style="display: flex; gap: 30px; justify-content: center; align-items: center; padding: 20px; border: 2px dashed gold; border-radius: 12px; background-color: #fff9e6; box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);">
                <label
                    style="display: flex; align-items: center; position: relative; cursor: pointer; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold;">
                    <input type="radio" name="otherinfo" value="Yes" style="display: none;"
                        <?= (isset($otherinfo) && $otherinfo == 'Yes') || !isset($otherinfo) ? 'checked' : '' ?>>
                    <span
                        style="padding: 12px 24px; border: 2px solid gold; border-radius: 8px; background: gold; color: white; transition: 0.3s; box-shadow: rgba(0, 0, 0, 0.4) 0px 6px 10px; text-transform: uppercase; display: inline-block;">
                        Yes
                    </span>
                </label>
                <label
                    style="display: flex; align-items: center; position: relative; cursor: pointer; font-family: Arial, sans-serif; font-size: 16px; font-weight: bold;">
                    <input type="radio" name="otherinfo" value="No" style="display: none;"
                        <?= isset($otherinfo) && $otherinfo == 'No' ? 'checked' : '' ?>>
                    <span
                        style="padding: 12px 24px; border: 2px solid gold; border-radius: 8px; background: linear-gradient(145deg, #f5f5f5, #ffffff); color: gold; transition: all 0.3s ease; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2); text-transform: uppercase; display: inline-block;">
                        No
                    </span>
                </label>
            </div>


            <!-- Display error message -->
            <p id="error-message" style="color: red; text-align: center; font-weight: bold; display: none;">
                Please select "Yes" or "No" before submitting.
            </p>

            <input type="file" name="import_file" accept=".csv, .txt">


            <div class="grid-container">
                <input type="number" name="pos_card_number" placeholder="Card Number Pos" required>

                <input type="number" name="pos_exp_month" placeholder="Exp Month Pos">
                <input type="number" name="pos_exp_year" placeholder="Exp Year Pos">
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
                <input type="number" name="base_name" placeholder="Base Pose">
                <input type="number" name="email_address" placeholder="Email Address">
                <input type="number" name="driverslicense" placeholder="Drivers License">
                <input type="number" name="sinss" placeholder="SIN/SSN">
                <input type="number" name="pin" placeholder="pin">
                <!-- <label
                    style="font-size: 18px; font-weight: bold; display: flex; align-items: center; margin-bottom: 15px; color: #333;">
                    <input type="checkbox" id="use_mm_yyyy" name="use_mm_yyyy" value="1"
                        style="margin-right: 10px; cursor: pointer;">
                    <span style="color: #555;">Expiration Date Format: mm/yyyy</span>
                </label>

                <div id="mapping-fields" style="display: flex; flex-direction: column; margin-bottom: 20px;">
                    <input type="number" name="pos_exp_month" placeholder="Exp Month Pos"
                        style="width: 100%; padding: 12px; margin-bottom: 12px; border: 2px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s;">
                    <input type="number" name="pos_exp_year" placeholder="Exp Year Pos"
                        style="width: 100%; padding: 12px; margin-bottom: 12px; border: 2px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s;">
                </div>

                <div id="mm-yyyy-field" style="display: none; flex-direction: column; margin-bottom: 20px;">
                    <input type="text" name="pos_exp_mm_yyyy" placeholder="Exp Date (mm/yyyy) Pos"
                        style="width: 100%; padding: 12px; margin-bottom: 12px; border: 2px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s;">
                </div> -->


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
        radio.addEventListener('change', function() {
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

    document.getElementById('use_mm_yyyy').addEventListener('change', function() {
        const isChecked = this.checked;
        const mappingFields = document.getElementById('mapping-fields');
        const mmYyyyField = document.getElementById('mm-yyyy-field');


        mappingFields.style.display = isChecked ? 'none' : 'block';
        mmYyyyField.style.display = isChecked ? 'block' : 'none';

        const useMmYyyyField = document.querySelector('input[name="pos_exp_mm_yyyy"]');
        if (isChecked) {
            useMmYyyyField.setAttribute('required', true);
            useMmYyyyField.closest('div').style.display = 'block';
        } else {
            useMmYyyyField.removeAttribute('required');
            useMmYyyyField.closest('div').style.display = 'none';
        }
    });


    function validateForm() {
        const otherinfo = document.querySelector('input[name="otherinfo"]:checked');
        const errorMessage = document.getElementById('error-message');

        if (!otherinfo) {
            errorMessage.style.display = 'block';
            return false;
        }

        errorMessage.style.display = 'none';
        return true;
    }
    </script>

</body>

</html>