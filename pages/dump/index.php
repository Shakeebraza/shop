<?php
include_once('../../header.php');
?>
    <!-- Main Content Area -->
    <div class="main-content">
    <div id="dumps" class="">
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

    </div>
</div>
<?php
include_once('../../footer.php');
?>