
<style>
 
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3); 
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px); 
        z-index: 1040; 
        display: none; 
	
    }

  
    .payment-modal {
        display: none;
        position: sticky;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1050; 
        width: 100%;
        max-width: 500px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        text-align: center;
    }

    #closeModalBtn {
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

   
    #closeModalBtn:hover {
        background-color: red; 
        transform: rotate(360deg);
    }
    .copy-btn {
    background-color: #6c5ce7;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.copy-btn:hover {
    background-color: #28a745; 
    transform: scale(1.2);
}
.sliding-text {
    position: fixed;
    background-color: #f8f9fa;
    color: #000;
    padding: 12px;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    font-size: 14px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    transition: transform 2s ease-out, opacity 2s ease-out;
    z-index: 1000;
}

.sliding-text.animate {
    transform: translateY(-50px) translateX(100px);
    opacity: 0;
}
    /* Styling the form */
    .form-control {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            flex-grow: 1;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .copy-btn {
            background-color: #6c5ce7;
            color: white;
            width: 40px;
            height: 40px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .copy-btn:hover {
            background-color: #5a4bce;
        }


.input-progress {
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #6c5ce7 0%,rgb(255, 255, 255) 100%);
    z-index: 1;
    pointer-events: none;
    transition: width 0.6s ease-out, background-color 0.3s ease;
    border-radius: 5px;
}

.form-control {
    position: relative;
    background-color: #f1f1f1;
    padding: 8px 12px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
    min-width: 200px;
}

.form-control .text-content {
    position: relative;
    z-index: 2;
    font-weight: bold;
    color: #333;
}

/* Copied message animation */
.copied-message {
    font-size: 12px;
    color: #4caf50;
    font-weight: bold;
    margin-left: 10px;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    transform: translateY(-5px);
}

.copied-message.active {
    opacity: 1;
    transform: translateY(0);
}

/* Button styling */
.copy-btn {
    background-color: #6c5ce7;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 6px 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.copy-btn:hover {
    background-color: #4caf50;
    transform: scale(1.05);
}

.copy-btn i {
    font-size: 18px;
    vertical-align: middle;
}


</style>


<div id="modalBackdrop" class="modal-backdrop">


<div id="paymentModal" class="payment-modal">
    <div class="payment-modal-content">
<button id="closeModalBtn">
    <i class="fas fa-times"></i>
</button>

        <div class="text-end mb-3">
            <span style="border: 1px solid #6c5ce7; color: #6c5ce7; padding: 5px 15px; border-radius: 5px;" id="timer"></span>
        </div>

        <h1 class="text-center mb-4" style="font-size: 28px; font-weight: bold;">Pay For Your Order</h1>

        <div class="text-center mb-4">
            <img id="btcQRCode" src="/placeholder.svg" alt="Bitcoin QR Code" style="width: 200px; height: 200px;">
        </div>
        <div class="container mt-5">
  
        <div class="mb-4">
            <p style="font-size: 14px; margin-bottom: 8px;">Amount to pay</p>
            <div class="d-flex align-items-center">
                <div class="form-control">
                    <div class="input-progress"></div>
                    <div class="text-content" id="btcAmount"></div>
                </div>
                <button class="btn ms-2 copy-btn" data-copy-target="#btcAmount">
                    <i class="bi bi-files"></i>
                </button>
                <span class="copied-message" id="amountCopiedMessage">Copied!</span>
            </div>
        </div>

        <div class="mb-4">
            <p style="font-size: 14px; margin-bottom: 8px;">Pay to this address</p>
            <div class="d-flex align-items-center">
                <div class="form-control">
                    <div class="input-progress"></div>
                    <div class="text-content" id="btcAddress"></div>
                </div>
                <button class="btn ms-2 copy-btn" data-copy-target="#btcAddress">
                    <i class="bi bi-files"></i>
                </button>
                <span class="copied-message" id="addressCopiedMessage">Copied!</span>
            </div>
        </div>
    </div>
    </div>
</div>
</div>
