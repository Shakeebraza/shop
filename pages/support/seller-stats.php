<?php
include_once('../../header.php');
$balance_saller = $user['credit_cards_balance'] + $user['dumps_balance'];

?>
<style>
#seller-stats {
    width: 100% !important;
    max-width: 100% !important;
    display: block !important;
    margin: 0px !important;
    box-shadow: none !important;
}

.stats-container {
    box-shadow: none !important;
    background-color: #f9f9f94a !important;
    border-radius: 0px;
    border: 1px solid #e6e6e7 !important;
}

.stats-container h3 {
    font-size: 32px !important;
    font: weight 400px;
    ;
    color: #0c182f !important;
    margin-bottom: 10px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid #e6e6e7 !important;
}

.stat-item strong {
    font-size: 18px !important;
    font: weight 400px;
    ;
    color: #0c182f !important;

}

/* Styling for input fields */
.inpt-wtdr {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 300px;
    margin: auto;
    margin-top: 20px;
}

.withdrawal_amount {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

/* Styling for buttons */
#withdrawal_amount3 {
    padding: 10px 20px;
    border: none;
    background-color: #0c182f;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btw-sbm {
    display: block;
    padding: 10px 20px;
    border: none;
    background-color: #0c182f;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

#withdrawal_amount3:hover {
    background-color: #0c182f;
}

#withdrawal_amount3:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}


.loader {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0c182f;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 5px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}


.btw-sbm.disabled-btn {
    background-color: #d6d6d6;
    color: #999;
    cursor: not-allowed;
}
</style>
<!-- Main Content Area -->
<div class="main-content">

    <?php if ($user['seller'] == 1): ?>
    <div id="seller-stats" class="section uuper">
        <h2><i class="fas fa-chart-bar"></i> Seller Stats</h2> <!-- Main title -->

        <!-- Seller Percentage -->
        <div class="stats-container">
            <h3>Seller Percentage</h3>
            <div class="stat-item">Percentage:
                <strong><?php echo number_format($user['seller_percentage'], 2); ?>%</strong>
            </div>
            <div class="stat-item">Actual Balance: <strong>
                    <?php 
                    $totalEarned = $user['credit_cards_balance'] + $user['dumps_balance'];
                    echo '$' . number_format($totalEarned, 2);
                ?>
                </strong></div>
            <div class="stat-item">Total earned from Credit Cards:
                <strong>$<?php echo number_format($user['credit_cards_total_earned'], 2); ?></strong>
            </div>
            <div class="stat-item">Total earned from Dumps
                <strong>$<?php echo number_format($user['dumps_total_earned'], 2); ?></strong>
            </div>
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
        <div class="stats-container">
            <h3>Do you want to Withdraw</h3>
            <div>
                <button id="withdrawal_amount3" onclick="toggleInputForm(this)">Withdraw Balance</button>
            </div>
            <div class="inpt-wtdr" style="display: none;">
                <form id="withdrawalForm">
                    <div class="inpt-wtdr">
                        <input type="text" class="withdrawal_amount" name="BTC_Address" placeholder="BTC Address"
                            required>
                        <input type="text" class="withdrawal_amount" name="Secret_Code" placeholder="Secret Code"
                            required>
                        <p class="withdrawal_amount readonly"
                            style="background-color: #f5f5f5; color: #333; font-weight: bold; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            Your balance is: $<?php echo number_format($balance_saller, 2); ?>
                        </p>
                        <input type="button" value="Submit"
                            class="btw-sbm <?= $balance_saller == 0 ? 'disabled-btn' : '' ?>"
                            <?= $balance_saller == 0 ? 'disabled' : '' ?> id="submitBtn">
                    </div>
                </form>
            </div>


        </div>
    </div>
    <?php endif; ?>

</div>
</div>
<?php
include_once('../../footer.php');
?>
<script>
function toggleInputForm(button) {

    button.disabled = true;
    button.innerHTML = 'Loading <span class="loader"></span>';


    setTimeout(() => {

        const form = document.querySelector('.inpt-wtdr');
        if (form.style.display === 'none') {
            form.style.display = 'flex';
            button.innerHTML = 'Hide Form';
        } else {
            form.style.display = 'none';
            button.innerHTML = 'Withdraw Balance';
        }


        button.disabled = false;
    }, 1000);
}


document.getElementById("submitBtn").addEventListener("click", function() {
    var btcAddress = document.querySelector('input[name="BTC_Address"]').value;
    var secretCode = document.querySelector('input[name="Secret_Code"]').value;

    if (!btcAddress || !secretCode) {
        alert("Please fill in both BTC Address and Secret Code.");
        return false;
    }

    // AJAX request to check session and validate the secret code
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "validate_withdrawal.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === "success") {

                var formData = new FormData();
                formData.append("BTC_Address", btcAddress);
                formData.append("Secret_Code", secretCode);

                showPopupMessage(response.message);
                setTimeout(function() {
                    window.location.href = "<?= $urlval?>pages/support/index.php";
                }, 5000);



                withdrawalXhr.send(formData);

            } else {

                showPopupMessage(response.message);
            }
        } else {
            showPopupMessage("Something went wrong. Please try again.");
        }
    };

    xhr.send("btcAddress=" + btcAddress + "&secretCode=" + secretCode);
});

function closeRulesPopup() {
    const popup = document.getElementById('rules-popup');
    popup.style.display = 'none';
}
</script>