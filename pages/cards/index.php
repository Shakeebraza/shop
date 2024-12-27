<?php
include_once('../../header.php');
?>
<style>
    .message-success {
    color: green;
    font-weight: bold;
    text-align: center;
}


.message-error {
    color: red;
    font-weight: bold;
    text-align: center;
}
</style>

    <div class="main-content">
    <div id="credit-cards" class="uuper">
    <h2>Credit Cards Section</h2>


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


 
<?php
// var_dump($creditCards);
?>
    <div style="display: flex; gap: 20px; padding: 20px; flex-wrap: wrap; justify-content: center;">
    <?php if (!empty($creditCards)): ?>
    <?php foreach ($creditCards as $card):
    if ($card['card_type'] == 'Mastercard') {
        $background = 'background-image: url(https://wallpapers.com/images/hd/mastercard-logo-black-background-6ud73xlg936woct6.jpg); background-size: cover; background-position: center;';
    } elseif ($card['card_type'] == 'Amex') {
       
        $background = 'background: linear-gradient(45deg, #b8860b, #000000);';
    } elseif ($card['card_type'] == 'Visa') {
        $background = 'background: linear-gradient(45deg, #2E4053, #000000);';
    }else{
        $background = 'background: linear-gradient(45deg,rgb(48, 46, 83),rgb(46, 43, 43));';
        
    }
        $zip = $card['zip'];
        $displayZip = substr($zip, 0, 3) . '****';?>
        <div style="width: 420px; height: 265px; 
            <?= $background ?> 
            background-size: cover; 
            background-position: center; 
            border-radius: 15px; 
            padding: 25px; 
            position: relative; 
            overflow: hidden; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
            <h1 style="color: white; font-size: 24px; margin: 0;"><?php echo $card['card_type']; ?></h1>
            <div style="color: white; font-size: 23px; letter-spacing: 2px; margin-bottom: 20px; font-family: 'Courier New', monospace;">
                <?php echo $card['card_number']; ?>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="color: #888; font-size: 12px;">
                    STATE<br>
                    <span style="color: white;"><?php echo $card['country']; ?>/<?php echo $card['city']; ?></span>
                </div>
                <div style="color: #888; font-size: 12px;">
                    EXPIRES END<br>
                    <span style="color: white;"><?php echo $card['mm_exp']; ?>/<?php echo $card['yyyy_exp']; ?></span>
                </div>
            </div>
            <div style="color: white; font-size: 16px; margin-bottom: 5px;"><?php echo $card['name_on_card']; ?></div>
            <div style="color: white; font-size: 16px;"><?php echo $displayZip ?></div>
            <div style="position: absolute; bottom: 25px; right: 25px;">
            <a href="javascript:void(0);" 
                class="buy-button" 
                onclick="return showConfirm(<?php echo htmlspecialchars($card['id']); ?>, <?php echo htmlspecialchars($card['price']); ?>);">
                <span class="price">$<?php echo $card['price']; ?></span>
                <span class="buy-now">Buy Now</span>
                </a>
            </div>
            <div style="position: absolute; top: 25px; right: 25px; color: white; transform: rotate(90deg);"></div>
        </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No credit cards available.</p>
        <?php endif; ?>


    </div>

</div>

    </div>
</div>

<div id="rules-popup" class="popup-modal" style="display: none;">
    <div class="popup-content" style="position: absolute;top: 50%;right: 20%;">
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <p class="message"></p>  <!-- This will be dynamically replaced by success/error message -->
    </div>
</div>
<?php
include_once('../../footer.php');
?>



<script>
function showConfirm(cardId, price) {
    alertify.confirm(
        'Confirm Purchase',
        `Are you sure you want to buy this card for $${price}?`,
        function() {
            $.ajax({
                url: 'buy_card.php',
                type: 'POST',
                data: { card_id: cardId },
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;

                        if (result.success) {
                            showPopupMessage('success', result.message || 'Purchase successful.');
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            showPopupMessage('error', result.message || 'An error occurred.');
                        }
                    } catch (error) {
                        console.error('JSON parse error:', error);
                        showPopupMessage('error', 'Unexpected server response. Please try again.');
                    }
                },
                error: function() {
                    showPopupMessage('error', 'Transaction failed. Please try again.');
                }
            });
        },
        function() {
            alertify.error('Purchase cancelled.');
        }
    ).set('labels', { ok: 'Confirm', cancel: 'Cancel' });

    return false;
}


function showPopupMessage(type, message) {
    const popup = document.getElementById('rules-popup');
    const popupContent = popup.querySelector('.popup-content');


    popupContent.innerHTML = `
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <p class="${type === 'success' ? 'message-success' : 'message-error'}">${message}</p>
    `;

    popup.style.display = 'block';
}


function closeRulesPopup() {
    const popup = document.getElementById('rules-popup');
    popup.style.display = 'none';
}


</script>