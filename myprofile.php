<?php
require 'config.php';

$errors = [];
$successMessage = '';
$logoutAfterUpdate = false;
$jabberUpdated = false;
$telegramUpdated = false;

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];

// Retrieve user's jabber, telegram, and other details
$stmt = $pdo->prepare("SELECT jabber, telegram, password, secret_code FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: login.php");
    exit;   
}

// Initialize newJabberValue and newTelegramValue for immediate field update
$newJabberValue = $user['jabber'];
$newTelegramValue = $user['telegram'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jabber = trim($_POST['jabber']);
    $telegram = trim($_POST['telegram']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $secretCode = trim($_POST['secret_code']);

    if ($user['secret_code'] !== $secretCode) {
        $errors[] = "Secret Code is incorrect";
    } else {
        $updateFields = [];
        $updateValues = [];

        // Validate and update Jabber
        if (!empty($jabber) && !filter_var($jabber, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address for Jabber";
        } elseif ($jabber !== $user['jabber']) {
            $updateFields[] = "jabber = ?";
            $updateValues[] = $jabber;
            $jabberUpdated = true;
            $newJabberValue = $jabber; // Store the new value for JavaScript update
        }

        // Validate and update Telegram
        if (!empty($telegram) && !preg_match('/^(@?\w+|\+\d{7,15}|\d{7,15})$/', $telegram)) {
            $errors[] = "Please enter a valid Telegram username or phone number.";
        } elseif ($telegram !== $user['telegram']) {
            $updateFields[] = "telegram = ?";
            $updateValues[] = $telegram;
            $telegramUpdated = true;
            $newTelegramValue = $telegram; // Store the new value for JavaScript update
        }

        // Determine success message based on updates
        if ($jabberUpdated && $telegramUpdated) {
            $successMessage = "Your Jabber and Telegram have been successfully updated.";
        } elseif ($jabberUpdated) {
            $successMessage = "Your Jabber has been successfully updated.";
        } elseif ($telegramUpdated) {
            $successMessage = "Your Telegram has been successfully updated.";
        }

        // Update Password
        if (!empty($newPassword)) {
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $newPassword)) {
                $errors[] = "New password must be at least 6 characters, contain an uppercase letter, a digit, and a special character";
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = "Passwords do not match";
            }
            if (empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateFields[] = "password = ?";
                $updateValues[] = $hashedPassword;
                $successMessage = "You have successfully changed your password. You will be automatically logged out in 5 seconds.";
                $logoutAfterUpdate = true;
            }
        }

        if (empty($errors) && !empty($updateFields)) {
            $updateValues[] = $userId;
            $updateStmt = $pdo->prepare("UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?");
            $updateStmt->execute($updateValues);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Profile Message Box Styling */
        #profileMessageBox {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            font-size: 15px;
            z-index: 1001;
            width: 300px;
            max-width: 80%;
            text-align: center;
            opacity: 1;
            transition: opacity 0.5s ease;
            box-sizing: border-box;
        }
        #profileMessageBox.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        #profileMessageBox.error ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        #profileOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            pointer-events: all;
        }
        .popup-message {
            display: none;
            position: absolute;
            background-color: #555;
            color: white;
            padding: 10px;
            border-radius: 5px;
            width: 250px;
            top: 30px;
            left: 0;
            z-index: 10;
        }

        .popup-message::after {
            content: "";
            position: absolute;
            top: -5px;
            left: 10px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent #555 transparent;
        }

        .help-link {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
            font-size: 0.9em;
            position: relative;
        }

        .help-container {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>My Profile</h2>

    <?php if (!empty($errors)) : ?>
        <div id="profileOverlay"></div>
        <div id="profileMessageBox" class="error">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($successMessage) : ?>
        <div id="profileOverlay"></div>
        <div id="profileMessageBox"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <form method="POST" action="myprofile.php">
        <label for="jabber">Jabber</label>
        <input type="text" id="jabber" name="jabber" placeholder="" value="<?php echo htmlspecialchars($user['jabber']); ?>">

        <label for="telegram">Telegram</label>
        <input type="text" id="telegram" name="telegram" placeholder="" value="<?php echo htmlspecialchars($user['telegram']); ?>">

        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password">

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password">

        <label for="secret_code">Secret Code
            <span class="help-container">
                <span class="help-link" id="whatsThisLink">What's this?</span>
                <div class="popup-message" id="popupMessage">
                    Please enter your secret code you set when registering the account.
                </div>
            </span>
        </label>
        <input type="text" id="secret_code" placeholder="Required to update your profile" name="secret_code" pattern="\d{6}" maxlength="6" required>

        <input type="submit" value="Update Profile">
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</div>

<script>
    // Immediately update the form fields if they were changed
    const newJabberValue = "<?php echo htmlspecialchars($newJabberValue); ?>";
    const newTelegramValue = "<?php echo htmlspecialchars($newTelegramValue); ?>";

    <?php if ($jabberUpdated): ?>
        document.getElementById("jabber").value = newJabberValue;
    <?php endif; ?>

    <?php if ($telegramUpdated): ?>
        document.getElementById("telegram").value = newTelegramValue;
    <?php endif; ?>

    document.addEventListener("DOMContentLoaded", function () {
        const messageBox = document.getElementById("profileMessageBox");
        const overlay = document.getElementById("profileOverlay");
        const logoutAfterUpdate = <?php echo json_encode($logoutAfterUpdate); ?>;

        if (messageBox) {
            if (logoutAfterUpdate) {
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 5000);
            } else {
                setTimeout(() => {
                    messageBox.style.opacity = '0';
                    overlay.style.opacity = '0';
                    setTimeout(() => {
                        messageBox.style.display = 'none';
                        overlay.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const popupMessage = document.getElementById("popupMessage");
        const whatsThisLink = document.getElementById("whatsThisLink");

        whatsThisLink.addEventListener("click", function(event) {
            event.preventDefault();
            popupMessage.style.display = (popupMessage.style.display === "block") ? "none" : "block";
        });

        document.addEventListener("click", function(event) {
            if (popupMessage.style.display === "block" && !popupMessage.contains(event.target) && event.target !== whatsThisLink) {
                popupMessage.style.display = "none";
            }
        });
    });
</script>

</body>
</html>
