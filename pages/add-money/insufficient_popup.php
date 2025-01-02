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
<div id="insufficient-popup" class="popup-modal">
    <div class="popup-content">
        <span class="close" onclick="closeinsufficient_popup()">
            <i class="fas fa-times"></i>
        </span>
        <h2>Insufficient Payment</h2>
        <p>The amount we received is less than the amount you requested to add. Please contact customer support via chat or Telegram to discuss the payment you sent. Once the issue is resolved, the money will be manually added to your account.</p>
  
    </div>
</div>