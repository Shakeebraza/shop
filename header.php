<?php  
session_start();
require 'config.php';
require 'global.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: {$urlval}login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, balance, seller, credit_cards_balance, dumps_balance, credit_cards_total_earned, dumps_total_earned, status, seller_percentage FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();


if ($user['status'] === 'banned') {
    session_destroy();
    header("Location: login.php?error=You are banned.");
    exit();
}


$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = ? AND user_unread = 1");
$stmt->execute([$user_id]);
$unreadCount = $stmt->fetchColumn() > 0;


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

$stmt = $pdo->query("SELECT section_name, section_view FROM sections");
$sectionsVisibility = $stmt->fetchAll(PDO::FETCH_ASSOC);


$visibility = [];
foreach ($sectionsVisibility as $section) {
    $visibility[$section['section_name']] = (int)$section['section_view'];
}


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


$ccBin = isset($_POST['cc_bin']) ? trim($_POST['cc_bin']) : '';
$ccCountry = isset($_POST['cc_country']) ? trim($_POST['cc_country']) : '';
$ccState = isset($_POST['cc_state']) ? trim($_POST['cc_state']) : '';
$ccCity = isset($_POST['cc_city']) ? trim($_POST['cc_city']) : '';
$ccZip = isset($_POST['cc_zip']) ? trim($_POST['cc_zip']) : '';
$ccType = isset($_POST['cc_type']) ? trim($_POST['cc_type']) : 'all';
$cardsPerPage = isset($_POST['cards_per_page']) ? (int)$_POST['cards_per_page'] : 10;


$sql = "SELECT id, card_type,name_on_card, card_number, mm_exp, yyyy_exp, country, state, city, zip, price 
        FROM credit_cards 
        WHERE buyer_id IS NULL AND status = 'unsold'";
$params = [];


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


$sql .= " ORDER BY id DESC LIMIT " . intval($cardsPerPage);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creditCards = $stmt->fetchAll();


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

if (!isset($_SESSION['cards'])) {
    $_SESSION['cards'] = [];
}
if (!isset($_SESSION['dumps'])) {
    $_SESSION['dumps'] = [];
}


$cartItemCount = count($_SESSION['cards'] ?? 0) + count($_SESSION['dumps'] ?? 0 ) ?? 0;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@300;400;500&display=swap"
        rel="stylesheet">
    <?php
    $condition = false;  // Replace this with your actual condition
    if ($condition):
?>
    <link rel="stylesheet" href="<?= $urlval?>css/dashboard.css">
    <?php 
else:
    echo '<link rel="stylesheet" href="'.$urlval.'css/new.css">'; 
endif;
?>
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
    <link rel="stylesheet" href="<?= $urlval?>css/popup.css">

    <!--<script src="js/section-navigation.js" defer></script> -->
    <script src="<?= $urlval?>js/support.js" defer></script>
    <script src="<?= $urlval?>js/clearFilters.js" defer></script>
    <script src="<?= $urlval?>js/copy-button.js" defer></script>
    <!-- <script src="//$urlvaljs/refresh-cards.js" defer></script>
    <script src="//$urlvaljs/refresh-dumps.js" defer></script> -->
    <script src="<?= $urlval?>js/cc-message.js" defer></script>
    <script src="<?= $urlval?>js/dumps-message.js" defer></script>
    <script src="<?= $urlval?>js/tools-message.js" defer></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Bootstrap Icons (optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
    #particles-js {
        position: fixed;
        top: 0;
        left: 14%;
        width: 100%;
        height: 100%;
        z-index: 0;
        background-color: transparent;
        /* pointer-events: none;  */
    }

    .see-all {
        display: none;
    }

    @media (min-width: 300px) and (max-width: 767.98px) {
        .dashboard-container {
            display: block !important;
            overflow: hidden !important;
        }

        .sidebar {
            width: 100% !important;
            overflow: hidden !important;
        }

        .sidebar ul {
            display: flex;
            overflow-x: scroll !important;
            scroll-behavior: smooth;
        }

        .sidebar ul li a {
            text-wrap: nowrap !important;
            padding: 0px 15px !important;
        }

        .balance-container .balance,
        .username-container .username {
            font-size: 12px !important;
        }

        .see-all {
            display: block;
        }
    }

    .cart-icon {
        font-size: 24px;
        cursor: pointer;
        position: relative;
    }

    .cart-badge {
        position: absolute;
        top: -5px;
        right: -10px;
        background-color: #dc3545;
        color: #ffffff;
        font-size: 12px;
        border-radius: 50%;
        padding: 2px 6px;
    }

    /* Sidebar Styles */
    .cart-sidebar {
        position: fixed;
        top: 0;
        right: -300px;
        /* Initially hidden off-screen */
        width: 300px;
        height: 100%;
        background-color: #0c182f;
        color: #ffffff;
        padding: 20px;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
        transition: right 0.3s ease;
        /* Smooth transition */
        overflow-y: auto;
        z-index: 1000;
    }

    .cart-sidebar.open {
        right: 0;
        /* Show sidebar */
    }

    .cart-sidebar h2 {
        margin: 0 0 20px;
        font-size: 18px;
        border-bottom: 1px solid #444;
        padding-bottom: 10px;
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .cart-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }

    .cart-item-details {
        flex-grow: 1;
        margin-left: 10px;
    }

    .cart-item-details h4 {
        margin: 0;
        font-size: 14px;
    }

    .cart-item-details p {
        margin: 0;
        font-size: 12px;
        color: #ccc;
    }

    .close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 18px;
        cursor: pointer;
        color: #ffffff;
    }

    .checkout-btn {
        display: block;
        width: 100%;
        background-color: #6c5ce7;
        color: #ffffff;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 15px;
        margin-top: 20px;
    }

    .checkout-btn:hover {
        background-color: #5a4ebbbf;
    }

    .empty-cart-btn {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50%;
        background-color: rgb(255, 5, 5);
        color: #ffffff;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        margin-top: 20px;
        position: relative;
    }

    .empty-cart-btn i {
        margin-right: 8px;
        font-size: 18px;
        transition: opacity 0.3s ease;
    }

    .empty-cart-btn .btn-text {
        display: none;
        margin-left: 8px;
        transition: opacity 0.3s ease;
    }

    .empty-cart-btn:hover .btn-text {
        display: inline;
        left: 3px;
        position: absolute;
    }

    .empty-cart-btn:hover i {
        opacity: 0;
    }

    .empty-cart-btn:hover {
        background-color: rgba(182, 27, 27, 0.75);
    }




    .user-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    </style>

