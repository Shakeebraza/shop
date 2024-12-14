<?php
require_once("../../global.php");

// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php?redirect=panel.php");
//     exit();
// }

function generateSettingsForm($pdo) {
    $query = "SELECT * FROM site_settings ORDER BY id ASC LIMIT 100";
    $stmt = $pdo->query($query);
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formHtml = '<form method="POST" action="" enctype="multipart/form-data" style="max-width: 100%; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;">';

    foreach ($settings as $setting) {
        $key = htmlspecialchars($setting['key']);
        $value = htmlspecialchars($setting['value']);  
        $inputType = htmlspecialchars($setting['input_type']);
        
        $label = ucwords(str_replace('_', ' ', $key));

        $formHtml .= "<div style='margin-bottom: 15px;'>";
        $formHtml .= "<label for='{$key}' style='display: block; margin-bottom: 5px; font-weight: bold; color: #333;'>{$label}</label>";

        switch ($inputType) {
            case 'text':
                $formHtml .= "<input type='text' id='{$key}' name='{$key}' value='{$value}' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
                break;

            case 'url':
                $formHtml .= "<input type='url' id='{$key}' name='{$key}' value='{$value}' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
                break;

            case 'image':
                $formHtml .= "<input type='file' id='{$key}' name='{$key}' style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
                if (!empty($value)) {
                    $formHtml .= "<img src='{$value}' alt='{$key}' style='width: 100px; height: auto; margin-top: 5px;'><br>";
                }
                $formHtml .= "<small style='color: #555;'>Upload a new image if needed.</small>";
                break;

            default:
                $formHtml .= "<input type='text' id='{$key}' name='{$key}' value='{$value}' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
                break;
        }

        $formHtml .= "</div>";
    }


    $formHtml .= '<div>
                    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Save Settings</button>
                  </div>';
    $formHtml .= '</form>';

    return $formHtml;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = $_POST;
    try {
        foreach ($settings as $key => $value) {
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/';
                $uploadFile = $uploadDir . basename($_FILES[$key]['name']);
                if (move_uploaded_file($_FILES[$key]['tmp_name'], $uploadFile)) {
                    $value = $uploadFile; 
                }
            }

            $stmt = $pdo->prepare("UPDATE site_settings SET value = :value WHERE `key` = :key");
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
        }

        $_SESSION['message'] = "Settings saved successfully!";
    } catch (Exception $e) {

        $_SESSION['message'] = "Error saving settings: " . $e->getMessage();
    }


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; 
            color: #333;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        h3 {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
        }

        .page-container {
            padding: 30px 20px;
        }

        .settings-container {
            background-color: #fff; 
            border: 1px solid #ddd; 
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        }

        .form-control {
            border: 1px solid #ced4da; 
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
        }

        .btn-green {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-green:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #6c757d; /* Gray color for back button */
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .message {
            font-size: 16px;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="settings-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Site Settings</h3>
                            <button class="btn-back" onclick="history.back()">Back</button>
                        </div>

                        <?php
                        if (isset($_SESSION['message'])) {
                            echo "<p class='message'>" . $_SESSION['message'] . "</p>";
                            unset($_SESSION['message']); 
                        }

                        echo generateSettingsForm($pdo);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


