



<!-- Popup Modal for Rules -->
<div id="rules-popup" class="popup-modal">
    <div class="popup-content">
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <h2>System Rules</h2>
        <p>Here are the rules for using the system:</p>
        <ul>
            <li>The minimum deposit amount is $20.</li>
            <li>If you generate a payment, you won't be able to create a new one until the previously generated address is either used or expires—unless you cancel it first. Once canceled, you can request a new payment address.</li>
            <li>If you generate a payment transaction, you have 60 minutes to send the payment to the provided address before it becomes expired.</li>
            <li>You can send the payment anytime within the 60-minute countdown. Once the payment is detected, your transaction will no longer expire.</li>
            <li>When sending a payment, please use high-priority fees to ensure faster confirmation.</li>
            <li>We apply a 2% margin to all payments to account for BTC fluctuations.</li>
            <li>We need at least three confirmations from the blockchain for the transaction to be marked as CONFIRMED. Please be patient, as we cannot expedite the process. The confirmation time may vary depending on the blockchain network load, always use high priority fees.</li>
            <li>When sending a payment, wait at least one minute for the transaction to be detected. Do not cancel the transaction if you have already sent the payment; otherwise, you will need to contact customer support to manually update your balance.</li>
        </ul>

        <h3>Status Legend:</h3>
        <ul>
            <li><strong>CONFIRMED</strong> - Your transaction has been completed successfully, and you will see the money updated in your user balance.</li>
            <li><strong>RECEIVING</strong> - Your transaction has been detected, and we are waiting for it to complete.</li>
            <li><strong>INSUFFICIENT</strong> - In this case, you need to contact customer support via chat or telegram to discuss the payment you sent. The money will be manually added to your account after the issue is resolved.</li>
            <li><strong>EXPIRED</strong> - The transaction you requested has expired, and you will need to generate a new BTC payment.</li>
        </ul>
    </div>
</div>