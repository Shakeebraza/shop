<?php
require 'global.php';
session_start();

$csrfToken = $settings->generateCsrfToken();
if (isset($_SESSION['user_id']) && isset($_SESSION['active'])) {
    header("Location: ".$urlval."pages/add-money/index.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="<?= $urlval?>css/popup.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>


<body>
    <div class="container">
        <h2>Login</h2>
        <div id="error-container" class="error-container" style="display: none;"></div>

        <form id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <div class="captcha-row">
                <label for="captcha">Enter CAPTCHA:</label>
                <img src="captcha.php" alt="CAPTCHA" class="captcha-image" id="captcha-image">
                <button type="button" id="refresh-captcha">Refresh</button>
            </div>
            <input type="text" name="captcha" id="captcha" required>

            <button class="sub_btn" type="submit">Login</button>

            <a href="register.php">Don't have an account? Register here</a>
        </form>
        <div id="rules-popup" class="popup-modal">
            <div class=" popup-content" style="position: absolute;top: 50%;right: 20%;">
                <span class="close" onclick="closeRulesPopup()">
                    <i class="fas fa-times"></i>
                </span>
                <p class="message"></p>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const form = $('#login-form');
        const errorContainer = $('#error-container');

        $('#refresh-captcha').click(function() {
            $('#captcha-image').attr('src', 'captcha.php?' + Date.now());
        });

        form.submit(function(event) {
            event.preventDefault();

            $.ajax({
                url: '<?= $urlval ?>ajax/login.php',
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        if (response.active === 0) {
                            showRulesPopup(
                                "<li>Your account is inactive. To activate your account, please top up your balance with at least $20.</li>" +
                                "<li>Attention: Accounts that remain inactive for more than 15 days will be automatically deleted.</li>"
                            );
                            setTimeout(function() {
                                window.location.reload(); // Reload to allow login
                            }, response.delay * 1000);
                        } else {

                            window.location.href = response.redirect;
                        }
                    } else {
                        if (response.active === 0) {
                            showRulesPopup(
                                "<p>Your account is inactive. To activate your account, please top up your balance with at least $20.</p>" +
                                "<p>Attention: Accounts that remain inactive for more than 15 days will be automatically deleted.</p>"
                            );
                            setTimeout(function() {
                                window.location.reload(); // Reload to allow login
                            }, response.delay * 1000);
                        } else {
                            if (response.remaining_time) {
                                startTimer(response.remaining_time);
                                disableForm();
                            }

                            const message = response.errors.join('<br>');
                            showRulesPopup(message);
                        }
                    }
                },
                error: function() {
                    const message = 'An error occurred. Please try again.';
                    showRulesPopup(message);
                }
            });
        });


        function startTimer(duration) {
            let timer = duration;
            const interval = setInterval(() => {
                timer--;
                errorContainer.find('.timer').text(timer + ' seconds');

                if (timer <= 0) {
                    clearInterval(interval);
                    enableForm();
                }
            }, 1000);
        }

        function disableForm() {
            form.find('input, button').prop('disabled', true);
            errorContainer.append('<p class="timer"></p>');
        }

        function enableForm() {
            form.find('input, button').prop('disabled', false);
        }

        function showRulesPopup(message) {
            const popup = document.getElementById('rules-popup');
            const messageContainer = popup.querySelector('.message');
            messageContainer.innerHTML = message;
            popup.style.display = 'block';
        }


    });

    function closeRulesPopup() {
        const popup = document.getElementById('rules-popup');
        popup.style.display = 'none';
    }
    </script>
</body>

</html>