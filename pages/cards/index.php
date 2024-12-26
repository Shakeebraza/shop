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


    <!-- <div id="credit-card-list">
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
    </div> -->
<?php
// var_dump($creditCards);
?>
    <div style="display: flex; gap: 20px; padding: 20px; flex-wrap: wrap; justify-content: center;">
    <?php if (!empty($creditCards)): ?>
    <?php foreach ($creditCards as $card): ?>
        <div style="width: 420px; height: 265px; background: linear-gradient(45deg, #000000, #1a1a1a); border-radius: 15px; padding: 25px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
            <h1 style="color: white; font-size: 24px; margin: 0;"><?php echo $card['card_type']; ?></h1>
            <div style="color: white; font-size: 23px; letter-spacing: 2px; margin-bottom: 20px; font-family: 'Courier New', monospace;">
                <?php echo $card['card_number']; ?>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="color: #888; font-size: 12px;">
                    VALID FROM<br>
                    <span style="color: white;"><?php echo $card['mm_exp']; ?>/<?php echo $card['yyyy_exp']; ?></span>
                </div>
                <div style="color: #888; font-size: 12px;">
                    EXPIRES END<br>
                    <span style="color: white;"><?php echo $card['mm_exp']; ?>/<?php echo $card['yyyy_exp']; ?></span>
                </div>
            </div>
            <div style="color: white; font-size: 16px; margin-bottom: 5px;">CARDHOLDER NAME</div>
            <div style="color: white; font-size: 16px;">BUSINESS NAME</div>
            <div style="position: absolute; bottom: 25px; right: 25px;">
            <a href="buy_card.php?id=<?php echo htmlspecialchars($card['id']); ?>" 
                class="buy-button" 
                onclick="return confirm('Are you sure you want to buy this card?');">
                    Buy Now
                </a>
            </div>
            <div style="position: absolute; top: 25px; right: 25px; color: white; transform: rotate(90deg);"></div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No credit cards available.</p>
<?php endif; ?>
        <!-- Card 2 - Blue/Silver Premium -->
        <div style="width: 420px; height: 265px; background: linear-gradient(45deg, #1e3c72, #2a5298); border-radius: 15px; padding: 25px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
            <div style="position: absolute; top: 50%; left: 60%; width: 400px; height: 400px; background: radial-gradient(circle, transparent 20%, #silver 20%, #silver 80%, transparent 80%); transform: translate(-50%, -50%); opacity: 0.1;"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
                <h1 style="color: white; font-size: 24px; margin: 0;">LLOYDS BANK</h1>
                <span style="color: white; font-size: 14px;">Premium Credit</span>
            </div>

            <div style="width: 45px; height: 35px; background: #bdb69c; border-radius: 5px; margin-bottom: 20px;"></div>

            <div style="color: white; font-size: 23px; letter-spacing: 2px; margin-bottom: 20px; font-family: 'Courier New', monospace;">
                1234 5678 9123 4567
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="color: #ccc; font-size: 12px;">
                    VALID FROM<br>
                    <span style="color: white;">07/21</span>
                </div>
                <div style="color: #ccc; font-size: 12px;">
                    EXPIRES END<br>
                    <span style="color: white;">02/24</span>
                </div>
            </div>

            <div style="color: white; font-size: 16px; margin-bottom: 5px;">CARDHOLDER NAME</div>
            <div style="color: white; font-size: 16px;">BUSINESS NAME</div>

            <div style="position: absolute; bottom: 25px; right: 25px;">
                <div style="display: flex;">
                    <div style="width: 35px; height: 35px; background: #ff0000; border-radius: 50%; opacity: 0.8;"></div>
                    <div style="width: 35px; height: 35px; background: #ff9900; border-radius: 50%; margin-left: -15px; opacity: 0.8;"></div>
                </div>
            </div>

            <div style="position: absolute; top: 25px; right: 25px; color: white; transform: rotate(90deg);">)))</div>

            <div style="position: absolute; top: 60px; right: 25px;">
                <div style="width: 40px; height: 40px; border: 2px solid white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <div style="color: white; font-size: 24px; transform: scaleX(-1);">♘</div>
                </div>
            </div>
        </div>

        <!-- Card 3 - Gold/Black Luxury -->
        <div style="width: 420px; height: 265px; background: linear-gradient(45deg, #b8860b, #000000); border-radius: 15px; padding: 25px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
            <div style="position: absolute; top: 50%; left: 60%; width: 400px; height: 400px; background: radial-gradient(circle, transparent 20%, #ffd700 20%, #ffd700 80%, transparent 80%); transform: translate(-50%, -50%); opacity: 0.1;"></div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
                <h1 style="color: #ffd700; font-size: 24px; margin: 0;">LLOYDS BANK</h1>
                <span style="color: #ffd700; font-size: 14px;">Luxury Credit</span>
            </div>

            <div style="width: 45px; height: 35px; background: #bdb69c; border-radius: 5px; margin-bottom: 20px;"></div>

            <div style="color: #ffd700; font-size: 23px; letter-spacing: 2px; margin-bottom: 20px; font-family: 'Courier New', monospace;">
                1234 5678 9123 4567
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="color: #daa520; font-size: 12px;">
                    VALID FROM<br>
                    <span style="color: #ffd700;">07/21</span>
                </div>
                <div style="color: #daa520; font-size: 12px;">
                    EXPIRES END<br>
                    <span style="color: #ffd700;">02/24</span>
                </div>
            </div>

            <div style="color: #ffd700; font-size: 16px; margin-bottom: 5px;">CARDHOLDER NAME</div>
            <div style="color: #ffd700; font-size: 16px;">BUSINESS NAME</div>

            <div style="position: absolute; bottom: 25px; right: 25px;">
                <div style="display: flex;">
                    <div style="width: 35px; height: 35px; background: #ff0000; border-radius: 50%; opacity: 0.8;"></div>
                    <div style="width: 35px; height: 35px; background: #ff9900; border-radius: 50%; margin-left: -15px; opacity: 0.8;"></div>
                </div>
            </div>

            <div style="position: absolute; top: 25px; right: 25px; color: #ffd700; transform: rotate(90deg);">)))</div>

            <div style="position: absolute; top: 60px; right: 25px;">
                <div style="width: 40px; height: 40px; border: 2px solid #ffd700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <div style="color: #ffd700; font-size: 24px; transform: scaleX(-1);">♘</div>
                </div>
            </div>
        </div>

    </div>

</div>

    </div>
</div>
<?php
include_once('../../footer.php');
?>
<script>
    

</script>