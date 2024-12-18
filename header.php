<?php  
session_start();
require 'config.php';
require 'global.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's information
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, balance, seller, credit_cards_balance, dumps_balance, credit_cards_total_earned, dumps_total_earned, status, seller_percentage FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if the user is banned
if ($user['status'] === 'banned') {
    session_destroy();
    header("Location: login.php?error=You are banned.");
    exit();
}

// Check if there are any unread support tickets for this user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = ? AND user_unread = 1");
$stmt->execute([$user_id]);
$unreadCount = $stmt->fetchColumn() > 0;

// Default visibility (all sections visible)
$defaultVisibility = [
    'Tools' => 1,
    'Leads' => 1,
    'Pages' => 1,
    'My Orders' => 1,
    'Credit Cards' => 1,
    'Dumps' => 1,
    'My Cards' => 1,
    'My Dumps' => 1,
];
// Fetch section visibility from the "sections" table
$stmt = $pdo->query("SELECT section_name, section_view FROM sections");
$sectionsVisibility = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert section visibility to an associative array
$visibility = [];
foreach ($sectionsVisibility as $section) {
    $visibility[$section['section_name']] = (int)$section['section_view'];
}

// Retrieve available countries for dropdowns, eliminating duplicates and ensuring current entries for credit cards
$creditCardCountries = $pdo->query("
    SELECT DISTINCT UPPER(TRIM(REPLACE(REPLACE(country, CHAR(160), ''), CHAR(9), ''))) AS country 
    FROM credit_cards 
    WHERE country IS NOT NULL AND country != '' 
    GROUP BY UPPER(TRIM(REPLACE(REPLACE(country, CHAR(160), ''), CHAR(9), '')))
")->fetchAll(PDO::FETCH_COLUMN);

// Retrieve available countries for dropdowns, eliminating duplicates and ensuring current entries for dumps
$dumpCountries = $pdo->query("
    SELECT DISTINCT UPPER(TRIM(REPLACE(REPLACE(country, CHAR(160), ''), CHAR(9), ''))) AS country 
    FROM dumps 
    WHERE country IS NOT NULL AND country != '' 
    GROUP BY UPPER(TRIM(REPLACE(REPLACE(country, CHAR(160), ''), CHAR(9), '')))
")->fetchAll(PDO::FETCH_COLUMN);

// Capture filter values for credit cards
$ccBin = isset($_POST['cc_bin']) ? trim($_POST['cc_bin']) : '';
$ccCountry = isset($_POST['cc_country']) ? trim($_POST['cc_country']) : '';
$ccState = isset($_POST['cc_state']) ? trim($_POST['cc_state']) : '';
$ccCity = isset($_POST['cc_city']) ? trim($_POST['cc_city']) : '';
$ccZip = isset($_POST['cc_zip']) ? trim($_POST['cc_zip']) : '';
$ccType = isset($_POST['cc_type']) ? trim($_POST['cc_type']) : 'all';
$cardsPerPage = isset($_POST['cards_per_page']) ? (int)$_POST['cards_per_page'] : 10;

// Build SQL query for credit cards based on filters
$sql = "SELECT id, card_type, card_number, mm_exp, yyyy_exp, country, state, city, zip, price 
        FROM credit_cards 
        WHERE buyer_id IS NULL AND status = 'unsold'";
$params = [];

// Handle multiple BINs for credit cards
if (!empty($ccBin)) {
    $bins = array_map('trim', explode(',', $ccBin));
    $sql .= " AND (" . implode(" OR ", array_fill(0, count($bins), "card_number LIKE ?")) . ")";
    foreach ($bins as $bin) {
        $params[] = $bin . '%';
    }
}
if (!empty($ccCountry)) {
    $sql .= " AND UPPER(TRIM(country)) = ?";
    $params[] = strtoupper(trim($ccCountry));
}
if (!empty($ccState)) {
    $sql .= " AND state = ?";
    $params[] = $ccState;
}
if (!empty($ccCity)) {
    $sql .= " AND city = ?";
    $params[] = $ccCity;
}
if (!empty($ccZip)) {
    $sql .= " AND zip = ?";
    $params[] = $ccZip;
}
if ($ccType !== 'all') {
    $sql .= " AND card_type = ?";
    $params[] = $ccType;
}

// Limit and order results for credit cards
$sql .= " ORDER BY id DESC LIMIT " . intval($cardsPerPage);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creditCards = $stmt->fetchAll();

// Fetch sold credit cards for "My Cards" section in descending order
$stmt = $pdo->prepare("
    SELECT * 
    FROM credit_cards 
    WHERE buyer_id = ? 
    AND status = 'sold' 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$soldCards = $stmt->fetchAll();

// Capture filter values for dumps
$dumpBin = isset($_POST['dump_bin']) ? trim($_POST['dump_bin']) : '';
$dumpCountry = isset($_POST['dump_country']) ? trim($_POST['dump_country']) : '';
$dumpType = isset($_POST['dump_type']) ? trim($_POST['dump_type']) : 'all';
$dumpPin = isset($_POST['dump_pin']) ? trim($_POST['dump_pin']) : 'all';
$dumpsPerPage = isset($_POST['dumps_per_page']) ? (int)$_POST['dumps_per_page'] : 10;

// Build SQL query for dumps based on filters
$sql = "SELECT id, track1, track2, monthexp, yearexp, pin, card_type, price, country 
        FROM dumps 
        WHERE buyer_id IS NULL AND status = 'unsold'";
$params = [];

// Handle multiple BINs for dumps
if (!empty($dumpBin)) {
    $bins = array_map('trim', explode(',', $dumpBin));
    $sql .= " AND (" . implode(" OR ", array_fill(0, count($bins), "track2 LIKE ?")) . ")";
    foreach ($bins as $bin) {
        $params[] = $bin . '%';
    }
}
if (!empty($dumpCountry)) {
    $sql .= " AND UPPER(TRIM(country)) = ?";
    $params[] = strtoupper(trim($dumpCountry));
}
if ($dumpType !== 'all') {
    $sql .= " AND card_type = ?";
    $params[] = $dumpType;
}
if ($dumpPin === 'yes') {
    $sql .= " AND pin IS NOT NULL";
} elseif ($dumpPin === 'no') {
    $sql .= " AND pin IS NULL";
}

// Limit and order results for dumps
$sql .= " ORDER BY id DESC LIMIT " . intval($dumpsPerPage);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dumps = $stmt->fetchAll();

// Fetch sold dumps for "My Dumps" section
$stmt = $pdo->prepare("SELECT * FROM dumps WHERE buyer_id = ? AND status = 'sold' ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$soldDumps = $stmt->fetchAll();

// Fetch the user's orders in descending order by purchase date
$stmt = $pdo->prepare("
    SELECT uploads.id AS tool_id, uploads.name, uploads.description, uploads.price, uploads.file_path, orders.created_at 
    FROM orders 
    JOIN uploads ON orders.tool_id = uploads.id 
    WHERE orders.user_id = ? 
    ORDER BY orders.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Additional information for the dashboard
$successMessage = isset($_GET['success']) ? "Purchase successful! The card or dump is now available in the 'My Cards' or 'My Dumps' section." : "";
$newsItems = $pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();
$sections = ['Tools', 'Leads', 'Pages', 'Dumps', 'My Cards'];
$files = [];
foreach ($sections as $section) {
    $stmt = $pdo->prepare("SELECT * FROM uploads WHERE section = ? ORDER BY created_at DESC");
    $stmt->execute([$section]);
    $files[$section] = $stmt->fetchAll();
}

// Stats for seller dashboard
$seller_id = $user_id;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM credit_cards WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$totalCardsUploaded = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM credit_cards WHERE seller_id = ? AND buyer_id IS NULL AND status = 'unsold'");
$stmt->execute([$seller_id]);
$unsoldCards = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM credit_cards WHERE seller_id = ? AND buyer_id IS NOT NULL AND status = 'sold'");
$stmt->execute([$seller_id]);
$soldCardsCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM dumps WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$totalDumpsUploaded = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM dumps WHERE seller_id = ? AND buyer_id IS NULL AND status = 'unsold'");
$stmt->execute([$seller_id]);
$unsoldDumps = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM dumps WHERE seller_id = ? AND buyer_id IS NOT NULL AND status = 'sold'");
$stmt->execute([$seller_id]);
$soldDumpsCount = $stmt->fetchColumn();

// Fetch existing tickets for the user
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

// Check if there are any tickets with unread replies from the admin
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = ? AND user_unread = 1");
$stmt->execute([$user_id]);
$unreadCount = $stmt->fetchColumn();

// Check if `username` is stored in the session
if (!isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $username = $stmt->fetchColumn();
    $_SESSION['username'] = $username;
} else {
    $username = $_SESSION['username'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $urlval?>css/dashboard.css">
    <link rel="stylesheet" href="<?= $urlval?>css/user-info.css">
    <link rel="stylesheet" href="<?= $urlval?>css/dumpbutton.css">
    <link rel="stylesheet" href="<?= $urlval?>css/support.css">
    <link rel="stylesheet" href="<?= $urlval?>css/filter-container-dumps.css">
    <link rel="stylesheet" href="<?= $urlval?>css/filter-container-cards.css">
    <link rel="stylesheet" href="<?= $urlval?>css/credit-card-item.css">
    <link rel="stylesheet" href="<?= $urlval?>css/dump-item.css">
    <link rel="stylesheet" href="<?= $urlval?>css/cc-logo.css">
    <link rel="stylesheet" href="<?= $urlval?>css/cc-message.css">
    <link rel="stylesheet" href="<?= $urlval?>css/dumps-message.css">
    <link rel="stylesheet" href="<?= $urlval?>css/tools-message.css">
    <link rel="stylesheet" href="<?= $urlval?>css/history-cc.css">
    <link rel="stylesheet" href="<?= $urlval?>css/history-dumps.css">
    <!--<script src="js/section-navigation.js" defer></script> -->
     <script src="<?= $urlval?>js/support.js" defer></script>
    <script src="<?= $urlval?>js/clearFilters.js" defer></script>
    <script src="<?= $urlval?>js/copy-button.js" defer></script>
    <!-- <script src="//$urlvaljs/refresh-cards.js" defer></script>
    <script src="//$urlvaljs/refresh-dumps.js" defer></script> -->
    <script src="<?= $urlval?>js/cc-message.js" defer></script>
    <script src="<?= $urlval?>js/dumps-message.js" defer></script>
    <script src="<?= $urlval?>js/tools-message.js" defer></script> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <!-- Message Box -->
    <div id="messageBox" tabindex="-1" style="display: none;">
    <span id="messageText"></span>
</div>

    <!-- Overlay -->
   <div id="overlay" style="display: none;"></div>
   		<!-- Dumps Message Box -->
<div id="dumpsMessageBox" tabindex="-1" style="display: none;">
    <span id="dumpsMessageText"></span>
</div>

<!-- Overlay for Dumps -->
<div id="dumpsOverlay" style="display: none;"></div>
	
	<!-- Tool Message Box -->
<div id="toolMessageBox" tabindex="-1" style="display: none;">
    <span id="toolMessageText"></span>
</div>

<!-- Overlay for Tool Message -->
<div id="toolOverlay" style="display: none;"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Top Navbar (Sticky) -->
<nav class="top-navbar">
    <div class="logo">CardVault</div>
    <div class="user-info-container">
        <!-- User Information with Dropdown -->
        <div class="user-container" id="userDropdownToggle">
            <div class="username-container">
                <span class="username">Logged in as: <?php echo $user['username']; ?></span>
            </div>
            <div class="balance-container">
                <span class="balance">Balance: $<?php echo number_format($user['balance'], 2); ?></span>
            </div>
            <span class="arrow" id="dropdownArrow"><i class="fas fa-chevron-down"></i></span>
            <div class="user-dropdown" id="userDropdownMenu">
                <!-- My Profile Link -->
                <a href="<?= $urlval?>myprofile.php"><i class="fas fa-user"></i> My Profile</a>
                <?php
                // if(isset($_SESSION['role']) && $_SESSION['role'] == 1){
                if(1==0){
                    echo '
                    <a href="'.$urlval.'admin/setting/index.php"><i class="fas fa-user"></i> Admin Setting</a>
                    
                    ';
                }
                ?>
                <a href="<?= $urlval?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>


<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <ul>
            <li><a href="<?= $urlval?>pages/news/index.php" id="news-nav"><i class="fas fa-newspaper"></i> News</a></li>
            <?php if ($visibility['Tools'] === 1): ?>
                <li><a href="<?= $urlval?>pages/tools/index.php" id="tools-nav"><i class="fas fa-wrench"></i> Tools</a></li>
            <?php endif; ?>
            <?php if ($visibility['Leads'] === 1): ?>
                <li><a href="<?= $urlval?>pages/lead/index.php" id="leads-nav"><i class="fas fa-envelope"></i> Leads</a></li>
            <?php endif; ?>
            <?php if ($visibility['Pages'] === 1): ?>
                <li><a href="<?= $urlval?>pages/page/index.php" id="pages-nav"><i class="fas fa-file-alt"></i> Pages</a></li>
            <?php endif; ?>
            <?php if ($visibility['My Orders'] === 1): ?>
                <li><a href="<?= $urlval?>pages/order/index.php" id="my-orders-nav"><i class="fas fa-box"></i> My Orders</a></li>
            <?php endif; ?>
            <?php if ($visibility['Credit Cards'] === 1): ?>
                <li><a href="<?= $urlval?>pages/cards/index.php" id="credit-cards-nav"><i class="far fa-credit-card"></i> Credit Cards</a></li>
            <?php endif; ?>
            <?php if ($visibility['Dumps'] === 1): ?>
                <li><a href="<?= $urlval?>pages/dump/index.php" id="dumps-nav"><i class="far fa-credit-card"></i> Dumps</a></li>
            <?php endif; ?>
            <?php if ($visibility['My Cards'] === 1): ?>
                <li><a href="<?= $urlval?>pages/cards/my-cards.php" id="my-cards-nav"><i class="fas fa-id-card"></i> My Cards</a></li>
            <?php endif; ?>
            <?php if ($visibility['My Dumps'] === 1): ?>
                <li><a href="<?= $urlval?>pages/dump/my-dumps.php" id="my-dumps-nav"><i class="fas fa-id-card"></i> My Dumps</a></li>
            <?php endif; ?>
            <li><a href="<?= $urlval?>pages/add-money/index.php" id="add-money-nav"><i class="fas fa-dollar-sign"></i> Add Money</a></li>
            <li><a href="<?= $urlval?>pages/add-money/rules.php" id="rules-nav"><i class="fas fa-gavel"></i> Rules</a></li>
            <li>
                <a href="<?= $urlval?>pages/support/index.php" id="support-link">
                    <i class="fas fa-life-ring"></i> Support 
                    <?php if ($unreadCount): ?>
                        <span class="notification-dot"></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if ($user['seller'] == 1): ?>
                <li><a href="<?= $urlval?>pages/support/seller-stats.php" id="seller-stats-nav"><i class="fas fa-chart-bar"></i> Seller Stats</a></li>
            <?php endif; ?>
        </ul>
    </nav>


