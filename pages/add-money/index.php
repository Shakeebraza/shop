<?php
include_once('../../header.php');
?>
<style>
    .transaction_btn{
        box-shadow: 2px 3px black;
        background-color: #04AA6D;
        border: none;
        color: white;
        padding: 6px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
    }
</style>
    <div class="main-content">
<div id="add-money" class="">
    <h2>Add Money</h2>

    <form id="add-money-form" action="#">
        <label for="crypto-method">Choose Payment Method:</label>
        <select id="crypto-method" name="crypto-method" required>
            <option value="" disabled selected>Select your payment method</option>
            <option value="btc">Bitcoin (BTC)</option>
        </select>
  

        <label for="amount">Amount to Recharge (Minimum $0.96 USD):</label>
        <input type="number" id="amount" name="amount" min="0.94" required placeholder="Enter amount in USD">

        <div id="payment-info" style="display: none; margin-top: 20px;">
            <p id="payment-address"></p>
        </div>

        <input type="submit" value="Generate Payment Address" style="margin-top: 20px;">
    </form>
    <div id="transaction-history" style="margin-top: 30px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd;">
    <h3 style="margin-bottom: 15px; font-size: 24px; font-weight: bold;">Transaction History</h3>
    <table id="transaction-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">ID</th>
		<!-- <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">Username</th> -->
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">Date</th>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">Amount (USD)</th>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">Amount (BTC)</th>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">BTC Address</th>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">TX Hash</th>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">Verified Transition</th>
                <th style="padding: 10px; text-align: left; background-color: rgb(52, 58, 64); border: 1px solid #ddd;">Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data will be populated here by DataTables -->
        </tbody>
    </table>
</div>

</div>


    </div>
</div>

<?php
include_once('../../footer.php');
?>
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
   

    try {
        const rateResponse = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
        const rateData = await rateResponse.json();
        const btcRate = rateData.bitcoin.usd;

        const margin = 0.02;
        const btcAmount = (usdAmount / btcRate) * (1 + margin);

        
        const requestResponse = await fetch('<?= $urlval?>ajax/generate-payment-request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount_btc: btcAmount, memo: `Recharge for $${usdAmount} USD` })
        });

        const requestData = await requestResponse.json();

        if (requestData.success) {
            paymentInfo.style.display = 'block';
            paymentAddress.innerHTML = `
                <strong>Send BTC to this Address:</strong>
                <span>${requestData.btcAddress}</span><br>
                <strong>Amount to Send:</strong> ${btcAmount.toFixed(8)} BTC
                <button onclick="navigator.clipboard.writeText('${requestData.btcAddress}')">Copy Address</button>
            `;
            $('#transaction-table').DataTable().ajax.reload();
        } else {
            alert('Error generating payment request. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Unable to process your request. Please try again later.');
    }
});

$(document).ready(function() {
    $('#transaction-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= $urlval?>ajax/get_transactions.php", 
            "type": "GET"
        },
        "columns": [
            { "data": "id" },
            // { "data": "username" },
            { "data": "created_at" },
            { "data": "amount_usd" },
            { "data": "amount_btc" },
            { "data": "btc_address" },
            { "data": "tx_hash" },
            {
                "data": "tx_hash",
                "render": function(data, type, row) {
                    if (data) {
                        return `<button class="transaction_btn" onclick="window.open('https://blockstream.info/testnet/tx/${data}', '_blank')">Verify Transaction</button>`;
                    } else {
                        return '<span>Waiting for Transaction</span>';
                    }
                }
            },
            { "data": "status" }
        ],
        "order": [[0, 'desc']]
    });
});




function checkPayments() {
            $.ajax({
                url: '<?= $urlval?>ajax/getadd.php', 
                type: 'GET',
                success: function (response) {
                    console.log(response); 
                 
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    $("#status").html('<p style="color: red;">An error occurred while checking payments.</p>');
                }
            });
        }

        setInterval(checkPayments, 60000);
        checkPayments();
</script>
</body>
</html>