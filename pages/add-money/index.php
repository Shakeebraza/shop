<?php
include_once('../../header.php');

?>
<?php
include_once('popup.php');
?>
<?php include_once('style.php')?>
<style>
    div#transaction-table_wrapper {
    overflow-x: scroll;

}
@media(min-width: 300px) and (max-width: 767.98px) {
    .payment-modal {
   
    top: 20px;
    left: 50%;
    transform: none;
    z-index: 1050;
    width: 100%;
    max-width: 100% !important;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    text-align: center;
}
.modal-backdrop {
    padding: 20px;
}
.popup-content {
width: 100% !important;
}
}
</style>
<div class="main-content">
<div class="breadcrumb-container uuper" style="margin-top: 20px; font-family: Arial, sans-serif; background-color: #0c182f; padding: 10px; border-radius: 5px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb" style="display: flex; list-style: none; padding: 0; margin: 0; background-color: transparent;">
            <li class="breadcrumb-item" style="margin-right: 5px; font-size: 16px;">
                <a href="<?php echo $urlval; ?>" style="text-decoration: none; color:rgb(255, 255, 255);">Home</a>
            </li>
            <li class="breadcrumb-item active uuper" style="margin-right: 5px; font-size: 16px; color: rgba(255, 255, 255, 0.69); ">
                Add Money
            </li>
        </ol>
    </nav>
</div>


    <div id="add-money" class="">
    <h2 style="text-align: center; font-size: 24px; color: #333; margin-top: 20px;">Add Money</h2>


        <form id="add-money-form" class="uuper" action="#">
            <label for="crypto-method">Choose Payment Method:</label>
            <select id="crypto-method" name="crypto-method" required>
                <option value="" disabled selected>Select your payment method</option>
                <option value="btc">Bitcoin (BTC)</option>
            </select>

            <label for="amount">Amount to Recharge (Minimum $20 USD):</label>
            <input type="number" id="amount" name="amount" min="0.96" step="0.01" required placeholder="Enter amount in USD">

            <div id="payment-info" style="display: none; margin-top: 20px;">
                <p id="payment-address"></p>
            </div>

            <input type="submit" value="Generate Payment Address" style="margin-top: 20px;">
        </form>
        <div id="transaction-history" class="uuper" style="margin-top: 30px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd;">
    <h3 style="margin-bottom: 15px; font-size: 24px; font-weight: bold;">
        Transaction History
        <div style="display: flex; align-items: center; gap: 10px;">

    <button id="refresh-table" style="padding: 5px 15px; background-color: #0c182f; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; display: flex; align-items: center; gap: 5px;">
        <i class="fas fa-sync-alt"></i> 
    </button>

   
    <button id="rules-btn" 
    style="padding: 5px 15px; background-color: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; display: flex; align-items: center; gap: 5px;" 
    onclick="openRulesPopup()">
    <i class="fas fa-gavel"></i>Rules
    </button>

    </div>
    </h3>
    <div id="customLoader" style="display: none; text-align: center; margin-bottom: 15px;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
    <table id="transaction-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount (USD)</th>
                <th>Amount (BTC)</th>
                <th>BTC Address</th>
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

<div id="errorPopup" style="display: none;">
    <div id="errorPopupContent">
    <span class="close" onclick="closePopupDef()">
            <i class="fas fa-times"></i>
        </span>
        <p id="errorMessage"></p>
        
    </div>
