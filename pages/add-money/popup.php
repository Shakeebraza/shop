
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
        position: fixed;
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
</style>


<div id="modalBackdrop" class="modal-backdrop"></div>


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
        <div class="mb-4">
            <p style="font-size: 14px; margin-bottom: 8px;">Amount to pay</p>
            <div class="d-flex align-items-center">
                <input type="text" class="form-control" style="background-color: #f8f9fa; padding: 12px; border-radius: 5px; flex-grow: 1;" id="btcAmount" value="0.00123456 BTC" readonly>
                <button class="btn ms-2 copy-btn" style="background-color: #6c5ce7; color: white; width: 40px; height: 40px;" data-copy-target="#btcAmount">
                    <i class="bi bi-files"></i>
                </button>
            </div>
        </div>

        <div class="mb-4">
            <p style="font-size: 14px; margin-bottom: 8px;">Pay to this address</p>
            <div class="d-flex align-items-center">
                <input type="text" class="form-control" style="background-color: #f8f9fa; padding: 12px; border-radius: 5px; flex-grow: 1; overflow: hidden; text-overflow: ellipsis;" id="btcAddress" value="1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa" readonly>
                <button class="btn ms-2 copy-btn" style="background-color: #6c5ce7; color: white; width: 40px; height: 40px;" data-copy-target="#btcAddress">
                    <i class="bi bi-files"></i>
                </button>
            </div>
        </div>
    </div>
</div>

