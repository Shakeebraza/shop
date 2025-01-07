<?php
require 'config.php';

$errors = [];
$accountCreated = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $contactOption = $_POST['contact_option'];
    $contactValue = trim($_POST['contact_value']);
    $secretCode = trim($_POST['secret_code']);
    $captcha = $_POST['captcha'];

    session_start();
    if (strlen($username) < 6) {
        $errors[] = "Username must be at least 6 characters.";
    }

    // Validate based on selected contact option
    if ($contactOption === "Jabber") {
        if (!empty($contactValue) && !filter_var($contactValue, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address for Jabber.";
        }
        $jabber = $contactValue;
        $telegram = null;
    } elseif ($contactOption === "Telegram") {
        if (!empty($contactValue) && !preg_match('/^(@?\w+|\+\d{7,15}|\d{7,15})$/', $contactValue)) {
            $errors[] = "Please enter a valid Telegram username or phone number.";
        }
        $jabber = null;
        $telegram = $contactValue;
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
        $errors[] = "Password must be at least 6 characters, contain an uppercase letter, a digit, and a special character.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match. Please enter the same password.";
    }
    if (!preg_match('/^\d{6}$/', $secretCode)) {
        $errors[] = "Secret Code must be exactly 6 digits.";
    }
    if ($captcha !== $_SESSION['captcha_code']) {
        $errors[] = "Invalid CAPTCHA.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, jabber, telegram, secret_code, seller_percentage) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $jabber, $telegram, $secretCode, 0]);
            $accountCreated = true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "The username is already taken. Please choose a different one.";
            } else {
                $errors[] = "Failed to register. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Style for the message popup */
        .popup-message {
            display: none;
            position: absolute;
            background-color: #555;
            color: white;
            padding: 10px;
            border-radius: 5px;
            width: 250px;
            top: 20px;
            left: 10px;
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
            position: relative;
            font-size: 0.9em;
        }

        .help-container {
            position: relative;
            display: inline-block;
        }

        /* Style for dropdown to match input fields */
        select, input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <?php if (!empty($errors)) : ?>
        <div class="error-container">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li class="error-message"><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registerForm">
    <label for="username">Username</label>
    <input type="text" name="username" required>

    <!-- Preferred Contact Option -->
    <label for="contact_option">Preferred Contact Option</label>
    <select name="contact_option" id="contact_option" required>
        <option value="Jabber" selected>Jabber</option>
        <option value="Telegram">Telegram</option>
    </select>

    <input type="text" name="contact_value" id="contact_value" placeholder="Enter your Jabber email" pattern="^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$" required>

    <label for="password">Password</label>
<input type="password" name="password" id="password" required>
<ul id="passwordRules" style="list-style-type: none; padding: 0; display: none; flex-wrap: wrap; gap: 10px;">
    <li id="lengthRule" style="display: inline-block;">At least 8-20 characters</li>
    <li id="specialCharRule" style="display: inline-block;">At least one special character (!@#$%^&*)</li>
    <li id="numberRule" style="display: inline-block;">At least one number</li>
    <li id="letterRule" style="display: inline-block;">At least one letter</li>
</ul>

<label for="confirm_password">Confirm Password</label>
<input type="password" name="confirm_password" id="confirm_password" required>
<p id="confirmMessage" style="color: red; display: none;">Passwords do not match.</p>

    <!-- Secret Code Section -->
    <label for="secret_code">Secret Code
        <span class="help-container">
            <span class="help-link" onclick="showPopup()">What's this?</span>
            <div class="popup-message" id="popupMessage">
                Set your secret code from 6 digits, and keep it secure. You will need this code when you want to change or edit your account profile.
            </div>
        </span>
    </label>
    <input type="text" name="secret_code" pattern="\d{6}" maxlength="6" required>

    <!-- CAPTCHA Section -->
    <div class="captcha-row">
        <label for="captcha">Enter CAPTCHA:</label>
        <img src="captcha.php" alt="CAPTCHA" class="captcha-image">
    </div>
    <input type="text" name="captcha" class="captcha-input" required>

    <input type="submit" value="Register">

    <a href="login.php">Already have an account? Login here</a>
</form>


    <?php if ($accountCreated): ?>
        <div class="modal" id="successModal">
            <div class="modal-content">
                <h3>You successfully created your account!</h3>
                <p>You will be redirected to the login page in 5 seconds...</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="js/catpcha.js"></script>

<script>
    function showPopup() {
        var popup = document.getElementById("popupMessage");
        popup.style.display = (popup.style.display === "none" || popup.style.display === "") ? "block" : "none";
    }

    
    document.addEventListener("click", function(event) {
        var popup = document.getElementById("popupMessage");
        var helpLink = document.querySelector(".help-link");
        if (popup.style.display === "block" && !popup.contains(event.target) && event.target !== helpLink) {
            popup.style.display = "none";
        }
    });

    document.getElementById("contact_option").addEventListener("change", function() {
    const contactValue = document.getElementById("contact_value");
    if (this.value === "Jabber") {
        contactValue.placeholder = "Enter your Jabber email";
        contactValue.pattern = "^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$";
    } else if (this.value === "Telegram") {
        contactValue.placeholder = "Enter Telegram @username or phone number";
        contactValue.pattern = "^(@?\\w+|\\+?\\d{7,15}|\\d{7,15})$";
    }
});

const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const confirmMessage = document.getElementById('confirmMessage');
const passwordRulesContainer = document.getElementById('passwordRules'); // container for rules
const passwordRules = {
    lengthRule: /^.{8,20}$/,
    specialCharRule: /[!@#$%^&*]/,
    numberRule: /\d/,
    letterRule: /[a-zA-Z]/
};

const rulesList = {
    lengthRule: document.getElementById('lengthRule'),
    specialCharRule: document.getElementById('specialCharRule'),
    numberRule: document.getElementById('numberRule'),
    letterRule: document.getElementById('letterRule')
};

// Validate password as user types
passwordInput.addEventListener('input', function () {
    const value = passwordInput.value;

    // Show password rules container when user starts typing
    passwordRulesContainer.style.display = value.length > 0 ? "flex" : "none"; 

    Object.keys(passwordRules).forEach(rule => {
        if (passwordRules[rule].test(value)) {
            rulesList[rule].style.color = 'green';
            rulesList[rule].style.textDecoration = 'line-through';
        } else {
            rulesList[rule].style.color = 'red';
            rulesList[rule].style.textDecoration = 'none';
        }
    });

    // Check confirm password matches
    checkPasswordsMatch();
});

// Validate confirm password
confirmPasswordInput.addEventListener('input', checkPasswordsMatch);

function checkPasswordsMatch() {
    if (passwordInput.value && confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
        confirmMessage.style.display = 'block';
    } else {
        confirmMessage.style.display = 'none';
    }
}

</script>

</body>
</html>
