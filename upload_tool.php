<?php
require 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php?redirect=panel.php");
    exit();
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $section = $_POST['section'];
    $file = $_FILES['file'];

    // Directory to upload files
    $uploadDir = 'uploads/' . strtolower($section) . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadFile = $uploadDir . basename($file['name']);

    // Check if a file with the same name exists in the database for this section
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM uploads WHERE name = ? AND section = ?");
    $stmt->execute([$name, $section]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errors[] = "A {$section} with the name '{$name}' already exists. Please choose a different name.";
        error_log("Admin notification: The name '{$name}' already exists in the '{$section}' section.");
    } elseif (file_exists($uploadFile)) {
        $errors[] = "A file with the name '{$file['name']}' already exists. Please rename your file and try again.";
    } else {
        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            $stmt = $pdo->prepare("INSERT INTO uploads (name, description, file_path, price, section) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $uploadFile, $price, $section]);

            // Redirect to avoid resubmission
            header("Location: upload_tool.php?success=1&file_name=" . urlencode($name));
            exit();
        } else {
            $errors[] = "Failed to upload the file.";
        }
    }
}

// Show success message if redirected with success status
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $fileName = isset($_GET['file_name']) ? htmlspecialchars($_GET['file_name']) : '';
    $successMessage = "{$fileName} uploaded successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Tool</title>
    <link rel="stylesheet" href="css/upload_tool.css">
</head>
<body>

<div class="upload-container">
    <h2>Upload a File</h2>

    <!-- Display error/success messages -->
    <?php if (!empty($errors)): ?>
        <div class="message error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif (!empty($successMessage)): ?>
        <div class="message success-message">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="name">File Name</label>
        <input type="text" name="name" required><br>

        <label for="description">Description</label>
        <textarea name="description" required></textarea><br>

        <label for="price">Price (if applicable)</label>
        <input type="text" name="price" required><br>

        <label for="section">Select Section</label>
        <select name="section" required>
            <option value="Tools">Tools</option>
            <option value="Leads">Leads</option>
            <option value="Pages">Pages</option>
        </select><br><br>
        
        <input type="file" name="file" required><br>
        <button type="submit" class="upload-btn">Upload File</button><br><br>
        <center><a href="panel.php" class="back-button">Back to Selection</a></center>

    </form>
</div>

</body>
</html>