</head>
<div id="particles-js"></div>

<body>


    <div id="messageBox" tabindex="-1" style="display: none;">
        <span id="messageText"></span>
    </div>


    <div id="overlay" style="display: none;"></div>

    <div id="dumpsMessageBox" tabindex="-1" style="display: none;">
        <span id="dumpsMessageText"></span>
    </div>


    <div id="dumpsOverlay" style="display: none;"></div>


    <div id="toolMessageBox" tabindex="-1" style="display: none;">
        <span id="toolMessageText"></span>
    </div>


    <div id="toolOverlay" style="display: none;"></div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>

    <body>

        <!-- Top Navbar (Sticky) -->
        <nav class="top-navbar">
            <div class="logo">CardVault</div>
            <div class="user-info-container">

                <div class="user-container">
                    <div class="username-container">
                        <span class="username">Logged in as: <?php echo $user['username']; ?></span>
                    </div>
                    <div class="balance-container">
                        <span class="balance">Balance: $<?php echo number_format($user['balance'], 2); ?></span>
                    </div>
                    <div class="user-actions">
                        <div class="cart-icon" id="cartIcon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-badge" id="cartBadge"><?= $cartItemCount ?></span>
                        </div>
                        <div id="userDropdownToggle">
                            <span class="arrow" id="dropdownArrow"><i class="fa-solid fa-bars"></i></span>
                        </div>
                    </div>
                    <div class="user-dropdown" id="userDropdownMenu"
                        style="display: none;margin-top:15px; background-color: #0c182f; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); width: 220px; padding: 15px; font-family: Arial, sans-serif; color: #ffffff;">


                        <div
                            style="border-bottom: 1px solid #444; margin-bottom: 10px; padding-bottom: 10px; text-align: center;">
                            <p style="margin: 0; font-size: 16px; font-weight: bold; color: #0dcaf0;">
                                <?php echo $user['username']; ?></p>
                            <p style="margin: 0; font-size: 14px; color: #ffc107;">Balance:
                                $<?php echo number_format($user['balance'], 2); ?></p>
                        </div>



                        <a href="<?= $urlval ?>myprofile.php"
                            style="text-decoration: none; color: #ffffff; display: block; padding: 8px 0; font-size: 14px; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#0dcaf0'; this.style.color='#0c182f'; this.style.paddingLeft='12px';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='#ffffff'; this.style.paddingLeft='0';">
                            <i class="fas fa-user" style="margin-right: 10px; color: #0dcaf0;"></i> My Profile
                        </a>


                        <?php if (1 == 0): ?>
                        <a href="<?= $urlval ?>admin/setting/index.php"
                            style="text-decoration: none; color: #ffffff; display: block; padding: 8px 0; font-size: 14px; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#ffc107'; this.style.color='#0c182f'; this.style.paddingLeft='12px';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='#ffffff'; this.style.paddingLeft='0';">
                            <i class="fas fa-user-cog" style="margin-right: 10px; color: #ffc107;"></i> Admin Setting
                        </a>
                        <?php endif; ?>



                        <a href="<?= $urlval ?>logout.php"
                            style="text-decoration: none; color: #ffffff; display: block; padding: 8px 0; font-size: 14px; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#dc3545'; this.style.color='#ffffff'; this.style.paddingLeft='12px';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='#ffffff'; this.style.paddingLeft='0';">
                            <i class="fas fa-sign-out-alt" style="margin-right: 10px; color: #dc3545;"></i> Logout
                        </a>

                    </div>

                </div>
            </div>
        </nav>


        <div class="dashboard-container">

            <nav class="sidebar uuper">
                <ul class="sdbr-ct32">
                    <li><a href="<?= $urlval?>pages/news/index.php" id="news-nav"><i class="fas fa-newspaper"></i>
                            News</a></li>
                    <?php if ($visibility['Tools'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/tools/index.php" id="tools-nav"><i class="fas fa-wrench"></i>
                            Tools</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['Leads'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/lead/index.php" id="leads-nav"><i class="fas fa-envelope"></i>
                            Leads</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['Pages'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/page/index.php" id="pages-nav"><i class="fas fa-file-alt"></i>
                            Pages</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['My Orders'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/order/index.php" id="my-orders-nav"><i class="fas fa-box"></i> My
                            Orders</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['Credit Cards'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/cards/index.php" id="credit-cards-nav"><i
                                class="far fa-credit-card"></i> Credit Cards</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['Dumps'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/dump/index.php" id="dumps-nav"><i class="far fa-credit-card"></i>
                            Dumps</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['My Cards'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/cards/my-cards.php" id="my-cards-nav"><i class="fas fa-id-card"></i>
                            My Cards</a></li>
                    <?php endif; ?>
                    <?php if ($visibility['My Dumps'] === 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/dump/my-dumps.php" id="my-dumps-nav"><i class="fas fa-id-card"></i>
                            My Dumps</a></li>
                    <?php endif; ?>
                    <li><a href="<?= $urlval?>pages/add-money/index.php" id="add-money-nav"><i
                                class="fas fa-dollar-sign"></i> Add Money</a></li>
                    <?php if ($_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/add-money/rules.php" id="rules-nav"><i class="fas fa-gavel"></i>
                            Rules</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['active'] == 1): ?>
                    <li>
                        <a href="<?= $urlval?>pages/support/index.php" id="support-link">
                            <i class="fas fa-life-ring"></i> Support
                            <?php if ($unreadCount != 0): ?>
                            <span class="notification-dot"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user['seller'] == 1 && $_SESSION['active'] == 1): ?>
                    <li><a href="<?= $urlval?>pages/support/seller-stats.php" id="seller-stats-nav"><i
                                class="fas fa-chart-bar"></i> Seller Stats</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex justify-content-center">
                    <a href="" class="see-all" style="color:#fff;">See all</a>
                </div>
            </nav>


            <div class="cart-sidebar" id="cartSidebar">
                <span class="close-btn close" id="closeSidebar" style="top:7px !important;">&times;</span>
                <h2>Add to Cart</h2>

                <div id="cartSections">
                    <!-- Section for Cards -->
                    <div id="cardsSection">
                        <h3>Cards</h3>
                        <div id="cartCards"></div> <!-- Container for card items -->
                    </div>

                    <!-- Section for Dumps -->
                    <div id="dumpsSection" style="margin-top: 20px;">
                        <h3>Dumps</h3>
                        <div id="cartDumps"></div> <!-- Container for dump items -->
                    </div>
                </div>

                <div style="margin-top: 20px; font-weight: bold;">
                    <p>Total: $<span id="cartTotal">0.00</span></p>
                </div>

                <div class="user-actions">
                    <a href="#" class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</a>
                    <a href="#" class="empty-cart-btn" onclick="removeAllFromCart()">
                        <i class="fa-regular fa-file"></i>
                        <span class="btn-text">Empty Cart</span>
                    </a>
                </div>
            </div>


            <script>
            document.querySelector('.see-all').addEventListener('click', function(event) {
                const hiddenContent = document.querySelector('.sdbr-ct32');
                const button = this;

                event.preventDefault();


                const currentDisplay = window.getComputedStyle(hiddenContent).display;

                if (currentDisplay === 'flex') {

                    hiddenContent.style.display = 'block';
                    button.textContent = 'Hide All';
                } else {

                    hiddenContent.style.display = 'flex';
                    button.textContent = 'See All';
                }
            });
            </script>