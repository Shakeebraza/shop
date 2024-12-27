<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">
    <div id="credit-cards" class="uuper">
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

    </div>
</div>
<?php
include_once('../../footer.php');
?>
<script>
    

</script>