<style>
#refresh-table, #rules-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-align: center;
}

#refresh-table {
    background-color: #3b5998;
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#refresh-table:hover {
    background-color: #2a437b; /* Darker shade */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
}

#rules-btn {
    background-color: #f39c12;
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#rules-btn:hover {
    background-color: #e67e22; /* Darker shade */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
}

/* Style for the button container to make buttons inline */
button-container {
    display: flex;
    gap: 20px;
    align-items: center;
}

.popup-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease-out;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
}

/* Popup Content */
.popup-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 15px;
    width: 50%; /* Set a fixed width */
    max-width: 90%; /* Allow for smaller screens */
    max-height: 80%; /* Set a maximum height */
    overflow-y: auto; /* Add vertical scroll if content exceeds the height */
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(50px);
    animation: slideUp 0.5s ease-out;
}

/* Title style */
.popup-content h2 {
    font-size: 26px;
    margin-bottom: 15px;
    color: #333;
}

/* List and text style */
.popup-content p {
    font-size: 16px;
    margin-bottom: 20px;
    color: #555;
}

.popup-content ul {
    text-align: left;
    list-style-type: disc;
    padding-left: 20px;
    margin-bottom: 20px;
    font-size: 16px;
    color: #444;
}

/* Style the button inside the popup */
.popup-content button {
    background-color: #3b5998;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    padding: 12px 25px;
    font-size: 16px;
    transition: background-color 0.3s ease;
    margin-top: 20px;
    width: 100%;
}

.popup-content button:hover {
    background-color: #2a437b;
}

/* Close button styling */
.close {
    background-color: #6c5ce7; 
    color: #fff; 
    border: none;
    font-size: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease; 
}

.close:hover {
    background-color: red; 
    transform: rotate(360deg);
}

@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

@keyframes slideUp {
    0% { transform: translateY(50px); }
    100% { transform: translateY(0); }
}
</style>



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