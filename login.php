<?php
require 'global.php';
session_start();

$csrfToken = $settings->generateCsrfToken();

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
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
        <!-- <button type="button" id="refresh-captcha">Refresh</button> -->
    </div>
    <input type="text" name="captcha" id="captcha" required>

    <button class="sub_btn" type="submit">Login</button>

    <a href="register.php">Don't have an account? Register here</a>
</form>

    </div>

    <script>
        $(document).ready(function () {
            const form = $('#login-form');
            const errorContainer = $('#error-container');

       
            $('#refresh-captcha').click(function () {
                $('#captcha-image').attr('src', 'captcha.php?' + Date.now());
            });

 
            form.submit(function (event) {
                event.preventDefault(); 

                $.ajax({
                    url: '<?= $urlval?>ajax/login.php',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            window.location.href = response.redirect; 
                        } else {
                            errorContainer.empty().show();

                            if (response.remaining_time) {
                                startTimer(response.remaining_time);
                                disableForm();
                            }

                            response.errors.forEach(error => {
                                errorContainer.append('<p>' + error + '</p>');
                            });
                        }
                    },
                    error: function () {
                        errorContainer.html('<p>An error occurred. Please try again.</p>').show();
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
                        errorContainer.hide();
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
        });
    </script>
</body>
</html>

