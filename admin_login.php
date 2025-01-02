<?php
ob_start(); // Start output buffering to prevent header issues
session_start();
require 'config.php'; // Ensure config.php defines $pdo

$error = '';
$login_success = false; // Flag to indicate successful login

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve username and password from POST
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if username and password are set
    if (!empty($username) && !empty($password)) {
        // Prepare a statement to get admin details
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Fetch the admin record
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            // If login is successful, set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id']; // Optional

            $login_success = true; // Set the flag for successful login
        } else {
            // If credentials are invalid, set error message
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}

ob_end_flush(); // End output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style> /* Basic Reset */ * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; } /* Center Container */ body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f0f2f5; padding: 20px; } .login-container { width: 100%; max-width: 400px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1); text-align: center; } h2 { color: #333; margin-bottom: 20px; font-size: 1.8em; } label { font-weight: bold; color: #555; display: block; margin-bottom: 8px; text-align: left; } input[type="text"], input[type="password"] { width: 100%; padding: 12px; font-size: 1em; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 20px; } button { width: 100%; padding: 12px; font-size: 1em; color: #fff; background-color: #4CAF50; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; } button:hover { background-color: #45a049; } .error { color: #ff4444; background-color: #ffe6e6; border: 1px solid #ff4444; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.95em; } /* Small screen adjustments */ @media (max-width: 600px) { h2 { font-size: 1.5em; } .login-container { padding: 20px; } } </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>

    <!-- Display error message if set -->
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>
</div>

<!-- JavaScript for redirection after successful login -->
<?php if ($login_success): ?>
    <script>
        // Redirect to importers.php if login is successful
        window.location.href = 'panel.php';
    </script>
<?php endif; ?>

</body>
</html>
