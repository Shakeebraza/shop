<?php
include_once('../../header.php');
?>
<?php include_once('popup.php')?>
<style>
    .transaction_btn {
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

    .pay_now_btn {
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

    .expired-status {
        text-align: center;
        font-weight: bold;
        color: red;
    }


body.popup-active {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px); 
    background: rgba(255, 255, 255, 0.5); 
    transition: backdrop-filter 0.3s, background 0.3s;
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
            <input type="number" id="amount" name="amount" min="0.96" step="0.01" required placeholder="Enter amount in USD">

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
                        <!-- <th>ID</th> -->
                        <th>Date</th>
                        <th>Amount (USD)</th>
                        <th>Amount (BTC)</th>
                        <th>BTC Address</th>
                        <!-- <th>TX Hash</th> -->
                        <th>Action</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                   
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
    const cryptoMethod = document.getElementById('crypto-method').value;

    if (!cryptoMethod) {
        alert('Please select a payment method.');
        return;
    }

    const usdAmount = parseFloat(amountInput.value);

    try {
        const requestResponse = await fetch('<?= $urlval?>ajax/generate-payment-request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount_usd: usdAmount,
                memo: `Recharge for $${usdAmount} USD`
            })
        });

        const requestData = await requestResponse.json();
        if (requestData.success) {
            const btcAmount = requestData.data.amountBtc;
            const btcAddress = requestData.data.btcAddress;

            generateQRCode(btcAddress, btcAmount);
            startCountdown(3600000, Date.now()); 
            $('#paymentModal').find('#btcAmount').val(btcAmount + ' BTC');
            $('#paymentModal').find('#btcAddress').val(btcAddress);
            $('#modalBackdrop').show();
            $('body').addClass('popup-active'); 
            $('#paymentModal').fadeIn();
            $('#transaction-table').DataTable().ajax.reload();
        } else {
            alert('Error generating payment request. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Unable to process your request. Please try again later.');
    }
});

function generateQRCode(address, amount) {
    new QRious({
        element: document.getElementById('btcQRCode'),
        value: `bitcoin:${address}?amount=${amount}`,
        size: 200,
    });
}

function startCountdown(remainingTime, startTime) {
    clearInterval(window.countdownInterval);

    window.countdownInterval = setInterval(function () {
        const endTime = startTime + remainingTime;
        const now = Date.now(); 
        const timeLeft = endTime - now;

        if (timeLeft <= 0) {
            clearInterval(window.countdownInterval);
            $('#timer').text('Expired');
            $('.pay_now_btn').each(function () {
                $(this).replaceWith('<span class="expired-status">-</span>');
            });
            return;
        }

        const minutes = Math.floor(timeLeft / 60000);
        const seconds = Math.floor((timeLeft % 60000) / 1000);

        $('#timer').text(`${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
    }, 1000);
}

$(document).ready(function () {
    $('#transaction-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= $urlval?>ajax/get_transactions.php',
            type: 'GET',
        },
        columns: [
            // { data: 'id' },
            { data: 'created_at' },
            { data: 'amount_usd' },
            { data: 'amount_btc' },
            { data: 'btc_address' },
            // { data: 'tx_hash' },
            {
                data: 'tx_hash',
                render: function (data, type, row) {
                    if (row.status === 'EXPIRED') {
                        return '<span class="expired-status">-</span>';
                    }else if(row.status ==='INSUFFICIENT'){

                        return `<a class="transaction_btn" style="background-color:red;" href="<?= $urlval?>pages/support/index.php">Contact Support</a>`;
                    } 
                    else if (data) {
                        return `<button class="transaction_btn" onclick="window.open('https://www.blockchain.com/explorer/transactions/btc/${data}', '_blank')">Verify Transaction</button>`;
                    }
                    else {
                        return `<button class="pay_now_btn" data-id="${row.id}" data-amount="${row.amount_btc}" data-address="${row.btc_address}" data-time="${row.created_at}" data-now='<?= $currentDateTime?>'>Pay Now</button>`;
                    }
                },
            },
            { data: 'status' },
        ],
        order: [[0, 'desc']],
    });

    function initializeTimer(createdAt, currentTime) {
        console.log(createdAt, currentTime);
    const expirationDuration = 60 * 60 * 1000; 

    const createdTime = new Date(createdAt).getTime(); 
    const currentTimeMs = new Date(currentTime).getTime(); 


    const elapsedTime = currentTimeMs - createdTime;
    const remainingTime = expirationDuration - elapsedTime;

   
    if (remainingTime <= 0) {
        $('#timer').text('Expired').css('color', 'red');
        return null;
    }

    let remainingSeconds = Math.floor(remainingTime / 1000);

    function updateDisplay() {
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const seconds = remainingSeconds % 60;

        $('#timer').text(
            `${minutes}m ${seconds}s`
        );
    }

    updateDisplay();

   
    const intervalId = setInterval(() => {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            clearInterval(intervalId);
            $('#timer').text('Expired').css('color', 'red');
            return;
        }
        updateDisplay();
    }, 1000);

    return intervalId;
}
const countdownIntervals = {};

$(document).on('click', '.pay_now_btn', function () {
    const btcAmount = $(this).data('amount');
    const btcAddress = $(this).data('address');
    const createdAt = $(this).data('time'); 
    const currentTime = $(this).data('now');
    const buttonId = $(this).data('id');

 
    $('#btcAmount').text(`${btcAmount} BTC`);
    $('#btcAddress').text(btcAddress);
    $('#modalBackdrop').show();
    generateQRCode(btcAddress, btcAmount);
    $('body').addClass('popup-active');
    $('#paymentModal').fadeIn();

 
    if (countdownIntervals[buttonId]) {
        clearInterval(countdownIntervals[buttonId]);
    }

  
    countdownIntervals[buttonId] = initializeTimer(createdAt, currentTime);
});

$('#closeModalBtn').click(function () {
    $('#paymentModal').fadeOut();
    $('#modalBackdrop').hide();
    $('body').removeClass('popup-active');
    $('#timer').text('');


    for (let id in countdownIntervals) {
        clearInterval(countdownIntervals[id]);
        delete countdownIntervals[id];
    }

    $('#qrcode').empty();
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

    $(document).ready(function () {
        checkPayments();
        setInterval(checkPayments, 3600000); 
    });
    document.addEventListener('DOMContentLoaded', function () {
    const copyButtons = document.querySelectorAll('.copy-btn');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function () {
            const target = document.querySelector(button.getAttribute('data-copy-target'));
            
     
            target.select();
            target.setSelectionRange(0, 99999); 
      
            document.execCommand('copy');
            alert('Copied to clipboard!');
        });
    });
});


</script>


</body>
</html>
