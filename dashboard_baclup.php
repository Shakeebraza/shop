<?php  
session_start();
require 'config.php';

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
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-info.css">
    <link rel="stylesheet" type="text/css" href="css/dumpbutton.css">
    <link rel="stylesheet" href="css/support.css">
    <link rel="stylesheet" href="css/filter-container-dumps.css">
    <link rel="stylesheet" href="css/filter-container-cards.css">
    <link rel="stylesheet" href="css/credit-card-item.css">
    <link rel="stylesheet" href="css/dump-item.css">
    <link rel="stylesheet" href="css/cc-logo.css">
    <link rel="stylesheet" href="css/cc-message.css">
    <link rel="stylesheet" href="css/dumps-message.css">
    <link rel="stylesheet" href="css/tools-message.css">
    <link rel="stylesheet" href="css/history-cc.css">
    <link rel="stylesheet" href="css/history-dumps.css">
    <script src="js/section-navigation.js" defer></script>
    <script src="js/support.js" defer></script>
    <script src="js/clearFilters.js" defer></script>
    <script src="js/copy-button.js" defer></script>
    <script src="js/refresh-cards.js" defer></script>
    <script src="js/refresh-dumps.js" defer></script>
    <script src="js/cc-message.js" defer></script>
    <script src="js/dumps-message.js" defer></script>
    <script src="js/tools-message.js" defer></script>
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
                <a href="myprofile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>


