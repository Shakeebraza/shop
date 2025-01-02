<?php
require 'global.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $captcha = $_POST['captcha'];

  
    if ($captcha !== $_SESSION['captcha_code']) {
        $errors[] = "Invalid CAPTCHA";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {


            if ($user['banned'] == 1) {
                $errors[] = "Your account has been banned. Please contact support.";
            } else {
      
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username '] = $user['username'];
                $_SESSION['role'] = $user['role'];
               
                header("Location: ".$urlval."/pages/news/index.php"); 
                exit();
            }
        } else {
            $errors[] = "Invalid username or password";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <!-- Error container -->
        <?php if (!empty($errors)) : ?>
            <div class="error-container">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li class="error-message"><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Username</label>
            <input type="text" name="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <!-- CAPTCHA Section -->
            <div class="captcha-row">
                <label for="captcha">Enter CAPTCHA:</label>
                <img src="captcha.php" alt="CAPTCHA" class="captcha-image">
            </div>
            <input type="text" name="captcha" class="captcha-input" required>

            <input type="submit" value="Login">

            <a href="register.php">Don't have an account? Register here</a>
        </form>
    </div>

    <script src="js/captcha.js"></script>
</body>

</html>
