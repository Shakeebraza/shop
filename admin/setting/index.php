<?php
require_once("../../global.php");
include_once('../../header.php');
if ($_SESSION['role'] != 1) {
   
    echo "<script type='text/javascript'>
            window.location.href = '" . $urlval . "pages/news/index.php';
          </script>";
    exit(); 
}

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

<div class="main-content">
    <div class="page-container">
        <div class="main-content">
            <div class="section__content section__content--p30">
                <div class="container-fluid d-flex justify-content-center" style="min-height: 100vh;">
                    <div class="row" style="width:100%;">
                        <div style="display: flex;justify-content: space-between;margin-bottom: 20px;">
                            <h3>Site Setting</h3>
                        </div>

                        <?php
                      
                        if (isset($_SESSION['message'])) {
                            echo "<p style='color: green;'>" . $_SESSION['message'] . "</p>";
                            unset($_SESSION['message']); 
                        }

                        echo generateSettingsForm($pdo);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php
include_once('../../footer.php');
?>
