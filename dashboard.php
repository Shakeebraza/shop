

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