</div>
<?php
include_once('../../footer.php');
include_once('popuprules.php');
include_once('insufficient_popup.php');
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
            $('#paymentModal').find('#btcAmount').text(btcAmount);
            $('#paymentModal').find('#btcAddress').text(btcAddress);
            $('#modalBackdrop').show();
            $('body').addClass('popup-active'); 
            $('#paymentModal').fadeIn();
            $('#transaction-table').DataTable().ajax.reload();
        } else {
            displayErrorPopup(requestData.message);
        }
    } catch (error) {
        console.error('Error:', error);
        
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
    $(document).ready(function () {
    var customLoader = $('#customLoader');

    $('#transaction-table').DataTable({
        serverSide: true,
        processing: false, // Disable built-in processing indicator
        searching: false,
        ajax: {
            url: '<?= $urlval?>ajax/get_transactions.php',
            type: 'GET',
            beforeSend: function () {
                // Show custom loader
                customLoader.show();
            },
            complete: function () {
                // Hide custom loader
                customLoader.hide();
            },
            error: function () {
                alert('Failed to load data. Please try again.');
            }
        },
        columns: [
            { data: 'created_at' },
            { data: 'amount_usd' },
            { data: 'amount_btc' },
            { data: 'btc_address' },
            {
                data: 'tx_hash',
                render: function (data, type, row) {
                    if (row.status === 'EXPIRED' || row.status === 'CANCELLED') {
                        return '<span class="expired-status">-</span>';
                    } else if (row.status === 'INSUFFICIENT') {
                        return `<a class="transaction_btn" style="background-color:red;" href="<?= $urlval?>pages/support/index.php">Contact Support</a>`;
                    } else if (row.status === 'RECEIVING') {
                        return `<span class="receiving-status" style="
                                    padding: 5px 10px;
                                    background-color: #fffae6;
                                    color: #0c182f;
                                    border: 1px solid #0c182f;
                                    border-radius: 5px;
                                    font-weight: bold;
                                    white-space: nowrap;
                                    animation: blink 1.5s steps(2, start) infinite;">Please wait...</span>`;
                    } else if (row.status === 'CONFIRMED') {
                        return `<button class="transaction_btn" onclick="window.open('https://www.blockchain.com/explorer/transactions/btc/${data}', '_blank')">View Transaction</button>`;
                    } else {
                        return `<div class="button-container">
                            <button class="pay_now_btn" data-id="${row.id}" data-amount="${row.amount_btc}" data-address="${row.btc_address}" data-time="${row.created_at}" data-now='<?= $currentDateTime?>'>PayNow</button>
                            <button class="cancle_transaction_btn" data-id="${row.id}">Cancel</button>
                        </div>`;
                    }
                }
            },
            {
                data: 'status',
                render: function (data) {
                    var statusClasses = {
                        'EXPIRED': 'expired-status',
                        'CONFIRMED': 'confirmed-status',
                        'PENDING': 'pending-status',
                        'INSUFFICIENT': 'insufficient-status',
                        'RECEIVING': 'receiving-status'
                    };
                    var statusClass = statusClasses[data] || 'default-status';
                    return `<span class="${statusClass}">${data}</span>`;
                }
            }
        ],
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: '_all' }]
    });
});
    $('#refresh-table').on('click', function () {
        $('#transaction-table').DataTable().ajax.reload();
    });
    $(document).on('click', '.cancle_transaction_btn', function() {
    var transactionId = $(this).data('id');

    if (confirm('Are you sure you want to cancel this transaction?')) {
        $.ajax({
            url: '<?= $urlval ?>ajax/cancel_transaction.php',
            method: 'POST',
            data: {
                transaction_id: transactionId, 
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                   
                    $('#transaction-table').DataTable().ajax.reload();
                } else {
                   
                    $('#transaction-table').DataTable().ajax.reload();
                }
            },
            error: function() {
                alert('Error occurred while processing the request.');
            }
        });
    }
});
    function initializeTimer(createdAt, currentTime) {
   
    const EXPIRATION_DURATION = 60 * 60 * 1000; 

    const createdTime = new Date(createdAt).getTime();
    const currentTimeMs = new Date(currentTime).getTime();

    if (isNaN(createdTime) || isNaN(currentTimeMs)) {
        $('#timer').text('Invalid time').css('color', 'red');
        return null;
    }

    const elapsedTime = currentTimeMs - createdTime;
    const remainingTime = EXPIRATION_DURATION - elapsedTime;

    if (remainingTime <= 0) {
        $('#timer').text('Expired').css('color', 'red');
        return null;
    }

    let remainingSeconds = Math.floor(remainingTime / 1000);

    function updateDisplay() {
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const seconds = remainingSeconds % 60;

        $('#timer').text(`${minutes}m ${seconds}s`);
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
    const buttonId = $(this).data('id');
    $('#btcAmount').text(`${btcAmount}`);
    $('#btcAddress').text(btcAddress);
    $('#modalBackdrop').show();
    generateQRCode(btcAddress, btcAmount);
    $('body').addClass('popup-active');
    $('#paymentModal').fadeIn();
   

    const $button = $(this);
    $button.prop('disabled', true);

    if (countdownIntervals[buttonId]) {
        clearInterval(countdownIntervals[buttonId]);
    }

    $.ajax({
        url: '<?= $urlval?>ajax/server-time-endpoint.php', 
        method: 'GET',
        success: function (response) {
      
            const currentTime = response.currentTime; 
           

            countdownIntervals[buttonId] = initializeTimer(createdAt, currentTime);
        },
        error: function (error) {
            console.error('Error fetching current time:', error);
            $('#timer').text('Error fetching time').css('color', 'red');
        },
        complete: function () {
      
            $button.prop('disabled', false);
        }
    });
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
            const inputProgress = target.closest('.form-control').querySelector('.input-progress');
            const copiedMessage = target.closest('.d-flex').querySelector('.copied-message');


            inputProgress.style.width = '100%';

            const textContent = target.textContent.trim();

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textContent).then(() => {
                    handleCopySuccess(button, inputProgress, copiedMessage);
                }).catch(() => alert('Failed to copy text. Please try again.'));
            } else {
                fallbackCopyText(textContent, button, inputProgress, copiedMessage);
            }
        });
    });

    function fallbackCopyText(text, button, inputProgress, copiedMessage) {
        const tempInput = document.createElement('textarea');
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand('copy');
            handleCopySuccess(button, inputProgress, copiedMessage);
        } catch {
            alert('Failed to copy text. Please try again.');
        }
        document.body.removeChild(tempInput);
    }

    function handleCopySuccess(button, inputProgress, copiedMessage) {
        button.innerHTML = '<i class="bi bi-check2-circle"></i>';
        inputProgress.classList.add('progress-done');

        // Show copied message with animation
        setTimeout(() => {
            copiedMessage.classList.add('active');

            setTimeout(() => {
                // Reset animations
                inputProgress.style.width = '0';
                button.innerHTML = '<i class="bi bi-files"></i>';
                inputProgress.classList.remove('progress-done');
                copiedMessage.classList.remove('active');
            }, 1200); // Reset after 1.2s
        }, 200); // Small delay for smooth visuals
    }
});



$(document).on('click', '.insufficient-status', function () {
    insufficient_popup();
});
function insufficient_popup() {
    document.getElementById("insufficient-popup").style.display = "flex";
}


function closeinsufficient_popup() {
    document.getElementById("insufficient-popup").style.display = "none";
}

function openRulesPopup() {
    document.getElementById("rules-popup1").style.display = "flex";
}


function closeRulesPopup() {
    document.getElementById("rules-popup1").style.display = "none";
}



function displayErrorPopup(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorPopup').style.display = 'flex';
    
    setTimeout(() => {
        document.getElementById('errorPopup').style.display = 'none';
    }, 5000);
    
 
        
}

function closePopupDef() {
    document.getElementById('errorPopup').style.display = 'none';
}


const rulesBtn = document.getElementById('rules-btn');
setInterval(() => {

    rulesBtn.classList.add('shake');
    setTimeout(() => {
        rulesBtn.classList.remove('shake');
    }, 500); 
}, 2000);

</script>


</body>
</html>