<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <ul>
            <li><a href="#news" onclick="showSection('news')" id="news-nav"><i class="fas fa-newspaper"></i> News</a></li>
            <?php if ($visibility['Tools'] === 1): ?>
                <li><a href="#tools" onclick="showSection('tools')" id="tools-nav"><i class="fas fa-wrench"></i> Tools</a></li>
            <?php endif; ?>
            <?php if ($visibility['Leads'] === 1): ?>
                <li><a href="#leads" onclick="showSection('leads')" id="leads-nav"><i class="fas fa-envelope"></i> Leads</a></li>
            <?php endif; ?>
            <?php if ($visibility['Pages'] === 1): ?>
                <li><a href="#pages" onclick="showSection('pages')" id="pages-nav"><i class="fas fa-file-alt"></i> Pages</a></li>
            <?php endif; ?>
            <?php if ($visibility['My Orders'] === 1): ?>
                <li><a href="#my-orders" onclick="showSection('my-orders')" id="my-orders-nav"><i class="fas fa-box"></i> My Orders</a></li>
            <?php endif; ?>
            <?php if ($visibility['Credit Cards'] === 1): ?>
                <li><a href="#credit-cards" onclick="showSection('credit-cards')" id="credit-cards-nav"><i class="far fa-credit-card"></i> Credit Cards</a></li>
            <?php endif; ?>
            <?php if ($visibility['Dumps'] === 1): ?>
                <li><a href="#dumps" onclick="showSection('dumps')" id="dumps-nav"><i class="far fa-credit-card"></i> Dumps</a></li>
            <?php endif; ?>
            <?php if ($visibility['My Cards'] === 1): ?>
                <li><a href="#my-cards" onclick="showSection('my-cards')" id="my-cards-nav"><i class="fas fa-id-card"></i> My Cards</a></li>
            <?php endif; ?>
            <?php if ($visibility['My Dumps'] === 1): ?>
                <li><a href="#my-dumps" onclick="showSection('my-dumps')" id="my-dumps-nav"><i class="fas fa-id-card"></i> My Dumps</a></li>
            <?php endif; ?>
            <li><a href="#add-money" onclick="showSection('add-money')" id="add-money-nav"><i class="fas fa-dollar-sign"></i> Add Money</a></li>
            <li><a href="#rules" onclick="showSection('rules')" id="rules-nav"><i class="fas fa-gavel"></i> Rules</a></li>
            <li>
                <a href="#support" onclick="showSection('support')" id="support-link">
                    <i class="fas fa-life-ring"></i> Support 
                    <?php if ($unreadCount): ?>
                        <span class="notification-dot"></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if ($user['seller'] == 1): ?>
                <li><a href="#seller-stats" onclick="showSection('seller-stats')" id="seller-stats-nav"><i class="fas fa-chart-bar"></i> Seller Stats</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content">

        <!-- Display success message if a purchase was successful -->
        <?php if ($successMessage): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- Group 1: News, Tools, Leads, Pages, My Orders -->
        <div id="news" class="section">
            <h2>News Section</h2>
            <?php foreach ($newsItems as $news): ?>
                <div class="news-item">
                    <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($news['content'])); ?></p>
                    <small>Published on: <?php echo $news['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="tools" class="section">
            <h2>Tools Section</h2>
            <?php if (empty($files['Tools'])): ?>
                <p>No files available in the Tools section.</p>
            <?php else: ?>
                <?php foreach ($files['Tools'] as $file): ?>
                    <div class="tool-item">
                        <h3><?php echo htmlspecialchars($file['name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                        <p>Price: $<?php echo number_format($file['price'], 2); ?></p>
                        <a href="buy_tool.php?tool_id=<?php echo $file['id']; ?>&section=tools" onclick="return confirm('Are you sure you want to buy this item?');" style="background-color: #28a745; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; margin-top: 10px; display: inline-block;">Buy</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="leads" class="section">
            <h2>Leads Section</h2>
            <?php if (empty($files['Leads'])): ?>
                <p>No files available in the Leads section.</p>
            <?php else: ?>
                <?php foreach ($files['Leads'] as $file): ?>
                    <div class="tool-item">
                        <h3><?php echo htmlspecialchars($file['name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                        <p>Price: $<?php echo number_format($file['price'], 2); ?></p>
                        <a href="buy_tool.php?tool_id=<?php echo $file['id']; ?>&section=leads" 
                           onclick="return confirm('Are you sure you want to buy this item?');" 
                           style="background-color: #28a745; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;" >Buy </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="pages" class="section">
            <h2>Pages Section</h2>
            <?php if (empty($files['Pages'])): ?>
                <p>No files available in the Pages section.</p>
            <?php else: ?>
                <?php foreach ($files['Pages'] as $file): ?>
                    <div class="tool-item">
                        <h3><?php echo htmlspecialchars($file['name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                        <p>Price: $<?php echo number_format($file['price'], 2); ?></p>
                        <a href="buy_tool.php?tool_id=<?php echo $file['id']; ?>&section=pages" 
                           onclick="return confirm('Are you sure you want to buy this item?');" 
                           style="background-color: #28a745; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;" >Buy </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="my-orders" class="section">
            <h2>My Orders</h2>
            <?php if (empty($orders)): ?>
                <p>You haven't made any purchases yet.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($orders as $order): ?>
                        <div class="tool-item">
                            <h3><?php echo htmlspecialchars($order['name']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($order['description'])); ?></p>
                            <p>Price: $<?php echo number_format($order['price'], 2); ?></p>
                            <a href="download_tool.php?tool_id=<?php echo $order['tool_id']; ?>" class="download-button">Download</a>
                            <a href="delete_order.php?tool_id=<?php echo $order['tool_id']; ?>&section=my-orders" 
                               onclick="return confirm('Are you sure you want to delete this item?');" 
                               class="delete-button">Delete</a>
                        </div>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="section-divider"></div>
<?php
// Check if the user has any open tickets
$hasOpenTicket = false;
foreach ($tickets as $ticket) {
    if ($ticket['status'] === 'open') {
        $hasOpenTicket = true;
        break;
    }
}

?>
<!-- Support Section -->
<div id="support" class="section">
    <h2>Support</h2>

    <!-- Always show the ticket form, but disable if there is an open ticket -->
    <div class="ticket-form">
        <h3>Open a New Ticket</h3>
        <form method="POST" action="submit_ticket.php">
            <textarea name="message" id="ticket-message" placeholder="Describe your issue..." rows="4" maxlength="500" required <?php echo $hasOpenTicket ? 'disabled' : ''; ?>></textarea>
            <small id="ticket-char-count">0/500</small>
            <button type="submit" <?php echo $hasOpenTicket ? 'disabled' : ''; ?> id="submit-ticket-btn">Submit Ticket</button>
        </form>
        <?php if ($hasOpenTicket): ?>
            <p class="disabled-message">Please have an admin close this ticket before opening a new one.</p>
        <?php endif; ?>
    </div>

    <!-- Check if there are tickets available -->
    <?php if (!empty($tickets)): ?>
        <div class="ticket-list">
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item">
                    <div class="ticket-summary" onclick="toggleConversation(<?php echo htmlspecialchars($ticket['id']); ?>)">
                        <span>Ticket #<?php echo htmlspecialchars($ticket['id']); ?> - <?php echo htmlspecialchars($ticket['created_at']); ?></span>
                        <small>Status: <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?></small>
                    </div>

                    <div id="conversation-<?php echo htmlspecialchars($ticket['id']); ?>" class="conversation-details" style="display: none;">
                        <p><?php echo htmlspecialchars($ticket['message']); ?></p>

                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM support_replies WHERE ticket_id = ? ORDER BY created_at ASC");
                        $stmt->execute([$ticket['id']]);
                        $replies = $stmt->fetchAll();

                        $userReplyCount = 0; // Track consecutive user replies
                        foreach ($replies as $reply) {
                            $messageClass = ($reply['sender'] === 'user') ? 'user-message' : 'admin-message';
                            $senderName = ($reply['sender'] === 'user') ? htmlspecialchars($username) : 'Admin';

                            if ($reply['sender'] === 'user') {
                                $userReplyCount++;
                            } else {
                                $userReplyCount = 0; // Reset after admin reply
                            }
                        ?>
                            <div class="<?php echo $messageClass; ?>">
                                <p class="message-tag"><strong><?php echo htmlspecialchars($senderName); ?>:</strong></p>
                                <p><?php echo htmlspecialchars($reply['message']); ?></p>
                                <small><?php echo htmlspecialchars($reply['created_at']); ?></small>
                            </div>
                        <?php } ?>

                        <?php if ($ticket['status'] === 'open' && $userReplyCount < 3): ?>
    <form method="POST" action="submit_reply.php" class="reply-section" onsubmit="submitReply(event, <?php echo htmlspecialchars($ticket['id']); ?>)">
        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
        <textarea name="message" id="reply-message-<?php echo htmlspecialchars($ticket['id']); ?>" placeholder="Reply..." rows="2" maxlength="500" required></textarea>
        <small id="reply-char-count-<?php echo htmlspecialchars($ticket['id']); ?>">0/500</small>
        <button type="submit" id="reply-btn-<?php echo htmlspecialchars($ticket['id']); ?>">Send</button>
    </form>
<?php elseif ($userReplyCount >= 3): ?>
    <p class="disabled-message">The conversation has been closed by the Admin. You may proceed by opening a new ticket if further assistance is required.</p>
<?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="ticket-list">
            <p>No open tickets at the moment.</p>
        </div>
    <?php endif; ?>
</div>

       <!-- Group 2: Credit Cards, Dumps, My Cards, My Dumps -->
<div id="credit-cards" class="section active">
    <h2>Credit Cards Section</h2>

    <!-- Filter Form -->
    <div class="filter-container-cards">
        <form id="credit-card-filters" method="post" action="#credit-cards">
            <label for="credit-card-bin">BIN</label>
            <input type="text" name="cc_bin" id="credit-card-bin" placeholder="Comma-separated for multiple - e.g., 123456, 654321">
            <label for="credit-card-country">Country</label>
            <select name="cc_country" id="credit-card-country">
                <option value="">All</option>
                <?php foreach ($creditCardCountries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>">
                        <?php echo htmlspecialchars($country); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="state">State</label>
            <input type="text" name="cc_state" id="state" placeholder="">
            <label for="city">City</label>
            <input type="text" name="cc_city" id="city" placeholder="">
            <label for="zip">ZIP</label>
            <input type="text" name="cc_zip" id="zip" placeholder="">
            <label for="type">Type</label>
            <select name="cc_type" id="type">
                <option value="all">All</option>
                <option value="visa">Visa</option>
                <option value="mastercard">Mastercard</option>
                <option value="amex">Amex</option>
                <option value="discover">Discover</option>
            </select>
            <label for="cards_per_page">Cards per Page</label>
            <select name="cards_per_page" id="cards_per_page">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </form>
    </div>

    <!-- Credit Card List (will be dynamically updated) -->
    <div id="credit-card-list">
        <?php if (!empty($creditCards)): ?>
            <?php foreach ($creditCards as $card): ?>
                <div class="credit-card-container">
                    <div class="credit-card-info">
                        <div><span class="label">Type:</span> <?php echo htmlspecialchars($card['card_type']); ?></div>
                        <div><span class="label">BIN:</span> <?php echo htmlspecialchars(substr($card['card_number'], 0, 6)); ?></div>
                        <div><span class="label">Exp Date:</span> <?php echo htmlspecialchars($card['mm_exp'] . '/' . $card['yyyy_exp']); ?></div>
                        <div><span class="label">Country:</span> <?php echo htmlspecialchars($card['country']); ?></div>
                        <div><span class="label">State:</span> <?php echo htmlspecialchars($card['state'] ?: 'N/A'); ?></div>
                        <div><span class="label">City:</span> <?php echo htmlspecialchars($card['city'] ?: 'N/A'); ?></div>
                        <div><span class="label">Zip:</span> <?php echo htmlspecialchars(substr($card['zip'], 0, 3)) . '***'; ?></div>
                        <div><span class="label">Price:</span> $<?php echo htmlspecialchars($card['price']); ?></div>
                        <div>
                            <a href="buy_card.php?id=<?php echo htmlspecialchars($card['id']); ?>" 
                               class="buy-button" 
                               onclick="return confirm('Are you sure you want to buy this card?');">Buy</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No credit cards available.</p>
        <?php endif; ?>
    </div>
</div>

<div id="dumps" class="section">
    <h2>Dumps Section</h2>
    <div class="filter-container-dumps">
        <form id="dump-filters" method="post" action="#dumps">
            <label for="dump-bin">BIN</label>
            <input type="text" name="dump_bin" id="dump-bin" placeholder="Comma-separated for multiple - e.g., 123456, 654321">
            <label for="dump-country">Country</label>
            <select name="dump_country" id="dump-country">
                <option value="">All</option>
                <?php foreach ($dumpCountries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>">
                        <?php echo htmlspecialchars($country); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="type">Type</label>
            <select name="dump_type" id="type">
                <option value="all">All</option>
                <option value="visa">Visa</option>
                <option value="mastercard">Mastercard</option>
                <option value="amex">Amex</option>
                <option value="discover">Discover</option>
            </select>
            <label for="pin">PIN</label>
            <select name="dump_pin" id="pin">
                <option value="all">All</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
            <label for="dumps_per_page">Dumps per Page</label>
            <select name="dumps_per_page" id="dumps_per_page">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </form>
    </div>
    
     <!-- Dumps List (this will be dynamically updated) -->
    <div id="dumps-list">
        <?php if (!empty($dumps)): ?>
            <?php foreach ($dumps as $dump): ?>
                <div class="dump-container">
                    <div class="dump-info">
                        <div><span class="label">Type:</span>
                            <img src="images/cards/<?php echo strtolower($dump['card_type']); ?>.png" alt="<?php echo htmlspecialchars($dump['card_type']); ?> logo" class="card-logo">
                        </div>
                        <div><span class="label">BIN:</span> <?php echo htmlspecialchars(substr($dump['track2'], 0, 6)); ?></div>
                        <div><span class="label">Exp Date:</span> <?php echo htmlspecialchars($dump['monthexp'] . '/' . $dump['yearexp']); ?></div>
                        <div><span class="label">PIN:</span> <?php echo !empty($dump['pin']) ? 'Yes' : 'No'; ?></div>
                        <div><span class="label">Country:</span> <?php echo htmlspecialchars($dump['country']); ?></div>
                        <div><span class="label">Price:</span> $<?php echo htmlspecialchars($dump['price']); ?></div>
                        <div>
                            <a href="buy_dump.php?dump_id=<?php echo htmlspecialchars($dump['id']); ?>" 
                               class="buy-button-dump" 
                               onclick="return confirm('Are you sure you want to buy this dump?');">Buy</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No dumps available.</p>
        <?php endif; ?>
    </div>
</div>
 <div id="my-cards" class="section">
    <h2>My Cards Section</h2>
    <?php if (empty($soldCards)): ?>
        <p>No purchased cards available.</p>
    <?php else: ?>
        <?php foreach ($soldCards as $card): ?>
            <div id="card-<?php echo htmlspecialchars($card['id']); ?>" class="credit-card-item">
                <div class="info-field"><strong>Card Number:</strong> <?php echo htmlspecialchars($card['card_number']); ?></div>
                <div class="info-field"><strong>Expiration:</strong> <?php echo htmlspecialchars($card['mm_exp'] . '/' . $card['yyyy_exp']); ?></div>
                <div class="info-field"><strong>CVV:</strong> <?php echo htmlspecialchars($card['cvv']); ?></div>
                <div class="info-field"><strong>Name on Card:</strong> <?php echo htmlspecialchars($card['name_on_card']); ?></div>
                <div class="info-field"><strong>Address:</strong> <?php echo htmlspecialchars($card['address']); ?></div>
                <div class="info-field"><strong>City:</strong> <?php echo htmlspecialchars($card['city']); ?></div>
                <div class="info-field"><strong>ZIP:</strong> <?php echo htmlspecialchars($card['zip']); ?></div>
                <div class="info-field"><strong>Country:</strong> <?php echo htmlspecialchars($card['country']); ?></div>
                <div class="info-field"><strong>Phone Number:</strong> <?php echo htmlspecialchars($card['phone_number']); ?></div>
                <div class="info-field"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($card['date_of_birth']); ?></div>
                <button class="copy-button" onclick="copyCardInfo(<?php echo htmlspecialchars($card['id']); ?>)">Copy</button>
                <button class="check-card-button" onclick="checkCard(<?php echo htmlspecialchars($card['id']); ?>)">Check</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Card Activity Log Section -->
    <div id="card-activity-log">
        <h2>Card Activity Log</h2>
        <table id="activity-log-table" class="activity-log-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Card Number</th>
                    <th>Date Checked</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($checkedHistory)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No activity logged yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($checkedHistory as $history): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['id']); ?></td>
                            <td><?php echo htmlspecialchars($history['card_number']); ?></td>
                            <td><?php echo htmlspecialchars($history['date_checked']); ?></td>
                            <td><?php echo htmlspecialchars($history['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



 <div id="my-dumps" class="section">
    <h2>My Dumps Section</h2>
    <?php if (empty($soldDumps)): ?>
        <p>No purchased dumps available.</p>
    <?php else: ?>
        <?php foreach ($soldDumps as $dump): ?>
            <div id="dump-<?php echo htmlspecialchars($dump['id']); ?>" class="dump-item">
                <div class="info-field"><strong>Track 1:</strong> <?php echo htmlspecialchars($dump['track1']); ?></div>
                <div class="info-field"><strong>Track 2:</strong> <?php echo htmlspecialchars($dump['track2']); ?></div>
                <div class="info-field"><strong>PIN:</strong> <?php echo htmlspecialchars($dump['pin'] ?: 'No'); ?></div>
                <div class="info-field"><strong>Country:</strong> <?php echo htmlspecialchars($dump['country']); ?></div>
                <button class="copy-button" onclick="copyDumpInfo(<?php echo htmlspecialchars($dump['id']); ?>)">Copy</button>
                <button class="check-dump-button" onclick="checkDump(<?php echo htmlspecialchars($dump['id']); ?>)">Check</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Dumps Activity Log Section -->
    <div id="dumps-activity-log">
        <h2>Dumps Activity Log</h2>
        <table id="activity-log-table" class="activity-log-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Card Number</th>
                    <th>Date Checked</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($checkedDumpsHistory)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No activity logged yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($checkedDumpsHistory as $history): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['id']); ?></td>
                            <td><?php echo htmlspecialchars($history['card_number']); ?></td>
                            <td><?php echo htmlspecialchars($history['date_checked']); ?></td>
                            <td><?php echo htmlspecialchars($history['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


    <?php if ($user['seller'] == 1): ?>
    <div id="seller-stats" class="section">
        <h2><i class="fas fa-chart-bar"></i> Seller Stats</h2> <!-- Main title -->

        <!-- Seller Percentage -->
        <div class="stats-container">
            <h3>Seller Percentage</h3>
            <div class="stat-item">Percentage: <strong><?php echo number_format($user['seller_percentage'], 2); ?>%</strong></div>
            <div class="stat-item">Actual Balance: <strong>
                <?php 
                    $totalEarned = $user['credit_cards_balance'] + $user['dumps_balance'];
                    echo '$' . number_format($totalEarned, 2);
                ?>
            </strong></div>
            		<div class="stat-item">Total earned from Credit Cards: <strong>$<?php echo number_format($user['credit_cards_total_earned'], 2); ?></strong></div>
            		<div class="stat-item">Total earned from Dumps <strong>$<?php echo number_format($user['dumps_total_earned'], 2); ?></strong></div>
        </div>

        <!-- Credit Cards Stats -->
        <div class="stats-container">
            <h3>Credit Cards Stats</h3>
            <div class="stat-item">Uploaded Cards: <strong><?php echo $totalCardsUploaded; ?></strong></div>
            <div class="stat-item">Unsold Cards: <strong><?php echo $unsoldCards; ?></strong></div>
            <div class="stat-item">Sold Cards: <strong><?php echo $soldCardsCount; ?></strong></div>
        </div>

        <!-- Dumps Stats -->
        <div class="stats-container">
            <h3>Dumps Stats</h3>
            <div class="stat-item">Uploaded Dumps: <strong><?php echo $totalDumpsUploaded; ?></strong></div>
            <div class="stat-item">Unsold Dumps: <strong><?php echo $unsoldDumps; ?></strong></div>
            <div class="stat-item">Sold Dumps: <strong><?php echo $soldDumpsCount; ?></strong></div>
        </div>
    </div>
<?php endif; ?>

<div class="section-divider"></div>

<!-- Group 3: Add Money and Rules -->
<div id="add-money" class="section">
    <h2>Add Money</h2>
    <form id="add-money-form" action="#">
        <label for="crypto-method">Choose Payment Method:</label>
        <select id="crypto-method" name="crypto-method" required>
            <option value="" disabled selected>Select your payment method</option>
            <option value="btc">Bitcoin (BTC)</option>
        </select>

        <label for="amount">Amount to Recharge (Minimum $5.00 USD):</label>
        <input type="number" id="amount" name="amount" min="5" required placeholder="Enter amount in USD">

        <!-- This section will display the BTC address and amount -->
        <div id="payment-info" style="display: none; margin-top: 20px;">
            <p id="payment-address"></p> <!-- BTC address will appear here -->
        </div>

        <input type="submit" value="Generate Payment Address" style="margin-top: 20px;">
    </form>

    <!-- Transaction History Section (ONLY in Add Money section) -->
    <div id="transaction-history" style="margin-top: 30px;">
        <h3>Transaction History</h3>

        <!-- Table for transaction details -->
        <table id="transaction-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Amount (USD)</th>
                    <th>Amount (BTC)</th>
                    <th>BTC Address</th>
                    <th>TX Hash</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
        <div id="rules" class="section">
            <h2>Rules Section</h2>
            <div class="rules-container">
                <p>Please read and follow the rules to ensure proper usage of the platform.</p>
                <ul>
                    <li>No fraudulent activities.</li>
                    <li>Respect other users.</li>
                    <li>Do not share your account information.</li>
                    <li>Any violation of these rules may result in account suspension or ban.</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        &copy; CardVault 2025
    </div>
</footer>
<script>
        document.getElementById('add-money-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const amountInput = document.getElementById('amount');
        const paymentInfo = document.getElementById('payment-info');
        const paymentAddress = document.getElementById('payment-address');
        const cryptoMethod = document.getElementById('crypto-method').value;

        if (!cryptoMethod) {
            alert('Please select a payment method.');
            return;
        }

        const usdAmount = parseFloat(amountInput.value);
        if (usdAmount < 5) {
            alert('Minimum amount to recharge is $5.');
            return;
        }

        try {
            const rateResponse = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
            const rateData = await rateResponse.json();
            const btcRate = rateData.bitcoin.usd;
            const margin = 0.02;
            const btcAmount = (usdAmount / btcRate) * (1 + margin);
            const requestResponse = await fetch('ajax/generate-payment-request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: btcAmount, memo: `Recharge for $${usdAmount} USD` })
            });
            const requestData = await requestResponse.json();

            if (requestData.success) {
                paymentInfo.style.display = 'block';
                paymentAddress.innerHTML = `
                    <strong>Send BTC to this Address:</strong> ${requestData.btcAddress} <br>
                    <strong>Amount to Send:</strong> ${btcAmount.toFixed(8)} BTC
                `;
            } else {
                alert('Error generating payment request. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Unable to process your request. Please try again later.');
        }
    });
</script>
</body>
</html>
