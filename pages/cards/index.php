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

/* Table container */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    font-family: Arial, sans-serif;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Table header */
thead tr {
    background-color: #007bff;
    color: white;
    text-align: left;
}

thead th {
    padding: 12px 15px;
    border: 1px solid #ddd;
}

/* Table rows */
tbody tr {
    border-bottom: 1px solid #ddd;
}

tbody tr:nth-of-type(even) {
    background-color: #f9f9f9; /* Alternating row color */
}

tbody tr:hover {
    background-color: #f1f1f1; /* Row hover effect */
}

/* Table cells */
td {
    padding: 10px 15px;
    border: 1px solid #ddd;
    vertical-align: middle;
}

/* Action button */
a.buy-button {
    display: inline-block;
    background-color: #28a745;
    color: #fff;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

a.buy-button:hover {
    background-color: #218838;
    cursor: pointer;
}
#credit-card-filters{
    display: flex !important;
    align-items: center !important;
    gap: 20px !important;
}
#credit-cards{
    display: inline-block !important;
    width: auto !important;
    margin-top:20px;
    border-radius: 0px !important;
    box-shadow:none !important;
}

/* Responsive design */
@media (max-width: 768px) {
    table {
        font-size: 14px;
    }
    td, th {
        padding: 8px 15px;
        text-wrap:nowrap !important;
    }
    a.buy-button {
        font-size: 12px;
        padding: 6px 10px;
    }
    .main-tbl321{
    width: 100% !important;
    overflow-x:scroll !important;}
    a.buy-button {
    height: 30px !important;
 
}
}

a.buy-button {
    height: 38px !important;
    width: 100px !important;
    text-align: center !important;
}
td{
    padding: 10px; border: 1px solid #ddd;
}
</style>


    <div class="main-content">
    <div id="credit-cards" class="uuper">
    <h2>Credit Cards Section</h2>


    <div class="filter-container-cards">
    <form id="credit-card-filters" method="post" action="#credit-cards">
    <div class="inpt-dmps-bx">
        <label for="credit-card-bin">BIN</label>
        <input type="text" name="cc_bin" id="credit-card-bin" placeholder="Comma-separated for multiple - e.g., 123456, 654321">
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="credit-card-country">Country</label>
        <select name="cc_country" id="credit-card-country">
            <option value="">All</option>
            <?php foreach ($creditCardCountries as $country): ?>
                <option value="<?php echo htmlspecialchars($country); ?>">
                    <?php echo htmlspecialchars($country); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="state">State</label>
        <input type="text" name="cc_state" id="state" placeholder="">
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="city">City</label>
        <input type="text" name="cc_city" id="city" placeholder="">
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="zip">ZIP</label>
        <input type="text" name="cc_zip" id="zip" placeholder="">
    </div>
    
    <div class="inpt-dmps-bx">
        <label for="type">Type</label>
        <select name="cc_type" id="type">
            <option value="all">All</option>
            <option value="visa">Visa</option>
            <option value="mastercard">Mastercard</option>
            <option value="amex">Amex</option>
            <option value="discover">Discover</option>
        </select>
    </div>
    

    <div class="inpt-dmps-bx">
    <label for="dumps_per_page">Base name</label>
    <select name="basename" id="basename">
        <option value="all">All</option>
        
        <?php
        $baseNames = $settings->getCreditCardBaseNames();
             
        foreach ($baseNames as $baseName) {
            if($baseName['base_name'] != NULL){

                echo '<option value="' . htmlspecialchars($baseName['base_name']) . '">' . htmlspecialchars($baseName['base_name']) . '</option>';
            }
        }
        ?>
    </select>
</div>

    <div class="inpt-dmps-bx">
        <label for="dumps_per_page">All</label>
        <select name="dumps_per_page" id="dumps_per_page">
            <option value="No">No</option>
            <option value="yes">Yes</option>
           
        </select>
    </div>
    </form>

    </div>


 
<?php
// var_dump($creditCards);
?>
<?php if (!empty($creditCards)): ?>
    <div class="main-tbl321">
    <table id="creditCardsTable" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background-color:#0c182f;">
                <th style="padding: 10px; border: 1px solid #ddd;">Type</th>
                <th style="padding: 10px; border: 1px solid #ddd;">BIN</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Expiry</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Country</th>
                <th style="padding: 10px; border: 1px solid #ddd;">State</th>
                <th style="padding: 10px; border: 1px solid #ddd;">City</th>
                <th style="padding: 10px; border: 1px solid #ddd;">ZIP</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Price</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Buy</th>
            </tr>
        </thead>
        
    </table>
</div>

<?php else: ?>
<p>No credit cards available.</p>
<?php endif; ?>



    <div style="display: none; gap: 20px; padding: 20px; flex-wrap: wrap; justify-content: center;">
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

$(document).ready(function () {
 
    $('#creditCardsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false, 
        ajax: {
            url: '<?= $urlval ?>ajax/carddata.php',
            type: 'POST',
            data: function (d) {
             
                d.cc_bin = $('#credit-card-bin').val();
                d.cc_country = $('#credit-card-country').val();
                d.cc_state = $('#state').val();
                d.cc_city = $('#city').val();
                d.cc_zip = $('#zip').val();
                d.cc_type = $('#type').val();
                d.basename = $('#basename').val();  
                d.dumps_per_page = $('#dumps_per_page').val(); 
            }
        },
        columns: [
            { data: 'card_logo' },
            { data: 'card_number' },
            { data: 'expiry' },
            { data: 'country' },
            { data: 'state' },
            { data: 'city' },
            { data: 'zip' },
            { data: 'price' },
            { data: 'actions' }
        ]
    });

    
    $('#credit-card-filters input, #credit-card-filters select').on('change', function () {
        $('#creditCardsTable').DataTable().ajax.reload();  
    });
});

</script>